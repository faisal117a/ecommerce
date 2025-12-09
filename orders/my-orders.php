<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/auth.php';

requireLogin();

$pdo = getDB();
$userId = $_SESSION['user_id'];

// Get user orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
include __DIR__ . '/../includes/header.php';
?>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-semibold mb-1">My Orders</h1>
                <p class="text-muted mb-0">View your order history</p>
            </div>
            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Continue Shopping</a>
        </div>

        <?php displayFlashMessage(); ?>

        <?php if (empty($orders)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <p class="mb-3 text-muted">You haven't placed any orders yet.</p>
                <a href="../index.php" class="btn btn-primary px-4">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($orders as $order): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="fw-semibold mb-1">Order #<?= $order['id'] ?></div>
                                        <div class="text-muted small"><?= date('M d, Y', strtotime($order['created_at'])) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small mb-1">Total</div>
                                        <div class="fw-semibold"><?= formatPrice($order['total_amount']) ?></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small mb-1">Status</div>
                                        <span class="badge bg-<?= match($order['status']) {
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        } ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3 text-md-end">
                                        <a href="view.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

