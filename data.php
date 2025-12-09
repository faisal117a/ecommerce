<?php
// Shared product data & helpers

$products = [
    [
        'id' => 1,
        'name' => 'Classic White T-Shirt',
        'category' => 'men',
        'type' => 'T-Shirts',
        'price' => 19.99,
        'image' => 'https://images.pexels.com/photos/1002638/pexels-photo-1002638.jpeg',
        'badge' => 'Best Seller'
    ],
    [
        'id' => 2,
        'name' => 'Slim Fit Denim Jeans',
        'category' => 'men',
        'type' => 'Jeans',
        'price' => 49.99,
        'image' => 'https://images.pexels.com/photos/1036623/pexels-photo-1036623.jpeg',
        'badge' => 'New'
    ],
    [
        'id' => 3,
        'name' => 'Casual Hoodie',
        'category' => 'men',
        'type' => 'Hoodies',
        'price' => 39.99,
        'image' => 'https://images.pexels.com/photos/7671166/pexels-photo-7671166.jpeg',
        'badge' => 'Hot'
    ],
    [
        'id' => 4,
        'name' => 'Floral Summer Dress',
        'category' => 'women',
        'type' => 'Dresses',
        'price' => 59.99,
        'image' => 'https://images.pexels.com/photos/977601/pexels-photo-977601.jpeg',
        'badge' => 'Trending'
    ],
    [
        'id' => 5,
        'name' => 'High-Waist Skinny Jeans',
        'category' => 'women',
        'type' => 'Jeans',
        'price' => 54.99,
        'image' => 'https://images.pexels.com/photos/7671162/pexels-photo-7671162.jpeg',
        'badge' => 'Best Seller'
    ],
    [
        'id' => 6,
        'name' => 'Oversized Sweater',
        'category' => 'women',
        'type' => 'Knitwear',
        'price' => 44.99,
        'image' => 'https://images.pexels.com/photos/6311572/pexels-photo-6311572.jpeg',
        'badge' => 'New'
    ],
    [
        'id' => 7,
        'name' => 'Unisex Graphic Tee',
        'category' => 'unisex',
        'type' => 'T-Shirts',
        'price' => 24.99,
        'image' => 'https://images.pexels.com/photos/6311650/pexels-photo-6311650.jpeg',
        'badge' => 'Limited'
    ],
    [
        'id' => 8,
        'name' => 'Minimalist Hoodie',
        'category' => 'unisex',
        'type' => 'Hoodies',
        'price' => 42.00,
        'image' => 'https://images.pexels.com/photos/7671165/pexels-photo-7671165.jpeg',
        'badge' => 'New'
    ],
];

function formatPrice(float $price): string
{
    return '$' . number_format($price, 2);
}

function getProductById(int $id): ?array
{
    foreach ($GLOBALS['products'] as $product) {
        if ($product['id'] === $id) {
            return $product;
        }
    }
    return null;
}

function getCartCount(): int
{
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}


