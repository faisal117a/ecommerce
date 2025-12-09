<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/auth.php';

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
}

// Get product variants
$variants = [];
$variantsByType = [];
if ($product) {
    $variantStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_type, variant_value");
    $variantStmt->execute([$id]);
    $variants = $variantStmt->fetchAll();
    
    // Group variants by type
    foreach ($variants as $variant) {
        $type = $variant['variant_type'];
        if (!isset($variantsByType[$type])) {
            $variantsByType[$type] = [];
        }
        $variantsByType[$type][] = $variant;
    }
}

$pageTitle = $product ? htmlspecialchars($product['name'], ENT_QUOTES) : 'Product Not Found';
include __DIR__ . '/includes/header.php';
?>

<main class="py-5 bg-light min-vh-100">
    <div class="container">
        <?php if (!$product): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                <h1 class="h4 mb-3">Product not found</h1>
                <p class="text-muted mb-4">The item you are looking for does not exist or may have been removed.</p>
                <a href="index.php" class="btn btn-primary px-4">Back to shop</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="bg-white rounded-4 shadow-sm p-3">
                        <img src="<?= htmlspecialchars(getImageUrl($product['image_url']), ENT_QUOTES) ?>"
                             alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                             class="img-fluid rounded-3 product-detail-image">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-white rounded-4 shadow-sm p-4 h-100 d-flex flex-column">
                        <span class="text-uppercase small text-muted mb-1">
                            <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
                        </span>
                        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                            <h1 class="h3 fw-semibold mb-0">
                                <?= htmlspecialchars($product['name'], ENT_QUOTES) ?>
                            </h1>
                            <?php if (!empty($product['badge'])): ?>
                                <span class="badge bg-primary" style="font-size: 0.7rem; padding: 0.3rem 0.6rem; white-space: nowrap;"><?= htmlspecialchars($product['badge'], ENT_QUOTES) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($product['description'])): ?>
                            <p class="mb-3 text-muted">
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </p>
                        <?php else: ?>
                            <p class="mb-3 text-muted">
                                Designed for everyday comfort and style. Soft, breathable fabric with a modern tailored fit
                                that works for any occasion.
                            </p>
                        <?php endif; ?>

                        <div class="mb-3">
                            <span class="h4 text-primary fw-bold me-2"><?= formatPrice($product['price']) ?></span>
                            <span class="text-muted small">Inclusive of all taxes</span>
                        </div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <div class="mb-3">
                                <span class="badge bg-success">In Stock (<?= $product['stock_quantity'] ?> available)</span>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <span class="badge bg-danger">Out of Stock</span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($variantsByType)): ?>
                            <div class="mb-3">
                                <?php foreach ($variantsByType as $type => $typeVariants): ?>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold"><?= ucfirst($type) ?> *</label>
                                        <select class="form-select variant-select" name="variant_<?= $type ?>" data-type="<?= $type ?>" required>
                                            <option value="">-- Select <?= ucfirst($type) ?> --</option>
                                            <?php foreach ($typeVariants as $variant): ?>
                                                <option value="<?= $variant['id'] ?>" 
                                                        data-price="<?= $variant['price_modifier'] ?>"
                                                        data-stock="<?= $variant['stock'] ?>">
                                                    <?= htmlspecialchars($variant['variant_value']) ?>
                                                    <?php if ($variant['price_modifier'] != 0): ?>
                                                        (<?= $variant['price_modifier'] > 0 ? '+' : '' ?><?= formatPrice($variant['price_modifier']) ?>)
                                                    <?php endif; ?>
                                                    <?php if ($variant['stock'] <= 0): ?>
                                                        - Out of Stock
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mb-3">
                                <span class="h5 text-primary fw-bold" id="variant-price"><?= formatPrice($product['price']) ?></span>
                                <span class="text-muted small">Inclusive of all taxes</span>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="cart.php" class="mt-2" id="addToCartForm">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                            <?php if (!empty($variantsByType)): ?>
                                <?php foreach ($variantsByType as $type => $typeVariants): ?>
                                    <input type="hidden" name="variant_<?= $type ?>" id="variant_input_<?= $type ?>" value="">
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-lg px-4 mb-2" 
                                    id="addToCartBtn"
                                    <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                                <?= $product['stock_quantity'] > 0 ? 'Add to cart' : 'Out of Stock' ?>
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-lg px-4 ms-0 ms-md-2">
                                Continue shopping
                            </a>
                        </form>
                        
                        <?php if (!empty($variantsByType)): ?>
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const variantSelects = document.querySelectorAll('.variant-select');
                            const basePrice = <?= $product['price'] ?>;
                            const priceDisplay = document.getElementById('variant-price');
                            const addToCartBtn = document.getElementById('addToCartBtn');
                            
                            function updatePrice() {
                                let totalPrice = basePrice;
                                let allSelected = true;
                                let hasStock = true;
                                
                                variantSelects.forEach(select => {
                                    const selectedOption = select.options[select.selectedIndex];
                                    if (selectedOption.value) {
                                        const priceModifier = parseFloat(selectedOption.dataset.price || 0);
                                        const stock = parseInt(selectedOption.dataset.stock || 0);
                                        totalPrice += priceModifier;
                                        
                                        // Update hidden input
                                        const type = select.dataset.type;
                                        document.getElementById('variant_input_' + type).value = selectedOption.value;
                                        
                                        if (stock <= 0) {
                                            hasStock = false;
                                        }
                                    } else {
                                        allSelected = false;
                                    }
                                });
                                
                                priceDisplay.textContent = '$' + totalPrice.toFixed(2);
                                
                                if (allSelected && hasStock) {
                                    addToCartBtn.disabled = false;
                                    addToCartBtn.textContent = 'Add to cart';
                                } else if (!allSelected) {
                                    addToCartBtn.disabled = true;
                                    addToCartBtn.textContent = 'Please select all variants';
                                } else if (!hasStock) {
                                    addToCartBtn.disabled = true;
                                    addToCartBtn.textContent = 'Selected variant out of stock';
                                }
                            }
                            
                            variantSelects.forEach(select => {
                                select.addEventListener('change', updatePrice);
                            });
                            
                            updatePrice();
                        });
                        </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php 
// Include footer (which has closing body/html tags)
include __DIR__ . '/includes/footer.php'; 
?>
