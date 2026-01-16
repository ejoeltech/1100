<?php
include '../includes/session-check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $document_type = $input['document_type'] ?? '';
    $ids = $input['ids'] ?? [];

    if (!in_array($document_type, ['quote', 'invoice', 'receipt'])) {
        throw new Exception('Invalid document type');
    }

    if (empty($ids) || !is_array($ids)) {
        throw new Exception('No items selected');
    }

    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function ($id) {
        return $id > 0; });

    if (empty($ids)) {
        throw new Exception('Invalid item IDs');
    }

    $pdo->beginTransaction();

    // Restore: clear deleted_at timestamp
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        UPDATE documents 
        SET deleted_at = NULL,
            updated_at = NOW()
        WHERE id IN ($placeholders) 
        AND document_type = ? 
        AND deleted_at IS NOT NULL
    ");

    $params = array_merge($ids, [$document_type]);
    $stmt->execute($params);

    $restored_count = $stmt->rowCount();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'restored_count' => $restored_count,
        'message' => "Successfully restored $restored_count items"
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Bulk restore error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>