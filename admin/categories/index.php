<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = 'Categories';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Manage product categories</p>
        </div>
        <a href="add.php" class="btn btn-primary">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="me-1">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
            </svg>
            Add Category
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <?php if (empty($categories)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-3">No categories found.</p>
                <a href="add.php" class="btn btn-primary">Create First Category</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($category['name']) ?></td>
                                <td><code class="text-muted"><?= htmlspecialchars($category['slug']) ?></code></td>
                                <td class="text-muted"><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                <td class="text-muted small"><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit.php?id=<?= $category['id'] ?>" class="btn btn-outline-primary">Edit</a>
                                        <a href="delete.php?id=<?= $category['id'] ?>" class="btn btn-outline-danger" 
                                           onclick="return confirm('Are you sure? This will set products in this category to have no category.')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

