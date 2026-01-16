<?php
include '../includes/session-check.php';

$quote_id = $_GET['id'] ?? null;

if (!$quote_id) {
    header('Location: ../pages/view-quotes.php?error=No quote specified');
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch quote
    $stmt = $pdo->prepare("
        SELECT * FROM documents 
        WHERE id = ? 
        AND document_type = 'quote' 
        AND status = 'finalized'
        AND deleted_at IS NULL
    ");
    $stmt->execute([$quote_id]);
    $quote = $stmt->fetch();

    if (!$quote) {
        throw new Exception('Quote not found or not finalized');
    }

    // Check if already converted
    $stmt = $pdo->prepare("
        SELECT id FROM documents 
        WHERE parent_document_id = ? 
        AND document_type = 'invoice'
    ");
    $stmt->execute([$quote_id]);
    if ($stmt->fetch()) {
        throw new Exception('Quote already converted to invoice');
    }

    // Generate invoice number
    $stmt = $pdo->query("
        SELECT document_number 
        FROM documents 
        WHERE document_type = 'invoice'
        ORDER BY id DESC 
        LIMIT 1
    ");
    $lastInvoice = $stmt->fetch();
    if ($lastInvoice) {
        $lastNumber = intval(substr($lastInvoice['document_number'], 4));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }
    $invoice_number = 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    // Fetch line items
    $stmt = $pdo->prepare("SELECT * FROM line_items WHERE document_id = ? ORDER BY item_number");
    $stmt->execute([$quote_id]);
    $line_items = $stmt->fetchAll();

    // Create invoice
    $stmt = $pdo->prepare("
        INSERT INTO documents (
            document_type, document_number, quote_title, customer_name, salesperson,
            quote_date, subtotal, total_vat, grand_total,
            amount_paid, balance_due, payment_terms, status,
            parent_document_id, created_by
        ) VALUES (
            'invoice', ?, ?, ?, ?,
            ?, ?, ?, ?,
            0.00, ?, ?, 'draft',
            ?, ?
        )
    ");

    $stmt->execute([
        $invoice_number,
        $quote['quote_title'],
        $quote['customer_name'],
        $quote['salesperson'],
        date('Y-m-d'),
        $quote['subtotal'],
        $quote['total_vat'],
        $quote['grand_total'],
        $quote['grand_total'], // balance_due = grand_total initially
        $quote['payment_terms'],
        $quote_id,
        $current_user['id']
    ]);

    $invoice_id = $pdo->lastInsertId();

    // Copy line items
    $stmt = $pdo->prepare("
        INSERT INTO line_items (
            document_id, item_number, quantity, description,
            unit_price, vat_applicable, vat_amount, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($line_items as $item) {
        $stmt->execute([
            $invoice_id,
            $item['item_number'],
            $item['quantity'],
            $item['description'],
            $item['unit_price'],
            $item['vat_applicable'],
            $item['vat_amount'],
            $item['line_total']
        ]);
    }

    $pdo->commit();

    header("Location: ../pages/view-invoice.php?id=" . $invoice_id . "&converted=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Convert to invoice error: " . $e->getMessage());
    header("Location: ../pages/view-quote.php?id=" . $quote_id . "&error=" . urlencode($e->getMessage()));
    exit;
}
?>