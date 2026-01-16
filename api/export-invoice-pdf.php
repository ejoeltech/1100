<?php
require_once '../config.php';
require_once '../vendor/autoload.php';

$invoice_id = $_GET['id'] ?? null;

if (!$invoice_id) {
    die('Invoice ID required');
}

// Fetch invoice
$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND document_type = 'invoice' AND deleted_at IS NULL");
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die('Invoice not found');
}

// Fetch line items
$stmt = $pdo->prepare("SELECT * FROM line_items WHERE document_id = ? ORDER BY item_number");
$stmt->execute([$invoice_id]);
$line_items = $stmt->fetchAll();

// Generate PDF using mPDF
try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15,
        'margin_header' => 10,
        'margin_footer' => 10
    ]);

    // Include the PDF template
    $html = include '../includes/invoice-pdf-template.php';

    $mpdf->WriteHTML($html);

    // Output PDF
    $filename = 'Invoice_' . $invoice['document_number'] . '.pdf';
    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);

} catch (\Mpdf\MpdfException $e) {
    error_log("PDF Generation Error: " . $e->getMessage());
    die('Error generating PDF: ' . $e->getMessage());
}
