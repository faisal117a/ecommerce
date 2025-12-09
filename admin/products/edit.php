<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
$product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$product->execute([$id]);
$product = $product->fetch();

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    redirect('index.php');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $stockQuantity = (int)($_POST['stock_quantity'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $badge = sanitize($_POST['badge'] ?? '');
    $imageUrl = $product['image_url'];
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Product name is required.';
    }
    
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = 'Valid price is required.';
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        $status = 'active';
    }
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $validation = validateImageUpload($_FILES['image']);
        
        if (!$validation['valid']) {
            $errors[] = $validation['error'];
        } else {
            // Get category slug for folder structure
            $categorySlug = 'unisex'; // Default
            if ($categoryId > 0) {
                $catStmt = $pdo->prepare("SELECT slug FROM categories WHERE id = ?");
                $catStmt->execute([$categoryId]);
                $category = $catStmt->fetch();
                if ($category) {
                    $categorySlug = $category['slug'];
                }
            } else {
                // If no category selected, try to get from existing product
                $catStmt = $pdo->prepare("SELECT c.slug FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
                $catStmt->execute([$id]);
                $existing = $catStmt->fetch();
                if ($existing && $existing['slug']) {
                    $categorySlug = $existing['slug'];
                }
            }
            
            $uploadDir = __DIR__ . '/../../assets/uploads/' . $categorySlug . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('product_') . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                // Delete old image if exists
                if ($imageUrl && file_exists(__DIR__ . '/../../' . $imageUrl)) {
                    @unlink(__DIR__ . '/../../' . $imageUrl);
                }
                $imageUrl = 'assets/uploads/' . $categorySlug . '/' . $fileName;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    } elseif (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
        // Allow URL input as fallback
        $imageUrl = sanitize($_POST['image_url']);
    }
    
    if (empty($errors)) {
        $slug = generateSlug($name);
        
        // Check if slug exists (excluding current product)
        $stmt = $pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, slug = ?, description = ?, price = ?, 
                                   category_id = ?, image_url = ?, stock_quantity = ?, status = ?, badge = ? 
                                   WHERE id = ?");
            $stmt->execute([
                $name, $slug, $description, $price, 
                $categoryId > 0 ? $categoryId : null, 
                $imageUrl ?: null, 
                $stockQuantity, 
                $status, 
                $badge ?: null,
                $id
            ]);
            
            setFlashMessage('success', 'Product updated successfully!');
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update product. Please try again.';
            error_log("Product update error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Edit Product';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Product</h1>
            <p class="text-muted mb-0">Update product information</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">← Back</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($_POST['description'] ?? $product['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label fw-semibold">Price *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           step="0.01" min="0" required 
                                           value="<?= htmlspecialchars($_POST['price'] ?? $product['price']) ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock_quantity" class="form-label fw-semibold">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                       min="0" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? $product['stock_quantity']) ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label fw-semibold">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                                <?= (($product['category_id'] ?? 0) == $cat['id'] || (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id'])) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="active" <?= ($product['status'] === 'active' || (!isset($_POST['status']) && $product['status'] === 'active')) ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= ($product['status'] === 'inactive' || (isset($_POST['status']) && $_POST['status'] === 'inactive')) ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="badge" class="form-label fw-semibold">Badge (e.g., New, Best Seller)</label>
                            <input type="text" class="form-control" id="badge" name="badge" 
                                   value="<?= htmlspecialchars($_POST['badge'] ?? $product['badge'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="image" class="form-label fw-semibold">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Upload new image to replace current</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image_url" class="form-label fw-semibold">Or Image URL/Path</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" 
                                   placeholder="https://example.com/image.jpg or assets/uploads/category/image.jpg"
                                   value="<?= htmlspecialchars($_POST['image_url'] ?? $product['image_url'] ?? '') ?>">
                            <small class="text-muted">Enter a full URL or local path (optional)</small>
                        </div>
                        
                        <?php if ($product['image_url']): ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Image</label>
                                <img src="<?= htmlspecialchars(getImageUrl($product['image_url'])) ?>" 
                                     alt="Current" class="img-thumbnail" style="max-width: 100%;">
                            </div>
                        <?php endif; ?>
                        
                        <div id="image-preview" class="mb-3" style="display: none;">
                            <label class="form-label fw-semibold">New Image Preview</label>
                            <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 100%;">
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

