<?php
// This file is deprecated - redirect to appropriate login page
session_start();
require_once __DIR__ . '/../config/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../index.php');
    }
}

// Redirect to appropriate login page
if (isset($_GET['admin']) && $_GET['admin'] == '1') {
    redirect('../admin/login.php');
} else {
    redirect('../login.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cur1 Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shop.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold mb-2">Welcome Back</h2>
            <p class="text-muted">Sign in to your account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php displayFlashMessage(); ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
        </form>
        
        <div class="text-center">
            <p class="text-muted small mb-2">Don't have an account?</p>
            <a href="register.php" class="text-decoration-none">Create account</a>
        </div>
        
        <div class="text-center mt-4 pt-3 border-top">
            <a href="../index.php" class="text-muted small text-decoration-none">← Back to shop</a>
        </div>
    </div>
</body>
</html>

