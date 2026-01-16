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
