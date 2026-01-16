<?php
// Generate password hash for admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Generated hash for 'admin123':\n";
echo $hash . "\n\n";

// Test it
if (password_verify($password, $hash)) {
    echo "✓ Hash verified successfully!\n";
} else {
    echo "✗ Hash verification failed!\n";
}

// Update database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=bluedots_quotes', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);

    echo "✓ Database updated successfully!\n";
    echo "\nLogin credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
