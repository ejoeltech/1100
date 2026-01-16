<?php
include '../includes/session-check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Check if user is admin or super_admin
    $user_role = $current_user['user_role'] ?? 'user';

    if (!in_array($user_role, ['admin', 'super_admin'])) {
        throw new Exception('Permission denied. Only admins can permanently delete documents.');
    }

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

    // First, delete associated line items
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        DELETE FROM line_items 
        WHERE document_id IN ($placeholders)
    ");
    $stmt->execute($ids);

    // Then permanently delete the documents
    $stmt = $pdo->prepare("
        DELETE FROM documents 
        WHERE id IN ($placeholders) 
        AND document_type = ?
        AND deleted_at IS NOT NULL
    ");

    $params = array_merge($ids, [$document_type]);
    $stmt->execute($params);

    $deleted_count = $stmt->rowCount();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'deleted_count' => $deleted_count,
        'message' => "Successfully deleted $deleted_count items permanently"
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Permanent delete error: " . $e->getMessage());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>