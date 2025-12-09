<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Terms & Conditions';
include __DIR__ . '/includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="h2 fw-bold mb-4">Terms & Conditions</h1>
                
                <div class="mb-4">
                    <p class="text-muted small">Last updated: <?= date('F d, Y') ?></p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">1. Acceptance of Terms</h3>
                    <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">2. Products and Pricing</h3>
                    <p>We reserve the right to change prices and product availability at any time. All prices are in USD unless otherwise stated.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">3. Orders</h3>
                    <p>When you place an order, you are making an offer to purchase products. We reserve the right to accept or reject any order. Orders are subject to product availability.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">4. Payment</h3>
                    <p>We currently accept Cash on Delivery (COD) as a payment method. Payment is due upon delivery of the products.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">5. Shipping and Delivery</h3>
                    <p>We will make every effort to deliver your order in a timely manner. Delivery times are estimates and not guaranteed. Risk of loss passes to you upon delivery.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">6. Returns and Refunds</h3>
                    <p>Please review our return policy. Items must be returned in their original condition within the specified time period.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">7. Limitation of Liability</h3>
                    <p>Cur1 Fashion shall not be liable for any indirect, incidental, special, or consequential damages arising out of or in connection with your use of this website.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">8. Contact Us</h3>
                    <p>If you have questions about these Terms & Conditions, please <a href="contact.php">contact us</a>.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

