<?php
include '../includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminInitials = strtoupper(substr($_SESSION['fullname'] ?? 'AW', 0, 2));

// Fetch Inventory Categories and their Stock
$invQuery = "SELECT c.name as category_name, SUM(i.stock) as total_stock 
             FROM categories c 
             LEFT JOIN inventory i ON c.name = i.category 
             GROUP BY c.name 
             ORDER BY total_stock DESC";
$invRes = mysqli_query($conn, $invQuery);
$remainingData = [];
$i = 0;
if ($invRes) {
    while ($row = mysqli_fetch_assoc($invRes)) {
        $val = $row['total_stock'] ? (int) $row['total_stock'] : 0;
        // Only add to chart if there's stock, or we can add 0 and it won't render a slice
        $remainingData[] = [
            'name' => $row['category_name'],
            'value' => $val
        ];
        $i++;
    }
}
$baseColors = ['#0f3d2e', '#1f6a4d', '#9ab89a', '#b08a4a', '#d4b078', '#6b7268'];
$chartColors = [];
foreach ($remainingData as $idx => $r) {
    $chartColors[] = $baseColors[$idx % count($baseColors)];
}

// Fetch Audit Logs
$auditQuery = "SELECT * FROM audit_logs ORDER BY timestamp DESC LIMIT 20";
$auditRes = mysqli_query($conn, $auditQuery);
$auditLogs = array();
if ($auditRes) {
    while ($row = mysqli_fetch_assoc($auditRes)) {
        $auditLogs[] = $row;
    }
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
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Analytics
        Reports</a>
    <a href="admin_AuditReports.php" class="rounded-full px-5 py-2 transition-all"
        style="background: #082820; color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Audit
        Reports</a>
</div>

<div class="w-full flex-1">
    <div class="mb-8">
        <h2 style="font-family: var(--font-serif); font-size: 32px; font-weight: 500; color: #082820;">Audit Reports
        </h2>
        <p style="font-family: var(--font-sans); font-size: 14px; color: #6b7268; margin-top: 4px;">Monitor system
            activities and live inventory levels.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">

        <!-- Donut chart -->
        <div class="rounded-2xl p-6 flex flex-col"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <p
                style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                Widget · Live</p>
            <h3 class="mt-1" style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #082820;">
                Remaining Inventory Items</h3>

            <div class="mt-4 flex-1 flex items-center justify-center relative" style="min-height: 256px;">
                <?php if (empty($remainingData)): ?>
                    <p style="font-family: var(--font-sans); font-size: 13px; color: #6b7268;">No inventory data available.
                    </p>
                <?php else: ?>
                    <canvas id="inventoryChart" style="max-height: 250px;"></canvas>
                <?php endif; ?>
            </div>

            <?php if (!empty($remainingData)): ?>
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <?php foreach ($remainingData as $idx => $r): ?>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full shrink-0"
                                style="background: <?= $chartColors[$idx] ?>;"></span>
                            <span class="truncate"
                                style="font-family: var(--font-sans); font-size: 11px; color: #082820;"><?= htmlspecialchars($r['name']) ?></span>
                            <span
                                style="font-family: var(--font-sans); font-size: 11px; font-weight: 600; color: #6b7268; margin-left: auto;"><?= $r['value'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Audit log -->
        <div class="rounded-2xl p-6"
            style="background: linear-gradient(180deg, #082820, #0f3d2e); color: #f5f2e8; display: flex; flex-direction: column;">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase; color: #d4b078;">
                        Console · Live Feed</p>
                    <h3 class="mt-1" style="font-family: var(--font-serif); font-size: 20px; font-weight: 500;">Audit
                        Log Report</h3>
                </div>
                <div class="flex items-center gap-2 rounded-full border px-3 py-1.5"
                    style="border-color: rgba(212,176,120,0.35);">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full" style="background: #d4b078;"></span>
                    <span
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.5px; color: #d4b078;">SESSION:
                        <?= htmlspecialchars($adminInitials) ?></span>
                </div>
            </div>

            <div class="space-y-3 overflow-y-auto pr-2 no-scrollbar" style="max-height: 400px; flex: 1;">
                <?php if (empty($auditLogs)): ?>
                    <p
                        style="font-family: var(--font-sans); font-size: 13px; color: rgba(245,242,232,0.5); text-align: center; margin-top: 20px;">
                        No audit logs found.</p>
                <?php else: ?>
                    <?php foreach ($auditLogs as $log): ?>
                        <div class="flex gap-3 rounded-xl border p-3.5 transition-colors hover:bg-white/5"
                            style="border-color: rgba(212,176,120,0.12); background: rgba(255,255,255,0.03);">
                            <div class="shrink-0 tabular-nums"
                                style="font-family: ui-monospace, monospace; font-size: 10px; color: #d4b078; padding-top: 2px;">
                                <?= date('H:i:s', strtotime($log['timestamp'])) ?>
                            </div>
                            <div>
                                <div
                                    style="font-family: var(--font-sans); font-size: 12px; color: rgba(245,242,232,0.9); line-height: 1.4;">
                                    <?= htmlspecialchars($log['action']) ?>
                                </div>
                                <div class="mt-1"
                                    style="font-family: var(--font-sans); font-size: 10px; color: rgba(245,242,232,0.4);">
                                    user · <?= htmlspecialchars($log['admin_email']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php if (!empty($remainingData)): ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('inventoryChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_column($remainingData, 'name')) ?>,
                    datasets: [{
                        data: <?= json_encode(array_column($remainingData, 'value')) ?>,
                        backgroundColor: <?= json_encode($chartColors) ?>,
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#082820',
                            titleFont: { family: 'Inter', size: 12 },
                            bodyFont: { family: 'Inter', size: 12 },
                            padding: 10,
                            cornerRadius: 12,
                            displayColors: true
                        }
                    }
                }
            });
        });
    </script>
<?php endif; ?>

<?php include '../includes/admin_footer.php'; ?>