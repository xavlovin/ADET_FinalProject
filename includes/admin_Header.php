<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not admin
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['fullname'] ?? 'Admin';
$adminInitials = strtoupper(substr($adminName, 0, 2));

// Calculate Global Inventory Stats for Admin Header
if (isset($conn)) {
    $headerStatsQuery = "SELECT COUNT(*) as total_items, SUM(price * stock) as total_value, SUM(IF(stock < 15, 1, 0)) as low_stock FROM inventory";
    $headerStatsRes = mysqli_query($conn, $headerStatsQuery);
    if ($headerStatsRes) {
        $headerStats = mysqli_fetch_assoc($headerStatsRes);
        $hTotalItems = $headerStats['total_items'] ?: 0;
        $hTotalValue = $headerStats['total_value'] ?: 0;
        $hLowStockCount = $headerStats['low_stock'] ?: 0;
    } else {
        $hTotalItems = 0;
        $hTotalValue = 0;
        $hLowStockCount = 0;
    }
} else {
    $hTotalItems = 0;
    $hTotalValue = 0;
    $hLowStockCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Console - G-Health</title>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['var(--font-sans)'],
                        serif: ['var(--font-serif)'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --font-serif: 'Playfair Display', ui-serif, Georgia, serif;
            --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            --background: #fbfaf6;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--background);
            color: #1a201c;
            font-family: var(--font-sans);
            background-image:
                radial-gradient(ellipse at 15% 10%, rgba(154, 184, 154, 0.18), transparent 55%),
                radial-gradient(ellipse at 85% 90%, rgba(176, 138, 74, 0.10), transparent 60%),
                radial-gradient(ellipse at 50% 50%, rgba(15, 61, 46, 0.04), transparent 70%);
            background-attachment: fixed;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">
    <?php include 'admin_sidebar.php'; ?>
    <main class="flex flex-1 flex-col overflow-y-auto p-8 relative">

        <!-- GLOBAL SUMMARY STATS -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; width: 100%;">
            <div class="rounded-2xl p-5"
                style="background: #ffffff; border: 1px solid rgba(15,61,46,0.08); box-shadow: 0 10px 30px rgba(15,61,46,0.12); display: flex; align-items: center; gap: 16px;">
                <div
                    style="width: 48px; height: 48px; border-radius: 12px; background: rgba(15,61,46,0.06); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="package" class="h-6 w-6" style="color: #082820;"></i>
                </div>
                <div>
                    <p
                        style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: #6b7268; margin-bottom: 2px;">
                        Total Items</p>
                    <h4 style="font-family: var(--font-serif); font-size: 24px; font-weight: 600; color: #082820;">
                        <?= number_format($hTotalItems) ?>
                    </h4>
                </div>
            </div>

            <div class="rounded-2xl p-5"
                style="background: #ffffff; border: 1px solid rgba(15,61,46,0.08); box-shadow: 0 10px 30px rgba(15,61,46,0.12); display: flex; align-items: center; gap: 16px;">
                <div
                    style="width: 48px; height: 48px; border-radius: 12px; background: rgba(176,138,74,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="circle-dollar-sign" class="h-6 w-6" style="color: #b08a4a;"></i>
                </div>
                <div>
                    <p
                        style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: #6b7268; margin-bottom: 2px;">
                        Total Value</p>
                    <h4 style="font-family: var(--font-serif); font-size: 24px; font-weight: 600; color: #082820;">
                        ₱<?= number_format($hTotalValue, 2) ?></h4>
                </div>
            </div>

            <div class="rounded-2xl p-5"
                style="background: #ffffff; border: 1px solid rgba(15,61,46,0.08); box-shadow: 0 10px 30px rgba(15,61,46,0.12); display: flex; align-items: center; gap: 16px;">
                <div
                    style="width: 48px; height: 48px; border-radius: 12px; background: rgba(192,57,43,0.08); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="alert-triangle" class="h-6 w-6" style="color: #c0392b;"></i>
                </div>
                <div>
                    <p
                        style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; letter-spacing: 1px; text-transform: uppercase; color: #6b7268; margin-bottom: 2px;">
                        Low Stock</p>
                    <h4 style="font-family: var(--font-serif); font-size: 24px; font-weight: 600; color: #082820;">
                        <?= number_format($hLowStockCount) ?>
                    </h4>
                </div>
            </div>
        </div>