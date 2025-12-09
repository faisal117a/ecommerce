<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

$pageTitle = 'Coupons';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage discount coupons and promotional codes</p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Add Coupon
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <?php if (empty($coupons)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">No coupons found.</p>
                <a href="add.php" class="btn btn-primary">Create First Coupon</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Purchase</th>
                            <th>Usage</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td class="fw-semibold"><code><?= htmlspecialchars($coupon['code']) ?></code></td>
                                <td><?= ucfirst($coupon['type']) ?></td>
                                <td>
                                    <?php if ($coupon['type'] === 'percentage'): ?>
                                        <?= number_format($coupon['value'], 0) ?>%
                                    <?php else: ?>
                                        <?= formatPrice($coupon['value']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $coupon['min_purchase'] > 0 ? formatPrice($coupon['min_purchase']) : '-' ?></td>
                                <td>
                                    <?= $coupon['used_count'] ?>
                                    <?php if ($coupon['usage_limit']): ?>
                                        / <?= $coupon['usage_limit'] ?>
                                    <?php else: ?>
                                        / ∞
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($coupon['expiry_date']): ?>
                                        <?= date('M d, Y', strtotime($coupon['expiry_date'])) ?>
                                        <?php if (strtotime($coupon['expiry_date']) < time()): ?>
                                            <span class="badge bg-danger small">Expired</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No expiry</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $coupon['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($coupon['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $coupon['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?= $coupon['id'] ?>" class="btn btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this coupon?')">Delete</a>
                                    </div>
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

