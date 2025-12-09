# Product Setup Guide

## Step 1: Insert Products into Database

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `cur1_ecommerce` database
3. Go to the **SQL** tab
4. Copy and paste the contents of `insert_products.sql`
5. Click **Go** to execute

This will insert 30 products:
- 10 Men's products
- 10 Women's products  
- 10 Unisex products

The products will initially use Pexels image URLs (which work immediately).

## Step 2: Download Images Locally (Optional)

If you want to store images locally instead of using Pexels URLs:

1. Open in browser: `http://localhost/cur1/download_product_images.php`
2. The script will:
   - Download all 30 images from Pexels
   - Save them to category folders:
     - `assets/uploads/men/` (10 images)
     - `assets/uploads/women/` (10 images)
     - `assets/uploads/unisex/` (10 images)
   - Update the database with local paths
3. After completion, **delete** `download_product_images.php` for security

## Folder Structure

After running the download script, you'll have:

```
assets/uploads/
├── men/
│   ├── classic-white-tshirt.jpg
│   ├── slim-fit-denim-jeans.jpg
│   ├── casual-hoodie.jpg
│   └── ... (7 more)
├── women/
│   ├── floral-summer-dress.jpg
│   ├── high-waist-skinny-jeans.jpg
│   ├── oversized-sweater.jpg
│   └── ... (7 more)
└── unisex/
    ├── unisex-graphic-tee.jpg
    ├── minimalist-hoodie.jpg
    ├── crew-neck-sweatshirt.jpg
    └── ... (7 more)
```

## Product List

### Men's Products (10)
1. Classic White T-Shirt - $24.99
2. Slim Fit Denim Jeans - $59.99
3. Casual Hoodie - $49.99
4. Formal Dress Shirt - $39.99
5. Chino Pants - $54.99
6. Polo Shirt - $34.99
7. Leather Jacket - $129.99
8. Cargo Shorts - $44.99
9. Sweater - $64.99
10. Track Pants - $49.99

### Women's Products (10)
1. Floral Summer Dress - $69.99
2. High-Waist Skinny Jeans - $64.99
3. Oversized Sweater - $54.99
4. Blouse - $44.99
5. Midi Skirt - $49.99
6. Cardigan - $59.99
7. Wide Leg Pants - $64.99
8. Crop Top - $29.99
9. Maxi Dress - $79.99
10. Denim Jacket - $69.99

### Unisex Products (10)
1. Unisex Graphic Tee - $29.99
2. Minimalist Hoodie - $54.99
3. Crew Neck Sweatshirt - $44.99
4. Baseball Cap - $24.99
5. Joggers - $49.99
6. Zip-Up Hoodie - $59.99
7. Long Sleeve Tee - $34.99
8. Beanie - $19.99
9. Oversized T-Shirt - $27.99
10. Fleece Jacket - $64.99

## Notes

- All images are from Pexels (free stock photos)
- Images are optimized for web (800px width)
- Products have realistic prices and stock quantities
- Some products have badges (Best Seller, New, Hot, Limited, Trending)

