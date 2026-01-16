<?php
/**
 * Security Enhancement Script
 * Implements critical security features
 */

echo "🔒 Implementing Security Enhancements...\n\n";

// 1. Create security functions file
$securityFunctions = <<<'PHP'
<?php
/**
 * Security Functions
 * CSRF, Rate Limiting, Input Validation
 */

// ============================================
// CSRF Protection
// ============================================

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

// ============================================
// Rate Limiting (Login Attempts)
// ============================================

function checkLoginAttempts($username) {
    $maxAttempts = 5;
    $lockoutTime = 900; // 15 minutes
    
    $key = "login_attempts_" . md5($username);
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
    
    if ($attempts['count'] >= $maxAttempts) {
        if (time() - $attempts['time'] < $lockoutTime) {
            $minutesLeft = ceil(($lockoutTime - (time() - $attempts['time'])) / 60);
            return "Too many login attempts. Try again in $minutesLeft minutes.";
        } else {
            // Reset after lockout period
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
    }
    
    return true;
}

function recordFailedLogin($username) {
    $key = "login_attempts_" . md5($username);
    $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
    $attempts['count']++;
    $attempts['time'] = time();
    $_SESSION[$key] = $attempts;
}

function clearLoginAttempts($username) {
    $key = "login_attempts_" . md5($username);
    unset($_SESSION[$key]);
}

// ============================================
// Output Escaping
// ============================================

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function escapeJS($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

// ============================================
// Input Validation
// ============================================

function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        
        // Required check
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = "$field is required";
            continue;
        }
        
        // Skip other checks if empty and not required
        if (empty($value)) {
            continue;
        }
        
        // Type checking
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "Invalid email format";
                    }
                    break;
                case 'number':
                    if (!is_numeric($value)) {
                        $errors[$field] = "$field must be a number";
                    }
                    break;
                case 'decimal':
                    if (!preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
                        $errors[$field] = "$field must be a valid decimal";
                    }
                    break;
            }
        }
        
        // Min/Max length
        if (isset($rule['min']) && strlen($value) < $rule['min']) {
            $errors[$field] = "$field must be at least {$rule['min']} characters";
        }
        if (isset($rule['max']) && strlen($value) > $rule['max']) {
            $errors[$field] = "$field must be less than {$rule['max']} characters";
        }
    }
    
    return $errors;
}

// ============================================
// Session Security
// ============================================

function secureSession() {
    // Session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

PHP;

file_put_contents('includes/security.php', $securityFunctions);
echo "✅ Created includes/security.php\n";

// 2. Create audit logging migration
$auditSQL = <<<'SQL'
-- Audit logging table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    resource_type VARCHAR(50),
    resource_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

file_put_contents('database/create-audit-log.sql', $auditSQL);
echo "✅ Created database/create-audit-log.sql\n";

// 3. Create .htaccess for Apache
$htaccess = <<<'HTACCESS'
# Security headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "DENY"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable directory listing
Options -Indexes

# Protect sensitive files
<FilesMatch "\.(env|log|sql|md|git)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Enable HTTPS redirect (uncomment for production)
# <IfModule mod_rewrite.c>
#     RewriteEngine On
#     RewriteCond %{HTTPS} off
#     RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
# </IfModule>
HTACCESS;

file_put_contents('.htaccess', $htaccess);
echo "✅ Created .htaccess\n";

// 4. Create error logging configuration
$errorConfig = <<<'PHP'
<?php
/**
 * Error Handling Configuration
 */

// Disable error display in production (change to 1 for development)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-errors.log');

// Error reporting level
error_reporting(E_ALL);

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_NOTICE => 'NOTICE',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE'
    ];
    
    $type = $errorTypes[$errno] ?? 'UNKNOWN';
    error_log("[$type] $errstr in $errfile on line $errline");
    
    // Don't execute PHP internal error handler
    return true;
}

set_error_handler('customErrorHandler');

// Exception handler
function customExceptionHandler($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . 
              $exception->getFile() . " on line " . $exception->getLine());
    
    // Show generic message to user
    echo "An error occurred. Please contact support if this persists.";
}

set_exception_handler('customExceptionHandler');
PHP;

file_put_contents('includes/error-handler.php', $errorConfig);
echo "✅ Created includes/error-handler.php\n";

// 5. Create logs directory
if (!file_exists('logs')) {
    mkdir('logs', 0755, true);
    file_put_contents('logs/.htaccess', "Order allow,deny\nDeny from all");
    echo "✅ Created logs directory with protection\n";
}

// 6. Create security README
$securityReadme = <<<'MD'
# Security Implementation Guide

## Files Created

1. **includes/security.php** - CSRF, rate limiting, validation functions
2. **includes/error-handler.php** - Secure error handling
3. **database/create-audit-log.sql** - Audit logging table
4. **.htaccess** - Apache security headers
5. **logs/** - Error log directory

## Implementation Steps

### 1. Include Security Functions
Add to the top of `config.php`:
```php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/error-handler.php';
```

### 2. Add CSRF to Forms
In all forms, add after opening `<form>` tag:
```php
<?php echo csrfField(); ?>
```

### 3. Validate CSRF in APIs
At the top of all POST APIs, add:
```php
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die('Invalid CSRF token');
}
```

### 4. Implement Rate Limiting
In `login.php`, before authentication:
```php
$rateLimitCheck = checkLoginAttempts($_POST['username']);
if ($rateLimitCheck !== true) {
    $error = $rateLimitCheck;
    // Show error and exit
}
```

### 5. Run Database Migration
```bash
mysql -u root bluedots_quotes < database/create-audit-log.sql
```

### 6. Production Checklist
- [ ] Change database password
- [ ] Enable HTTPS redirect in .htaccess
- [ ] Set display_errors to 0 in error-handler.php
- [ ] Create limited database user
- [ ] Set proper file permissions (755/644)
- [ ] Remove test/development files

## Security Monitoring

Check logs regularly:
- `logs/php-errors.log` - PHP errors
- Database `audit_log` table - User actions

## Priority Items

1. ⚠️ **Critical:** Add CSRF protection to all forms
2. ⚠️ **Critical:** Implement login rate limiting
3. ⚠️ **Important:** Run audit log migration
4. **Recommended:** Review all output for XSS
5. **Recommended:** Create limited DB user for production

MD;

file_put_contents('SECURITY.md', $securityReadme);
echo "✅ Created SECURITY.md\n";

echo "\n✅ Security enhancement files created!\n";
echo "\nNext steps:\n";
echo "1. Review security_hardening.md in brain folder\n";
echo "2. Follow SECURITY.md implementation guide\n";
echo "3. Run audit log migration\n";
echo "4. Add CSRF tokens to forms\n";
echo "5. Test all security features\n";
?>