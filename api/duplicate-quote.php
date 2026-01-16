<?php
require_once '../config.php';

$quote_id = $_GET['id'] ?? null;

if (!$quote_id) {
    header('Location: ../pages/view-quotes.php?error=No quote specified');
    exit;
}

try {
    $pdo->beginTransaction();

    // Fetch original quote
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$quote_id]);
    $original = $stmt->fetch();

    if (!$original) {
        throw new Exception('Quote not found');
    }

    // Fetch line items
    $stmt = $pdo->prepare("SELECT * FROM line_items WHERE document_id = ? ORDER BY item_number");
    $stmt->execute([$quote_id]);
    $line_items = $stmt->fetchAll();

    // Generate new quote number
    $new_quote_number = generateQuoteNumber($pdo);

    // Insert new quote
    $stmt = $pdo->prepare("
        INSERT INTO documents (
            document_number, quote_title, customer_name, salesperson,
            quote_date, subtotal, total_vat, grand_total,
            payment_terms, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
    ");

    $stmt->execute([
        $new_quote_number,
        $original['quote_title'] . ' (Copy)',
        $original['customer_name'],
        $original['salesperson'],
        date('Y-m-d'), // Today's date
        $original['subtotal'],
        $original['total_vat'],
        $original['grand_total'],
        $original['payment_terms']
    ]);

    $new_document_id = $pdo->lastInsertId();

    // Copy line items
    $stmt = $pdo->prepare("
        INSERT INTO line_items (
            document_id, item_number, quantity, description,
            unit_price, vat_applicable, vat_amount, line_total
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($line_items as $item) {
        $stmt->execute([
            $new_document_id,
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

    header("Location: ../pages/view-quote.php?id=" . $new_document_id . "&duplicated=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Duplicate error: " . $e->getMessage());
    header("Location: ../pages/view-quotes.php?error=Failed to duplicate quote");
    exit;
}
?>