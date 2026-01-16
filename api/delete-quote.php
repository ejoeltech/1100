<?php
require_once '../config.php';

$quote_id = $_GET['id'] ?? null;

if (!$quote_id) {
    header('Location: ../pages/view-quotes.php?error=No quote specified');
    exit;
}

try {
    // Soft Delete (recommended)
    $stmt = $pdo->prepare("UPDATE documents SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$quote_id]);

    header('Location: ../pages/view-quotes.php?deleted=1');
    exit;

} catch (PDOException $e) {
    error_log("Delete error: " . $e->getMessage());
    header('Location: ../pages/view-quotes.php?error=Failed to delete quote');
    exit;
}
?>