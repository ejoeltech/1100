<?php
session_start();
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../includes/permissions.php';
require_once '../includes/audit.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

requirePermission('delete_user');

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header('Location: ../pages/users/manage-users.php');
    exit;
}

// Cannot delete yourself
if ($user_id == $_SESSION['user_id']) {
    header('Location: ../pages/users/manage-users.php?error=Cannot delete your own account');
    exit;
}

try {
    // Get user details for audit log
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Log audit trail
    logUserDelete($user_id, $user['username']);

    header('Location: ../pages/users/manage-users.php?deleted=1');
    exit;

} catch (Exception $e) {
    $error = urlencode($e->getMessage());
    header("Location: ../pages/users/manage-users.php?error=$error");
    exit;
}
?>