<?php
// E-commerce Shop Homepage
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/settings.php';

$pdo = getDB();

// Get active category (by slug)
$activeCategorySlug = $_GET['category'] ?? 'all';

// Get all categories for navigation
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Get featured products (8 products)
$featuredProducts = $pdo->query("SELECT p.*, c.name as category_name, c.slug as category_slug 
                                 FROM products p 
                                 LEFT JOIN categories c ON p.category_id = c.id 
                                 WHERE p.status = 'active' AND p.featured = 1
                                 ORDER BY p.created_at DESC 
                                 LIMIT 8")->fetchAll();

// Get homepage category settings
$homepageCategory1 = getSetting('homepage_category_1');
$homepageCategory2 = getSetting('homepage_category_2');

// Get products for first category (4 products)
$category1Products = [];
if ($homepageCategory1) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.status = 'active' AND p.category_id = ?
                           ORDER BY p.created_at DESC 
                           LIMIT 4");
    $stmt->execute([$homepageCategory1]);
    $category1Products = $stmt->fetchAll();
    $category1Info = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $category1Info->execute([$homepageCategory1]);
    $category1Name = $category1Info->fetchColumn();
}

// Get products for second category (4 products)
$category2Products = [];
if ($homepageCategory2) {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.status = 'active' AND p.category_id = ?
                           ORDER BY p.created_at DESC 
                           LIMIT 4");
    $stmt->execute([$homepageCategory2]);
    $category2Products = $stmt->fetchAll();
    $category2Info = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $category2Info->execute([$homepageCategory2]);
    $category2Name = $category2Info->fetchColumn();
}

// Get products for category filter (if category is selected)
$products = [];
if ($activeCategorySlug !== 'all') {
    $query = "SELECT p.*, c.name as category_name, c.slug as category_slug 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active' AND c.slug = ?
              ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$activeCategorySlug]);
    $products = $stmt->fetchAll();
}

// Helper function for cart count
function getCartCount(): int {
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

// Set page title and SEO tags before including header
require_once __DIR__ . '/config/seo.php';
$pageTitle = 'Cur1 Fashion Store';
$pageDescription = getSetting('header_description', 'Premium quality men & women clothing with modern designs.');
$headerImage = getSetting('header_image');

// Store SEO data for header include
$seoTitle = getSEOTitle($pageTitle);
$seoTags = generateSEOTags($pageTitle, $pageDescription, null, $headerImage);

// Set page title for header include (it will add " - Cur1 Fashion" automatically)
$pageTitle = $seoTitle;
$headerOnly = true; // Tell header.php to only output the header section, not full HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $seoTitle ?></title>
    <?= $seoTags ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/shop.css">
</head>
<body>
<?php
// Include the shared header (it will use the $activeCategorySlug and $categories variables)
include __DIR__ . '/includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars(getSetting('header_title', 'Discover Your Everyday Style')) ?></h1>
                    <p class="lead text-muted mb-4">
                        <?= htmlspecialchars(getSetting('header_description', 'Premium quality men & women clothing with modern designs. Hand-picked outfits that look good on every screen.')) ?>
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#products" class="btn btn-primary btn-lg px-4">Shop Collection</a>
                        <?php if (!empty($categories)): ?>
                            <?php foreach (array_slice($categories, 0, 2) as $cat): ?>
                                <a href="index.php?category=<?= htmlspecialchars($cat['slug']) ?>" class="btn btn-outline-primary btn-lg px-4">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <div class="hero-image-wrapper">
                        <div class="hero-badge hero-badge-top">New Season</div>
                        <div class="hero-badge hero-badge-bottom">Up to 40% Off</div>
                        <?php 
                        $headerImage = getSetting('header_image');
                        if ($headerImage): ?>
                            <img src="<?= htmlspecialchars($headerImage) ?>"
                                 alt="Fashion banner" class="img-fluid rounded-4 shadow-lg hero-image">
                        <?php else: ?>
                            <img src="https://images.pexels.com/photos/7671163/pexels-photo-7671163.jpeg"
                                 alt="Fashion banner" class="img-fluid rounded-4 shadow-lg hero-image">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-5 bg-light">
        <div class="container">
            <?php if ($activeCategorySlug === 'all'): ?>
                <!-- Featured Products Section -->
                <?php if (!empty($featuredProducts)): ?>
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="h3 fw-semibold mb-1">Featured Products</h2>
                                <p class="text-muted mb-0">Hand-picked outfits to refresh your wardrobe.</p>
                            </div>
                            <a href="index.php?category=all" class="btn btn-outline-primary">View All</a>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($featuredProducts as $product): ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- First Category Section -->
                <?php if (!empty($category1Products) && !empty($category1Name)): ?>
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="h3 fw-semibold mb-1"><?= htmlspecialchars($category1Name) ?> Collection</h2>
                                <p class="text-muted mb-0">Explore our <?= htmlspecialchars(strtolower($category1Name)) ?> collection.</p>
                            </div>
                            <a href="index.php?category=<?= htmlspecialchars($categories[array_search($homepageCategory1, array_column($categories, 'id'))]['slug'] ?? '') ?>" class="btn btn-outline-primary">View All</a>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($category1Products as $product): ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Second Category Section -->
                <?php if (!empty($category2Products) && !empty($category2Name)): ?>
                    <div class="mb-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="h3 fw-semibold mb-1"><?= htmlspecialchars($category2Name) ?> Collection</h2>
                                <p class="text-muted mb-0">Discover our <?= htmlspecialchars(strtolower($category2Name)) ?> selection.</p>
                            </div>
                            <a href="index.php?category=<?= htmlspecialchars($categories[array_search($homepageCategory2, array_column($categories, 'id'))]['slug'] ?? '') ?>" class="btn btn-outline-primary">View All</a>
                        </div>
                        <div class="row g-4">
                            <?php foreach ($category2Products as $product): ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Category Filter View -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h3 fw-semibold mb-1">
                            <?php
                            $activeCat = array_filter($categories, fn($c) => $c['slug'] === $activeCategorySlug);
                            echo !empty($activeCat) ? htmlspecialchars(reset($activeCat)['name'] . ' Collection') : 'Products';
                            ?>
                        </h2>
                        <p class="text-muted mb-0">Hand-picked outfits to refresh your wardrobe.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <?php if (empty($products)): ?>
                        <div class="col-12">
                            <div class="text-center text-muted py-5">
                                <p class="mb-3">No products found in this category.</p>
                                <a href="index.php?category=all" class="btn btn-primary">View All Products</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <?php include __DIR__ . '/includes/product_card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
