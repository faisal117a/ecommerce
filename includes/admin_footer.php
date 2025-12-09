            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php 
    // Determine assets path based on how deep we are in admin directory
    $scriptPath = $_SERVER['PHP_SELF'];
    $isInAdmin = strpos($scriptPath, '/admin/') !== false;
    
    if ($isInAdmin) {
        // Count how many levels deep (admin/products/index.php = 2 levels, admin/dashboard.php = 1 level)
        $adminPath = substr($scriptPath, strpos($scriptPath, '/admin/') + 7); // Get path after /admin/
        $depth = substr_count($adminPath, '/');
        $assetsPath = str_repeat('../', $depth + 1) . 'assets/';
    } else {
        $assetsPath = 'assets/';
    }
    ?>
    <script src="<?= $assetsPath ?>js/admin.js"></script>
</body>
</html>

