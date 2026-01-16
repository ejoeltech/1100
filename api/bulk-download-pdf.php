<?php
include '../includes/session-check.php';
require_once '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

try {
    $document_type = $_POST['document_type'] ?? '';
    $ids = json_decode($_POST['ids'] ?? '[]', true);

    // Validate input
    if (!in_array($document_type, ['quote', 'invoice', 'receipt'])) {
        throw new Exception('Invalid document type');
    }

    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No items selected');
    }

    // Sanitize IDs
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function ($id) {
        return $id > 0; });

    if (empty($ids)) {
        throw new Exception('Invalid item IDs');
    }

    // Temporary directory for PDFs
    $tempDir = sys_get_temp_dir() . '/bluedots_bulk_' . uniqid();
    mkdir($tempDir);

    $pdfFiles = [];

    // Generate PDF for each document
    foreach ($ids as $id) {
        try {
            // Fetch document
            $stmt = $pdo->prepare("
                SELECT * FROM documents 
                WHERE id = ? AND document_type = ? AND deleted_at IS NULL
            ");
            $stmt->execute([$id, $document_type]);
            $document = $stmt->fetch();

            if (!$document)
                continue;

            // Fetch line items
            $stmt = $pdo->prepare("
                SELECT * FROM line_items WHERE document_id = ? ORDER BY item_number
            ");
            $stmt->execute([$id]);
            $line_items = $document['line_items'] = $stmt->fetchAll();

            // Fetch parent documents for breadcrumb (if applicable)
            $parent_invoice = null;
            $parent_quote = null;

            if ($document_type === 'receipt' && $document['parent_document_id']) {
                $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
                $stmt->execute([$document['parent_document_id']]);
                $parent_invoice = $stmt->fetch();

                if ($parent_invoice && $parent_invoice['parent_document_id']) {
                    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
                    $stmt->execute([$parent_invoice['parent_document_id']]);
                    $parent_quote = $stmt->fetch();
                }
            }

            // Generate HTML based on document type
            ob_start();
            if ($document_type === 'quote') {
                $quote = $document;
                include '../includes/quote-pdf-template.php';
            } elseif ($document_type === 'invoice') {
                $invoice = $document;
                include '../includes/invoice-pdf-template.php';
            } else { // receipt
                $receipt = $document;
                include '../includes/receipt-pdf-template.php';
            }
            $html = ob_get_clean();

            // Create PDF
            $mpdf = new \Mpdf\Mpdf([
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 15
            ]);

            $mpdf->WriteHTML($html);

            // Save PDF to temp directory
            $filename = $document['document_number'] . '.pdf';
            $filepath = $tempDir . '/' . $filename;
            $mpdf->Output($filepath, 'F');

            $pdfFiles[] = $filepath;

        } catch (Exception $e) {
            error_log("Error generating PDF for $document_type ID $id: " . $e->getMessage());
            continue;
        }
    }

    if (empty($pdfFiles)) {
        throw new Exception('No PDFs could be generated');
    }

    // Create ZIP file
    $zipFilename = $document_type . 's_' . date('Y-m-d_H-i-s') . '.zip';
    $zipPath = $tempDir . '/' . $zipFilename;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
        throw new Exception('Could not create ZIP file');
    }

    foreach ($pdfFiles as $file) {
        $zip->addFile($file, basename($file));
    }

    $zip->close();

    // Send ZIP file to browser
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);

    // Clean up temp files
    foreach ($pdfFiles as $file) {
        @unlink($file);
    }
    @unlink($zipPath);
    @rmdir($tempDir);

    exit;

} catch (Exception $e) {
    error_log("Bulk download error: " . $e->getMessage());
    die('Error: ' . $e->getMessage());
}
?>