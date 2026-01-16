<?php
// Script to update invoice and receipt JPEG exports with GD fallback

echo "🔧 Updating JPEG exports with GD library fallback...\n\n";

// Read the quote template
$quoteContent = file_get_contents('api/export-quote-jpeg.php');

// Generate invoice version
$invoiceContent = str_replace(
    ["'quote'", '$quote', 'quote_', 'Quote', 'QUOTATION', 'quote-pdf-template.php'],
    ["'invoice'", '$invoice', 'invoice_', 'Invoice', 'INVOICE', 'invoice-pdf-template.php'],
    $quoteContent
);

file_put_contents('api/export-invoice-jpeg.php', $invoiceContent);
echo "✅ Updated api/export-invoice-jpeg.php\n";

// Generate receipt version (with special handling)
$receiptContent = str_replace(
    ["'quote'", '$quote_id', '$quote[', 'quote_', 'Quote', 'QUOTATION', 'quote-pdf-template.php', 'formatNaira($quote[\'grand_total\'])'],
    ["'receipt'", '$receipt_id', '$receipt[', 'receipt_', 'Receipt', 'RECEIPT', 'receipt-pdf-template.php', 'formatNaira($receipt[\'amount_paid\'])'],
    $quoteContent
);

// Update receipt-specific fields
$receiptContent = preg_replace('/Fetch line items.*?fetchAll\(\);/s', '// Receipts don\'t have line items in the same way', $receiptContent);
$receiptContent = str_replace('$line_items as $item', '[] as $item', $receiptContent); // Empty line items for receipt

file_put_contents('api/export-receipt-jpeg.php', $receiptContent);
echo "✅ Updated api/export-receipt-jpeg.php\n";

// Clean up temp files
@unlink('api/export-invoice-jpeg-gd.php');
@unlink('api/export-receipt-jpeg-gd.php');

echo "\n✅ All JPEG exports now support GD library fallback!\n";
echo "Test by exporting any document toJPEG - it will work without Imagick.\n";
?>