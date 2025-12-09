<?php
session_start();
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';

requireAdmin();

$pdo = getDB();

// Get statistics
$stats = [
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn(),
    'total_customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
];

// Recent orders
$recentOrders = $pdo->query("SELECT o.*, u.name as customer_name 
                             FROM orders o 
                             LEFT JOIN users u ON o.user_id = u.id 
                             ORDER BY o.created_at DESC 
                             LIMIT 5")->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>!</p>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Orders</p>
                            <h3 class="mb-0 fw-bold"><?= number_format($stats['total_orders']) ?></h3>
                        </div>
                        <div class="text-primary">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l.5 2H5V5H3.14zM6 5v2h2V5H6zm3 0v2h2V5H9zm3 0v2h1.36l.5-2H12zm1.11 3H12v2h.61l.5-2zM11 8H9v2h2V8zM8 8H6v2h2V8zM5 8H3.89l.5 2H5V8zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Pending Orders</p>
                            <h3 class="mb-0 fw-bold text-warning"><?= number_format($stats['pending_orders']) ?></h3>
                        </div>
                        <div class="text-warning">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.03zm0 0A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.03zm0 0A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.03zm0 0A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.03zm0 0A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.03zm0 0A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.03z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Products</p>
                            <h3 class="mb-0 fw-bold"><?= number_format($stats['total_products']) ?></h3>
                        </div>
                        <div class="text-success">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Total Revenue</p>
                            <h3 class="mb-0 fw-bold"><?= formatPrice($stats['total_revenue']) ?></h3>
                        </div>
                        <div class="text-info">
                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V1.5c-.83-.616-1.478-1.023-1.478-1.5 0-.494.608-.939 1.478-1.216V0h1.043v.5c.83.277 1.478.722 1.478 1.216 0 .477-.648.884-1.478 1.5v3.228l.722.187c1.809.468 2.956 1.438 2.956 3.028 0 1.862-1.408 3.121-3.678 3.3V15H7.591v-1.216c-2.078-.153-3.443-1.336-3.591-3.003H4zm0-1.281h.648c-.145 1.482-.233 2.12-1.648 2.253V9.5H4zm6.5 0V9.5h-.5v1.253c-1.415-.133-1.503-.771-1.648-2.253H10.5z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Recent Orders</h5>
            <a href="orders/index.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentOrders)): ?>
                <div class="text-center py-5 text-muted">
                    <p class="mb-0">No orders yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td class="fw-semibold">#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                                    <td class="fw-semibold"><?= formatPrice($order['total_amount']) ?></td>
                                    <td>
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
                                    </td>
                                    <td class="text-muted small"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <a href="orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>

