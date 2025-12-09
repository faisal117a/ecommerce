<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();

// Filter by status
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT o.*, u.name as customer_name, u.email as customer_email 
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($statusFilter && in_array($statusFilter, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
    $query .= " AND o.status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pageTitle = 'Orders';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage customer orders</p>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Status Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="btn-group" role="group">
                <a href="index.php" class="btn btn-<?= !$statusFilter ? 'primary' : 'outline-primary' ?>">All</a>
                <a href="index.php?status=pending" class="btn btn-<?= $statusFilter === 'pending' ? 'primary' : 'outline-primary' ?>">Pending</a>
                <a href="index.php?status=processing" class="btn btn-<?= $statusFilter === 'processing' ? 'primary' : 'outline-primary' ?>">Processing</a>
                <a href="index.php?status=shipped" class="btn btn-<?= $statusFilter === 'shipped' ? 'primary' : 'outline-primary' ?>">Shipped</a>
                <a href="index.php?status=delivered" class="btn btn-<?= $statusFilter === 'delivered' ? 'primary' : 'outline-primary' ?>">Delivered</a>
                <a href="index.php?status=cancelled" class="btn btn-<?= $statusFilter === 'cancelled' ? 'primary' : 'outline-primary' ?>">Cancelled</a>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">No orders found.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="fw-semibold">#<?= $order['id'] ?></td>
                                <td>
                                    <div><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($order['customer_email'] ?? '') ?></small>
                                </td>
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
                                <td class="text-muted small"><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

