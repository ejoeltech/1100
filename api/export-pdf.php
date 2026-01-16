<?php
require_once '../config.php';

$quote_id = $_GET['id'] ?? null;

if (!$quote_id) {
    die('No quote specified');
}

// Check if vendor/autoload.php exists (mPDF installed)
if (!file_exists('../vendor/autoload.php')) {
    die('PDF export requires mPDF library. Please run "composer install" or see PHASE1-SETUP.md for installation instructions.');
}

require_once '../vendor/autoload.php';

try {
    // Fetch quote
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$quote_id]);
    $quote = $stmt->fetch();

    if (!$quote) {
        die('Quote not found');
    }

    // Fetch line items
    $stmt = $pdo->prepare("SELECT * FROM line_items WHERE document_id = ? ORDER BY item_number");
    $stmt->execute([$quote_id]);
    $line_items = $stmt->fetchAll();

    // Generate HTML for PDF
    ob_start();
    include '../includes/pdf-template.php';
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

    // Output PDF
    $filename = $quote['document_number'] . '_' . date('Ymd') . '.pdf';
    $mpdf->Output($filename, 'D'); // 'D' = Download

} catch (Exception $e) {
    error_log("PDF export error: " . $e->getMessage());
    die('Failed to generate PDF: ' . $e->getMessage());
}
?>