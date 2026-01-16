<?php
session_start();
require_once 'config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check rate limiting
    $rateLimitCheck = checkLoginAttempts($username);
    if ($rateLimitCheck !== true) {
        $error = $rateLimitCheck;
        logAudit('login_ratelimit', 'user', null, ['username' => $username]);
    } else {
        // Attempt login
        if (login($pdo, $username, $password)) {
            clearLoginAttempts($username);
            logAudit('login_success', 'user', $_SESSION['user_id'], ['username' => $username]);
            header('Location: dashboard.php');
            exit;
        } else {
            recordFailedLogin($username);
            logAudit('login_failed', 'user', null, ['username' => $username]);
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bluedots Technologies</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0076BE',
                        secondary: '#34A853',
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="flex justify-center items-center gap-2 mb-3">
                <div class="flex items-center gap-1">
                    <div class="w-3 h-3 bg-sky-500 rounded-full"></div>
                    <div class="w-5 h-5 bg-sky-600 rounded-full border-2 border-secondary"></div>
                    <div class="w-8 h-8 bg-sky-700 rounded-full"></div>
                    <div class="w-10 h-10 border-4 border-secondary rounded-full"></div>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Bluedots</h1>
            <p class="text-xs tracking-[0.3em] uppercase font-bold text-gray-600">TECHNOLOGIES</p>
            <p class="text-gray-600 mt-4">Quote Management System</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Login</h2>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-800 text-sm">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Username
                    </label>
                    <input type="text" name="username" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your username">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Password
                    </label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="Enter your password">
                </div>

                <button type="submit"
                    class="w-full bg-primary text-white py-3 rounded-lg hover:bg-blue-700 font-semibold transition-colors">
                    Login
                </button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                <p>Default credentials:</p>
                <p class="font-mono bg-gray-50 px-3 py-2 rounded mt-2">
                    Username: <strong>admin</strong><br>
                    Password: <strong>admin123</strong>
                </p>
                <p class="text-xs text-red-600 mt-2">⚠️ Change this password after first login!</p>
            </div>
        </div>

        <div class="text-center mt-6 text-sm text-gray-600">
            <p>©
                <?php echo date('Y'); ?> Bluedots Technologies
            </p>
        </div>
    </div>

</body>

</html>