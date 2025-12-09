<?php
// Suppress ALL errors and warnings to prevent output
@ini_set('display_errors', 0);
@ini_set('display_startup_errors', 0);
@error_reporting(0);

// Start output buffering - clean any existing buffers first
while (@ob_get_level() > 0) {
    @ob_end_clean();
}
@ob_start();

session_start();

// Include required files with error suppression
@require_once __DIR__ . '/../config/database.php';
@require_once __DIR__ . '/../config/functions.php';
@require_once __DIR__ . '/../config/auth.php';

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
$orderPlaced = isset($_GET['placed']) && $_GET['placed'] == '1';

// Store welcome email flag to send after page renders (prevents output issues)
$pendingWelcomeEmail = null;
if (isset($_SESSION['send_welcome_email'])) {
    $pendingWelcomeEmail = $_SESSION['send_welcome_email'];
    unset($_SESSION['send_welcome_email']);
}

// Get order
$query = "SELECT o.* FROM orders o WHERE o.id = ?";
$params = [$id];

// Allow viewing if:
// 1. Admin
// 2. Logged in and owns the order
// 3. Guest who just placed the order (check session)
$orderPlaced = isset($_GET['placed']) && $_GET['placed'] == '1';
$justPlacedOrder = isset($_SESSION['order_just_placed']) && $_SESSION['order_just_placed'] == $id;

if (!isAdmin()) {
    if (isLoggedIn()) {
        $query .= " AND o.user_id = ?";
        $params[] = $_SESSION['user_id'];
    } elseif ($justPlacedOrder || $orderPlaced) {
        // Allow guest to view order they just placed (no user_id check)
        // This is safe because we're checking the session flag
    } else {
        // Guest trying to view order they didn't just place - require login
        setFlashMessage('error', 'Please sign in to view your orders.');
        redirect('../login.php?redirect=' . urlencode('orders/view.php?id=' . $id));
    }
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Order not found.');
    if (isAdmin()) {
        redirect('../admin/orders/index.php');
    } elseif (isLoggedIn()) {
        redirect('my-orders.php');
    } else {
        redirect('../index.php');
    }
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image_url 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$id]);
$orderItems = $stmt->fetchAll();

$pageTitle = 'Order #' . $id;
include __DIR__ . '/../includes/header.php';
?>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <?php if ($orderPlaced || $justPlacedOrder): ?>
            <div class="alert alert-success mb-4">
                <div class="d-flex align-items-center">
                    <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5z"/>
                    </svg>
                    <div>
                        <h5 class="mb-1">Order Placed Successfully!</h5>
                        <p class="mb-0">Your order #<?= $id ?> has been confirmed. You will receive an email confirmation shortly.</p>
                    </div>
                </div>
            </div>
            
            <?php if (!isLoggedIn()): ?>
                <div class="alert alert-info mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong>Want to track your order easily?</strong><br>
                            <small>Sign in to view all your orders and get order updates.</small>
                        </div>
                        <a href="../login.php" class="btn btn-primary btn-sm">Sign In</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong>View all your orders</strong><br>
                            <small>Check your order history and track all your purchases.</small>
                        </div>
                        <a href="my-orders.php" class="btn btn-outline-primary btn-sm">My Orders</a>
                    </div>
                </div>
            <?php endif; ?>
            <?php unset($_SESSION['order_just_placed']); ?>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-semibold mb-1">Order #<?= $id ?></h1>
                <p class="text-muted mb-0">Order details</p>
            </div>
            <?php if (isLoggedIn()): ?>
                <a href="my-orders.php" class="btn btn-outline-secondary btn-sm">← Back to Orders</a>
            <?php else: ?>
                <a href="../index.php" class="btn btn-outline-secondary btn-sm">← Continue Shopping</a>
            <?php endif; ?>
        </div>

        <?php displayFlashMessage(); ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
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
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Order Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get status history
                        $stmt = $pdo->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC");
                        $stmt->execute([$id]);
                        $statusHistory = $stmt->fetchAll();
                        
                        $statuses = ['pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
                        $currentStatusIndex = array_search($order['status'], array_keys($statuses));
                        ?>
                        <div class="timeline" style="position: relative; padding-left: 2rem;">
                            <div style="position: absolute; left: 0.5rem; top: 0; bottom: 0; width: 2px; background: #dee2e6;"></div>
                            <?php foreach ($statuses as $status => $label): ?>
                                <?php if ($status === 'cancelled' && $order['status'] !== 'cancelled') continue; ?>
                                <?php
                                $statusIndex = array_search($status, array_keys($statuses));
                                $isActive = $status === $order['status'];
                                $isCompleted = $statusIndex < $currentStatusIndex;
                                ?>
                                <div class="mb-3" style="position: relative;">
                                    <div style="position: absolute; left: -1.75rem; top: 0.25rem; width: 12px; height: 12px; border-radius: 50%; background: <?= $isActive ? '#0d6efd' : ($isCompleted ? '#198754' : '#6c757d') ?>; border: 2px solid white; box-shadow: 0 0 0 2px <?= $isActive ? '#0d6efd' : ($isCompleted ? '#198754' : '#dee2e6') ?>;"></div>
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-semibold"><?= $label ?></h6>
                                            <?php if ($isActive): ?>
                                                <p class="text-primary small mb-0">Current status</p>
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
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-semibold">Shipping Address</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                        <p class="text-muted small mt-2 mb-0">
                            <strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($order['billing_address'])): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0 fw-semibold">Billing Address</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($order['billing_address'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
// Send welcome email AFTER all HTML output is complete
if ($pendingWelcomeEmail) {
    @ob_start();
    try {
        @require_once __DIR__ . '/../config/email.php';
        if (function_exists('sendWelcomeEmail')) {
            @sendWelcomeEmail($pendingWelcomeEmail['email'], $pendingWelcomeEmail['name']);
        }
    } catch (Exception $e) {
        @error_log("Welcome email error: " . $e->getMessage());
    }
    @ob_end_clean();
}
?>
