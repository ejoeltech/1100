<?php
/**
 * Create Test Users (Pre-Migration)
 * Creates test users without role column
 */

require_once 'config.php';

// Password hash for "admin123"
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);

try {
    // Check if role column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $hasRoleColumn = $stmt->fetch() !== false;

    if ($hasRoleColumn) {
        echo "✓ Role column exists - migration has been run\n";
        echo "Creating users with roles...\n\n";

        // Create manager1
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, full_name, email, phone, role, is_active) 
                               VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute(['manager1', $password_hash, 'John Manager', 'manager@bluedots.com.ng', '08012345678', 'manager']);
        echo "✓ Created manager1 (role: manager)\n";

        // Create salesrep1
        $stmt->execute(['salesrep1', $password_hash, 'Jane Sales', 'sales@bluedots.com.ng', '08087654321', 'sales_rep']);
        echo "✓ Created salesrep1 (role: sales_rep)\n";

        // Create salesrep2
        $stmt->execute(['salesrep2', $password_hash, 'Mike Sales', 'mike@bluedots.com.ng', '08098765432', 'sales_rep']);
        echo "✓ Created salesrep2 (role: sales_rep)\n";

    } else {
        echo "⚠️ Role column doesn't exist - migration not run yet\n";
        echo "Creating basic users without roles...\n\n";

        // Create manager1 (without role)
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password_hash, full_name, email) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute(['manager1', $password_hash, 'John Manager', 'manager@bluedots.com.ng']);
        echo "✓ Created manager1\n";

        // Create salesrep1
        $stmt->execute(['salesrep1', $password_hash, 'Jane Sales', 'sales@bluedots.com.ng']);
        echo "✓ Created salesrep1\n";

        // Create salesrep2
        $stmt->execute(['salesrep2', $password_hash, 'Mike Sales', 'mike@bluedots.com.ng']);
        echo "✓ Created salesrep2\n";
    }

    echo "\n✅ Test users created successfully!\n";
    echo "\nLogin credentials:\n";
    echo "- manager1 / admin123\n";
    echo "- salesrep1 / admin123\n";
    echo "- salesrep2 / admin123\n";

    if (!$hasRoleColumn) {
        echo "\n⚠️ Note: All users have admin privileges until migration is run\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>