<?php
// Run bank accounts table migration
require_once 'config.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database/create_bank_accounts_table.sql');
    $pdo->exec($sql);
    echo "✅ Bank accounts table created successfully!\n";
    echo "✅ Existing bank accounts migrated.\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>