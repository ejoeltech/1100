<?php
// Script to generate archived-invoices.php and archived-receipts.php from the quotes template

$quoteTemplate = file_get_contents('pages/archived-quotes.php');

// Generate archived-invoices.php
$invoiceContent = str_replace(
    ['Archived Quotes', 'archived quotes', 'Active Quotes', 'view-quotes.php', "document_type = 'quote'", 'quote(s)', "initBulkActions('quote')", 'text-gray-500'],
    ['Archived Invoices', 'archived invoices', 'Active Invoices', 'view-invoices.php', "document_type = 'invoice'", 'invoice(s)', "initBulkActions('invoice')", 'text-gray-500'],
    $quoteTemplate
);

// Add invoice-specific columns (balance_due)
$invoiceContent = str_replace(
    '<th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Title</th>',
    '<th class="px-4 py-3 text-left text-sm font-bold text-gray-700">Title</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-gray-700">Balance Due</th>',
    $invoiceContent
);

file_put_contents('pages/archived-invoices.php', $invoiceContent);
echo "✅ Created pages/archived-invoices.php\n";

// Generate archived-receipts.php
$receiptContent = str_replace(
    ['Archived Quotes', 'archived quotes', 'Active Quotes', 'view-quotes.php', "document_type = 'quote'", 'quote(s)', "initBulkActions('quote')", 'text-gray-500', 'text-gray-500'],
    ['Archived Receipts', 'archived receipts', 'Active Receipts', 'view-receipts.php', "document_type = 'receipt'", 'receipt(s)', "initBulkActions('receipt')", 'text-purple-500', 'text-purple-500'],
    $quoteTemplate
);

// Change Total to Amount Paid for receipts
$receiptContent = str_replace(
    ['<th class="px-4 py-3 text-right text-sm font-bold text-gray-700">Total</th>', 'grand_total'],
    ['<th class="px-4 py-3 text-right text-sm font-bold text-gray-700">Amount Paid</th>', 'amount_paid'],
    $receiptContent
);

file_put_contents('pages/archived-receipts.php', $receiptContent);
echo "✅ Created pages/archived-receipts.php\n";

echo "\n✅ All archive pages created!\n";
echo "Next: Update header.php to add Archives menu\n";
?>