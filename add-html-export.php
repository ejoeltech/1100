<?php
// Script to create HTML exports for invoices and receipts, and update dropdown menus

echo "🔧 Adding HTML export functionality...\n\n";

// Generate invoice HTML export
$quoteContent = file_get_contents('api/export-quote-html.php');
$invoiceContent = str_replace(
    ["'quote'", '$quote', 'Quote', 'QUOTATION', 'quotation'],
    ["'invoice'", '$invoice', 'Invoice', 'INVOICE', 'invoice'],
    $quoteContent
);
file_put_contents('api/export-invoice-html.php', $invoiceContent);
echo "✅ Created api/export-invoice-html.php\n";

// Generate receipt HTML export (simpler, no line items)
$receiptContent = str_replace(
    ["'quote'", '$quote', 'Quote', 'QUOTATION', 'quotation'],
    ["'receipt'", '$receipt', 'Receipt', 'RECEIPT', 'receipt'],
    $quoteContent
);
file_put_contents('api/export-receipt-html.php', $receiptContent);
echo "✅ Created api/export-receipt-html.php\n";

// Now update the dropdowns in view pages
$files = [
    'pages/view-quote.php' => ['quote', 'export-quote-html.php'],
    'pages/view-invoice.php' => ['invoice', 'export-invoice-html.php'],
    'pages/view-receipt.php' => ['receipt', 'export-receipt-html.php']
];

foreach ($files as $file => list($type, $htmlExport)) {
    $content = file_get_contents($file);

    // Find the Export dropdown and add HTML option before the closing </div>
    $htmlOption = '            <a href="../api/' . $htmlExport . '?id=<?php echo $' . $type . '[\'id\']; ?>" 
               target="_blank"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                Export as HTML
            </a>';

    // Insert HTML option before the closing </div> of dropdown
    $content = preg_replace(
        '/(Export as JPEG\s*<\/a>\s*)(\s*<\/div>)/s',
        '$1' . "\n" . $htmlOption . "\n" . '$2',
        $content
    );

    file_put_contents($file, $content);
    echo "✅ Updated $file with HTML export option\n";
}

echo "\n✅ HTML export added to all document types!\n";
echo "Refresh pages to see: Export → Export as HTML option\n";
?>