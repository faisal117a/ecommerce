<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/auth.php';

$pageTitle = 'About Us';
include __DIR__ . '/includes/header.php';
?>

<main class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="h2 fw-bold mb-4">About Cur1 Fashion</h1>
                
                <div class="mb-4">
                    <p class="lead">Welcome to Cur1 Fashion, your premier destination for modern, stylish clothing.</p>
                    
                    <p>At Cur1 Fashion, we believe that everyone deserves to look and feel their best. That's why we curate a collection of premium quality men's and women's clothing that combines modern designs with timeless style.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">Our Mission</h3>
                    <p>Our mission is to provide high-quality, fashionable clothing that empowers our customers to express their unique style. We hand-pick every item in our collection to ensure it meets our standards for quality, comfort, and style.</p>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">Why Choose Us</h3>
                    <ul>
                        <li>Premium quality materials and craftsmanship</li>
                        <li>Modern designs that stay ahead of trends</li>
                        <li>Affordable prices without compromising on quality</li>
                        <li>Excellent customer service</li>
                        <li>Fast and reliable delivery</li>
                    </ul>
                    
                    <h3 class="h5 fw-bold mt-4 mb-3">Contact Us</h3>
                    <p>Have questions or need assistance? <a href="contact.php">Contact us</a> and we'll be happy to help!</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

