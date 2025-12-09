<div class="card product-card h-100 border-0 shadow-sm">
    <div class="position-relative">
        <a href="product.php?id=<?= (int)$product['id'] ?>">
            <img src="<?= htmlspecialchars(getImageUrl($product['image_url']), ENT_QUOTES) ?>"
                 class="card-img-top product-image" alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">
        </a>
        <?php if (!empty($product['badge'])): ?>
            <span class="badge bg-primary position-absolute top-0 start-0 m-2" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                <?= htmlspecialchars($product['badge'], ENT_QUOTES) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body d-flex flex-column">
        <h5 class="card-title mb-1">
            <a href="product.php?id=<?= (int)$product['id'] ?>" class="product-link text-decoration-none">
                <?= htmlspecialchars($product['name'], ENT_QUOTES) ?>
            </a>
        </h5>
        <p class="card-text text-muted mb-2">
            <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?>
        </p>
        <div class="d-flex justify-content-between align-items-center mt-auto">
            <span class="fw-semibold text-primary fs-5"><?= formatPrice($product['price']) ?></span>
            <form method="post" action="cart.php" class="m-0">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary px-3">
                    Add to cart
                </button>
            </form>
        </div>
    </div>
</div>

