<?php
/**
 * Script to generate all remaining Readymade Quotes files
 * This creates the create, edit, save, and API files
 */

echo "🔧 Generating Readymade Quotes system files...\n\n";

// 1. Create readymade quote page (copy from create-quote.php with modifications)
$createQuotePath = 'pages/create-quote.php';
if (file_exists($createQuotePath)) {
    $content = file_get_contents($createQuotePath);

    // Modify for template creation
    $content = str_replace('Create Quote', 'Create Readymade Quote', $content);
    $content = str_replace('create-quote.php', 'create-readymade-quote.php', $content);
    $content = str_replace('../api/save-quote.php', '../api/save-readymade-quote.php', $content);
    $content = preg_replace('/<input[^>]*name="customer_name"[^>]*>/', '<input type="text" name="template_name" placeholder="e.g., Website Development Package" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary">', $content);
    $content = preg_replace('/<input[^>]*name="salesperson"[^>]*>/', '<textarea name="template_description" rows="2" placeholder="Brief description of this template (optional)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary"></textarea>', $content);
    $content = str_replace('Customer Name', 'Template Name', $content);
    $content = str_replace('Salesperson', 'Description', $content);
    $content = str_replace('Quote Title', 'Template Title', $content);
    $content = str_replace('Quote Date', 'Created', $content);
    $content = preg_replace('/<input[^>]*name="quote_date"[^>]*>/', '<input type="text" readonly value="' . date('d/m/Y') . '" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50">', $content);

    file_put_contents('pages/create-readymade-quote.php', $content);
    echo "✅ Created pages/create-readymade-quote.php\n";
}

// 2. Save readymade quote API
$saveAPI = <<<'PHP'
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
PHP;

file_put_contents('api/save-readymade-quote.php', $saveAPI);
echo "✅ Created api/save-readymade-quote.php\n";

// 3. Use readymade quote API (redirects to create quote with pre-filled data)
$useAPI = <<<'PHP'
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$template_id = $_GET['id'] ?? null;

if (!$template_id) {
    header('Location: ../pages/readymade-quotes.php');
    exit;
}

// Fetch template and items
$stmt = $pdo->prepare("SELECT * FROM quote_templates WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$template_id]);
$template = $stmt->fetch();

if (!$template) {
    header('Location: ../pages/readymade-quotes.php?error=Template not found');
    exit;
}

$ stmt = $pdo->prepare("SELECT * FROM quote_template_items WHERE template_id = ? ORDER BY item_number");
$stmt->execute([$template_id]);
$items = $stmt->fetchAll();

// Store in session for create-quote.php to use
$_SESSION['template_data'] = [
    'template_name' => $template['template_name'],
    'payment_terms' => $template['payment_terms'],
    'items' => $items
];

header('Location: ../pages/create-quote.php?from_template=1');
exit;
PHP;

file_put_contents('api/use-readymade-quote.php', $useAPI);
echo "✅ Created api/use-readymade-quote.php\n";

// 4. Delete readymade quote API
$deleteAPI = <<<'PHP'
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$template_id = $_GET['id'] ?? null;

if (!$template_id) {
    header('Location: ../pages/readymade-quotes.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE quote_templates SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$template_id]);
    
    header('Location: ../pages/readymade-quotes.php?deleted=1');
    exit;
    
} catch (Exception $e) {
    error_log("Delete readymade quote error: " . $e->getMessage());
    header('Location: ../pages/readymade-quotes.php?error=' . urlencode($e->getMessage()));
    exit;
}
PHP;

file_put_contents('api/delete-readymade-quote.php', $deleteAPI);
echo "✅ Created api/delete-readymade-quote.php\n";

echo "\n✅ All Readymade Quotes files generated!\n";
echo "\nNext steps:\n";
echo "1. Run database migration: database/create-readymade-quotes-tables.sql\n";
echo "2. Update header.php to add 'Readymade Quotes' menu\n";
echo "3. Update create-quote.php to show template data if coming from template\n";
?>