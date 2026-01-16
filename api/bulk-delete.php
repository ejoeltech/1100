<?php
include '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $document_type = $input['document_type'] ?? '';
    $ids = $input['ids'] ?? [];

    // Validate input
    if (!in_array($document_type, ['quote', 'invoice', 'receipt'])) {
        throw new Exception('Invalid document type');
    }

    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No items selected');
    }

    // Sanitize IDs
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function ($id) {
        return $id > 0; });

    if (empty($ids)) {
        throw new Exception('Invalid item IDs');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Soft delete: set deleted_at timestamp
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        UPDATE documents 
        SET deleted_at = NOW() 
        WHERE id IN ($placeholders) 
        AND document_type = ? 
        AND deleted_at IS NULL
    ");

    $params = array_merge($ids, [$document_type]);
    $stmt->execute($params);

    $deleted_count = $stmt->rowCount();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'deleted_count' => $deleted_count,
        'message' => "Successfully deleted $deleted_count items"
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Bulk delete error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>