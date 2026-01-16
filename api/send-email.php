<?php
session_start();
require_once '../config.php';
require_once '../includes/simple-mailer.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $documentType = $_POST['document_type'] ?? '';
    $documentId = intval($_POST['document_id'] ?? 0);
    $recipientEmail = trim($_POST['recipient_email'] ?? '');
    $recipientName = trim($_POST['recipient_name'] ?? '');
    $customMessage = trim($_POST['custom_message'] ?? '');

    // Validate inputs
    if (!in_array($documentType, ['quote', 'invoice', 'receipt'])) {
        throw new Exception('Invalid document type');
    }

    if (!$documentId) {
        throw new Exception('Document ID required');
    }

    if (!$recipientEmail) {
        throw new Exception('Recipient email required');
    }

    // Verify document exists and user has permission to view it
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND document_type = ? AND deleted_at IS NULL");
    $stmt->execute([$documentId, $documentType]);
    $document = $stmt->fetch();

    if (!$document) {
        throw new Exception('Document not found');
    }

    // Check permission (user must be able to view document to email it)
    if (function_exists('canViewDocument') && !canViewDocument($document)) {
        throw new Exception('You do not have permission to email this document');
    }

    // Send email
    $result = sendDocumentEmail(
        $documentType,
        $documentId,
        $recipientEmail,
        $recipientName,
        $customMessage
    );

    header('Content-Type: application/json');
    echo json_encode($result);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>