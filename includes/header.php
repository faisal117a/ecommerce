<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Cur1 Fashion';
}

// Ensure auth functions are available
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../config/auth.php';
}

// Calculate base path for links (works from any directory)
// Always use absolute paths pointing to the application root
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// Extract the first directory level as the app root (e.g., /cur1 from /cur1/index.php or /cur1/orders/view.php)
$normalizedDir = rtrim($scriptDir, '/');
if ($normalizedDir === '' || $normalizedDir === '/') {
    // We're at document root
    $basePath = '/';
} else {
    // Get the first path segment (app root)
    $pathParts = explode('/', ltrim($normalizedDir, '/'));
    if (!empty($pathParts)) {
        // Use the first directory as app root (e.g., 'cur1' from '/cur1/orders')
        $basePath = '/' . $pathParts[0] . '/';
    } else {
        $basePath = '/';
    }
}

// Check if we should output full HTML structure or just header
$headerOnly = isset($headerOnly) && $headerOnly === true;

if (!$headerOnly):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Cur1 Fashion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/shop.css">
</head>
<body>
<?php endif; ?>
    
<header class="shadow-sm sticky-top bg-white">
    <nav class="navbar navbar-expand-lg container py-3">
        <a class="navbar-brand fw-bold text-primary" href="<?= $basePath ?>index.php">
            Cur1<span class="text-dark">Fashion</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link <?= (isset($activeCategorySlug) && $activeCategorySlug === 'all') ? 'active' : '' ?>" href="<?= $basePath ?>index.php?category=all">All</a>
                </li>
                <?php
                // Get categories for navigation if not already set
                if (!isset($categories)) {
                    require_once __DIR__ . '/../config/database.php';
                    $pdo = getDB();
                    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
                }
                $activeCategorySlug = $activeCategorySlug ?? 'all';
                foreach ($categories as $cat): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeCategorySlug === $cat['slug'] ? 'active' : '' ?>"
                           href="<?= $basePath ?>index.php?category=<?= htmlspecialchars($cat['slug']) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                            </svg>
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= $basePath ?>account/dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= $basePath ?>orders/my-orders.php">My Orders</a></li>
                            <li><a class="dropdown-item" href="<?= $basePath ?>account/profile.php">My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $basePath ?>auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-sm btn-outline-dark position-relative" href="<?= $basePath ?>cart.php">
                        Cart
                        <?php 
                        $cartCount = 0;
                        if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                            $cartCount = array_sum($_SESSION['cart']);
                        }
                        ?>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count-badge"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (!isLoggedIn()): ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-sm btn-outline-primary" href="<?= $basePath ?>login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

