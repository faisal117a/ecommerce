<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/auth.php';

$pdo = getDB();
$orderId = (int)($_GET['id'] ?? 0);
$order = null;
$orderItems = [];
$statusHistory = [];

if ($orderId > 0) {
    // Get order
    $query = "SELECT o.* FROM orders o WHERE o.id = ?";
    $params = [$orderId];
    
    if (!isAdmin()) {
        // For non-admins, check if they own the order or it's a guest order
        if (isLoggedIn()) {
            $query .= " AND (o.user_id = ? OR o.user_id IS NULL)";
            $params[] = $_SESSION['user_id'];
        } else {
            // Guest orders - would need email/phone verification in production
            $query .= " AND o.user_id IS NULL";
        }
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $order = $stmt->fetch();
    
    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
        $stmt->execute([$orderId]);
        $orderItems = $stmt->fetchAll();
        
        // Get status history
        $stmt = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC");
        $stmt->execute([$orderId]);
        $statusHistory = $stmt->fetchAll();
        
        // Add current status to history if not already there
        $currentStatusInHistory = false;
        foreach ($statusHistory as $history) {
            if ($history['status'] === $order['status']) {
                $currentStatusInHistory = true;
                break;
            }
        }
        if (!$currentStatusInHistory) {
            $statusHistory[] = [
                'status' => $order['status'],
                'notes' => 'Current status',
                'created_at' => $order['updated_at'] ?? $order['created_at']
            ];
        }
    }
}

$pageTitle = $order ? 'Track Order #' . $orderId : 'Track Order';
?>
<style>
    .timeline {
        position: relative;
        padding-left: 2rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.75rem;
        top: 0.25rem;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #6c757d;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #dee2e6;
    }
    .timeline-item.active::before {
        background: #0d6efd;
        box-shadow: 0 0 0 2px #0d6efd;
    }
    .timeline-item.completed::before {
        background: #198754;
        box-shadow: 0 0 0 2px #198754;
    }
</style>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <h1 class="h3 fw-bold mb-4">Track Your Order</h1>
                    
                    <form method="GET" action="" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <input type="number" class="form-control" name="id" placeholder="Enter Order ID" 
                                       value="<?= $orderId ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Track Order</button>
                            </div>
                        </div>
                    </form>
                    
                    <?php if ($orderId > 0 && !$order): ?>
                        <div class="alert alert-warning">
                            Order not found. Please check your Order ID and try again.
                        </div>
                    <?php elseif ($order): ?>
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <h3 class="h5 fw-bold mb-3">Order Status Timeline</h3>
                                <div class="timeline">
                                    <?php
                                    $statuses = ['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
                                    $currentStatusIndex = array_search($order['status'], array_keys($statuses));
                                    
                                    foreach ($statuses as $status => $label):
                                        if ($status === 'cancelled' && $order['status'] !== 'cancelled') continue;
                                        $statusIndex = array_search($status, array_keys($statuses));
                                        $isActive = $status === $order['status'];
                                        $isCompleted = $statusIndex < $currentStatusIndex;
                                    ?>
                                        <div class="timeline-item <?= $isActive ? 'active' : ($isCompleted ? 'completed' : '') ?>">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1 fw-semibold"><?= $label ?></h5>
                                                    <?php if ($isActive): ?>
                                                        <p class="text-muted small mb-0">Current status</p>
                                                    <?php elseif ($isCompleted): ?>
                                                        <p class="text-success small mb-0">Completed</p>
                                                    <?php else: ?>
                                                        <p class="text-muted small mb-0">Pending</p>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($isActive): ?>
                                                    <span class="badge bg-primary">Active</span>
                                                <?php elseif ($isCompleted): ?>
                                                    <span class="badge bg-success">Done</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <h3 class="h5 fw-bold mb-3 mt-4">Order Items</h3>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
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
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($item['image_url']): ?>
                                                                <img src="<?= htmlspecialchars(getImageUrl($item['image_url'])) ?>" 
                                                                     alt="" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                                            <?php endif; ?>
                                                            <span class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></span>
                                                        </div>
                                                    </td>
                                                    <td><?= $item['quantity'] ?></td>
                                                    <td><?= formatPrice($item['price']) ?></td>
                                                    <td class="fw-semibold"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom">
                                        <h5 class="mb-0 fw-semibold">Order Summary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Order ID:</strong> #<?= $order['id'] ?><br>
                                            <strong>Date:</strong> <?= date('F d, Y', strtotime($order['created_at'])) ?><br>
                                            <strong>Status:</strong> 
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
                                        
                                        <hr>
                                        
                                        <div class="mb-2">
                                            <strong>Shipping Address:</strong><br>
                                            <span class="text-muted"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></span>
                                        </div>
                                        
                                        <?php if (!empty($order['billing_address'])): ?>
                                            <div class="mb-2">
                                                <strong>Billing Address:</strong><br>
                                                <span class="text-muted"><?= nl2br(htmlspecialchars($order['billing_address'])) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mb-2">
                                            <strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?>
                                        </div>
                                        
                                        <hr>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <strong><?= formatPrice($order['total_amount']) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Total:</span>
                                            <strong class="fs-5"><?= formatPrice($order['total_amount']) ?></strong>
                                        </div>
                                        
                                        <?php if (isLoggedIn() && $_SESSION['user_id'] == $order['user_id']): ?>
                                            <hr>
                                            <a href="view.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary w-100">View Full Details</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

