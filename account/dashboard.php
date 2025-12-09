<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/auth.php';

requireLogin();

$pdo = getDB();
$user = getCurrentUser();

// Get user orders
$orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$orders->execute([$_SESSION['user_id']]);
$recentOrders = $orders->fetchAll();

// Get order statistics
$stats = [
    'total_orders' => $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?")->execute([$_SESSION['user_id']]) ? $pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = " . $_SESSION['user_id'])->fetchColumn() : 0,
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = " . $_SESSION['user_id'] . " AND status = 'pending'")->fetchColumn(),
    'total_spent' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = " . $_SESSION['user_id'] . " AND status != 'cancelled'")->fetchColumn(),
];

$pageTitle = 'My Dashboard';
?>
<style>
    .dashboard-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .dashboard-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
</style>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 fw-bold mb-2">Welcome back, <?= htmlspecialchars($user['name']) ?>!</h1>
                <p class="text-muted mb-0">Manage your account, orders, and preferences</p>
            </div>
        </div>

        <?php displayFlashMessage(); ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm dashboard-card h-100">
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
            <div class="col-md-4">
                <div class="card border-0 shadow-sm dashboard-card h-100">
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
            <div class="col-md-4">
                <div class="card border-0 shadow-sm dashboard-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Total Spent</p>
                                <h3 class="mb-0 fw-bold text-success"><?= formatPrice($stats['total_spent']) ?></h3>
                            </div>
                            <div class="text-success">
                                <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V1.5c-.83-.616-1.478-1.023-1.478-1.5 0-.494.608-.939 1.478-1.216V0h1.043v.5c.83.277 1.478.722 1.478 1.216 0 .477-.648.884-1.478 1.5v3.228l.722.187c1.809.468 2.956 1.438 2.956 3.028 0 1.862-1.408 3.121-3.678 3.3V15H7.591v-1.216c-2.078-.153-3.443-1.336-3.591-3.003H4zm0-1.281h.648c-.145 1.482-.233 2.12-1.648 2.253V9.5H4zm6.5 0V9.5h-.5v1.253c-1.415-.133-1.503-.771-1.648-2.253H10.5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="profile.php" class="btn btn-outline-primary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                                </svg>
                                Edit Profile
                            </a>
                            <a href="../orders/my-orders.php" class="btn btn-outline-primary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                    <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l.5 2H5V5H3.14zM6 5v2h2V5H6zm3 0v2h2V5H9zm3 0v2h1.36l.5-2H12zm1.11 3H12v2h.61l.5-2zM11 8H9v2h2V8zM8 8H6v2h2V8zM5 8H3.89l.5 2H5V8zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
                                </svg>
                                View All Orders
                            </a>
                            <a href="../orders/track.php" class="btn btn-outline-primary">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                                    <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                                    <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
                                </svg>
                                Track Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Account Information</h5>
                        <div class="mb-2">
                            <strong>Name:</strong> <?= htmlspecialchars($user['name']) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Member Since:</strong> <?= date('F Y', strtotime($user['created_at'])) ?>
                        </div>
                        <a href="profile.php" class="btn btn-sm btn-primary mt-2">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <?php if (!empty($recentOrders)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Recent Orders</h5>
                    <a href="../orders/my-orders.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="fw-semibold">#<?= $order['id'] ?></td>
                                        <td class="text-muted small"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
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
                                        <td>
                                            <a href="../orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">You haven't placed any orders yet.</p>
                    <a href="../index.php" class="btn btn-primary">Start Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

