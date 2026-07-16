<?php
include '../includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Calculate Overall Stats
$salesQuery = "SELECT SUM(total_amount) as total_revenue, COUNT(id) as total_orders FROM orders";
$salesRes = mysqli_query($conn, $salesQuery);
$salesData = mysqli_fetch_assoc($salesRes);
$totalRevenue = $salesData['total_revenue'] ?: 0;
$totalOrders = $salesData['total_orders'] ?: 0;

$customersQuery = "SELECT COUNT(DISTINCT user_id) as active_customers FROM orders";
$customersRes = mysqli_query($conn, $customersQuery);
$activeCustomers = mysqli_fetch_assoc($customersRes)['active_customers'] ?: 0;

// Calculate Top Products
$topProductsQuery = "
    SELECT i.product_name, SUM(oi.quantity) as total_sold, SUM(oi.quantity * oi.price) as revenue
    FROM order_items oi
    JOIN inventory i ON oi.product_id = i.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
";
$topProductsRes = mysqli_query($conn, $topProductsQuery);
$topProducts = [];
while ($row = mysqli_fetch_assoc($topProductsRes)) {
    $topProducts[] = $row;
}

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;
$totalPages = max(1, ceil($totalOrders / $limit));

// Fetch Recent Orders
$recentOrdersQuery = "
    SELECT o.id, o.total_amount, o.status, o.created_at, u.fullname, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT $offset, $limit
";
$recentOrdersRes = mysqli_query($conn, $recentOrdersQuery);
$recentOrders = [];
while ($row = mysqli_fetch_assoc($recentOrdersRes)) {
    $recentOrders[] = $row;
}

include '../includes/admin_header.php';
?>

<!-- Tab bar -->
<div class="inline-flex self-start rounded-full p-1.5 mb-6"
    style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
    <a href="admin_Users.php" class="rounded-full px-5 py-2 transition-all"
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">User
        Roles</a>
    <a href="admin_Inventory.php" class="rounded-full px-5 py-2 transition-all"
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Live
        Inventory</a>
    <a href="admin_Categories.php" class="rounded-full px-5 py-2 transition-all"
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Categories</a>
    <a href="admin_Reports.php" class="rounded-full px-5 py-2 transition-all"
        style="background: #082820; color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Analytics
        Reports</a>
    <a href="admin_AuditReports.php" class="rounded-full px-5 py-2 transition-all"
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Audit
        Reports</a>
</div>

<div style="display: flex; flex-direction: column; gap: 20px;">

    <!-- Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
        <!-- Total Revenue -->
        <div class="rounded-2xl p-6 relative overflow-hidden"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div style="position: absolute; right: -20px; top: -20px; opacity: 0.05; transform: rotate(15deg);">
                <i data-lucide="dollar-sign" style="width: 120px; height: 120px;"></i>
            </div>
            <p
                style="font-family: var(--font-sans); font-size: 11px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a; margin-bottom: 8px;">
                Total Revenue</p>
            <p
                style="font-family: var(--font-serif); font-size: 36px; font-weight: 500; color: #082820; line-height: 1.1;">
                ₱<?= number_format($totalRevenue, 2) ?>
            </p>
            <div
                style="margin-top: 12px; display: inline-flex; align-items: center; gap: 4px; background: rgba(39,174,96,0.1); padding: 4px 8px; border-radius: 6px;">
                <i data-lucide="trending-up" class="h-3 w-3" style="color: #27ae60;"></i>
                <span style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; color: #27ae60;">All
                    time</span>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="rounded-2xl p-6 relative overflow-hidden"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div style="position: absolute; right: -20px; top: -20px; opacity: 0.05; transform: rotate(-10deg);">
                <i data-lucide="shopping-bag" style="width: 120px; height: 120px;"></i>
            </div>
            <p
                style="font-family: var(--font-sans); font-size: 11px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a; margin-bottom: 8px;">
                Total Orders</p>
            <p
                style="font-family: var(--font-serif); font-size: 36px; font-weight: 500; color: #082820; line-height: 1.1;">
                <?= number_format($totalOrders) ?>
            </p>
            <div
                style="margin-top: 12px; display: inline-flex; align-items: center; gap: 4px; background: rgba(15,61,46,0.06); padding: 4px 8px; border-radius: 6px;">
                <span
                    style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; color: #6b7268;">Completed</span>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="rounded-2xl p-6 relative overflow-hidden"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div style="position: absolute; right: -20px; top: -20px; opacity: 0.05; transform: rotate(5deg);">
                <i data-lucide="users" style="width: 120px; height: 120px;"></i>
            </div>
            <p
                style="font-family: var(--font-sans); font-size: 11px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a; margin-bottom: 8px;">
                Active Customers</p>
            <p
                style="font-family: var(--font-serif); font-size: 36px; font-weight: 500; color: #082820; line-height: 1.1;">
                <?= number_format($activeCustomers) ?>
            </p>
            <div
                style="margin-top: 12px; display: inline-flex; align-items: center; gap: 4px; background: rgba(15,61,46,0.06); padding: 4px 8px; border-radius: 6px;">
                <span style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; color: #6b7268;">With at
                    least 1 order</span>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">

        <!-- Recent Orders -->
        <div class="overflow-hidden rounded-2xl"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div class="px-6 py-5" style="border-bottom: 1px solid rgba(15,61,46,0.05);">
                <h3 style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #082820;">Recent
                    Orders</h3>
            </div>

            <div class="grid px-6 py-3"
                style="grid-template-columns: 80px 1fr 120px 100px; background: rgba(15,61,46,0.02); border-bottom: 1px solid rgba(15,61,46,0.08);">
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Order
                    #</span>
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Customer</span>
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Date</span>
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; text-align: right;">Amount</span>
            </div>

            <?php if (empty($recentOrders)): ?>
                <div class="px-6 py-10 text-center">
                    <p style="font-family: var(--font-serif); font-size: 15px; color: #6b7268;">No orders yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentOrders as $order): ?>
                    <div class="grid items-center px-6 py-3.5 transition-colors hover:bg-white/40"
                        style="grid-template-columns: 80px 1fr 120px 100px; border-bottom: 1px solid rgba(15,61,46,0.05);">
                        <span
                            style="font-family: var(--font-sans); font-size: 12px; font-weight: 600; color: #082820;">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        <div>
                            <div style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #082820;">
                                <?= htmlspecialchars($order['fullname']) ?>
                            </div>
                            <div style="font-family: var(--font-sans); font-size: 11px; color: #6b7268;">
                                <?= htmlspecialchars($order['email']) ?>
                            </div>
                        </div>
                        <span
                            style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;"><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                        <span
                            style="font-family: var(--font-serif); font-size: 14px; font-weight: 600; color: #082820; text-align: right;">₱<?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination footer -->
            <?php if ($totalPages > 1): ?>
                <div
                    style="display: flex; align-items: center; justify-content: space-between; padding: 14px 24px; border-top: 1px solid rgba(15,61,46,0.07);">
                    <span style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;">
                        Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $totalOrders) ?> of <?= $totalOrders ?> orders
                    </span>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <a href="?page=<?= max(1, $page - 1) ?>" <?= $page == 1 ? 'style="pointer-events: none; opacity: 0.35;"' : '' ?>
                            style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="chevron-left" class="h-4 w-4" style="color: #082820;"></i>
                        </a>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>"
                                style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-family: var(--font-sans); font-size: 12px; font-weight: 500; <?= $i == $page ? 'background: linear-gradient(135deg, #1f6a4d, #0f3d2e); color: #f5f2e8;' : 'background: rgba(255,255,255,0.6); color: #082820;' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <a href="?page=<?= min($totalPages, $page + 1) ?>" <?= $page == $totalPages ? 'style="pointer-events: none; opacity: 0.35;"' : '' ?>
                            style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="chevron-right" class="h-4 w-4" style="color: #082820;"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Top Products -->
        <div class="overflow-hidden rounded-2xl"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); align-self: start;">
            <div class="px-6 py-5" style="border-bottom: 1px solid rgba(15,61,46,0.05);">
                <h3 style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #082820;">Top
                    Products</h3>
            </div>

            <?php if (empty($topProducts)): ?>
                <div class="px-6 py-8 text-center">
                    <p style="font-family: var(--font-sans); font-size: 13px; color: #6b7268;">Not enough data yet.</p>
                </div>
            <?php else: ?>
                <div class="p-2">
                    <?php foreach ($topProducts as $idx => $prod): ?>
                        <div class="flex items-center gap-4 p-4 rounded-xl transition-colors hover:bg-white/40">
                            <div
                                style="width: 28px; height: 28px; border-radius: 8px; background: <?= $idx === 0 ? 'linear-gradient(135deg, #d4b078, #b08a4a)' : 'rgba(15,61,46,0.06)' ?>; display: flex; align-items: center; justify-content: center; font-family: var(--font-sans); font-size: 12px; font-weight: 600; color: <?= $idx === 0 ? '#fff' : '#082820' ?>;">
                                <?= $idx + 1 ?>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <p
                                    style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #082820; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars($prod['product_name']) ?>
                                </p>
                                <p style="font-family: var(--font-sans); font-size: 11px; color: #6b7268;">
                                    <?= $prod['total_sold'] ?> units sold
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php include '../includes/admin_footer.php'; ?>