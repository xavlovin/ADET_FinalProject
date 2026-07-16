<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$adminInitials = strtoupper(substr($_SESSION['fullname'] ?? 'AW', 0, 2));
$adminName = $_SESSION['fullname'] ?? 'Amara W.';

$stockData = ['in_stock' => 0, 'medium' => 0, 'low_stock' => 0, 'no_stock' => 0];
if (isset($conn)) {
  $stockQ = mysqli_query($conn, "SELECT 
        SUM(CASE WHEN stock > 80 THEN 1 ELSE 0 END) as in_stock,
        SUM(CASE WHEN stock > 20 AND stock <= 80 THEN 1 ELSE 0 END) as medium,
        SUM(CASE WHEN stock > 0 AND stock <= 20 THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as no_stock
    FROM inventory");
  if ($stockQ) {
    $stockData = mysqli_fetch_assoc($stockQ);
  }
}
?>
<div x-data="{ showLogoutModal: false }" style="padding: 12px 0 12px 12px; flex-shrink: 0;">
  <aside class="flex flex-col h-full"
    style="width: 240px; background: linear-gradient(180deg, #0f3d2e 0%, #082820 100%); color: #f5f2e8; border-radius: 20px; overflow: hidden;">
    <!-- Logo -->
    <div class="px-5 py-5" style="border-bottom: 1px solid rgba(212,176,120,0.1);">
      <div class="flex items-center gap-2">
        <div style="width: 32px; height: 32px; flex-shrink: 0; position: relative;">
          <svg class="absolute block inset-0 size-full" style="width: 100%; height: 100%;" fill="none"
            preserveAspectRatio="none" viewBox="0 0 40 40">
            <g>
              <path
                d="M0 20C0 8.95431 8.95431 0 20 0V0C31.0457 0 40 8.95431 40 20V20C40 31.0457 31.0457 40 20 40V40C8.95431 40 0 31.0457 0 20V20Z"
                fill="#F5F2E8" />
              <path
                d="M7.79433 8L9.02675 9.75054C9.42591 10.3164 10.2224 11.3954 12.1998 12.2452C14.2103 13.1067 17.5065 13.7607 22.931 13.279C26.6467 12.9501 29.2421 14.2777 30.8884 16.3259C32.4997 18.331 33.1233 20.9313 33.1233 23.0657C33.1233 23.5147 33.1067 23.9938 33.0736 24.5029C31.076 22.58 28.2985 21.6167 25.5632 21.1075C21.8549 20.5436 17.8597 20.2362 15.2808 17.1032C15.0196 16.786 14.5414 16.9035 14.5837 17.3225C14.7823 19.2513 16.6144 20.8863 17.9756 21.8418C22.335 24.9082 28.5928 21.6108 31.9553 27.8552C32.6457 29.0195 33.7061 31.374 34.489 33.7862C33.5797 34.6978 33.0871 35.1439 32.1954 35.8515C31.9422 34.0704 31.4608 32.3421 30.6437 30.763C28.8429 32.6329 25.8851 33.8352 21.167 33.8352C17.7567 33.8352 15.0601 32.77 12.9815 31.0253C10.914 29.2885 9.50684 26.9231 8.58529 24.4011C6.74586 19.3844 6.74586 13.561 7.38966 10.1461L7.79433 8Z"
                fill="#0F3D2E" />
            </g>
          </svg>
        </div>
        <div
          style="font-family: var(--font-serif); font-size: 22px; font-weight: 600; letter-spacing: -0.5px; color: #f5f2e8;">
          NutriLife+
        </div>
      </div>
      <div
        style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 2px; text-transform: uppercase; color: #d4b078; margin-top: 4px;">
        Seller Console</div>
    </div>

    <!-- Nav items -->
    <nav class="flex flex-col gap-1 px-3 pt-4 flex-1" style="overflow-y: auto;">
      <a href="admin_Users.php" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all text-left"
        style="<?= $currentPage == 'admin_Users.php' ? 'background: rgba(255,255,255,0.1); color: #f5f2e8; border-left: 3px solid #d4b078;' : 'background: transparent; color: rgba(245,242,232,0.6); border-left: 3px solid transparent;' ?>">
        <i data-lucide="users" class="h-4 w-4 shrink-0"></i>
        <span
          style="font-family: var(--font-sans); font-size: 13px; font-weight: <?= $currentPage == 'admin_Users.php' ? '500' : '400' ?>;">User
          Roles</span>
      </a>
      <a href="admin_Inventory.php" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all text-left"
        style="<?= $currentPage == 'admin_Inventory.php' ? 'background: rgba(255,255,255,0.1); color: #f5f2e8; border-left: 3px solid #d4b078;' : 'background: transparent; color: rgba(245,242,232,0.6); border-left: 3px solid transparent;' ?>">
        <i data-lucide="boxes" class="h-4 w-4 shrink-0"></i>
        <span
          style="font-family: var(--font-sans); font-size: 13px; font-weight: <?= $currentPage == 'admin_Inventory.php' ? '500' : '400' ?>;">Live
          Inventory</span>
      </a>
      <a href="admin_Categories.php" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all text-left"
        style="<?= $currentPage == 'admin_Categories.php' ? 'background: rgba(255,255,255,0.1); color: #f5f2e8; border-left: 3px solid #d4b078;' : 'background: transparent; color: rgba(245,242,232,0.6); border-left: 3px solid transparent;' ?>">
        <i data-lucide="tag" class="h-4 w-4 shrink-0"></i>
        <span
          style="font-family: var(--font-sans); font-size: 13px; font-weight: <?= $currentPage == 'admin_Categories.php' ? '500' : '400' ?>;">Categories</span>
      </a>
      <a href="admin_Reports.php" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all text-left"
        style="<?= $currentPage == 'admin_Reports.php' ? 'background: rgba(255,255,255,0.1); color: #f5f2e8; border-left: 3px solid #d4b078;' : 'background: transparent; color: rgba(245,242,232,0.6); border-left: 3px solid transparent;' ?>">
        <i data-lucide="line-chart" class="h-4 w-4 shrink-0"></i>
        <span
          style="font-family: var(--font-sans); font-size: 13px; font-weight: <?= $currentPage == 'admin_Reports.php' ? '500' : '400' ?>;">Analytics
          Reports</span>
      </a>
      <a href="admin_AuditReports.php" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all text-left"
        style="<?= $currentPage == 'admin_AuditReports.php' ? 'background: rgba(255,255,255,0.1); color: #f5f2e8; border-left: 3px solid #d4b078;' : 'background: transparent; color: rgba(245,242,232,0.6); border-left: 3px solid transparent;' ?>">
        <i data-lucide="shield-alert" class="h-4 w-4 shrink-0"></i>
        <span
          style="font-family: var(--font-sans); font-size: 13px; font-weight: <?= $currentPage == 'admin_AuditReports.php' ? '500' : '400' ?>;">Audit
          Reports</span>
      </a>
    </nav>

    <!-- Stock Status Widget -->
    <div class="px-5 py-4" style="border-top: 1px solid rgba(212,176,120,0.1);">
      <div
        style="font-family: var(--font-sans); font-size: 9px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: #d4b078; margin-bottom: 12px; padding-left: 2px;">
        Stock Status
      </div>
      <div class="flex flex-col gap-2.5">
        <div class="flex items-center justify-between px-2">
          <div class="flex items-center gap-2.5">
            <span
              style="display: block; width: 6px; height: 6px; border-radius: 50%; background: #27ae60; box-shadow: 0 0 6px rgba(39,174,96,0.6);"></span>
            <span
              style="font-family: var(--font-sans); font-size: 12px; font-weight: 500; color: rgba(245,242,232,0.8);">In
              Stock</span>
          </div>
          <span
            style="font-family: var(--font-sans); font-size: 12px; font-weight: 600; color: #f5f2e8;"><?= (int) $stockData['in_stock'] ?></span>
        </div>
        <div class="flex items-center justify-between px-2">
          <div class="flex items-center gap-2.5">
            <span
              style="display: block; width: 6px; height: 6px; border-radius: 50%; background: #f39c12; box-shadow: 0 0 6px rgba(243,156,18,0.6);"></span>
            <span
              style="font-family: var(--font-sans); font-size: 12px; font-weight: 500; color: rgba(245,242,232,0.8);">Medium</span>
          </div>
          <span
            style="font-family: var(--font-sans); font-size: 12px; font-weight: 600; color: #f5f2e8;"><?= (int) $stockData['medium'] ?></span>
        </div>
        <div class="flex items-center justify-between px-2">
          <div class="flex items-center gap-2.5">
            <span
              style="display: block; width: 6px; height: 6px; border-radius: 50%; background: #e67e22; box-shadow: 0 0 6px rgba(230,126,34,0.6);"></span>
            <span
              style="font-family: var(--font-sans); font-size: 12px; font-weight: 500; color: rgba(245,242,232,0.8);">Low
              Stock</span>
          </div>
          <span
            style="font-family: var(--font-sans); font-size: 12px; font-weight: 600; color: #f5f2e8;"><?= (int) $stockData['low_stock'] ?></span>
        </div>
        <div class="flex items-center justify-between px-2">
          <div class="flex items-center gap-2.5">
            <span
              style="display: block; width: 6px; height: 6px; border-radius: 50%; background: #c0392b; box-shadow: 0 0 6px rgba(192,57,43,0.6);"></span>
            <span
              style="font-family: var(--font-sans); font-size: 12px; font-weight: 500; color: rgba(245,242,232,0.8);">No
              Stocks</span>
          </div>
          <span
            style="font-family: var(--font-sans); font-size: 12px; font-weight: 600; color: #f5f2e8;"><?= (int) $stockData['no_stock'] ?></span>
        </div>
      </div>
    </div>

    <!-- Session + Logout -->
    <div class="px-4 pb-6 pt-4" style="border-top: 1px solid rgba(212,176,120,0.1);">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-full"
          style="background: #b08a4a; color: #082820; font-family: var(--font-sans); font-size: 11px; font-weight: 600;">
          <?= htmlspecialchars($adminInitials) ?>
        </div>
        <div class="flex-1 min-w-0">
          <div
            style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #f5f2e8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            <?= htmlspecialchars($adminName) ?>
          </div>
          <div style="font-family: var(--font-sans); font-size: 10px; color: rgba(245,242,232,0.5);">Administrator</div>
        </div>
        <button type="button" title="Sign out" @click="showLogoutModal = true"
          class="rounded-full p-1.5 transition-opacity hover:opacity-70"
          style="background: none; border: none; cursor: pointer; color: rgba(245,242,232,0.6);">
          <i data-lucide="log-out" class="h-4 w-4"></i>
        </button>
      </div>
    </div>
  </aside>

  <!-- LOGOUT CONFIRM MODAL -->
  <div x-show="showLogoutModal" class="fixed inset-0 z-[100] flex items-center justify-center"
    style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
    @click.self="showLogoutModal = false">
    <div
      style="width: 380px; background: rgba(255,255,255,0.97); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden; display: flex; flex-direction: column; align-items: center;">
      <div
        style="width: 100%; background: linear-gradient(170deg, #7f1d1d, #c0392b); padding: 32px 28px 28px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
        <div
          style="width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.12); border: 2px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
          <i data-lucide="log-out" class="h-6 w-6" style="color: #fff;"></i>
        </div>
        <div style="text-align: center;">
          <p
            style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #fff; margin-bottom: 4px;">
            Sign Out</p>
          <p style="font-family: var(--font-sans); font-size: 12px; color: rgba(255,255,255,0.7); line-height: 18px;">
            Are you sure you want to log out of your session?</p>
        </div>
      </div>

      <div style="padding: 20px 28px 28px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <button type="button" @click="showLogoutModal = false"
          style="padding: 12px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">
          Cancel
        </button>
        <a href="../logout.php"
          style="padding: 12px 0; border-radius: 12px; border: none; background: linear-gradient(135deg, #c0392b, #7f1d1d); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;">
          <i data-lucide="log-out" class="h-3.5 w-3.5"></i> Yes, Sign Out
        </a>
      </div>
    </div>
  </div>

</div>