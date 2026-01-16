<?php
session_start();
require_once '../config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/view-invoices.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Get form data
    $invoice_id = $_POST['invoice_id'] ?? null;
    $quote_title = $_POST['quote_title'] ?? '';
    $customer_name = $_POST['customer_name'] ?? '';
    $salesperson = $_POST['salesperson'] ?? '';
    $quote_date = $_POST['quote_date'] ?? date('Y-m-d');
    $subtotal = $_POST['subtotal'] ?? 0;
    $total_vat = $_POST['total_vat'] ?? 0;
    $grand_total = $_POST['grand_total'] ?? 0;
    $amount_paid = $_POST['amount_paid'] ?? 0;
    $balance_due = $_POST['balance_due'] ?? 0;
    $payment_terms = $_POST['payment_terms'] ?? DEFAULT_PAYMENT_TERMS;
    $status = $_POST['status'] ?? 'draft';

    if (!$invoice_id) {
        throw new Exception('Invoice ID required');
    }

    // Verify invoice exists
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND document_type = 'invoice' AND deleted_at IS NULL");
    $stmt->execute([$invoice_id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        throw new Exception('Invoice not found');
    }

    // Phase 4: Check if invoice has receipts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE parent_document_id = ? AND document_type = 'receipt' AND deleted_at IS NULL");
    $stmt->execute([$invoice_id]);
    $has_receipts = $stmt->fetchColumn() > 0;

    if ($has_receipts) {
        throw new Exception('Cannot edit invoice - receipts have been generated for this invoice');
    }

    // Phase 4: Check if finalized - only admins can edit
    $is_finalized = $invoice['status'] === 'finalized';
    if ($is_finalized) {
        if (!function_exists('hasPermission') || !hasPermission('edit_finalized')) {
            throw new Exception('Only administrators can edit finalized invoices');
        }
    }

    // Validate required fields
    if (empty($quote_title) || empty($customer_name)) {
        throw new Exception('Required fields missing');
    }

    // Build UPDATE with edit tracking
    $update_sql = "
        UPDATE documents SET
            quote_title = ?,
            customer_name = ?,
            salesperson = ?,
            quote_date = ?,
            subtotal = ?,
            total_vat = ?,
            grand_total = ?,
            amount_paid = ?,
            balance_due = ?,
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
        $amount_paid,
        $balance_due,
        $payment_terms,
        $status
    ];

    // Phase 4: Track edit if finalized
    if ($is_finalized && isset($_SESSION['user_id'])) {
        $update_sql .= ", last_edited_by = ?, last_edited_at = NOW()";
        $params[] = $_SESSION['user_id'];
    }

    $update_sql .= " WHERE id = ?";
    $params[] = $invoice_id;

    $stmt = $pdo->prepare($update_sql);
    $stmt->execute($params);

    // Handle line items
    $line_items = $_POST['line_items'] ?? [];

    if (!empty($line_items)) {
        // Delete existing line items
        $stmt = $pdo->prepare("DELETE FROM line_items WHERE document_id = ?");
        $stmt->execute([$invoice_id]);

        // Insert new line items
        $stmt = $pdo->prepare("
            INSERT INTO line_items (document_id, item_number, quantity, description, unit_price, vat_applicable, vat_amount, line_total)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $item_number = 1;
        foreach ($line_items as $item) {
            $stmt->execute([
                $invoice_id,
                $item_number++,
                $item['quantity'],
                $item['description'],
                $item['unit_price'],
                isset($item['vat_applicable']) ? 1 : 0,
                $item['vat_amount'] ?? 0,
                $item['line_total']
            ]);
        }
    }

    // Add customer if not exists
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE name = ?");
    $stmt->execute([$customer_name]);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO customers (name) VALUES (?)");
        $stmt->execute([$customer_name]);
    }

    // Phase 4: Log audit trail if finalized was edited
    if ($is_finalized && function_exists('logDocumentEdit')) {
        logDocumentEdit('invoice', $invoice_id, $invoice['document_number'], [
            'edited_by' => $_SESSION['full_name'] ?? 'Unknown',
            'status' => 'finalized',
            'action' => 'edited_finalized_invoice'
        ]);
    }

    $pdo->commit();

    // Redirect to invoice view
    header('Location: ../pages/view-invoice.php?id=' . $invoice_id . '&updated=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Update Invoice Error: " . $e->getMessage());
    header('Location: ../pages/edit-invoice.php?id=' . ($invoice_id ?? '') . '&error=' . urlencode($e->getMessage()));
    exit;
}
?>