<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$productId = (int)($_GET['product_id'] ?? 0);

if ($productId <= 0) {
    setFlashMessage('error', 'Invalid product ID.');
    redirect('index.php');
}

// Get product info
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect('index.php');
}

// Get existing variants
$variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_type, variant_value");
$variants->execute([$productId]);
$variants = $variants->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_variant'])) {
        $variantType = sanitize($_POST['variant_type'] ?? '');
        $variantValue = sanitize($_POST['variant_value'] ?? '');
        $priceModifier = $_POST['price_modifier'] ?? 0;
        $stock = (int)($_POST['stock'] ?? 0);
        $sku = sanitize($_POST['sku'] ?? '');
        
        if (empty($variantType) || empty($variantValue)) {
            setFlashMessage('error', 'Variant type and value are required.');
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO product_variants (product_id, variant_type, variant_value, price_modifier, stock, sku) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$productId, $variantType, $variantValue, $priceModifier, $stock, $sku ?: null]);
                setFlashMessage('success', 'Variant added successfully!');
                redirect('variants.php?product_id=' . $productId);
            } catch (PDOException $e) {
                setFlashMessage('error', 'Failed to add variant. It may already exist.');
            }
        }
    } elseif (isset($_POST['delete_variant'])) {
        $variantId = (int)$_POST['variant_id'];
        $stmt = $pdo->prepare("DELETE FROM product_variants WHERE id = ? AND product_id = ?");
        $stmt->execute([$variantId, $productId]);
        setFlashMessage('success', 'Variant deleted successfully!');
        redirect('variants.php?product_id=' . $productId);
    }
}

$pageTitle = 'Product Variants';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage variants for: <strong><?= htmlspecialchars($product['name']) ?></strong></p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">← Back to Products</a>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Add Variant</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="variant_type" class="form-label">Variant Type *</label>
                            <select class="form-select" id="variant_type" name="variant_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="size">Size</option>
                                <option value="color">Color</option>
                                <option value="style">Style</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="variant_value" class="form-label">Variant Value *</label>
                            <input type="text" class="form-control" id="variant_value" name="variant_value" required
                                   placeholder="e.g., Small, Red, etc.">
                        </div>
                        
                        <div class="mb-3">
                            <label for="price_modifier" class="form-label">Price Modifier</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price_modifier" name="price_modifier" 
                                       value="0" step="0.01">
                            </div>
                            <small class="text-muted">Additional amount (can be negative for discount)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock" name="stock" 
                                   value="0" min="0">
                        </div>
                        
                        <div class="mb-3">
                            <label for="sku" class="form-label">SKU (Optional)</label>
                            <input type="text" class="form-control" id="sku" name="sku" 
                                   placeholder="Product SKU">
                        </div>
                        
                        <button type="submit" name="add_variant" class="btn btn-primary w-100">Add Variant</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Existing Variants</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($variants)): ?>
                        <div class="text-center py-5 text-muted">
                            <p class="mb-0">No variants added yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Price Modifier</th>
                                        <th>Stock</th>
                                        <th>SKU</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variants as $variant): ?>
                                        <tr>
                                            <td class="text-capitalize"><?= htmlspecialchars($variant['variant_type']) ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($variant['variant_value']) ?></td>
                                            <td>
                                                <?php if ($variant['price_modifier'] > 0): ?>
                                                    +<?= formatPrice($variant['price_modifier']) ?>
                                                <?php elseif ($variant['price_modifier'] < 0): ?>
                                                    <?= formatPrice($variant['price_modifier']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No change</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $variant['stock'] ?></td>
                                            <td><code class="text-muted"><?= htmlspecialchars($variant['sku'] ?: '-') ?></code></td>
                                            <td>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this variant?')">
                                                    <input type="hidden" name="variant_id" value="<?= $variant['id'] ?>">
                                                    <button type="submit" name="delete_variant" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

