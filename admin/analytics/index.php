<?php
session_start();
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

requireAdmin();

$pdo = getDB();

// Get date range filter
$period = $_GET['period'] ?? 'month';
$startDate = '';
$endDate = date('Y-m-d');

switch ($period) {
    case 'today':
        $startDate = date('Y-m-d');
        break;
    case 'week':
        $startDate = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'month':
        $startDate = date('Y-m-d', strtotime('-30 days'));
        break;
    case 'year':
        $startDate = date('Y-m-d', strtotime('-365 days'));
        break;
    case 'all':
        $startDate = '2000-01-01';
        break;
}

// Sales statistics
$salesStats = [
    'total_revenue' => $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled' AND DATE(created_at) >= ?")->execute([$startDate]) ? $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled' AND DATE(created_at) >= '$startDate'")->fetchColumn() : 0,
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) >= '$startDate'")->fetchColumn(),
    'completed_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered' AND DATE(created_at) >= '$startDate'")->fetchColumn(),
    'average_order_value' => 0,
];

if ($salesStats['total_orders'] > 0) {
    $salesStats['average_order_value'] = $salesStats['total_revenue'] / $salesStats['total_orders'];
}

// Top selling products
$topProducts = $pdo->query("SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
                           FROM order_items oi
                           JOIN products p ON oi.product_id = p.id
                           JOIN orders o ON oi.order_id = o.id
                           WHERE o.status != 'cancelled' AND DATE(o.created_at) >= '$startDate'
                           GROUP BY p.id, p.name
                           ORDER BY total_sold DESC
                           LIMIT 10")->fetchAll();

// Order status breakdown
$statusBreakdown = $pdo->query("SELECT status, COUNT(*) as count 
                               FROM orders 
                               WHERE DATE(created_at) >= '$startDate'
                               GROUP BY status")->fetchAll();

// Daily sales for chart (last 30 days)
$dailySales = $pdo->query("SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as orders
                          FROM orders
                          WHERE status != 'cancelled' AND DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY date ASC")->fetchAll();

$pageTitle = 'Analytics';
include __DIR__ . '/../../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-0">Sales analytics and reports</p>
        </div>
        <div class="btn-group" role="group">
            <a href="?period=today" class="btn btn-<?= $period === 'today' ? 'primary' : 'outline-primary' ?> btn-sm">Today</a>
            <a href="?period=week" class="btn btn-<?= $period === 'week' ? 'primary' : 'outline-primary' ?> btn-sm">Week</a>
            <a href="?period=month" class="btn btn-<?= $period === 'month' ? 'primary' : 'outline-primary' ?> btn-sm">Month</a>
            <a href="?period=year" class="btn btn-<?= $period === 'year' ? 'primary' : 'outline-primary' ?> btn-sm">Year</a>
            <a href="?period=all" class="btn btn-<?= $period === 'all' ? 'primary' : 'outline-primary' ?> btn-sm">All Time</a>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Revenue</p>
                    <h3 class="mb-0 fw-bold"><?= formatPrice($salesStats['total_revenue']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Total Orders</p>
                    <h3 class="mb-0 fw-bold"><?= number_format($salesStats['total_orders']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Completed Orders</p>
                    <h3 class="mb-0 fw-bold text-success"><?= number_format($salesStats['completed_orders']) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted small mb-1">Average Order Value</p>
                    <h3 class="mb-0 fw-bold"><?= formatPrice($salesStats['average_order_value']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Revenue Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Order Status Breakdown -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-semibold">Order Status</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($statusBreakdown as $status): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="badge bg-<?= match($status['status']) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                } ?> me-2">
                                    <?= ucfirst($status['status']) ?>
                                </span>
                            </div>
                            <span class="fw-semibold"><?= $status['count'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-semibold">Top Selling Products</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Units Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">No sales data available</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($topProducts as $product): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= number_format($product['total_sold']) ?></td>
                                    <td class="fw-semibold"><?= formatPrice($product['revenue']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const dailySalesData = <?= json_encode($dailySales) ?>;

const labels = dailySalesData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
});

const revenueData = dailySalesData.map(item => parseFloat(item.revenue));
const ordersData = dailySalesData.map(item => parseInt(item.orders));

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revenue ($)',
            data: revenueData,
            borderColor: 'rgb(13, 110, 253)',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../../includes/admin_footer.php'; ?>

