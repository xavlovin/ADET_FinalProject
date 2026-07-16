<?php
include '../includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Helper for building filter/sort URLs
function buildUrl($params)
{
    $current = $_GET;
    $current['page'] = 1; // Reset to page 1 on filter/sort change
    $merged = array_merge($current, $params);
    return '?' . http_build_query($merged);
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    // Directory for uploads
    $targetDir = "../assets/img/products/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if ($_POST['action'] == 'add_product') {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $tagline = mysqli_real_escape_string($conn, trim($_POST['tagline']));
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $price = (float) $_POST['price'];
        $stock = (int) $_POST['stock'];

        $imagePath = '';
        if (!empty($_FILES["image"]["name"])) {
            $fileName = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $imagePath = 'assets/img/products/' . $fileName;
            }
        }

        $query = "INSERT INTO inventory (product_name, tagline, category, price, stock, image_file) VALUES ('$name', '$tagline', '$category', $price, $stock, '$imagePath')";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Added product: $name");
        header("Location: admin_Inventory.php");
        exit();
    }

    if ($_POST['action'] == 'edit_product') {
        $id = (int) $_POST['id'];
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $tagline = mysqli_real_escape_string($conn, trim($_POST['tagline']));
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $price = (float) $_POST['price'];
        $stock = (int) $_POST['stock'];

        // Handle optional new image
        $imageUpdateSql = "";
        if (!empty($_FILES["image"]["name"])) {
            $fileName = time() . '_' . basename($_FILES["image"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $imagePath = 'assets/img/products/' . $fileName;
                $imageUpdateSql = ", image_file = '$imagePath'";
            }
        }

        $query = "UPDATE inventory SET product_name = '$name', tagline = '$tagline', category = '$category', price = $price, stock = $stock $imageUpdateSql WHERE id = $id";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Updated product: $name");
        header("Location: admin_Inventory.php");
        exit();
    }

    if ($_POST['action'] == 'delete_product') {
        $id = (int) $_POST['id'];

        // Optional: remove image file from server if it exists
        $fetchImg = mysqli_query($conn, "SELECT image_file FROM inventory WHERE id = $id");
        if ($row = mysqli_fetch_assoc($fetchImg)) {
            if (!empty($row['image_file']) && file_exists("../" . $row['image_file'])) {
                @unlink("../" . $row['image_file']);
            }
        }

        $query = "DELETE FROM inventory WHERE id = $id";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Deleted product ID: $id");
        header("Location: admin_Inventory.php");
        exit();
    }
}

// Fetch Categories for dropdowns
$catResult = mysqli_query($conn, "SELECT name, icon FROM categories ORDER BY name ASC");
$categories = array();
while ($c = mysqli_fetch_assoc($catResult)) {
    $categories[] = $c;
}

// Build WHERE and ORDER BY clauses based on filters
$whereClauses = [];
if (!empty($_GET['cat'])) {
    $c = mysqli_real_escape_string($conn, $_GET['cat']);
    $whereClauses[] = "i.category = '$c'";
}

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
    $whereClauses[] = "(i.product_name LIKE '%$search%' OR i.tagline LIKE '%$search%')";
}

$orderBy = "i.id DESC"; // default

if (!empty($_GET['stock'])) {
    $s = $_GET['stock'];
    if ($s === 'no_stock')
        $whereClauses[] = "i.stock = 0";
    else if ($s === 'low')
        $whereClauses[] = "i.stock > 0 AND i.stock <= 20";
    else if ($s === 'medium')
        $whereClauses[] = "i.stock > 20 AND i.stock <= 80";
    else if ($s === 'in_stock')
        $whereClauses[] = "i.stock > 80";
    else if ($s === 'asc')
        $orderBy = "i.stock ASC";
    else if ($s === 'desc')
        $orderBy = "i.stock DESC";
}

if (!empty($_GET['sort_price'])) {
    $sp = $_GET['sort_price'];
    if ($sp === 'asc')
        $orderBy = "i.price ASC";
    else if ($sp === 'desc')
        $orderBy = "i.price DESC";
}

$whereSql = empty($whereClauses) ? "" : "WHERE " . implode(" AND ", $whereClauses);

// Pagination setup
$limit = 7;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total records
$totalResult = mysqli_query($conn, "SELECT COUNT(*) as t FROM inventory i $whereSql");
$totalRow = mysqli_fetch_assoc($totalResult);
$totalProducts = $totalRow['t'];
$totalPages = max(1, ceil($totalProducts / $limit));

// Fetch Inventory items
$query = "SELECT i.*, c.icon as cat_icon FROM inventory i LEFT JOIN categories c ON i.category = c.name $whereSql ORDER BY $orderBy LIMIT $offset, $limit";
$result = mysqli_query($conn, $query);
$products = array();
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

include '../includes/admin_header.php';
?>

<!-- Top Controls: Tab bar & Search -->
<div class="flex mb-6 w-full items-stretch" style="gap: 20px;">
    <!-- Tab bar -->
    <div class="inline-flex rounded-full p-1.5"
        style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
        <a href="admin_Users.php" class="rounded-full px-5 py-2 transition-all"
            style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">User
            Roles</a>
        <a href="admin_Inventory.php" class="rounded-full px-5 py-2 transition-all"
            style="background: #082820; color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Live
            Inventory</a>
        <a href="admin_Categories.php" class="rounded-full px-5 py-2 transition-all"
            style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Categories</a>
        <a href="admin_Reports.php" class="rounded-full px-5 py-2 transition-all"
            style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Analytics
            Reports</a>
        <a href="admin_AuditReports.php" class="rounded-full px-5 py-2 transition-all"
            style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Audit
            Reports</a>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center relative flex-1 rounded-full"
        style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);"
        x-data="{
        searchQuery: '<?= isset($_GET['search']) ? addslashes(htmlspecialchars($_GET['search'])) : '' ?>',
        searchTimer: null,
        doSearch() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => {
                const url = new URL(window.location.href);
                if (this.searchQuery) {
                    url.searchParams.set('search', this.searchQuery);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', 1);
                
                fetch(url.toString())
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContainer = doc.querySelector('#inventory-table-container');
                        if (newContainer) {
                            document.querySelector('#inventory-table-container').innerHTML = newContainer.innerHTML;
                            if (window.lucide) {
                                lucide.createIcons();
                            }
                        }
                    });
                    
                window.history.replaceState({}, '', url.toString());
            }, 300);
        }
    }">
        <i data-lucide="search" class="h-4 w-4 absolute left-4" style="color: #6b7268;"></i>
        <input type="text" placeholder="Search products..." x-model="searchQuery" @input="doSearch()"
            class="w-full h-full bg-transparent outline-none transition-all pl-11 pr-4 rounded-full"
            style="font-family: var(--font-sans); font-size: 13px; color: #082820;">
    </div>
</div>

<div x-data="{ 
    editingProduct: null, 
    deletingProduct: null,
    previewAddImage: null,
    previewEditImage: null,
    
    handleImageAdd(e) {
        const file = e.target.files[0];
        if (file) {
            this.previewAddImage = URL.createObjectURL(file);
        }
    },
    
    openEdit(p) {
        this.editingProduct = p;
        this.previewEditImage = p.image_file ? '../' + p.image_file : null;
    },
    
    handleImageEdit(e) {
        const file = e.target.files[0];
        if (file) {
            this.previewEditImage = URL.createObjectURL(file);
        }
    }
}">

    <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">

        <!-- ADD PRODUCT CARD (left) -->
        <div class="rounded-2xl p-6 flex flex-col"
            style="width: 320px; flex-shrink: 0; background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div class="mb-5">
                <h3 style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #082820;">Add New
                    Product</h3>
                <p style="font-family: var(--font-sans); font-size: 12px; color: #6b7268; margin-top: 2px;">Fill in the
                    details to add a new item.</p>
            </div>

            <form action="admin_Inventory.php" method="POST" enctype="multipart/form-data"
                style="display: flex; flex-direction: column; gap: 10px; flex: 1;">
                <input type="hidden" name="action" value="add_product">

                <!-- Image upload zone -->
                <div style="display: flex; flex-direction: column; gap: 5px; margin-bottom: 2px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Product
                        Image</label>
                    <label
                        style="width: 100%; aspect-ratio: 16/9; border-radius: 12px; border: 1.5px dashed rgba(15,61,46,0.2); cursor: pointer; overflow: hidden; position: relative; display: flex; align-items: center; justify-content: center; background: rgba(15,61,46,0.02);">

                        <template x-if="previewAddImage">
                            <img :src="previewAddImage" style="width: 100%; height: 100%; object-fit: cover;" />
                        </template>
                        <template x-if="!previewAddImage">
                            <div
                                style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;">
                                <i data-lucide="image" class="h-6 w-6" style="color: rgba(15,61,46,0.25);"></i>
                                <span
                                    style="font-family: var(--font-sans); font-size: 11px; color: rgba(15,61,46,0.4);">Click
                                    to upload</span>
                            </div>
                        </template>

                        <input type="file" name="image" accept="image/*" style="display: none;"
                            @change="handleImageAdd">
                    </label>
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Product
                        Name</label>
                    <input type="text" name="name" required
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                        placeholder="e.g. Omega-3 Fish Oil">
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Description</label>
                    <input type="text" name="tagline"
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                        placeholder="e.g. Cold-pressed, molecular distilled">
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Category</label>
                    <select name="category" required
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none; cursor: pointer;">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label
                            style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Price
                            (₱)</label>
                        <input type="number" name="price" step="0.01" min="0" required
                            style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                            placeholder="0.00">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label
                            style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Stock</label>
                        <input type="number" name="stock" min="0" required
                            style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                            placeholder="0">
                    </div>
                </div>

                <button type="submit" class="transition-transform hover:-translate-y-0.5"
                    style="margin-top: 10px; width: 100%; background: linear-gradient(173deg, #1f6a4d, #0f3d2e); color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 1.8px; border: none; border-radius: 12px; padding: 11px 0; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px;">
                    <i data-lucide="plus" class="h-3.5 w-3.5"></i> ADD PRODUCT
                </button>
            </form>
        </div>

        <!-- PRODUCT TABLE (right) -->
        <div id="inventory-table-container" class="overflow-hidden rounded-2xl flex-1 min-w-0"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div class="px-6 py-5" style="border-bottom: 1px solid rgba(15,61,46,0.05);">
                <h3 style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #082820;">Live
                    Inventory Control</h3>
                <p style="font-family: var(--font-sans); font-size: 12px; color: #6b7268; margin-top: 2px;">Click the
                    edit icon on any row to modify its details.</p>
            </div>

            <div x-data="{ showCat: false, showPrice: false, showStock: false }" class="grid px-6 py-3"
                style="grid-template-columns: 56px 3fr 2fr 90px 90px 90px; background: rgba(15,61,46,0.02); border-bottom: 1px solid rgba(15,61,46,0.08);">
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Image</span>
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Product</span>

                <!-- Category Dropdown -->
                <div class="relative" @click.away="showCat = false">
                    <button @click="showCat = !showCat"
                        style="background: none; border: none; padding: 0; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; cursor: pointer; display: flex; align-items: center; gap: 4px; outline: none;">
                        CATEGORY
                        <i data-lucide="chevron-down" class="h-3 w-3"></i>
                    </button>
                    <div x-show="showCat"
                        style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; width: 180px; background: rgba(255,255,255,0.97); border-radius: 12px; box-shadow: 0 16px 40px rgba(15,61,46,0.15); border: 1px solid rgba(15,61,46,0.08); z-index: 10; overflow: hidden; padding: 4px;">
                        <a href="<?= buildUrl(['cat' => '']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">All
                            Categories</a>
                        <?php foreach ($categories as $c): ?>
                            <a href="<?= buildUrl(['cat' => $c['name']]) ?>" class="hover:bg-black/5"
                                style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;"><?= htmlspecialchars($c['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Dropdown -->
                <div class="relative" @click.away="showPrice = false">
                    <button @click="showPrice = !showPrice"
                        style="background: none; border: none; padding: 0; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; cursor: pointer; display: flex; align-items: center; gap: 4px; outline: none;">
                        PRICE
                        <i data-lucide="chevron-down" class="h-3 w-3"></i>
                    </button>
                    <div x-show="showPrice"
                        style="display: none; position: absolute; top: 100%; left: 0; margin-top: 8px; width: 160px; background: rgba(255,255,255,0.97); border-radius: 12px; box-shadow: 0 16px 40px rgba(15,61,46,0.15); border: 1px solid rgba(15,61,46,0.08); z-index: 10; overflow: hidden; padding: 4px;">
                        <a href="<?= buildUrl(['sort_price' => '']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Default</a>
                        <a href="<?= buildUrl(['sort_price' => 'desc']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Highest
                            to Lowest</a>
                        <a href="<?= buildUrl(['sort_price' => 'asc']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Lowest
                            to Highest</a>
                    </div>
                </div>

                <!-- Stock Dropdown -->
                <div class="relative" @click.away="showStock = false">
                    <button @click="showStock = !showStock"
                        style="background: none; border: none; padding: 0; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; cursor: pointer; display: flex; align-items: center; gap: 4px; outline: none;">
                        STOCK
                        <i data-lucide="chevron-down" class="h-3 w-3"></i>
                    </button>
                    <div x-show="showStock"
                        style="display: none; position: absolute; top: 100%; right: 0; margin-top: 8px; width: 160px; background: rgba(255,255,255,0.97); border-radius: 12px; box-shadow: 0 16px 40px rgba(15,61,46,0.15); border: 1px solid rgba(15,61,46,0.08); z-index: 10; overflow: hidden; padding: 4px;">
                        <a href="<?= buildUrl(['stock' => '']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">All
                            Stock</a>
                        <a href="<?= buildUrl(['stock' => 'in_stock']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #27ae60; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">In
                            Stock</a>
                        <a href="<?= buildUrl(['stock' => 'medium']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #f39c12; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Medium</a>
                        <a href="<?= buildUrl(['stock' => 'low']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #e67e22; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Low
                            Stock</a>
                        <a href="<?= buildUrl(['stock' => 'no_stock']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #c0392b; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">No
                            Stock</a>
                        <div style="height: 1px; background: rgba(15,61,46,0.08); margin: 4px 0;"></div>
                        <a href="<?= buildUrl(['stock' => 'asc']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Lowest
                            to Highest</a>
                        <a href="<?= buildUrl(['stock' => 'desc']) ?>" class="hover:bg-black/5"
                            style="display: block; padding: 8px 12px; font-family: var(--font-sans); font-size: 13px; color: #082820; text-decoration: none; border-radius: 8px; margin-bottom: 2px;">Highest
                            to Lowest</a>
                    </div>
                </div>

                <span></span>
            </div>

            <?php if (empty($products)): ?>
                <div class="px-6 py-10 text-center">
                    <p style="font-family: var(--font-serif); font-size: 15px; color: #6b7268;">No products yet. Add one
                        above.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <?php
                    $stock = $p['stock'];
                    $isNoStock = $stock == 0;
                    $isLowStock = $stock > 0 && $stock <= 20;
                    $isMediumStock = $stock > 20 && $stock <= 80;

                    if ($isNoStock) {
                        $dotColor = '#c0392b';
                        $pulseColor = '#e74c3c';
                        $textColor = '#c0392b';
                        $doPulse = true;
                    } elseif ($isLowStock) {
                        $dotColor = '#e67e22';
                        $pulseColor = '#f39c12';
                        $textColor = '#e67e22';
                        $doPulse = true;
                    } elseif ($isMediumStock) {
                        $dotColor = '#f1c40f';
                        $textColor = '#d4ac0d';
                        $doPulse = false;
                    } else {
                        $dotColor = '#27ae60';
                        $textColor = '#27ae60';
                        $doPulse = false;
                    }

                    $catIcon = $p['cat_icon'] ?: '📦';
                    ?>
                    <div class="grid items-center px-6 py-3.5 transition-colors hover:bg-white/40"
                        style="grid-template-columns: 56px 3fr 2fr 90px 90px 90px; border-bottom: 1px solid rgba(15,61,46,0.05);">
                        <div
                            style="width: 44px; height: 44px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: rgba(15,61,46,0.06); display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($p['image_file'])): ?>
                                <img src="../<?= htmlspecialchars($p['image_file']) ?>"
                                    alt="<?= htmlspecialchars($p['product_name']) ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i data-lucide="image" class="h-4 w-4" style="color: rgba(15,61,46,0.25);"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-family: var(--font-serif); font-size: 13px; font-weight: 500; color: #082820;">
                                <?= htmlspecialchars($p['product_name']) ?>
                            </div>
                            <div style="font-family: var(--font-sans); font-size: 11px; color: #6b7268;">
                                <?= htmlspecialchars($p['tagline']) ?>
                            </div>
                        </div>
                        <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-1"
                            style="background: rgba(15,61,46,0.06); font-family: var(--font-sans); font-size: 10px; color: #082820;">
                            <?= $catIcon ?>         <?= htmlspecialchars($p['category']) ?>
                        </span>
                        <span
                            style="font-family: var(--font-serif); font-size: 15px; font-weight: 600; background-image: linear-gradient(135deg, #d4b078, #b08a4a); -webkit-background-clip: text; background-clip: text; color: transparent;">
                            ₱<?= number_format($p['price'], 2) ?>
                        </span>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                                <?php if ($doPulse): ?>
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full"
                                        style="background: <?= $pulseColor ?>;"></span>
                                <?php endif; ?>
                                <span class="relative inline-flex h-2 w-2 rounded-full"
                                    style="background: <?= $dotColor ?>;"></span>
                            </span>
                            <span
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: <?= $textColor ?>;"><?= $p['stock'] ?></span>
                        </div>
                        <div class="flex justify-end gap-1">
                            <button @click='openEdit(<?= json_encode($p) ?>)'
                                class="rounded-full p-2 hover:bg-white/70 transition-colors"
                                style="background: none; border: none; cursor: pointer;" title="Edit">
                                <i data-lucide="edit-3" class="h-3.5 w-3.5" style="color: #082820;"></i>
                            </button>
                            <button @click='deletingProduct = <?= json_encode($p) ?>'
                                class="rounded-full p-2 hover:bg-white/70 transition-colors"
                                style="background: none; border: none; cursor: pointer;" title="Delete">
                                <i data-lucide="trash-2" class="h-3.5 w-3.5" style="color: #c0392b;"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination footer -->
            <?php if ($totalPages > 1): ?>
                <div
                    style="display: flex; align-items: center; justify-content: space-between; padding: 14px 24px; border-top: 1px solid rgba(15,61,46,0.07);">
                    <span style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;">
                        Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $totalProducts) ?> of <?= $totalProducts ?>
                        products
                    </span>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <a href="<?= buildUrl(['page' => max(1, $page - 1)]) ?>" <?= $page == 1 ? 'style="pointer-events: none; opacity: 0.35;"' : '' ?>
                            style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="chevron-left" class="h-4 w-4" style="color: #082820;"></i>
                        </a>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= buildUrl(['page' => $i]) ?>"
                                style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-family: var(--font-sans); font-size: 12px; font-weight: 500; <?= $i == $page ? 'background: linear-gradient(135deg, #1f6a4d, #0f3d2e); color: #f5f2e8;' : 'background: rgba(255,255,255,0.6); color: #082820;' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <a href="<?= buildUrl(['page' => min($totalPages, $page + 1)]) ?>" <?= $page == $totalPages ? 'style="pointer-events: none; opacity: 0.35;"' : '' ?>
                            style="width: 32px; height: 32px; border-radius: 8px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; text-decoration: none;">
                            <i data-lucide="chevron-right" class="h-4 w-4" style="color: #082820;"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- EDIT PRODUCT MODAL -->
    <div x-show="editingProduct !== null" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="editingProduct = null">
        <div
            style="width: 720px; background: rgba(255,255,255,0.97); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden;">
            <div
                style="background: linear-gradient(170deg, #0f3d2e, #082820); padding: 24px 28px; display: flex; align-items: center; gap: 16px;">
                <div style="width: 56px; height: 56px; border-radius: 14px; overflow: hidden; flex-shrink: 0; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; border: 1.5px solid rgba(255,255,255,0.15); cursor: pointer;"
                    @click="$refs.editImageInput.click()">
                    <template x-if="previewEditImage">
                        <img :src="previewEditImage" style="width: 100%; height: 100%; object-fit: cover;">
                    </template>
                    <template x-if="!previewEditImage">
                        <i data-lucide="image" class="h-5 w-5" style="color: rgba(255,255,255,0.4);"></i>
                    </template>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <p
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #d4b078; margin-bottom: 4px;">
                        Editing Product</p>
                    <p style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #f5f2e8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                        x-text="editingProduct?.product_name"></p>
                </div>
                <button @click="editingProduct = null"
                    style="background: rgba(255,255,255,0.1); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #f5f2e8; flex-shrink: 0;">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <form action="admin_Inventory.php" method="POST" enctype="multipart/form-data"
                style="padding: 24px 28px 28px; display: flex; gap: 24px;">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="id" :value="editingProduct?.id">
                <input type="file" name="image" accept="image/*" style="display: none;" x-ref="editImageInput"
                    @change="handleImageEdit">

                <!-- Left side: Image Upload -->
                <div @click="$refs.editImageInput.click()"
                    style="width: 280px; flex-shrink: 0; border-radius: 16px; border: 1px solid rgba(15,61,46,0.15); cursor: pointer; overflow: hidden; position: relative; padding: 0; background: rgba(15,61,46,0.02);">
                    <template x-if="previewEditImage">
                        <img :src="previewEditImage"
                            style="width: 100%; height: 100%; object-fit: cover; display: block; position: absolute; inset: 0;">
                    </template>
                    <template x-if="!previewEditImage">
                        <div
                            style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 280px; gap: 6px;">
                            <i data-lucide="image" class="h-6 w-6" style="color: rgba(15,61,46,0.25);"></i>
                            <span
                                style="font-family: var(--font-sans); font-size: 11px; color: rgba(15,61,46,0.4);">Click
                                to change image</span>
                        </div>
                    </template>
                </div>

                <!-- Right side: Inputs -->
                <div style="flex: 1; display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label
                            style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Product
                            Name</label>
                        <input type="text" name="name" :value="editingProduct?.product_name" required
                            style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label
                            style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Description</label>
                        <input type="text" name="tagline" :value="editingProduct?.tagline"
                            style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label
                            style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Category</label>
                        <select name="category" :value="editingProduct?.category" required
                            style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none; cursor: pointer;">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <label
                                style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Price
                                (₱)</label>
                            <input type="number" name="price" step="0.01" min="0" :value="editingProduct?.price"
                                required
                                style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;">
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 5px;">
                            <label
                                style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Stock</label>
                            <input type="number" name="stock" min="0" :value="editingProduct?.stock" required
                                style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;">
                        </div>
                    </div>

                    <div style="flex: 1;"></div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                        <button type="button" @click="editingProduct = null"
                            style="padding: 12px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">
                            Cancel
                        </button>
                        <button type="submit"
                            style="padding: 12px 0; border-radius: 12px; border: none; background: linear-gradient(170deg, #1f6a4d, #0f3d2e); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #f5f2e8; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i data-lucide="check" class="h-3.5 w-3.5"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE PRODUCT CONFIRMATION MODAL -->
    <div x-show="deletingProduct !== null" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="deletingProduct = null">
        <div
            style="width: 380px; background: rgba(255,255,255,0.97); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden;">
            <div
                style="background: linear-gradient(170deg, #7f1d1d, #c0392b); padding: 32px 28px 28px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                <div
                    style="width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.12); border: 2px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="trash-2" class="h-6 w-6" style="color: #fff;"></i>
                </div>
                <div style="text-align: center;">
                    <p
                        style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #fff; margin-bottom: 4px;">
                        Delete Product</p>
                    <p
                        style="font-family: var(--font-sans); font-size: 12px; color: rgba(255,255,255,0.7); line-height: 18px;">
                        This action is permanent and cannot be undone.</p>
                </div>
            </div>

            <div style="padding: 24px 28px 0;">
                <div
                    style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 14px; background: rgba(192,57,43,0.05); border: 1px solid rgba(192,57,43,0.12);">
                    <div
                        style="width: 44px; height: 44px; border-radius: 10px; overflow: hidden; flex-shrink: 0; background: rgba(15,61,46,0.06); display: flex; align-items: center; justify-content: center;">
                        <template x-if="deletingProduct?.image_file">
                            <img :src="'../' + deletingProduct.image_file"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        </template>
                        <template x-if="!deletingProduct?.image_file">
                            <i data-lucide="image" class="h-4 w-4" style="color: rgba(15,61,46,0.25);"></i>
                        </template>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <p style="font-family: var(--font-serif); font-size: 14px; font-weight: 500; color: #082820; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                            x-text="deletingProduct?.product_name"></p>
                        <p style="font-family: var(--font-sans); font-size: 11px; color: #6b7268;">
                            <span x-text="deletingProduct?.category"></span> · ₱<span
                                x-text="deletingProduct?.price"></span>
                        </p>
                    </div>
                </div>
                <p
                    style="font-family: var(--font-sans); font-size: 13px; color: #6b7268; line-height: 20px; margin-top: 16px; text-align: center;">
                    Are you sure you want to permanently remove <strong style="color: #082820;"
                        x-text="deletingProduct?.product_name"></strong> from your inventory?
                </p>
            </div>

            <form action="admin_Inventory.php" method="POST"
                style="padding: 20px 28px 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="id" :value="deletingProduct?.id">

                <button type="button" @click="deletingProduct = null"
                    style="padding: 12px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">
                    Cancel
                </button>
                <button type="submit"
                    style="padding: 12px 0; border-radius: 12px; border: none; background: linear-gradient(135deg, #c0392b, #7f1d1d); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>