<?php
require_once '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

try {
    $pdo->beginTransaction();

    // Extract form data
    $quote_id = intval($_POST['quote_id']);
    $quote_title = trim($_POST['quote_title']);
    $customer_name = trim($_POST['customer_name']);
    $salesperson = trim($_POST['salesperson']);
    $quote_date = $_POST['quote_date'];
    $payment_terms = trim($_POST['payment_terms']);
    $subtotal = floatval($_POST['subtotal']);
    $total_vat = floatval($_POST['total_vat']);
    $grand_total = floatval($_POST['grand_total']);
    $status = $_POST['status'];
    $line_items = $_POST['line_items'];

    // Fetch existing quote
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$quote_id]);
    $quote = $stmt->fetch();

    if (!$quote) {
        throw new Exception('Quote not found');
    }

    // Phase 4: Check if finalized - only admins can edit
    $is_finalized = $quote['status'] === 'finalized';
    if ($is_finalized) {
        if (!function_exists('hasPermission') || !hasPermission('edit_finalized')) {
            throw new Exception('Only administrators can edit finalized quotes');
        }
    }

    // Validate required fields
    if (empty($quote_title) || empty($customer_name) || empty($salesperson)) {
        throw new Exception('Required fields are missing');
    }

    if (empty($line_items) || !is_array($line_items)) {
        throw new Exception('No line items provided');
    }

    // Build UPDATE query with conditional edit tracking
    $update_sql = "
        UPDATE documents SET
            quote_title = ?,
            customer_name = ?,
            salesperson = ?,
            quote_date = ?,
            subtotal = ?,
            total_vat = ?,
            grand_total = ?,
            payment_terms = ?,
            status = ?,
            updated_at = NOW()";

    $params = [
        $quote_title,
        $customer_name,
        $salesperson,
        $quote_date,
        $subtotal,
        $total_vat,
        $grand_total,
        $payment_terms,
        $status
    ];

    // Phase 4: Track edit if finalized
    if ($is_finalized && isset($_SESSION['user_id'])) {
        $update_sql .= ", last_edited_by = ?, last_edited_at = NOW()";
        $params[] = $_SESSION['user_id'];
    }

    $update_sql .= " WHERE id = ?";
    $params[] = $quote_id;

    $stmt = $pdo->prepare($update_sql);
    $stmt->execute($params);

    // Delete existing line items
    $stmt = $pdo->prepare("DELETE FROM line_items WHERE document_id = ?");
    $stmt->execute([$quote_id]);

    // Insert new line items
    $stmt = $pdo->prepare("
        INSERT INTO line_items (
            document_id, item_number, quantity, description,
            unit_price, vat_applicable, vat_amount, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $item_number = 1;
    foreach ($line_items as $item) {
        $quantity = floatval($item['quantity']);
        $description = trim($item['description']);
        $unit_price = floatval($item['unit_price']);
        $vat_applicable = isset($item['vat_applicable']) ? 1 : 0;
        $vat_amount = floatval($item['vat_amount']);
        $line_total = floatval($item['line_total']);

        if (empty($description) || $quantity <= 0 || $unit_price < 0) {
            throw new Exception("Invalid line item data");
        }

        $stmt->execute([
            $quote_id,
            $item_number,
            $quantity,
            $description,
            $unit_price,
            $vat_applicable,
            $vat_amount,
            $line_total
        ]);

        $item_number++;
    }

    // Phase 4: Log audit trail if finalized was edited
    if ($is_finalized && function_exists('logDocumentEdit')) {
        logDocumentEdit('quote', $quote_id, $quote['document_number'], [
            'edited_by' => $_SESSION['full_name'] ?? 'Unknown',
            'status' => 'finalized',
            'action' => 'edited_finalized_quote'
        ]);
    }

    $pdo->commit();

    header("Location: ../pages/view-quote.php?id=" . $quote_id . "&updated=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Quote update error: " . $e->getMessage());
    $redirect_id = isset($quote_id) ? $quote_id : '';
    header("Location: ../pages/edit-quote.php?id=" . $redirect_id . "&error=" . urlencode($e->getMessage()));
    exit;
}
?>