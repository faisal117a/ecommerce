<?php
// Shared utility functions

/**
 * Format price with currency symbol
 */
function formatPrice(float $price): string {
    return '$' . number_format($price, 2);
}

/**
 * Generate URL-friendly slug from string
 */
function generateSlug(string $string): string {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Redirect to URL
 */
function redirect(string $url) {
    header("Location: " . $url);
    exit;
}

/**
 * Get base URL
 */
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . rtrim($script, '/');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message helper
 */
function setFlashMessage(string $type, string $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function getFlashMessage(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'type' => $_SESSION['flash_type'] ?? 'info',
            'text' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlashMessage(): void {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = match($flash['type']) {
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['text']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

/**
 * Get correct image URL - handles both local paths and external URLs
 */
function getImageUrl(?string $imageUrl): string {
    if (empty($imageUrl)) {
        return 'https://via.placeholder.com/400';
    }
    
    // If it's already a full URL (http/https), return as is
    if (preg_match('/^https?:\/\//', $imageUrl)) {
        return $imageUrl;
    }
    
    // It's a local path - calculate correct relative path based on current location
    $scriptPath = $_SERVER['PHP_SELF'];
    
    // Determine base path
    if (strpos($scriptPath, '/admin/') !== false) {
        // We're in admin area - calculate depth
        $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7);
        $depth = substr_count($adminPath, '/');
        $basePath = str_repeat('../', $depth + 1);
    } elseif (strpos($scriptPath, '/orders/') !== false) {
        // We're in orders directory - need to go up one level
        $basePath = '../';
    } elseif (strpos($scriptPath, '/auth/') !== false) {
        // We're in auth directory - need to go up one level
        $basePath = '../';
    } else {
        // We're in root
        $basePath = '';
    }
    
    // Remove leading slash from image path if present
    $imageUrl = ltrim($imageUrl, '/');
    
    return $basePath . $imageUrl;
}

/**
 * Validate image upload
 * Returns array with 'valid' => bool and 'error' => string
 */
function validateImageUpload(array $file, int $maxSize = 5242880, array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']): array {
    // Check if file was uploaded
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valid' => false, 'error' => 'No file uploaded.'];
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
        ];
        return ['valid' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error.'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $maxSizeMB = round($maxSize / 1024 / 1024, 2);
        return ['valid' => false, 'error' => "File size exceeds maximum allowed size of {$maxSizeMB}MB."];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['valid' => false, 'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
    }
    
    // Additional validation: check if it's actually an image
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'File is not a valid image.'];
    }
    
    // Check image dimensions (optional - can be configured)
    $maxWidth = 5000;
    $maxHeight = 5000;
    if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
        return ['valid' => false, 'error' => "Image dimensions exceed maximum allowed size ({$maxWidth}x{$maxHeight}px)."];
    }
    
    return ['valid' => true, 'error' => '', 'mime_type' => $mimeType, 'width' => $imageInfo[0], 'height' => $imageInfo[1]];
}

