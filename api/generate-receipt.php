<?php
include '../includes/session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

try {
    $pdo->beginTransaction();

    $invoice_id = intval($_POST['invoice_id']);
    $receipt_date = $_POST['receipt_date'];
    $payment_method = trim($_POST['payment_method']);
    $amount_paid = floatval($_POST['amount_paid']);
    $payment_reference = trim($_POST['payment_reference']);
    $notes = trim($_POST['notes']);

    // Fetch invoice
    $stmt = $pdo->prepare("
        SELECT * FROM documents 
        WHERE id = ? 
        AND document_type = 'invoice'
        AND status = 'finalized'
        AND deleted_at IS NULL
    ");
    $stmt->execute([$invoice_id]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        throw new Exception('Invoice not found or not finalized');
    }

    // Check if receipt already exists
    $stmt = $pdo->prepare("
        SELECT id FROM documents 
        WHERE parent_document_id = ? 
        AND document_type = 'receipt'
    ");
    $stmt->execute([$invoice_id]);
    if ($stmt->fetch()) {
        throw new Exception('Receipt already exists for this invoice');
    }

    // Validate amount
    if ($amount_paid <= 0 || $amount_paid > $invoice['balance_due']) {
        throw new Exception('Invalid payment amount');
    }

    // Generate receipt number
    $stmt = $pdo->query("
        SELECT document_number 
        FROM documents 
        WHERE document_type = 'receipt'
        ORDER BY id DESC 
        LIMIT 1
    ");
    $lastReceipt = $stmt->fetch();
    if ($lastReceipt) {
        $lastNumber = intval(substr($lastReceipt['document_number'], 4));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }
    $receipt_number = 'REC-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    // Create receipt
    $stmt = $pdo->prepare("
        INSERT INTO documents (
            document_type, document_number, quote_title, customer_name, salesperson,
            quote_date, subtotal, total_vat, grand_total,
            amount_paid, balance_due, payment_method, payment_reference,
            payment_terms, status, notes,
            parent_document_id, created_by
        ) VALUES (
            'receipt', ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, 0.00, ?, ?,
            ?, 'finalized', ?,
            ?, ?
        )
    ");

    $stmt->execute([
        $receipt_number,
        $invoice['quote_title'],
        $invoice['customer_name'],
        $invoice['salesperson'],
        $receipt_date,
        $invoice['subtotal'],
        $invoice['total_vat'],
        $invoice['grand_total'],
        $amount_paid,
        $payment_method,
        $payment_reference,
        $invoice['payment_terms'],
        $notes,
        $invoice_id,
        $current_user['id']
    ]);

    $receipt_id = $pdo->lastInsertId();

    // Update invoice: increase amount_paid, decrease balance_due
    $new_amount_paid = $invoice['amount_paid'] + $amount_paid;
    $new_balance_due = $invoice['grand_total'] - $new_amount_paid;

    $stmt = $pdo->prepare("
        UPDATE documents 
        SET amount_paid = ?,
            balance_due = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([
        $new_amount_paid,
        $new_balance_due,
        $invoice_id
    ]);

    $pdo->commit();

    header("Location: ../pages/view-receipt.php?id=" . $receipt_id . "&generated=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Generate receipt error: " . $e->getMessage());
    header("Location: ../pages/view-invoice.php?id=" . $invoice_id . "&error=" . urlencode($e->getMessage()));
    exit;
}
?>