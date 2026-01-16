<?php
/**
 * Add CSRF Protection to All Forms
 * This script demonstrates where to add CSRF tokens
 */

echo "🔒 CSRF Protection Implementation Guide\n\n";

// List of files that need CSRF tokens
$formsToProtect = [
    // Quote Forms
    'pages/create-quote.php' => 'api/save-quote.php',
    'pages/edit-quote.php' => 'api/update-quote.php',

    // Invoice Forms
    'pages/edit-invoice.php' => 'api/update-invoice.php',

    // Receipt Forms
    'pages/create-receipt.php' => 'api/generate-receipt.php',

    // Readymade Quotes
    'pages/create-readymade-quote.php' => 'api/save-readymade-quote.php',
    'pages/edit-readymade-quote.php' => 'api/update-readymade-quote.php',
];

$apisToProtect = [
    'api/save-quote.php',
    'api/update-quote.php',
    'api/update-invoice.php',
    'api/generate-receipt.php',
    'api/save-readymade-quote.php',
    'api/update-readymade-quote.php',
    'api/bulk-delete.php',
    'api/bulk-restore.php',
    'api/permanent-delete.php',
];

echo "Forms that need CSRF tokens:\n";
foreach ($formsToProtect as $form => $api) {
    echo "  - $form → $api\n";
}

echo "\nAPIs that need CSRF validation:\n";
foreach ($apisToProtect as $api) {
    echo "  - $api\n";
}

echo "\n";
echo "IMPLEMENTATION:\n";
echo "===============\n\n";

echo "1. IN EACH FORM (add after <form> tag):\n";
echo "   <?php echo csrfField(); ?>\n\n";

echo "2. IN EACH API (add at the top, after session_start):\n";
echo "   if (!\$_POST || !validateCSRFToken(\$_POST['csrf_token'] ?? '')) {\n";
echo "       http_response_code(403);\n";
echo "       die('Invalid CSRF token');\n";
echo "   }\n\n";

echo "3. BULK ACTIONS (already uses data attributes):\n";
echo "   Update bulk-actions.js to send CSRF token with requests\n\n";

// Sample implementation for one file
echo "EXAMPLE IMPLEMENTATION:\n";
echo "=======================\n\n";

echo "File: pages/create-quote.php\n";
echo "Add after line with <form...>:\n";
echo "    <?php echo csrfField(); ?>\n\n";

echo "File: api/save-quote.php\n";
echo "Add after require_once statements:\n";
echo "    if (!\$_POST || !validateCSRFToken(\$_POST['csrf_token'] ?? '')) {\n";
echo "        http_response_code(403);\n";
echo "        die('Invalid CSRF token');\n";
echo "    }\n\n";

echo "✅ Review this guide and implement systematically\n";
echo "✅ Test each form after adding CSRF protection\n";
echo "✅ Check browser console for any errors\n";

?>