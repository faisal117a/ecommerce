<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('../index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = sanitize($_POST['name'] ?? '');
    
    $result = register($email, $password, $name);
    
    if ($result['success']) {
        // Auto login after registration
        login($email, $password);
        
        // Send welcome email
        require_once __DIR__ . '/../config/email.php';
        sendWelcomeEmail($email, $name);
        
        setFlashMessage('success', 'Account created successfully!');
        redirect('../index.php');
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cur1 Fashion</title>
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
        .register-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="register-card p-4 p-md-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold mb-2">Create Account</h2>
            <p class="text-muted">Sign up to start shopping</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php displayFlashMessage(); ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <small class="text-muted">At least 6 characters</small>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
        </form>
        
        <div class="text-center">
            <p class="text-muted small mb-2">Already have an account?</p>
            <a href="login.php" class="text-decoration-none">Sign in</a>
        </div>
        
        <div class="text-center mt-4 pt-3 border-top">
            <a href="../index.php" class="text-muted small text-decoration-none">← Back to shop</a>
        </div>
    </div>
</body>
</html>

