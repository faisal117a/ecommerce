<nav class="admin-navbar">
    <div class="navbar-content">
        <div class="navbar-title">
            <h4 class="mb-0 fw-semibold"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h4>
        </div>
        <div class="navbar-actions">
            <span class="text-muted small me-3"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
            <a href="../index.php" class="btn btn-sm btn-outline-secondary">View Shop</a>
        </div>
    </div>
</nav>

