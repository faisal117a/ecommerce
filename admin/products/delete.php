<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Get product to delete image
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete image file if exists
        if ($product['image_url'] && file_exists(__DIR__ . '/../../' . $product['image_url'])) {
            @unlink(__DIR__ . '/../../' . $product['image_url']);
        }
        
        setFlashMessage('success', 'Product deleted successfully.');
    } else {
        setFlashMessage('error', 'Product not found.');
    }
}

redirect('index.php');

