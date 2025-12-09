<?php
// Authentication helper functions

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to access this page.');
        
        // Calculate correct path to login.php based on current location
        $scriptPath = $_SERVER['PHP_SELF'];
        if (strpos($scriptPath, '/admin/') !== false) {
            $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7);
            $depth = substr_count($adminPath, '/');
            $basePath = str_repeat('../', $depth + 1);
            $loginPath = $basePath . 'login.php';
        } elseif (strpos($scriptPath, '/orders/') !== false) {
            $loginPath = '../login.php';
        } elseif (strpos($scriptPath, '/auth/') !== false) {
            // Already in auth directory, redirect to login.php in root
            $loginPath = '../login.php';
        } elseif (strpos($scriptPath, '/account/') !== false) {
            $loginPath = '../login.php';
        } else {
            // In root directory
            $loginPath = 'login.php';
        }
        
        redirect($loginPath);
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        
        // Calculate correct path to index.php based on current location
        $scriptPath = $_SERVER['PHP_SELF'];
        if (strpos($scriptPath, '/admin/') !== false) {
            $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7);
            $depth = substr_count($adminPath, '/');
            $basePath = str_repeat('../', $depth + 1);
        } elseif (strpos($scriptPath, '/orders/') !== false) {
            $basePath = '../';
        } elseif (strpos($scriptPath, '/auth/') !== false) {
            $basePath = '../';
        } else {
            $basePath = '';
        }
        
        redirect($basePath . 'index.php');
    }
}

/**
 * Get current user data
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, role, name FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Login user
 */
function login(string $email, string $password): bool {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, password_hash, role, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    
    return false;
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    session_start();
}

/**
 * Register new user
 */
function register(string $email, string $password, string $name): array {
    $errors = [];
    
    // Validation
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = 'Valid email is required.';
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    $pdo = getDB();
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'errors' => ['Email already registered.']];
    }
    
    // Create user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, 'customer')");
    
    try {
        $stmt->execute([$email, $passwordHash, $name]);
        return ['success' => true, 'user_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
    }
}

