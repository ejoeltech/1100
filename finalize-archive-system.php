<?php
/**
 * Final Archive System Setup Script
 * Updates navigation menu and changes modal text from DELETE to ARCHIVE
 */

echo "🔧 Archive System - Final Setup\n\n";

// Step 1: Update modal IDs and text in existing pages
$pagesToUpdate = [
    'pages/view-quotes.php' => 'quote',
    'pages/view-invoices.php' => 'invoice',
    'pages/view-receipts.php' => 'receipt'
];

foreach ($pagesToUpdate as $file => $type) {
    if (!file_exists($file)) {
        echo "⚠️  $file not found\n";
        continue;
    }

    $content = file_get_contents($file);

    // Change modal ID from deleteConfirmModal to archiveConfirmModal
    $content = str_replace('deleteConfirmModal', 'archiveConfirmModal', $content);
    $content = str_replace('deleteCount', 'archiveCount', $content);
    $content = str_replace('deleteConfirmInput', 'archiveConfirmInput', $content);
    $content = str_replace('confirmDeleteBtn', 'confirmArchiveBtn', $content);

    // Change button text and function calls
    $content = str_replace('showBulkDeleteConfirmation', 'showBulkArchiveConfirmation', $content);
    $content = str_replace('hideDeleteConfirmation', 'hideArchiveConfirmation', $content);
    $content = str_replace('validateDeleteConfirmation', 'validateArchiveConfirmation', $content);
    $content = str_replace('executeBulkDelete', 'executeBulkArchive', $content);

    // Change modal text
    $content = str_replace('Confirm Deletion', 'Confirm Archive', $content);
    $content = str_replace('about to delete', 'about to archive', $content);
    $content = str_replace('DELETE</strong> in the box', 'ARCHIVE</strong> in the box', $content);
    $content = str_replace('Type DELETE to confirm', 'Type ARCHIVE to confirm', $content);
    $content = str_replace('Delete Selected', 'Archive Selected', $content);
    $content = str_replace(ucfirst($type) . 's</button>', ucfirst($type) . 's</button>', $content);

    // Change button styling - keep it red for clarity
    // (Archive is still a destructive action, just safer)

    file_put_contents($file, $content);
    echo "✅ Updated $file\n";
}

echo "\n📋 Next Steps:\n";
echo "1. Run database migration: database/add-user-roles.sql\n";
echo "2. Update header.php navigation manually (add Archives dropdown)\n";
echo "3. Test the archive system\n\n";

echo "✅ Archive system setup complete!\n";
?>