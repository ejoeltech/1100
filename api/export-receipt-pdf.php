<?php
include '../includes/session-check.php';
require_once '../vendor/autoload.php';

$receipt_id = $_GET['id'] ?? null;

if (!$receipt_id) {
    die('No receipt specified');
}

try {
    // Fetch receipt
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND document_type = 'receipt' AND deleted_at IS NULL");
    $stmt->execute([$receipt_id]);
    $receipt = $stmt->fetch();

    if (!$receipt) {
        die('Receipt not found');
    }

    // Fetch parent invoice
    $parent_invoice = null;
    if ($receipt['parent_document_id']) {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
        $stmt->execute([$receipt['parent_document_id']]);
        $parent_invoice = $stmt->fetch();
    }

    // Generate HTML
    $html = include '../includes/receipt-pdf-template.php';

    // Create PDF
    $mpdf = new \Mpdf\Mpdf([
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);

    $mpdf->WriteHTML($html);

    // Output
    $filename = $receipt['document_number'] . '_' . date('Ymd') . '.pdf';
    $mpdf->Output($filename, 'D');

} catch (Exception $e) {
    error_log("Receipt PDF export error: " . $e->getMessage());
    die('Failed to generate PDF');
}
?>