<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->fetch()) {
        // Delete category (products will have category_id set to NULL due to ON DELETE SET NULL)
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        setFlashMessage('success', 'Category deleted successfully.');
    } else {
        setFlashMessage('error', 'Category not found.');
    }
}

redirect('index.php');

