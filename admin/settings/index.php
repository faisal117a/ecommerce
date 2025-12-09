<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/settings.php';

requireAdmin();

$pdo = getDB();
$activeTab = $_GET['tab'] ?? 'smtp';
$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [];
    
    if ($activeTab === 'smtp') {
        $settings = [
            'smtp_host' => sanitize($_POST['smtp_host'] ?? ''),
            'smtp_port' => sanitize($_POST['smtp_port'] ?? '587'),
            'smtp_username' => sanitize($_POST['smtp_username'] ?? ''),
            'smtp_password' => sanitize($_POST['smtp_password'] ?? ''),
            'smtp_encryption' => sanitize($_POST['smtp_encryption'] ?? 'tls'),
            'smtp_from_email' => sanitize($_POST['smtp_from_email'] ?? ''),
            'smtp_from_name' => sanitize($_POST['smtp_from_name'] ?? ''),
        ];
    } elseif ($activeTab === 'header') {
        $settings = [
            'header_title' => sanitize($_POST['header_title'] ?? ''),
            'header_description' => sanitize($_POST['header_description'] ?? ''),
        ];
        
        // Handle header image upload
        if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validation = validateImageUpload($_FILES['header_image']);
            
            if (!$validation['valid']) {
                $error = $validation['error'];
            } else {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['header_image']['name'], PATHINFO_EXTENSION);
                $filename = 'header_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['header_image']['tmp_name'], $filepath)) {
                    // Delete old header image if exists
                    $oldImage = getSetting('header_image');
                    if ($oldImage && file_exists(__DIR__ . '/../../' . $oldImage)) {
                        @unlink(__DIR__ . '/../../' . $oldImage);
                    }
                    $settings['header_image'] = 'assets/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload header image.';
                }
            }
        }
    } elseif ($activeTab === 'logo') {
        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Allow SVG for logos
            $validation = validateImageUpload($_FILES['logo'], 5242880, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
            
            if (!$validation['valid']) {
                $error = $validation['error'];
            } else {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $filepath)) {
                    // Delete old logo if exists
                    $oldLogo = getSetting('logo_path');
                    if ($oldLogo && file_exists(__DIR__ . '/../../' . $oldLogo)) {
                        @unlink(__DIR__ . '/../../' . $oldLogo);
                    }
                    $settings['logo_path'] = 'assets/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload logo.';
                }
            }
        }
    } elseif ($activeTab === 'seo') {
        $settings = [
            'meta_title' => sanitize($_POST['meta_title'] ?? ''),
            'meta_description' => sanitize($_POST['meta_description'] ?? ''),
            'meta_keywords' => sanitize($_POST['meta_keywords'] ?? ''),
        ];
    } elseif ($activeTab === 'homepage') {
        $settings = [
            'homepage_category_1' => sanitize($_POST['homepage_category_1'] ?? ''),
            'homepage_category_2' => sanitize($_POST['homepage_category_2'] ?? ''),
        ];
    } elseif ($activeTab === 'footer') {
        $settings = [
            'footer_description' => sanitize($_POST['footer_description'] ?? ''),
            'footer_facebook' => sanitize($_POST['footer_facebook'] ?? ''),
            'footer_twitter' => sanitize($_POST['footer_twitter'] ?? ''),
            'footer_instagram' => sanitize($_POST['footer_instagram'] ?? ''),
            'footer_linkedin' => sanitize($_POST['footer_linkedin'] ?? ''),
            'footer_youtube' => sanitize($_POST['footer_youtube'] ?? ''),
            'footer_phone' => sanitize($_POST['footer_phone'] ?? ''),
            'footer_email' => sanitize($_POST['footer_email'] ?? ''),
            'footer_address' => sanitize($_POST['footer_address'] ?? ''),
        ];
        
        // Handle footer logo upload
        if (isset($_FILES['footer_logo']) && $_FILES['footer_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validation = validateImageUpload($_FILES['footer_logo'], 5242880, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
            
            if (!$validation['valid']) {
                $error = $validation['error'];
            } else {
                $uploadDir = __DIR__ . '/../../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $extension = pathinfo($_FILES['footer_logo']['name'], PATHINFO_EXTENSION);
                $filename = 'footer_logo_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['footer_logo']['tmp_name'], $filepath)) {
                    // Delete old footer logo if exists
                    $oldLogo = getSetting('footer_logo_path');
                    if ($oldLogo && file_exists(__DIR__ . '/../../' . $oldLogo)) {
                        @unlink(__DIR__ . '/../../' . $oldLogo);
                    }
                    $settings['footer_logo_path'] = 'assets/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload footer logo.';
                }
            }
        } else {
            // Keep existing logo if no new file uploaded
            $settings['footer_logo_path'] = getSetting('footer_logo_path');
        }
    }
    
    if (!empty($settings)) {
        if (saveSettings($settings)) {
            $success = true;
            setFlashMessage('success', 'Settings saved successfully!');
        } else {
            $error = 'Failed to save settings.';
        }
    }
}

// Get categories for homepage settings
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = 'Site Settings';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Configure site settings and preferences</p>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'smtp' ? 'active' : '' ?>" 
                    onclick="window.location.href='?tab=smtp'" type="button">
                SMTP Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'header' ? 'active' : '' ?>" 
                    onclick="window.location.href='?tab=header'" type="button">
                Header Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'logo' ? 'active' : '' ?>" 
                    onclick="window.location.href='?tab=logo'" type="button">
                Logo Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $activeTab === 'seo' ? 'active' : '' ?>" 
                    onclick="window.location.href='?tab=seo'" type="button">
                SEO Settings
            </button>
        </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'homepage' ? 'active' : '' ?>" 
                onclick="window.location.href='?tab=homepage'" type="button">
            Homepage Settings
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'footer' ? 'active' : '' ?>" 
                onclick="window.location.href='?tab=footer'" type="button">
            Footer Settings
        </button>
    </li>
</ul>

    <!-- Tab Content -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if ($activeTab === 'smtp'): ?>
                <form method="POST" action="?tab=smtp">
                    <h5 class="fw-bold mb-4">SMTP Email Configuration</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtp_host" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                   value="<?= htmlspecialchars(getSetting('smtp_host')) ?>" 
                                   placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label for="smtp_port" class="form-label">SMTP Port</label>
                            <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                   value="<?= htmlspecialchars(getSetting('smtp_port', '587')) ?>" 
                                   placeholder="587">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtp_username" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                   value="<?= htmlspecialchars(getSetting('smtp_username')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="smtp_password" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                   value="<?= htmlspecialchars(getSetting('smtp_password')) ?>" 
                                   placeholder="Leave blank to keep current">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtp_encryption" class="form-label">Encryption</label>
                            <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                <option value="tls" <?= getSetting('smtp_encryption') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                <option value="ssl" <?= getSetting('smtp_encryption') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                <option value="none" <?= getSetting('smtp_encryption') === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="smtp_from_email" class="form-label">From Email</label>
                            <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email" 
                                   value="<?= htmlspecialchars(getSetting('smtp_from_email')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="smtp_from_name" class="form-label">From Name</label>
                            <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name" 
                                   value="<?= htmlspecialchars(getSetting('smtp_from_name', 'Cur1 Fashion')) ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save SMTP Settings</button>
                </form>
                
            <?php elseif ($activeTab === 'header'): ?>
                <form method="POST" action="?tab=header" enctype="multipart/form-data">
                    <h5 class="fw-bold mb-4">Header Section Settings</h5>
                    
                    <div class="mb-3">
                        <label for="header_title" class="form-label">Header Title</label>
                        <input type="text" class="form-control" id="header_title" name="header_title" 
                               value="<?= htmlspecialchars(getSetting('header_title', 'Discover Your Everyday Style')) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="header_description" class="form-label">Header Description</label>
                        <textarea class="form-control" id="header_description" name="header_description" rows="3"><?= htmlspecialchars(getSetting('header_description', 'Premium quality men & women clothing with modern designs.')) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="header_image" class="form-label">Header Image</label>
                        <?php if (getSetting('header_image')): ?>
                            <div class="mb-2">
                                <img src="../../<?= htmlspecialchars(getSetting('header_image')) ?>" 
                                     alt="Current header" class="img-thumbnail" style="max-height: 200px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="header_image" name="header_image" 
                               accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF, WebP</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Header Settings</button>
                </form>
                
            <?php elseif ($activeTab === 'logo'): ?>
                <form method="POST" action="?tab=logo" enctype="multipart/form-data">
                    <h5 class="fw-bold mb-4">Logo Settings</h5>
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Upload Logo</label>
                        <?php if (getSetting('logo_path')): ?>
                            <div class="mb-2">
                                <img src="../<?= htmlspecialchars(getSetting('logo_path')) ?>" 
                                     alt="Current logo" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="logo" name="logo" 
                               accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
                        <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF, WebP, SVG</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Upload Logo</button>
                </form>
                
            <?php elseif ($activeTab === 'seo'): ?>
                <form method="POST" action="?tab=seo">
                    <h5 class="fw-bold mb-4">SEO Settings</h5>
                    
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                               value="<?= htmlspecialchars(getSetting('meta_title', 'Cur1 Fashion Store')) ?>">
                        <small class="text-muted">Recommended: 50-60 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" rows="3"><?= htmlspecialchars(getSetting('meta_description', 'Premium quality men & women clothing with modern designs.')) ?></textarea>
                        <small class="text-muted">Recommended: 150-160 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" 
                               value="<?= htmlspecialchars(getSetting('meta_keywords', 'fashion, clothing, men, women')) ?>">
                        <small class="text-muted">Comma-separated keywords</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save SEO Settings</button>
                </form>
                
            <?php elseif ($activeTab === 'homepage'): ?>
                <form method="POST" action="?tab=homepage">
                    <h5 class="fw-bold mb-4">Homepage Category Settings</h5>
                    
                    <div class="mb-3">
                        <label for="homepage_category_1" class="form-label">First Category (4 products)</label>
                        <select class="form-select" id="homepage_category_1" name="homepage_category_1">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                        <?= getSetting('homepage_category_1') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="homepage_category_2" class="form-label">Second Category (4 products)</label>
                        <select class="form-select" id="homepage_category_2" name="homepage_category_2">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                        <?= getSetting('homepage_category_2') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Homepage Settings</button>
                </form>
                
            <?php elseif ($activeTab === 'footer'): ?>
                <form method="POST" action="?tab=footer" enctype="multipart/form-data">
                    <h5 class="fw-bold mb-4">Footer Settings</h5>
                    
                    <div class="mb-3">
                        <label for="footer_logo" class="form-label">Footer Logo</label>
                        <?php if (getSetting('footer_logo_path')): ?>
                            <div class="mb-2">
                                <img src="../../<?= htmlspecialchars(getSetting('footer_logo_path')) ?>" 
                                     alt="Current footer logo" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="footer_logo" name="footer_logo" 
                               accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
                        <small class="text-muted">Max size: 5MB. Formats: JPG, PNG, GIF, WebP, SVG</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="footer_description" class="form-label">Footer Description</label>
                        <textarea class="form-control" id="footer_description" name="footer_description" rows="3"><?= htmlspecialchars(getSetting('footer_description', 'Premium quality men & women clothing with modern designs.')) ?></textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="fw-bold mb-3">Contact Information</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="footer_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="footer_phone" name="footer_phone" 
                                   value="<?= htmlspecialchars(getSetting('footer_phone')) ?>" 
                                   placeholder="+1 234 567 8900">
                        </div>
                        <div class="col-md-6">
                            <label for="footer_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="footer_email" name="footer_email" 
                                   value="<?= htmlspecialchars(getSetting('footer_email')) ?>" 
                                   placeholder="info@cur1.com">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="footer_address" class="form-label">Address</label>
                        <textarea class="form-control" id="footer_address" name="footer_address" rows="2"><?= htmlspecialchars(getSetting('footer_address')) ?></textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="fw-bold mb-3">Social Media Links</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="footer_facebook" class="form-label">Facebook URL</label>
                            <input type="url" class="form-control" id="footer_facebook" name="footer_facebook" 
                                   value="<?= htmlspecialchars(getSetting('footer_facebook')) ?>" 
                                   placeholder="https://facebook.com/yourpage">
                        </div>
                        <div class="col-md-6">
                            <label for="footer_twitter" class="form-label">Twitter URL</label>
                            <input type="url" class="form-control" id="footer_twitter" name="footer_twitter" 
                                   value="<?= htmlspecialchars(getSetting('footer_twitter')) ?>" 
                                   placeholder="https://twitter.com/yourhandle">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="footer_instagram" class="form-label">Instagram URL</label>
                            <input type="url" class="form-control" id="footer_instagram" name="footer_instagram" 
                                   value="<?= htmlspecialchars(getSetting('footer_instagram')) ?>" 
                                   placeholder="https://instagram.com/yourhandle">
                        </div>
                        <div class="col-md-6">
                            <label for="footer_linkedin" class="form-label">LinkedIn URL</label>
                            <input type="url" class="form-control" id="footer_linkedin" name="footer_linkedin" 
                                   value="<?= htmlspecialchars(getSetting('footer_linkedin')) ?>" 
                                   placeholder="https://linkedin.com/company/yourcompany">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="footer_youtube" class="form-label">YouTube URL</label>
                        <input type="url" class="form-control" id="footer_youtube" name="footer_youtube" 
                               value="<?= htmlspecialchars(getSetting('footer_youtube')) ?>" 
                               placeholder="https://youtube.com/yourchannel">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Footer Settings</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

