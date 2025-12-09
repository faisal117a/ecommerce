<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);

// Get order
$stmt = $pdo->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                       FROM orders o 
                       LEFT JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    redirect('index.php');
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$id]);
$orderItems = $stmt->fetchAll();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        $oldStatus = $order['status'];
        
        if ($newStatus !== $oldStatus) {
            $pdo->beginTransaction();
            try {
                // Update order status
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $id]);
                
                // Add to status history
                $stmt = $pdo->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)");
                $notes = "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus);
                $stmt->execute([$id, $newStatus, $notes]);
                
                // Send email notification if customer email exists
                if (!empty($order['customer_email'])) {
                    require_once __DIR__ . '/../../config/email.php';
                    sendOrderStatusUpdateEmail(
                        $id,
                        array_merge($order, ['status' => $newStatus]),
                        $order['customer_email'],
                        $order['customer_name'] ?? 'Customer',
                        $newStatus
                    );
                }
                
                $pdo->commit();
                setFlashMessage('success', 'Order status updated successfully!');
            } catch (PDOException $e) {
                $pdo->rollBack();
                setFlashMessage('error', 'Failed to update order status.');
                error_log("Order status update error: " . $e->getMessage());
            }
        }
        
        redirect('view.php?id=' . $id);
    }
}

$pageTitle = 'Order #' . $id;
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Order #<?= $id ?></h1>
            <p class="text-muted mb-0">Order details and management</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">← Back to Orders</a>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Image</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?= htmlspecialchars(getImageUrl($item['image_url'])) ?>" 
                                                     alt="" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <small class="text-muted">No image</small>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= formatPrice($item['price']) ?></td>
                                        <td class="fw-semibold"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Order Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-semibold"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5"><?= formatPrice($order['total_amount']) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Customer Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Name:</strong><br>
                        <?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?>
                    </p>
                    <p class="mb-2">
                        <strong>Email:</strong><br>
                        <?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?>
                    </p>
                    <p class="mb-2">
                        <strong>Phone:</strong><br>
                        <?= htmlspecialchars($order['phone']) ?>
                    </p>
                </div>
            </div>
            
            <!-- Shipping Address -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Shipping Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

