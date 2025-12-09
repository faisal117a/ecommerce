<?php
// Suppress all errors and warnings to prevent output
error_reporting(0);
ini_set('display_errors', 0);

// Suppress errors to prevent output
@ini_set('display_errors', 0);
@error_reporting(0);

// Start output buffering to prevent any output before headers
while (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/auth.php';

$pdo = getDB();

// Handle coupon removal
if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['applied_coupon_code']);
    header('Location: checkout.php');
    exit;
}

// Check if cart is empty
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    setFlashMessage('error', 'Your cart is empty.');
    header('Location: ../cart.php');
    exit;
}

// Build cart items
$cartItems = [];
$cartTotal = 0.0;
$discountAmount = 0.0;
$couponCode = '';
$coupon = null;

foreach ($_SESSION['cart'] as $productId => $qty) {
    $stmt = $pdo->prepare("SELECT p.* FROM products p WHERE p.id = ? AND p.status = 'active'");
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
    }
}

if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty.');
    header('Location: ../cart.php');
    exit;
}

// Handle coupon application
if (isset($_POST['apply_coupon']) && !empty($_POST['coupon_code'])) {
    $couponCode = strtoupper(sanitize($_POST['coupon_code']));
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
    $stmt->execute([$couponCode]);
    $coupon = $stmt->fetch();
    
    if ($coupon) {
        // Check expiry
        if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
            $coupon = null;
            setFlashMessage('error', 'This coupon has expired.');
        } elseif ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
            $coupon = null;
            setFlashMessage('error', 'This coupon has reached its usage limit.');
        } elseif ($coupon['min_purchase'] > 0 && $cartTotal < $coupon['min_purchase']) {
            $coupon = null;
            setFlashMessage('error', 'Minimum purchase amount not met for this coupon.');
        } else {
            // Calculate discount
            if ($coupon['type'] === 'percentage') {
                $discountAmount = ($cartTotal * $coupon['value']) / 100;
            } else {
                $discountAmount = min($coupon['value'], $cartTotal);
            }
            $_SESSION['applied_coupon_code'] = $couponCode;
            setFlashMessage('success', 'Coupon applied successfully!');
        }
    } else {
        setFlashMessage('error', 'Invalid or inactive coupon code.');
    }
    
    // Redirect back to checkout to show the result
    header('Location: checkout.php');
    exit;
}

// Get coupon from session if already applied
if (!$coupon && isset($_SESSION['applied_coupon_code'])) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
    $stmt->execute([$_SESSION['applied_coupon_code']]);
    $coupon = $stmt->fetch();
    if ($coupon) {
        $couponCode = $coupon['code'];
        if ($coupon['type'] === 'percentage') {
            $discountAmount = ($cartTotal * $coupon['value']) / 100;
        } else {
            $discountAmount = min($coupon['value'], $cartTotal);
        }
    }
}

$finalTotal = max(0, $cartTotal - $discountAmount);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zip = sanitize($_POST['zip'] ?? '');
    $sameAsShipping = isset($_POST['same_as_shipping']) && $_POST['same_as_shipping'] === '1';
    $billingName = sanitize($_POST['billing_name'] ?? '');
    $billingAddress = sanitize($_POST['billing_address'] ?? '');
    $billingCity = sanitize($_POST['billing_city'] ?? '');
    $billingState = sanitize($_POST['billing_state'] ?? '');
    $billingZip = sanitize($_POST['billing_zip'] ?? '');
    $password = $_POST['password'] ?? '';
    $paymentMethod = 'cash_on_delivery';
    $appliedCouponCode = $_SESSION['applied_coupon_code'] ?? null;
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email) || !isValidEmail($email)) {
        $errors[] = 'Valid email is required.';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    }
    
    if (empty($address)) {
        $errors[] = 'Shipping address is required.';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required.';
    }
    
    if (empty($state)) {
        $errors[] = 'State is required.';
    }
    
    if (empty($zip)) {
        $errors[] = 'ZIP code is required.';
    }
    
    // Billing address validation
    if (!$sameAsShipping) {
        if (empty($billingName)) {
            $errors[] = 'Billing name is required.';
        }
        if (empty($billingAddress)) {
            $errors[] = 'Billing address is required.';
        }
        if (empty($billingCity)) {
            $errors[] = 'Billing city is required.';
        }
        if (empty($billingState)) {
            $errors[] = 'Billing state is required.';
        }
        if (empty($billingZip)) {
            $errors[] = 'Billing ZIP code is required.';
        }
    }
    
    // Password validation and account handling
    $userId = null;
    $accountCreated = false;
    
    if (isLoggedIn()) {
        // Logged-in user: verify password
        if (empty($password)) {
            $errors[] = 'Password is required for verification.';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors[] = 'Incorrect password. Please verify your password.';
            } else {
                $userId = $_SESSION['user_id'];
            }
        }
    } else {
        // Guest checkout: check if email exists, create account if not
        if (empty($password)) {
            $errors[] = 'Password is required. We will create an account for you to track your order.';
        } else {
            if (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters long.';
            } else {
                // Check if email already exists
                $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $existingUser = $stmt->fetch();
                
                if ($existingUser) {
                    // Email exists - ask user to sign in
                    $errors[] = 'An account with this email already exists. Please <a href="../login.php?redirect=' . urlencode('orders/checkout.php') . '" class="alert-link">sign in</a> to continue, or use a different email address.';
                } else {
                    // Create new account automatically
                    try {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'customer')");
                        $stmt->execute([$name, $email, $passwordHash]);
                        $userId = $pdo->lastInsertId();
                        $accountCreated = true;
                        
                        // Auto-login the new user
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_role'] = 'customer';
                        
                        // Store email sending flag in session to send after redirect
                        // This prevents any output during checkout
                        $_SESSION['send_welcome_email'] = ['email' => $email, 'name' => $name];
                    } catch (PDOException $e) {
                        $errors[] = 'Failed to create account. Please try again.';
                        error_log("Account creation error: " . $e->getMessage());
                    }
                }
            }
        }
    }
    
    if (empty($errors)) {
        $fullAddress = "$address, $city, $state $zip";
        $billingFullAddress = $sameAsShipping ? $fullAddress : "$billingAddress, $billingCity, $billingState $billingZip";
        
        // Create order
        try {
            $pdo->beginTransaction();
            
            // $userId is already set from password validation above
            
            // Calculate final total with coupon
            $finalOrderTotal = $cartTotal;
            $orderDiscount = 0;
            if ($appliedCouponCode) {
                $couponStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active'");
                $couponStmt->execute([$appliedCouponCode]);
                $appliedCoupon = $couponStmt->fetch();
                if ($appliedCoupon) {
                    if ($appliedCoupon['type'] === 'percentage') {
                        $orderDiscount = ($cartTotal * $appliedCoupon['value']) / 100;
                    } else {
                        $orderDiscount = min($appliedCoupon['value'], $cartTotal);
                    }
                    $finalOrderTotal = $cartTotal - $orderDiscount;
                    
                    // Update coupon usage count
                    $updateStmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
                    $updateStmt->execute([$appliedCoupon['id']]);
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, billing_address, phone, payment_method, coupon_code, discount_amount) 
                                   VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $finalOrderTotal, $fullAddress, $billingFullAddress, $phone, $paymentMethod, $appliedCouponCode, $orderDiscount]);
            $orderId = $pdo->lastInsertId();
            
            // Clear applied coupon from session
            unset($_SESSION['applied_coupon_code']);
            
            // Create order items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $item['qty'],
                    $item['product']['price']
                ]);
            }
            
            $pdo->commit();
            
            // Clear cart and set session data BEFORE any potential output
            $_SESSION['cart'] = [];
            $_SESSION['order_just_placed'] = $orderId;
            
            if ($accountCreated) {
                setFlashMessage('success', 'Account created and order placed successfully! Order #' . $orderId);
            } else {
                setFlashMessage('success', 'Order placed successfully! Order #' . $orderId);
            }
            
            // Close database connection
            $pdo = null;
            
            // CRITICAL: Clean ALL output buffers before redirect
            @ob_end_clean();
            while (@ob_get_level() > 0) {
                @ob_end_clean();
            }
            
            // Redirect immediately - suppress all errors
            $redirectUrl = 'redirect.php?id=' . (int)$orderId;
            if (!@headers_sent()) {
                @header('Location: ' . $redirectUrl, true, 302);
                @exit;
            }
            // Fallback if headers sent
            die('<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($redirectUrl) . '">');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to place order. Please try again.';
            error_log("Order creation error: " . $e->getMessage());
        }
    }
}

// Pre-fill form if user is logged in
$user = isLoggedIn() ? getCurrentUser() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Cur1 Fashion</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shop.css">
</head>
<body>
<header class="shadow-sm sticky-top bg-white">
    <nav class="navbar navbar-expand-lg container py-3">
        <a class="navbar-brand fw-bold text-primary" href="../index.php">
            Cur1<span class="text-dark">Fashion</span>
        </a>
    </nav>
</header>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <?php if (!isLoggedIn()): ?>
                    <!-- Tabs for Guest Checkout -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="new-customer-tab" data-bs-toggle="tab" data-bs-target="#new-customer" type="button" role="tab">
                                New Customer
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="signin-tab" data-bs-toggle="tab" data-bs-target="#signin" type="button" role="tab">
                                Sign In
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content">
                        <!-- Sign In Tab -->
                        <div class="tab-pane fade" id="signin" role="tabpanel">
                            <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                                <h2 class="h4 fw-bold mb-4">Sign In to Checkout</h2>
                                <p class="text-muted mb-4">Sign in to your account to checkout faster and track your orders.</p>
                                
                                <form method="POST" action="../login.php">
                                    <input type="hidden" name="redirect" value="orders/checkout.php">
                                    <div class="mb-3">
                                        <label for="login_email" class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" id="login_email" name="email" required
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="login_password" class="form-label fw-semibold">Password</label>
                                        <input type="password" class="form-control" id="login_password" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">Sign In</button>
                                    <div class="text-center">
                                        <a href="../auth/register.php" class="text-decoration-none">Don't have an account? Create one</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- New Customer Tab -->
                        <div class="tab-pane fade show active" id="new-customer" role="tabpanel">
                <?php endif; ?>
                
                <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
                    <h2 class="h4 fw-bold mb-4"><?= isLoggedIn() ? 'Shipping Information' : 'Checkout Information' ?></h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="checkoutForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-semibold">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                       value="<?= htmlspecialchars($_POST['name'] ?? $user['name'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Street Address *</label>
                            <input type="text" class="form-control" id="address" name="address" required
                                   value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label fw-semibold">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required
                                       value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label fw-semibold">State *</label>
                                <input type="text" class="form-control" id="state" name="state" required
                                       value="<?= htmlspecialchars($_POST['state'] ?? '') ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="zip" class="form-label fw-semibold">ZIP Code *</label>
                                <input type="text" class="form-control" id="zip" name="zip" required
                                       value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h3 class="h5 fw-bold mb-3">Billing Information</h3>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="same_as_shipping" name="same_as_shipping" value="1" 
                                       <?= (isset($_POST['same_as_shipping']) && $_POST['same_as_shipping'] === '1') || !isset($_POST['same_as_shipping']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="same_as_shipping">
                                    Same as shipping address
                                </label>
                            </div>
                        </div>
                        
                        <div id="billingFields" style="display: none;">
                            <div class="mb-3">
                                <label for="billing_name" class="form-label fw-semibold">Billing Name *</label>
                                <input type="text" class="form-control" id="billing_name" name="billing_name"
                                       value="<?= htmlspecialchars($_POST['billing_name'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="billing_address" class="form-label fw-semibold">Billing Street Address *</label>
                                <input type="text" class="form-control" id="billing_address" name="billing_address"
                                       value="<?= htmlspecialchars($_POST['billing_address'] ?? '') ?>">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="billing_city" class="form-label fw-semibold">Billing City *</label>
                                    <input type="text" class="form-control" id="billing_city" name="billing_city"
                                           value="<?= htmlspecialchars($_POST['billing_city'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="billing_state" class="form-label fw-semibold">Billing State *</label>
                                    <input type="text" class="form-control" id="billing_state" name="billing_state"
                                           value="<?= htmlspecialchars($_POST['billing_state'] ?? '') ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="billing_zip" class="form-label fw-semibold">Billing ZIP Code *</label>
                                    <input type="text" class="form-control" id="billing_zip" name="billing_zip"
                                           value="<?= htmlspecialchars($_POST['billing_zip'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h3 class="h5 fw-bold mb-3">Coupon Code</h3>
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" id="coupon_code" name="coupon_code" 
                                       placeholder="Enter coupon code" value="<?= htmlspecialchars($couponCode) ?>"
                                       style="text-transform: uppercase;">
                                <button type="submit" name="apply_coupon" class="btn btn-outline-primary">Apply</button>
                            </div>
                            <?php if ($coupon): ?>
                                <div class="alert alert-success mt-2 mb-0">
                                    <strong>Coupon Applied:</strong> <?= htmlspecialchars($couponCode) ?>
                                    <?php if ($coupon['type'] === 'percentage'): ?>
                                        - <?= number_format($coupon['value'], 0) ?>% off
                                    <?php else: ?>
                                        - <?= formatPrice($coupon['value']) ?> off
                                    <?php endif; ?>
                                    <a href="?remove_coupon=1" class="text-danger ms-2">Remove</a>
                                </div>
                                <?php $_SESSION['applied_coupon_code'] = $couponCode; ?>
                            <?php endif; ?>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                <?php if (isLoggedIn()): ?>
                                    Password (for verification) *
                                <?php else: ?>
                                    Create Password *
                                <?php endif; ?>
                            </label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="6">
                            <?php if (isLoggedIn()): ?>
                                <small class="text-muted">Enter your password to confirm this order.</small>
                            <?php else: ?>
                                <small class="text-muted">We'll create an account for you with this email and password so you can track your order. If an account with this email already exists, you'll be asked to sign in.</small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Payment Method:</strong> Cash on Delivery
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                            <a href="../cart.php" class="btn btn-outline-secondary btn-lg">Back to Cart</a>
                        </div>
                    </form>
                </div>
                
                <?php if (!isLoggedIn()): ?>
                        </div> <!-- End New Customer Tab -->
                    </div> <!-- End Tab Content -->
                <?php endif; ?>
                
                <script>
                document.getElementById('same_as_shipping').addEventListener('change', function() {
                    const billingFields = document.getElementById('billingFields');
                    if (this.checked) {
                        billingFields.style.display = 'none';
                        // Remove required attributes
                        document.getElementById('billing_name').removeAttribute('required');
                        document.getElementById('billing_address').removeAttribute('required');
                        document.getElementById('billing_city').removeAttribute('required');
                        document.getElementById('billing_state').removeAttribute('required');
                        document.getElementById('billing_zip').removeAttribute('required');
                    } else {
                        billingFields.style.display = 'block';
                        // Add required attributes
                        document.getElementById('billing_name').setAttribute('required', 'required');
                        document.getElementById('billing_address').setAttribute('required', 'required');
                        document.getElementById('billing_city').setAttribute('required', 'required');
                        document.getElementById('billing_state').setAttribute('required', 'required');
                        document.getElementById('billing_zip').setAttribute('required', 'required');
                    }
                });
                // Trigger on page load
                document.getElementById('same_as_shipping').dispatchEvent(new Event('change'));
                </script>
            </div>
            
            <div class="col-lg-4">
                <div class="bg-white rounded-4 shadow-sm p-4">
                    <h3 class="h5 fw-bold mb-3">Order Summary</h3>
                    
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-semibold small"><?= htmlspecialchars($item['product']['name']) ?></div>
                                <div class="text-muted small">Qty: <?= $item['qty'] ?></div>
                            </div>
                            <div class="text-end">
                                <div class="fw-semibold"><?= formatPrice($item['lineTotal']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span><?= formatPrice($cartTotal) ?></span>
                        </div>
                        <?php if ($discountAmount > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Discount (<?= htmlspecialchars($couponCode) ?>)</span>
                                <span class="text-success">-<?= formatPrice($discountAmount) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-5"><?= formatPrice($finalTotal) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="py-4 border-top bg-white mt-auto">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <span class="text-muted small">
            &copy; <?= date('Y') ?> Cur1 Fashion. All rights reserved.
        </span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

