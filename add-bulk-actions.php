<?php
// Helper script to add bulk actions HTML to invoices and receipts pages
// Run this once to update the pages

$bulkActionBar = <<<'EOD'

<!-- Bulk Actions Bar -->
<div id="bulkActionBar" class="hidden fixed bottom-0 left-0 right-0 bg-white border-t-2 border-gray-300 shadow-lg z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span class="font-semibold text-gray-900"><span id="selectedCount">0</span> item(s) selected</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="deselectAll()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg font-semibold">Deselect All</button>
            <button id="bulkDownloadBtn" onclick="bulkDownloadPDFs()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>Download PDFs</button>
            <button onclick="showBulkDeleteConfirmation()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold flex items-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>Delete Selected</button>
        </div>
    </div>
</div>

<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-md w-full mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-100 rounded-full p-3"><svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            <h3 class="text-xl font-bold text-gray-900">Confirm Deletion</h3>
        </div>
        <p class="text-gray-700 mb-4">You are about to delete <strong id="deleteCount">0</strong> DOCUMENT_TYPE_PLACEHOLDER(s). This action cannot be undone.</p>
        <p class="text-sm text-gray-600 mb-4">To confirm, please type <strong class="text-red-600">DELETE</strong> in the box below:</p>
        <input type="text" id="deleteConfirmInput" onkeyup="validateDeleteConfirmation()" placeholder="Type DELETE to confirm" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-6">
        <div class="flex gap-3 justify-end">
            <button onclick="hideDeleteConfirmation()" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">Cancel</button>
            <button id="confirmDeleteBtn" onclick="executeBulkDelete()" disabled class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold disabled:opacity-50 disabled:cursor-not-allowed">Delete DOCUMENT_TYPE_PLACEHOLDER</button>
        </div>
    </div>
</div>

<script src="../assets/js/bulk-actions.js"></script>
<script>initBulkActions('INIT_TYPE_PLACEHOLDER');</script>

EOD;

// Update invoices page
$invoicesFile = 'pages/view-invoices.php';
$content = file_get_contents($invoicesFile);
if (strpos($content, 'bulkActionBar') === false) {
    $invoicesBar = str_replace(['DOCUMENT_TYPE_PLACEHOLDER', 'INIT_TYPE_PLACEHOLDER'], ['invoice', 'invoice'], $bulkActionBar);
    $content = str_replace("<?php include '../includes/footer.php'; ?>", $invoicesBar . "\n<?php include '../includes/footer.php'; ?>", $content);
    file_put_contents($invoicesFile, $content);
    echo "✅ Updated $invoicesFile\n";
} else {
    echo "ℹ️  $invoicesFile already has bulk actions\n";
}

// Update receipts page
$receiptsFile = 'pages/view-receipts.php';
$content = file_get_contents($receiptsFile);
if (strpos($content, 'bulkActionBar') === false) {
    $receiptsBar = str_replace(['DOCUMENT_TYPE_PLACEHOLDER', 'INIT_TYPE_PLACEHOLDER'], ['receipt', 'receipt'], $bulkActionBar);
    $content = str_replace("<?php include '../includes/footer.php'; ?>", $receiptsBar . "\n<?php include '../includes/footer.php'; ?>", $content);
    file_put_contents($receiptsFile, $content);
    echo "✅ Updated $receiptsFile\n";
} else {
    echo "ℹ️  $receiptsFile already has bulk actions\n";
}

echo "\n✅ All pages updated! Delete this file after running.\n";
?>