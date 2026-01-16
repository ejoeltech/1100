<?php
/**
 * Reset Test User Passwords
 * Run this once to set passwords for test users
 */

require_once 'config.php';

// Hash for password "admin123"
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);

try {
    // Update manager1 password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'manager1'");
    $stmt->execute([$password_hash]);
    echo "✓ Updated manager1 password to: admin123\n";

    // Update salesrep1 password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'salesrep1'");
    $stmt->execute([$password_hash]);
    echo "✓ Updated salesrep1 password to: admin123\n";

    // Update salesrep2 password  
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'salesrep2'");
    $stmt->execute([$password_hash]);
    echo "✓ Updated salesrep2 password to: admin123\n";

    echo "\n✅ All test user passwords reset successfully!\n";
    echo "\nYou can now login with:\n";
    echo "- manager1 / admin123\n";
    echo "- salesrep1 / admin123\n";
    echo "- salesrep2 / admin123\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>