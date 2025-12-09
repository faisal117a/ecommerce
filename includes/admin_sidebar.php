<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <a href="../index.php" class="sidebar-brand">
            <span class="fw-bold text-primary">Cur1</span><span class="text-dark">Admin</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <?php 
        // Calculate relative path to admin root based on current directory depth
        $scriptPath = $_SERVER['PHP_SELF'];
        if (strpos($scriptPath, '/admin/') !== false) {
            $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7); // Get path after /admin/
            $depth = substr_count($adminPath, '/');
            $adminBase = str_repeat('../', $depth);
        } else {
            $adminBase = 'admin/';
        }
        ?>
        <a href="<?= $adminBase ?>dashboard.php" class="nav-item <?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>
            </svg>
            Dashboard
        </a>
        <a href="<?= $adminBase ?>products/index.php" class="nav-item <?= ($currentDir === 'products') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
            </svg>
            Products
        </a>
        <a href="<?= $adminBase ?>categories/index.php" class="nav-item <?= ($currentDir === 'categories') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
            </svg>
            Categories
        </a>
        <a href="<?= $adminBase ?>orders/index.php" class="nav-item <?= ($currentDir === 'orders') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5zM3.14 5l.5 2H5V5H3.14zM6 5v2h2V5H6zm3 0v2h2V5H9zm3 0v2h1.36l.5-2H12zm1.11 3H12v2h.61l.5-2zM11 8H9v2h2V8zM8 8H6v2h2V8zM5 8H3.89l.5 2H5V8zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0zm9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0z"/>
            </svg>
            Orders
        </a>
        <a href="<?= $adminBase ?>analytics/index.php" class="nav-item <?= ($currentDir === 'analytics') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.576l-3.5 4a.5.5 0 0 1-.564.53L7.3 7.1 4.83 9.47a.5.5 0 0 1-.707 0l-2-2a.5.5 0 0 1 0-.707l2-2a.5.5 0 0 1 .707 0L7.3 6.1l3.953-1.06a.5.5 0 0 1 .564.53Z"/>
            </svg>
            Analytics
        </a>
        <a href="<?= $adminBase ?>coupons/index.php" class="nav-item <?= ($currentDir === 'coupons') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
            </svg>
            Coupons
        </a>
        <a href="<?= $adminBase ?>contacts/index.php" class="nav-item <?= ($currentDir === 'contacts') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/>
            </svg>
            Contacts
        </a>
        <a href="<?= $adminBase ?>settings/index.php" class="nav-item <?= ($currentDir === 'settings') ? 'active' : '' ?>">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64.892-3.433.902-5.096 0-.135.09-.324.192-.5.29-.19.102-.38.205-.562.322-.101.067-.2.136-.287.208l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64-.902 3.433 0 5.096.09.135.192.324.29.5.102.19.205.38.322.562.067.101.136.2.208.287l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.892 3.433.902 5.096 0 .135-.09.324-.192.5-.29.19-.102.38-.205.562-.322.101-.067.2-.136.287-.208l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.892-1.64.902-3.433 0-5.096-.09-.135-.192-.324-.29-.5-.102-.19-.205-.38-.322-.562-.067-.101-.136-.2-.208-.287l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.292-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.292c.415.764-.42 1.6-1.185 1.184l-.292-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.292A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
            </svg>
            Settings
        </a>
    </nav>
    <div class="sidebar-footer">
        <?php 
        // Calculate relative path to root based on current directory depth
        $scriptPath = $_SERVER['PHP_SELF'];
        if (strpos($scriptPath, '/admin/') !== false) {
            $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7); // Get path after /admin/
            $depth = substr_count($adminPath, '/');
            $rootPath = str_repeat('../', $depth + 1); // +1 to go from admin to root
        } else {
            $rootPath = '';
        }
        ?>
        <a href="<?= $rootPath ?>index.php" class="nav-item">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path d="M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.354 1.146zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4H2.5z"/>
            </svg>
            View Shop
        </a>
        <a href="<?= $rootPath ?>auth/logout.php" class="nav-item text-danger">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16" class="me-2">
                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/>
                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
            </svg>
            Logout
        </a>
    </div>
</aside>

