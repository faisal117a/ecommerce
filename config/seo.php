<?php
// SEO Helper Functions

require_once __DIR__ . '/settings.php';

/**
 * Get SEO meta title
 */
function getSEOTitle(?string $pageTitle = null): string {
    $siteTitle = getSetting('meta_title', 'Cur1 Fashion Store');
    if ($pageTitle) {
        return htmlspecialchars($pageTitle) . ' - ' . $siteTitle;
    }
    return $siteTitle;
}

/**
 * Get SEO meta description
 */
function getSEODescription(?string $pageDescription = null): string {
    if ($pageDescription) {
        return htmlspecialchars($pageDescription);
    }
    return htmlspecialchars(getSetting('meta_description', 'Premium quality men & women clothing with modern designs.'));
}

/**
 * Get SEO meta keywords
 */
function getSEOKeywords(?string $pageKeywords = null): string {
    if ($pageKeywords) {
        return htmlspecialchars($pageKeywords);
    }
    return htmlspecialchars(getSetting('meta_keywords', 'fashion, clothing, men, women'));
}

/**
 * Generate Open Graph meta tags
 */
function getOpenGraphTags(string $title, string $description, ?string $image = null, string $type = 'website'): string {
    $siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $siteUrl = rtrim($siteUrl, '/');
    
    $tags = [
        '<meta property="og:type" content="' . htmlspecialchars($type) . '">',
        '<meta property="og:title" content="' . htmlspecialchars($title) . '">',
        '<meta property="og:description" content="' . htmlspecialchars($description) . '">',
        '<meta property="og:url" content="' . htmlspecialchars($siteUrl . $_SERVER['REQUEST_URI']) . '">',
    ];
    
    if ($image) {
        $imageUrl = strpos($image, 'http') === 0 ? $image : $siteUrl . '/' . ltrim($image, '/');
        $tags[] = '<meta property="og:image" content="' . htmlspecialchars($imageUrl) . '">';
    }
    
    return implode("\n    ", $tags);
}

/**
 * Generate full SEO meta tags for a page
 */
function generateSEOTags(?string $pageTitle = null, ?string $pageDescription = null, ?string $pageKeywords = null, ?string $pageImage = null): string {
    $title = getSEOTitle($pageTitle);
    $description = getSEODescription($pageDescription);
    $keywords = getSEOKeywords($pageKeywords);
    
    $tags = [
        '<meta name="description" content="' . $description . '">',
        '<meta name="keywords" content="' . $keywords . '">',
        getOpenGraphTags($title, $description, $pageImage),
    ];
    
    return implode("\n    ", $tags);
}

