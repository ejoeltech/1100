<?php
include '../includes/session-check.php';
requirePermission('manage_quotes');

if (!isset($_GET['id'])) {
    header('Location: ../pages/readymade-quotes.php?error=Template not specified');
    exit;
}

$template_id = intval($_GET['id']);

try {
    // Get original template
    $stmt = $pdo->prepare("SELECT * FROM quote_templates WHERE id = ?");
    $stmt->execute([$template_id]);
    $template = $stmt->fetch();

    if (!$template) {
        throw new Exception('Template not found');
    }

    // Get template items
    $stmt = $pdo->prepare("SELECT * FROM quote_template_items WHERE template_id = ? ORDER BY item_number");
    $stmt->execute([$template_id]);
    $items = $stmt->fetchAll();

    // Create duplicate template
    $new_name = $template['template_name'] . ' (Copy)';
    $stmt = $pdo->prepare("
        INSERT INTO quote_templates (template_name, template_description, payment_terms, estimated_total, created_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $new_name,
        $template['template_description'],
        $template['payment_terms'],
        $template['estimated_total'],
        $_SESSION['user_id']
    ]);

    $new_template_id = $pdo->lastInsertId();

    // Copy all items
    $stmt = $pdo->prepare("
        INSERT INTO quote_template_items (template_id, item_number, quantity, description, unit_price, vat_applicable, vat_amount, line_total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $stmt->execute([
            $new_template_id,
            $item['item_number'],
            $item['quantity'],
            $item['description'],
            $item['unit_price'],
            $item['vat_applicable'],
            $item['vat_amount'],
            $item['line_total']
        ]);
    }

    // Log audit
    if (function_exists('logAudit')) {
        logAudit('duplicate', 'quote_template', $new_template_id, [
            'original_id' => $template_id,
            'new_name' => $new_name
        ]);
    }

    header('Location: ../pages/readymade-quotes.php?duplicated=1');
    exit;

} catch (Exception $e) {
    error_log("Duplicate template error: " . $e->getMessage());
    header('Location: ../pages/readymade-quotes.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>