<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Admin Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Cur1 Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php 
    // Determine CSS path based on how deep we are in admin directory
    $scriptPath = $_SERVER['PHP_SELF'];
    $isInAdmin = strpos($scriptPath, '/admin/') !== false;
    
    if ($isInAdmin) {
        // Count how many levels deep (admin/products/index.php = 2 levels, admin/dashboard.php = 1 level)
        $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7); // Get path after /admin/
        $depth = substr_count($adminPath, '/');
        $cssPath = str_repeat('../', $depth + 1) . 'assets/css/admin.css';
    } else {
        $cssPath = 'assets/css/admin.css';
    }
    ?>
    <link rel="stylesheet" href="<?= $cssPath ?>">
</head>
<body>
    <div class="admin-wrapper">
        <?php 
        // We're already in the includes folder, so just use __DIR__
        include __DIR__ . '/admin_sidebar.php'; 
        ?>
        <div class="admin-main">
            <?php include __DIR__ . '/admin_navbar.php'; ?>
            <main class="admin-content-wrapper">

