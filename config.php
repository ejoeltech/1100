<?php
// Bluedots Technologies Quote Management System
// Database Configuration

// Security includes (optional - Phase 3A)
if (file_exists(__DIR__ . '/includes/security.php')) {
    require_once __DIR__ . '/includes/security.php';
}
if (file_exists(__DIR__ . '/includes/error-handler.php')) {
    require_once __DIR__ . '/includes/error-handler.php';
}

// Initialize secure session (if function exists)
if (function_exists('secureSession')) {
    secureSession();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bluedots_quotes');
define('DB_USER', 'root'); // Change for production
define('DB_PASS', '');     // Change for production

// Create PDO connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Company information
define('COMPANY_NAME', 'Bluedots Technologies');
define('COMPANY_ADDRESS', 'No. 9 Ugbor Village Road, Ugbor GRA, Benin City, Edo State');
define('COMPANY_PHONE', '07031635955');
define('COMPANY_EMAIL', 'bluedotsng@gmail.com');
define('COMPANY_WEBSITE', 'www.bluedots.com.ng');

define('BANK_ACCESS', '0107309773');
define('BANK_UBA', '1023821430');

define('DEFAULT_PAYMENT_TERMS', '80% Initial Deposit');
define('VAT_RATE', 0.075); // 7.5%

// Helper function for formatting currency
function formatNaira($amount)
{
    return '₦' . number_format($amount, 2);
}

// Helper function to generate next quote number
function generateQuoteNumber($pdo)
{
    $stmt = $pdo->query("
        SELECT document_number 
        FROM documents 
        WHERE document_type = 'quote'
        ORDER BY id DESC 
        LIMIT 1
    ");

    $lastQuote = $stmt->fetch();

    if ($lastQuote) {
        // Extract number from QT-0001 format
        $lastNumber = intval(substr($lastQuote['document_number'], 3));
        $nextNumber = $lastNumber + 1;
    } else {
        $nextNumber = 1;
    }

    return 'QT-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
}

// Phase 2B: Document type helper functions
function getDocumentTypeBadge($type)
{
    $badges = [
        'quote' => 'bg-blue-100 text-blue-800',
        'invoice' => 'bg-green-100 text-green-800',
        'receipt' => 'bg-purple-100 text-purple-800'
    ];
    return $badges[$type] ?? 'bg-gray-100 text-gray-800';
}

function getDocumentNumberColor($type)
{
    $colors = [
        'quote' => 'text-blue-600',
        'invoice' => 'text-green-600',
        'receipt' => 'text-purple-600'
    ];
    return $colors[$type] ?? 'text-gray-600';
}
?>
<?php
/**
 * Audit Logging Addition to config.php
 * Add this function before the closing ?> tag
 */

// ============================================
// Audit Logging Function
// ============================================

function logAudit($action, $resourceType = null, $resourceId = null, $details = [])
{
    global $pdo;

    try {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        $stmt = $pdo->prepare("
            INSERT INTO audit_log (user_id, action, resource_type, resource_id, ip_address, user_agent, details)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $userId,
            $action,
            $resourceType,
            $resourceId,
            $ipAddress,
            $userAgent,
            json_encode($details)
        ]);
    } catch (Exception $e) {
        // Log error but don't break application
        error_log("Audit log error: " . $e->getMessage());
    }
}
?>