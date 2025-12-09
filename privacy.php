<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'Privacy Policy';
include __DIR__ . '/includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="h2 fw-bold mb-4">Privacy Policy</h1>
                
                <div class="mb-4">
                    <p class="text-muted small">Last updated: <?= date('F d, Y') ?></p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">1. Information We Collect</h3>
                    <p>We collect information that you provide directly to us, including:</p>
                    <ul>
                        <li>Name and contact information</li>
                        <li>Email address</li>
                        <li>Shipping and billing addresses</li>
                        <li>Payment information</li>
                        <li>Order history</li>
                    </ul>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">2. How We Use Your Information</h3>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Process and fulfill your orders</li>
                        <li>Send you order confirmations and updates</li>
                        <li>Respond to your inquiries</li>
                        <li>Improve our services</li>
                        <li>Send you marketing communications (with your consent)</li>
                    </ul>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">3. Information Sharing</h3>
                    <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only with service providers who assist us in operating our website and conducting our business.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">4. Data Security</h3>
                    <p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">5. Your Rights</h3>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal information</li>
                        <li>Correct inaccurate information</li>
                        <li>Request deletion of your information</li>
                        <li>Opt-out of marketing communications</li>
                    </ul>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">6. Contact Us</h3>
                    <p>If you have questions about this Privacy Policy, please <a href="contact.php">contact us</a>.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

