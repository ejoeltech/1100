<?php
require_once '../config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Extract form data
    $quote_number = $_POST['quote_number'];
    $quote_title = trim($_POST['quote_title']);
    $customer_name = trim($_POST['customer_name']);
    $salesperson = trim($_POST['salesperson']);
    $quote_date = $_POST['quote_date'];
    $payment_terms = trim($_POST['payment_terms']);
    $subtotal = floatval($_POST['subtotal']);
    $total_vat = floatval($_POST['total_vat']);
    $grand_total = floatval($_POST['grand_total']);
    $status = $_POST['status']; // 'draft' or 'finalized'
    $line_items = $_POST['line_items'];

    // Validate required fields
    if (empty($quote_title) || empty($customer_name) || empty($salesperson)) {
        throw new Exception('Required fields are missing');
    }

    // Validate line items exist
    if (empty($line_items) || !is_array($line_items)) {
        throw new Exception('No line items provided');
    }

    // Save customer if new (INSERT IGNORE will skip if already exists)
    if (!empty($customer_name)) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO customers (name, created_at)
            VALUES (?, NOW())
        ");
        $stmt->execute([$customer_name]);
    }

    // Insert document
    $stmt = $pdo->prepare("
        INSERT INTO documents (
            document_number, quote_title, customer_name, salesperson,
            quote_date, subtotal, total_vat, grand_total, 
            payment_terms, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $quote_number,
        $quote_title,
        $customer_name,
        $salesperson,
        $quote_date,
        $subtotal,
        $total_vat,
        $grand_total,
        $payment_terms,
        $status
    ]);

    $document_id = $pdo->lastInsertId();

    // Insert line items
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

        // Validate line item
        if (empty($description) || $quantity <= 0 || $unit_price < 0) {
            throw new Exception("Invalid line item data");
        }

        $stmt->execute([
            $document_id,
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

    // Commit transaction
    $pdo->commit();

    // Redirect to view quote
    header("Location: ../pages/view-quote.php?id=" . $document_id . "&success=1");
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    // Log error (in production, use proper logging)
    error_log("Quote save error: " . $e->getMessage());

    // Redirect back with error
    header("Location: ../pages/create-quote.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>