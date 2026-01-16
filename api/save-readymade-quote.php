<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/readymade-quotes.php');
    exit;
}

try {
    $pdo->beginTransaction();
    
    $template_name = $_POST['template_name'] ?? '';
    $template_description = $_POST['template_description'] ?? '';
    $quote_title = $_POST['quote_title'] ?? '';
    $payment_terms = $_POST['payment_terms'] ?? DEFAULT_PAYMENT_TERMS;
    $subtotal = $_POST['subtotal'] ?? 0;
    $total_vat = $_POST['total_vat'] ?? 0;
    $grand_total = $_POST['grand_total'] ?? 0;
    
    // Insert template record
    $stmt = $pdo->prepare("
        INSERT INTO quote_templates (
            template_name, template_description, payment_terms, estimated_total, created_by
        ) VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$template_name, $template_description, $payment_terms, $grand_total, $_SESSION['user_id']]);
    $template_id = $pdo->lastInsertId();
    
    // Insert line items
    if (isset($_POST['line_items']) && is_array($_POST['line_items'])) {
        $stmt = $pdo->prepare("
            INSERT INTO quote_template_items (
                template_id, item_number, quantity, description, 
                unit_price, vat_applicable, vat_amount, line_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $item_number = 1;
        foreach ($_POST['line_items'] as $item) {
            $stmt->execute([
                $template_id, $item_number++, $item['quantity'], $item['description'],
                $item['unit_price'], isset($item['vat_applicable']) ? 1 : 0,
                $item['vat_amount'] ?? 0, $item['line_total'] ?? 0
            ]);
        }
    }
    
    $pdo->commit();
    header('Location: ../pages/readymade-quotes.php?created=1');
    exit;
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Save readymade quote error: " . $e->getMessage());
    header('Location: ../pages/create-readymade-quote.php?error=' . urlencode($e->getMessage()));
    exit;
}