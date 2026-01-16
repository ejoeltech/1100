<?php
/**
 * Audit Logging Addition to config.php
 * Add this function before the closing ?> tag
 */

// ============================================
// Audit Logging Function
// ============================================

function logAudit($action, $resourceType = null, $resourceId = null, $details = [])
{
    global $pdo;

    try {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $pdo->prepare("
            INSERT INTO audit_log (user_id, action, resource_type, resource_id, ip_address, user_agent, details)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $action,
            $resourceType,
            $resourceId,
            $ipAddress,
            $userAgent,
            json_encode($details)
        ]);
    } catch (Exception $e) {
        // Log error but don't break application
        error_log("Audit log error: " . $e->getMessage());
    }
}
?>