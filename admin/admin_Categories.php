<?php
include '../includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] == 'add_category') {
        $icon = mysqli_real_escape_string($conn, $_POST['icon'] ?: '📦');
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));

        if (!empty($name)) {
            // Check if exists
            $check = "SELECT id FROM categories WHERE name = '$name'";
            if (mysqli_num_rows(mysqli_query($conn, $check)) == 0) {
                $query = "INSERT INTO categories (name, icon) VALUES ('$name', '$icon')";
                mysqli_query($conn, $query);
                logAudit($conn, $_SESSION['email'], "Added category: $name");
            }
        }
        header("Location: admin_Categories.php");
        exit();
    }

    if ($_POST['action'] == 'edit_category') {
        $id = (int) $_POST['id'];
        $old_name = mysqli_real_escape_string($conn, $_POST['old_name']);
        $new_name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $icon = mysqli_real_escape_string($conn, $_POST['icon'] ?: '📦');

        if (!empty($new_name)) {
            $query = "UPDATE categories SET name = '$new_name', icon = '$icon' WHERE id = $id";
            mysqli_query($conn, $query);
            logAudit($conn, $_SESSION['email'], "Updated category: $new_name");

            if ($old_name !== $new_name) {
                // Update products that were linked to this category
                $updateProducts = "UPDATE inventory SET category = '$new_name' WHERE category = '$old_name'";
                mysqli_query($conn, $updateProducts);
            }
        }
        header("Location: admin_Categories.php");
        exit();
    }

    if ($_POST['action'] == 'delete_category') {
        $id = (int) $_POST['id'];

        $query = "DELETE FROM categories WHERE id = $id";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Deleted category ID: $id");
        header("Location: admin_Categories.php");
        exit();
    }
}

// Pagination setup
$limit = 8;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1)
    $page = 1;
$offset = ($page - 1) * $limit;

// Fetch total records
$totalResult = mysqli_query($conn, "SELECT COUNT(*) as t FROM categories");
$totalRow = mysqli_fetch_assoc($totalResult);
$totalCategories = $totalRow['t'];
$totalPages = max(1, ceil($totalCategories / $limit));

// Fetch Categories and Product Counts
$query = "SELECT c.*, COUNT(i.id) as product_count FROM categories c LEFT JOIN inventory i ON c.name = i.category GROUP BY c.id ORDER BY c.id DESC LIMIT $offset, $limit";
$result = mysqli_query($conn, $query);
$categories = array();
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
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
        style="background: #082820; color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Categories</a>
    <a href="admin_Reports.php" class="rounded-full px-5 py-2 transition-all"
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Analytics
        Reports</a>
    <a href="admin_AuditReports.php" class="rounded-full px-5 py-2 transition-all"
        style="background: transparent; color: #082820; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">Audit
        Reports</a>
</div>

<div x-data="{ 
    editingCat: null, 
    deletingCat: null,
    editForm: { id: '', old_name: '', name: '', icon: '' },
    
    openEdit(c) {
        this.editingCat = c;
        this.editForm.id = c.id;
        this.editForm.old_name = c.name;
        this.editForm.name = c.name;
        this.editForm.icon = c.icon;
    }
}">

    <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">

        <!-- ADD CATEGORY CARD (left, fixed width) -->
        <div class="rounded-2xl p-6 flex flex-col"
            style="width: 320px; flex-shrink: 0; background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div class="mb-5">
                <h3 style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #082820;">Add
                    Category</h3>
                <p style="font-family: var(--font-sans); font-size: 12px; color: #6b7268; margin-top: 2px;">Create a new
                    product category with an optional emoji icon.</p>
            </div>

            <form action="admin_Categories.php" method="POST"
                style="display: flex; flex-direction: column; gap: 12px; flex: 1;">
                <input type="hidden" name="action" value="add_category">

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Icon
                        (emoji)</label>
                    <input type="text" name="icon"
                        style="width: 100%; text-align: center; font-size: 20px; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); outline: none;"
                        placeholder="💊" maxlength="2">
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Category
                        Name</label>
                    <input type="text" name="name" required
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                        placeholder="e.g. Eye Health">
                </div>

                <button type="submit" class="transition-transform hover:-translate-y-0.5"
                    style="margin-top: 20px; width: 100%; background: linear-gradient(173deg, #1f6a4d, #0f3d2e); color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 1.8px; border: none; border-radius: 12px; padding: 11px 0; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 7px;">
                    <i data-lucide="plus" class="h-3.5 w-3.5"></i> ADD CATEGORY
                </button>
            </form>
        </div>

        <!-- ALL CATEGORIES TABLE (right, grows) -->
        <div class="overflow-hidden rounded-2xl"
            style="flex: 1; min-width: 0; background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
            <div class="px-6 py-5" style="border-bottom: 1px solid rgba(15,61,46,0.05);">
                <h3 style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #082820;">All
                    Categories</h3>
                <p style="font-family: var(--font-sans); font-size: 12px; color: #6b7268; margin-top: 2px;">
                    <?= count($categories) ?> <?= count($categories) === 1 ? 'category' : 'categories' ?> · rename or
                    remove any entry below
                </p>
            </div>

            <div class="grid px-6 py-3"
                style="grid-template-columns: 40px 1fr 110px 90px; background: rgba(15,61,46,0.02); border-bottom: 1px solid rgba(15,61,46,0.08);">
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Icon</span>
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Name</span>
                <span
                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Products</span>
                <span></span>
            </div>

            <?php if (empty($categories)): ?>
                <div class="px-6 py-10 text-center">
                    <p style="font-family: var(--font-serif); font-size: 15px; color: #6b7268;">No categories yet. Add one
                        to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $c): ?>
                    <div class="grid items-center px-6 py-3.5 transition-colors hover:bg-white/40"
                        style="grid-template-columns: 40px 1fr 110px 90px; border-bottom: 1px solid rgba(15,61,46,0.05);">
                        <span style="font-size: 18px;"><?= htmlspecialchars($c['icon']) ?></span>
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; color: #082820;"><?= htmlspecialchars($c['name']) ?></span>
                        <span style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;"><?= $c['product_count'] ?>
                            item<?= $c['product_count'] != 1 ? 's' : '' ?></span>

                        <div class="flex justify-end gap-1">
                            <button @click='openEdit(<?= json_encode($c) ?>)' title="Edit"
                                class="rounded-full p-2 hover:bg-white/70 transition-colors"
                                style="background: none; border: none; cursor: pointer;">
                                <i data-lucide="edit-3" class="h-3.5 w-3.5" style="color: #082820;"></i>
                            </button>
                            <button @click='deletingCat = <?= json_encode($c) ?>' title="Delete"
                                class="rounded-full p-2 hover:bg-white/70 transition-colors"
                                style="background: none; border: none; cursor: pointer;">
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
                        Showing <?= $offset + 1 ?>–<?= min($offset + $limit, $totalCategories) ?> of <?= $totalCategories ?>
                        categories
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

    </div>

    <!-- EDIT CATEGORY MODAL -->
    <div x-show="editingCat !== null" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="editingCat = null">
        <div
            style="width: 420px; background: rgba(255,255,255,0.97); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden;">
            <div
                style="background: linear-gradient(170deg, #0f3d2e, #082820); padding: 28px 28px 24px; display: flex; align-items: center; gap: 14px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.1); border: 1.5px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0;"
                    x-text="editForm.icon || '📦'">
                </div>
                <div style="flex: 1; min-width: 0;">
                    <p
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #d4b078; margin-bottom: 4px;">
                        Editing Category</p>
                    <p style="font-family: var(--font-serif); font-size: 18px; font-weight: 500; color: #f5f2e8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                        x-text="editForm.old_name"></p>
                </div>
                <button @click="editingCat = null"
                    style="background: rgba(255,255,255,0.1); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #f5f2e8; flex-shrink: 0;">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <form action="admin_Categories.php" method="POST"
                style="padding: 24px 28px 28px; display: flex; flex-direction: column; gap: 14px;">
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="id" x-model="editForm.id">
                <input type="hidden" name="old_name" x-model="editForm.old_name">

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Icon
                        (emoji)</label>
                    <input type="text" name="icon" x-model="editForm.icon"
                        style="width: 100%; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 22px; text-align: center; color: #082820; outline: none;"
                        placeholder="📦" maxlength="2">
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Category
                        Name</label>
                    <input type="text" name="name" x-model="editForm.name" required
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                        placeholder="e.g. Eye Health">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 6px;">
                    <button type="button" @click="editingCat = null"
                        style="padding: 12px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding: 12px 0; border-radius: 12px; border: none; background: linear-gradient(170deg, #1f6a4d, #0f3d2e); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #f5f2e8; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i data-lucide="check" class="h-3.5 w-3.5"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CATEGORY CONFIRMATION MODAL -->
    <div x-show="deletingCat !== null" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="deletingCat = null">
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
                        Delete Category</p>
                    <p
                        style="font-family: var(--font-sans); font-size: 12px; color: rgba(255,255,255,0.7); line-height: 18px;">
                        This action is permanent and cannot be undone.</p>
                </div>
            </div>

            <div style="padding: 24px 28px 0;">
                <div
                    style="display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-radius: 14px; background: rgba(192,57,43,0.05); border: 1px solid rgba(192,57,43,0.12);">
                    <span style="font-size: 28px; flex-shrink: 0;" x-text="deletingCat?.icon"></span>
                    <div style="flex: 1; min-width: 0;">
                        <p style="font-family: var(--font-serif); font-size: 15px; font-weight: 500; color: #082820;"
                            x-text="deletingCat?.name"></p>
                        <p style="font-family: var(--font-sans); font-size: 11px; color: #6b7268;">
                            <span x-text="deletingCat?.product_count"></span> products in this category
                        </p>
                    </div>
                </div>
                <p
                    style="font-family: var(--font-sans); font-size: 13px; color: #6b7268; line-height: 20px; margin-top: 16px; text-align: center;">
                    Are you sure you want to permanently remove the <strong style="color: #082820;"
                        x-text="deletingCat?.name"></strong> category? Products in this category will not be deleted.
                </p>
            </div>

            <form action="admin_Categories.php" method="POST"
                style="padding: 20px 28px 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="id" :value="deletingCat?.id">

                <button type="button" @click="deletingCat = null"
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