<?php
// 1100-ERP System
// Configuration File

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

// Company information - REPLACE WITH YOUR COMPANY DETAILS
define('COMPANY_NAME', 'Your Company Name');
define('COMPANY_ADDRESS', 'Your Company Address, City, State/Province, Country');
define('COMPANY_PHONE', '+1234567890');
define('COMPANY_EMAIL', 'contact@yourcompany.com');
define('COMPANY_WEBSITE', 'www.yourcompany.com');

// Legacy bank account constants (deprecated - use bank_accounts table instead)
define('BANK_ACCESS', '1234567890');
define('BANK_UBA', '0987654321');

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

// ============================================
// Bank Account Helper Functions
// ============================================

/**
 * Get bank accounts for document display
 */
function getBankAccountsForDisplay()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT bank_name, account_number, account_name 
            FROM bank_accounts 
            WHERE is_active = 1 AND show_on_documents = 1 
            ORDER BY display_order ASC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        // Table might not exist yet, return empty array
        return [];
    }
}

/**
 * Get all active bank accounts
 */
function getAllBankAccounts()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT * FROM bank_accounts 
            WHERE is_active = 1 
            ORDER BY display_order ASC
        ");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get count of bank accounts selected for display
 */
function getSelectedBankAccountsCount()
{
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT COUNT(*) FROM bank_accounts 
            WHERE is_active = 1 AND show_on_documents = 1
        ");
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
?>