<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
$category = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$category->execute([$id]);
$category = $category->fetch();

if (!$category) {
    setFlashMessage('error', 'Category not found.');
    redirect('index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    
    if (empty($name)) {
        $errors[] = 'Category name is required.';
    }
    
    if (empty($errors)) {
        $slug = generateSlug($name);
        
        // Check if slug exists (excluding current category)
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $id]);
            
            setFlashMessage('success', 'Category updated successfully!');
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update category. Please try again.';
        }
    }
}

$pageTitle = 'Edit Category';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Edit Category</h1>
            <p class="text-muted mb-0">Update category information</p>
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
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Category Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           value="<?= htmlspecialchars($_POST['name'] ?? $category['name']) ?>">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? $category['description'] ?? '') ?></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Category</button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

