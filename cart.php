<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/auth.php';

$pdo = getDB();

// Initialize cart
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0 && ($action === 'add' || $action === 'remove' || $action === 'decrease')) {
        // Verify product exists and is active
        $stmt = $pdo->prepare("SELECT id, price, stock_quantity FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product) {
            if (!isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id] = 0;
            }
            
            if ($action === 'add') {
                // Check stock
                if ($product['stock_quantity'] > $_SESSION['cart'][$id]) {
                    $_SESSION['cart'][$id]++;
                }
            } elseif ($action === 'decrease') {
                $_SESSION['cart'][$id]--;
                if ($_SESSION['cart'][$id] <= 0) {
                    unset($_SESSION['cart'][$id]);
                }
            } elseif ($action === 'remove') {
                unset($_SESSION['cart'][$id]);
            }
        }
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    header('Location: cart.php');
    exit;
}

// Build cart items from database
$cartItems = [];
$cartTotal = 0.0;

foreach ($_SESSION['cart'] as $productId => $qty) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        $lineTotal = $product['price'] * $qty;
        $cartTotal += $lineTotal;
        $cartItems[] = [
            'product' => $product,
            'qty' => $qty,
            'lineTotal' => $lineTotal,
        ];
    } else {
        // Remove invalid product from cart
        unset($_SESSION['cart'][$productId]);
    }
}

$pageTitle = 'Your Cart';
include __DIR__ . '/includes/header.php';
?>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-semibold mb-1">Your Cart</h1>
                <p class="text-muted mb-0">
                    <?= count($cartItems) > 0 ? 'Review your selected items.' : 'Your cart is currently empty.' ?>
                </p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Continue shopping</a>
        </div>

        <?php if (count($cartItems) === 0): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <p class="mb-3 text-muted">Looks like you haven't added anything yet.</p>
                <a href="index.php" class="btn btn-primary px-4">Browse collection</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="bg-white rounded-4 shadow-sm p-3 p-md-4">
                        <?php foreach ($cartItems as $item): ?>
                            <?php $p = $item['product']; ?>
                            <div class="cart-item d-flex flex-column flex-md-row align-items-md-center gap-3 py-3 border-bottom">
                                <div class="cart-item-image-wrapper">
                                    <img src="<?= htmlspecialchars(getImageUrl($p['image_url']), ENT_QUOTES) ?>"
                                         alt="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                         class="cart-item-image rounded-3">
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">
                                        <a href="product.php?id=<?= (int)$p['id'] ?>" class="product-link text-decoration-none">
                                            <?= htmlspecialchars($p['name'], ENT_QUOTES) ?>
                                        </a>
                                    </h5>
                                    <p class="text-muted mb-2 small">
                                        <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                                    </p>
                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <span class="fw-semibold"><?= formatPrice($p['price']) ?></span>
                                        <div class="d-flex align-items-center gap-1">
                                            <form method="post" action="cart.php" class="m-0">
                                                <input type="hidden" name="action" value="decrease">
                                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                <button class="btn btn-sm btn-outline-secondary" type="submit">-</button>
                                            </form>
                                            <span class="px-2"><?= (int)$item['qty'] ?></span>
                                            <form method="post" action="cart.php" class="m-0">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                                <button class="btn btn-sm btn-outline-secondary" type="submit">+</button>
                                            </form>
                                        </div>
                                        <span class="ms-auto fw-semibold"><?= formatPrice($item['lineTotal']) ?></span>
                                        <form method="post" action="cart.php" class="m-0 ms-md-3">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button class="btn btn-sm btn-link text-danger text-decoration-none" type="submit">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="bg-white rounded-4 shadow-sm p-3 p-md-4">
                        <h2 class="h5 fw-semibold mb-3">Summary</h2>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span><?= formatPrice($cartTotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-semibold">Total</span>
                            <span class="fw-semibold"><?= formatPrice($cartTotal) ?></span>
                        </div>
                        <a href="orders/checkout.php" class="btn btn-primary w-100 mb-2">
                            Proceed to Checkout
                        </a>
                        <form method="post" action="cart.php">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
                                Clear cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
