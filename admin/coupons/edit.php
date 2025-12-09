<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
$coupon = null;
$errors = [];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$id]);
    $coupon = $stmt->fetch();
}

if (!$coupon) {
    setFlashMessage('error', 'Coupon not found.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $type = $_POST['type'] ?? 'percentage';
    $value = $_POST['value'] ?? '';
    $minPurchase = $_POST['min_purchase'] ?? 0;
    $usageLimit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $status = $_POST['status'] ?? 'active';
    
    // Validation
    if (empty($code)) {
        $errors[] = 'Coupon code is required.';
    } elseif ($code !== $coupon['code']) {
        // Check if new code already exists
        $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ? AND id != ?");
        $stmt->execute([$code, $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Coupon code already exists.';
        }
    }
    
    if (!in_array($type, ['percentage', 'fixed'])) {
        $errors[] = 'Invalid coupon type.';
    }
    
    if (empty($value) || !is_numeric($value) || $value <= 0) {
        $errors[] = 'Valid discount value is required.';
    }
    
    if ($type === 'percentage' && $value > 100) {
        $errors[] = 'Percentage discount cannot exceed 100%.';
    }
    
    if (!is_numeric($minPurchase) || $minPurchase < 0) {
        $errors[] = 'Minimum purchase must be a positive number.';
    }
    
    if ($usageLimit !== null && $usageLimit < 1) {
        $errors[] = 'Usage limit must be at least 1.';
    }
    
    if ($expiryDate && strtotime($expiryDate) < time()) {
        $errors[] = 'Expiry date cannot be in the past.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, min_purchase = ?, usage_limit = ?, expiry_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$code, $type, $value, $minPurchase, $usageLimit, $expiryDate, $status, $id]);
            
            setFlashMessage('success', 'Coupon updated successfully!');
            redirect('index.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update coupon. Please try again.';
            error_log("Coupon update error: " . $e->getMessage());
        }
    }
}

$pageTitle = 'Edit Coupon';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Edit coupon details</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary">← Back to Coupons</a>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Coupon Code *</label>
                        <input type="text" class="form-control" id="code" name="code" required
                               value="<?= htmlspecialchars($_POST['code'] ?? $coupon['code']) ?>" 
                               style="text-transform: uppercase;">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="type" class="form-label">Discount Type *</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="percentage" <?= ($_POST['type'] ?? $coupon['type']) === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                            <option value="fixed" <?= ($_POST['type'] ?? $coupon['type']) === 'fixed' ? 'selected' : '' ?>>Fixed Amount</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="value" class="form-label">Discount Value *</label>
                        <div class="input-group">
                            <?php if (($_POST['type'] ?? $coupon['type']) === 'percentage'): ?>
                                <input type="number" class="form-control" id="value" name="value" required
                                       value="<?= htmlspecialchars($_POST['value'] ?? $coupon['value']) ?>" 
                                       min="1" max="100" step="0.01">
                                <span class="input-group-text">%</span>
                            <?php else: ?>
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="value" name="value" required
                                       value="<?= htmlspecialchars($_POST['value'] ?? $coupon['value']) ?>" 
                                       min="0.01" step="0.01">
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="min_purchase" class="form-label">Minimum Purchase</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="min_purchase" name="min_purchase"
                                   value="<?= htmlspecialchars($_POST['min_purchase'] ?? $coupon['min_purchase']) ?>" 
                                   min="0" step="0.01">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usage_limit" class="form-label">Usage Limit</label>
                        <input type="number" class="form-control" id="usage_limit" name="usage_limit"
                               value="<?= htmlspecialchars($_POST['usage_limit'] ?? $coupon['usage_limit'] ?? '') ?>" 
                               min="1">
                        <small class="text-muted">Leave blank for unlimited</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                               value="<?= htmlspecialchars($_POST['expiry_date'] ?? ($coupon['expiry_date'] ? date('Y-m-d', strtotime($coupon['expiry_date'])) : '')) ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?= ($_POST['status'] ?? $coupon['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($_POST['status'] ?? $coupon['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Coupon</button>
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    const valueInput = document.getElementById('value');
    const valueGroup = valueInput.closest('.input-group');
    const currentValue = valueInput.value;
    
    if (this.value === 'percentage') {
        valueInput.setAttribute('max', '100');
        valueGroup.innerHTML = '<input type="number" class="form-control" id="value" name="value" required min="1" max="100" step="0.01" value="' + currentValue + '"><span class="input-group-text">%</span>';
    } else {
        valueInput.removeAttribute('max');
        valueGroup.innerHTML = '<span class="input-group-text">$</span><input type="number" class="form-control" id="value" name="value" required min="0.01" step="0.01" value="' + currentValue + '">';
    }
});
</script>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

