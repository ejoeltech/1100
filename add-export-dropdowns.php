<?php
/**
 * Script to add Export dropdown to invoice and receipt view pages
 */

echo "🔧 Adding Export Dropdown to Invoice and Receipt pages...\n\n";

$exportDropdown = <<<'EOD'

    <!-- Export Dropdown Button -->
    <div class="relative group">
        <button class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            Export
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div class="hidden group-hover:block absolute top-full left-0 mt-1 bg-white shadow-lg rounded-lg py-2 min-w-[180px] z-50">
            <a href="../api/EXPORT_PDF_URL" 
               target="_blank"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Export as PDF
            </a>
            <a href="../api/EXPORT_JPEG_URL" 
               target="_blank"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Export as JPEG
            </a>
        </div>
    </div>
EOD;

// Update view-invoice.php
$file = 'pages/view-invoice.php';
$content = file_get_contents($file);

// Replace PDF download button with dropdown
$invoiceDropdown = str_replace(
    ['EXPORT_PDF_URL', 'EXPORT_JPEG_URL'],
    ['export-invoice-pdf.php?id=<?php echo $invoice[\'id\']; ?>', 'export-invoice-jpeg.php?id=<?php echo $invoice[\'id\']; ?>'],
    $exportDropdown
);

// Find and replace the PDF export button section
$pattern = '/<!-- PDF Export Button -->.*?Download PDF\s*<\/a>/s';
$content = preg_replace($pattern, $invoiceDropdown, $content);

file_put_contents($file, $content);
echo "✅ Updated $file\n";

// Update view-receipt.php
$file = 'pages/view-receipt.php';
$content = file_get_contents($file);

// Replace both PDF and JPEG buttons with single dropdown
$receiptDropdown = str_replace(
    ['EXPORT_PDF_URL', 'EXPORT_JPEG_URL'],
    ['export-receipt-pdf.php?id=<?php echo $receipt[\'id\']; ?>', 'export-receipt-jpeg.php?id=<?php echo $receipt[\'id\']; ?>'],
    $exportDropdown
);

// Find and replace PDF download button + JPEG button (they're separate now)
// First remove the JPEG button if it exists
$content = preg_replace('/<a href="\.\.\/api\/export-receipt-jpeg\.php\?.*?<\/a>\s*/s', '', $content);

// Then replace the PDF button with dropdown
$pattern = '/<!-- PDF Export Button -->.*?Download PDF\s*<\/a>/s';
if (!preg_match($pattern, $content)) {
    // Try alternate pattern
    $pattern = '/<a href="\.\.\/api\/export-receipt-pdf\.php.*?Download PDF\s*<\/a>/s';
}
$content = preg_replace($pattern, $receiptDropdown, $content);

file_put_contents($file, $content);
echo "✅ Updated $file\n";

echo "\n✅ All export dropdowns added!\n";
echo "Refresh the pages to see the Export dropdown with PDF and JPEG options.\n";
?>