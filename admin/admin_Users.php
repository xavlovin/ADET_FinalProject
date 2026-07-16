<?php
include '../includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_user') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $status = isset($_POST['enabled']) && $_POST['enabled'] === 'true' ? 'active' : 'inactive';

        $query = "INSERT INTO users (fullname, email, password, accesslevel, status) VALUES ('$name', '$email', '$password', '$role', '$status')";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Added new user: $email");
        header("Location: admin_Users.php");
        exit();
    }

    if ($_POST['action'] == 'edit_user') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $status = isset($_POST['enabled']) && $_POST['enabled'] === 'true' ? 'active' : 'inactive';

        $query = "UPDATE users SET accesslevel = '$role', status = '$status' WHERE email = '$email'";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Updated user: $email");
        header("Location: admin_Users.php");
        exit();
    }

    if ($_POST['action'] == 'delete_user') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $query = "DELETE FROM users WHERE email = '$email'";
        mysqli_query($conn, $query);
        logAudit($conn, $_SESSION['email'], "Deleted user: $email");
        header("Location: admin_Users.php");
        exit();
    }
}

// Fetch Users
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$whereClause = "";
if (!empty($search)) {
    $whereClause = "WHERE fullname LIKE '%$search%' OR email LIKE '%$search%'";
}

$query = "SELECT * FROM users $whereClause ORDER BY reg_date DESC";
$result = mysqli_query($conn, $query);
$users = array();
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

include '../includes/admin_header.php';
?>

<!-- Top Controls: Tab bar & Search -->
<div class="flex mb-6 w-full items-stretch" style="gap: 20px;">
    <!-- Tab bar -->
    <div class="inline-flex rounded-full p-1.5"
        style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
        <a href="admin_Users.php" class="rounded-full px-5 py-2 transition-all"
            style="background: #082820; color: #f5f2e8; font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.6px; text-decoration: none;">User
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
                
                fetch(url.toString())
                    .then(res => res.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newContainer = doc.querySelector('#users-table-container');
                        if (newContainer) {
                            document.querySelector('#users-table-container').innerHTML = newContainer.innerHTML;
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
        <input type="text" placeholder="Search by name or email..." x-model="searchQuery" @input="doSearch()"
            class="w-full h-full bg-transparent outline-none transition-all pl-11 pr-4 rounded-full"
            style="font-family: var(--font-sans); font-size: 13px; color: #082820;">
    </div>
</div>

<div x-data="{ 
    addingUser: false, 
    editingUser: null, 
    deletingUser: null,
    addForm: { name: '', email: '', password: '', confirmPassword: '', role: 'user', enabled: true },
    editForm: { role: 'user', enabled: true, confirmDelete: false },
    showPass: false,
    showConfirm: false,
    
    initials(name) {
        if(!name) return '??';
        return name.split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase();
    },
    
    openEdit(user) {
        this.editingUser = user;
        this.editForm.role = user.accesslevel;
        this.editForm.enabled = user.status === 'active';
        this.editForm.confirmDelete = false;
    }
}">

    <!-- Table Container -->
    <div id="users-table-container" class="overflow-hidden rounded-2xl"
        style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
        <!-- Header -->
        <div class="px-6 py-5 flex items-center justify-between" style="border-bottom: 1px solid rgba(15,61,46,0.05);">
            <div>
                <h3 style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #082820;">User
                    Roles</h3>
                <p class="mt-0.5" style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;">
                    Manage roles and account access for all registered users
                </p>
            </div>
            <button @click="addingUser = true; addForm = {role: 'user', enabled: true};"
                style="display: flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 12px; border: none; background: linear-gradient(170deg, #1f6a4d, #0f3d2e); font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.5px; color: #f5f2e8; cursor: pointer; flex-shrink: 0;">
                <i data-lucide="plus" class="h-3.5 w-3.5"></i> Add User
            </button>
        </div>

        <!-- Table header -->
        <div class="grid px-6 py-3"
            style="grid-template-columns: 2.5fr 160px 120px 96px; background: rgba(15,61,46,0.02); border-bottom: 1px solid rgba(15,61,46,0.08);">
            <span
                style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">User</span>
            <span
                style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Role</span>
            <span
                style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">Account</span>
            <span
                style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;"></span>
        </div>

        <?php if (empty($users)): ?>
            <div class="px-6 py-12 text-center">
                <p style="font-family: var(--font-serif); font-size: 15px; color: #6b7268;">No registered users yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($users as $u): ?>
                <div class="grid items-center px-6 py-4 transition-colors hover:bg-white/40"
                    style="grid-template-columns: 2.5fr 160px 120px 96px; border-bottom: 1px solid rgba(15,61,46,0.05); opacity: <?= $u['status'] === 'active' ? '1' : '0.6' ?>;">

                    <!-- User info -->
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full overflow-hidden"
                            style="background: <?= $u['accesslevel'] === 'admin' ? 'linear-gradient(135deg, #b08a4a, #8a6a34)' : 'linear-gradient(135deg, #0f3d2e, #1f6a4d)' ?>; color: #f5f2e8; font-family: var(--font-sans); font-size: 11px; font-weight: 600;">
                            <?php if (!empty($u['profile_picture'])): ?>
                                <img src="../<?= htmlspecialchars($u['profile_picture']) ?>" alt="Profile"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <?= strtoupper(substr($u['fullname'], 0, 2)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-family: var(--font-serif); font-size: 14px; font-weight: 500; color: #082820;">
                                <?= htmlspecialchars($u['fullname']) ?>
                            </div>
                            <div style="font-family: var(--font-sans); font-size: 11px; color: #6b7268;">
                                <?= htmlspecialchars($u['email']) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Role badge -->
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1"
                            style="background: <?= $u['accesslevel'] === 'admin' ? 'rgba(176,138,74,0.12)' : 'rgba(15,61,46,0.07)' ?>; font-family: var(--font-sans); font-size: 10px; font-weight: 500; color: <?= $u['accesslevel'] === 'admin' ? '#b08a4a' : '#1f6a4d' ?>;">
                            <i data-lucide="<?= $u['accesslevel'] === 'admin' ? 'shield-check' : 'user-circle-2' ?>"
                                class="h-3 w-3"></i>
                            <?= ucfirst($u['accesslevel']) ?>
                        </span>
                    </div>

                    <!-- Status badge -->
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1"
                            style="background: <?= $u['status'] === 'active' ? 'rgba(31,106,77,0.1)' : 'rgba(192,57,43,0.1)' ?>; font-family: var(--font-sans); font-size: 10px; font-weight: 500; color: <?= $u['status'] === 'active' ? '#1f6a4d' : '#c0392b' ?>;">
                            <i data-lucide="<?= $u['status'] === 'active' ? 'toggle-right' : 'toggle-left' ?>"
                                class="h-3.5 w-3.5"></i>
                            <?= $u['status'] === 'active' ? 'Enabled' : 'Disabled' ?>
                        </span>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                        <button @click='openEdit(<?= json_encode($u) ?>)'
                            style="background: none; border: none; cursor: pointer; padding: 8px; display: flex; align-items: center; justify-content: center;"
                            title="Edit user">
                            <i data-lucide="edit-3" class="h-4 w-4" style="color: #082820;"></i>
                        </button>
                        <button @click='deletingUser = <?= json_encode($u) ?>'
                            style="background: none; border: none; cursor: pointer; padding: 8px; display: flex; align-items: center; justify-content: center;"
                            title="Delete user">
                            <i data-lucide="trash-2" class="h-4 w-4" style="color: #c0392b;"></i>
                        </button>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ADD USER MODAL -->
    <div x-show="addingUser" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="addingUser = false">
        <div
            style="width: 440px; background: rgba(255,255,255,0.97); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden;">
            <div
                style="background: linear-gradient(170deg, #0f3d2e, #082820); padding: 28px 28px 24px; display: flex; align-items: center; gap: 14px;">
                <div
                    style="width: 52px; height: 52px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1.5px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="users" class="h-5 w-5" style="color: #d4b078;"></i>
                </div>
                <div style="flex: 1;">
                    <p
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 2px; text-transform: uppercase; color: #d4b078; margin-bottom: 4px;">
                        Admin · User Roles</p>
                    <p style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #f5f2e8;">Create
                        New User</p>
                </div>
                <button @click="addingUser = false"
                    style="background: rgba(255,255,255,0.1); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #f5f2e8; flex-shrink: 0;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <form action="admin_Users.php" method="POST" class="flex flex-col gap-3.5" style="padding: 24px 28px 28px;">
                <input type="hidden" name="action" value="add_user">
                <!-- Role -->
                <input type="hidden" name="role" x-model="addForm.role">
                <!-- Enabled -->
                <input type="hidden" name="enabled" x-model="addForm.enabled">



                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Full
                        Name</label>
                    <input type="text" name="name" x-model="addForm.name" required
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                        placeholder="e.g. Jane Doe">
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Email
                        Address</label>
                    <input type="email" name="email" x-model="addForm.email" required
                        style="width: 100%; padding: 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                        placeholder="e.g. jane@example.com">
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Password</label>
                    <div style="position: relative;">
                        <input :type="showPass ? 'text' : 'password'" name="password" x-model="addForm.password"
                            required minlength="6"
                            style="width: 100%; padding: 9px 40px 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                            placeholder="Min. 6 characters">
                        <button type="button" @click="showPass = !showPass"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; color: #6b7268; display: flex; align-items: center;">
                            <div x-show="!showPass"><i data-lucide="eye" class="h-4 w-4"></i></div>
                            <div x-show="showPass" style="display: none;"><i data-lucide="eye-off" class="h-4 w-4"></i>
                            </div>
                        </button>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 5px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Confirm
                        Password</label>
                    <div style="position: relative;">
                        <input :type="showConfirm ? 'text' : 'password'" x-model="addForm.confirmPassword" required
                            style="width: 100%; padding: 9px 40px 9px 12px; border-radius: 10px; border: 1px solid rgba(15,61,46,0.15); background: rgba(255,255,255,0.7); font-family: var(--font-sans); font-size: 13px; color: #082820; outline: none;"
                            :style="{ borderColor: addForm.confirmPassword && addForm.confirmPassword !== addForm.password ? 'rgba(192,57,43,0.5)' : '' }"
                            placeholder="Re-enter password">
                        <button type="button" @click="showConfirm = !showConfirm"
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; color: #6b7268; display: flex; align-items: center;">
                            <div x-show="!showConfirm"><i data-lucide="eye" class="h-4 w-4"></i></div>
                            <div x-show="showConfirm" style="display: none;"><i data-lucide="eye-off"
                                    class="h-4 w-4"></i></div>
                        </button>
                    </div>
                    <p x-show="addForm.confirmPassword && addForm.confirmPassword !== addForm.password"
                        style="font-family: var(--font-sans); font-size: 11px; color: #c0392b; margin-top: 2px;">
                        Passwords do not match.</p>
                    <p x-show="addForm.confirmPassword && addForm.confirmPassword === addForm.password && addForm.password.length >= 6"
                        style="font-family: var(--font-sans); font-size: 11px; color: #1f6a4d; margin-top: 2px; display: flex; align-items: center; gap: 4px;">
                        <i data-lucide="check" style="width: 12px; height: 12px;"></i> Passwords match.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Assign
                        Role</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button type="button" @click="addForm.role = 'user'"
                            :style="{ background: addForm.role === 'user' ? 'linear-gradient(135deg, #1f6a4d, #0f3d2e)' : 'rgba(15,61,46,0.05)', color: addForm.role === 'user' ? '#f5f2e8' : '#6b7268' }"
                            style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 0; border-radius: 12px; border: none; cursor: pointer; font-family: var(--font-sans); font-size: 13px; font-weight: 500; transition: all 0.15s;">
                            <i data-lucide="user-circle-2" class="h-4 w-4"></i> User
                        </button>
                        <button type="button" @click="addForm.role = 'admin'"
                            :style="{ background: addForm.role === 'admin' ? 'linear-gradient(135deg, #b08a4a, #8a6a34)' : 'rgba(15,61,46,0.05)', color: addForm.role === 'admin' ? '#f5f2e8' : '#6b7268' }"
                            style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 0; border-radius: 12px; border: none; cursor: pointer; font-family: var(--font-sans); font-size: 13px; font-weight: 500; transition: all 0.15s;">
                            <i data-lucide="shield-check" class="h-4 w-4"></i> Admin
                        </button>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.6px; text-transform: uppercase; color: #b08a4a;">Account
                        Status</label>
                    <button type="button" @click="addForm.enabled = !addForm.enabled"
                        :style="{ border: addForm.enabled ? '1.5px solid rgba(31,106,77,0.25)' : '1.5px solid rgba(192,57,43,0.25)', background: addForm.enabled ? 'rgba(31,106,77,0.06)' : 'rgba(192,57,43,0.06)' }"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; cursor: pointer;">
                        <span x-text="addForm.enabled ? 'Account Enabled' : 'Account Disabled'"
                            :style="{ color: addForm.enabled ? '#1f6a4d' : '#c0392b' }"
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 500;"></span>
                        <template x-if="addForm.enabled">
                            <i data-lucide="toggle-right" class="h-6 w-6 text-[#1f6a4d]"></i>
                        </template>
                        <template x-if="!addForm.enabled">
                            <i data-lucide="toggle-left" class="h-6 w-6 text-[#c0392b]"></i>
                        </template>
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 2px;">
                    <button type="button"
                        @click="addingUser = false; addForm = { name: '', email: '', password: '', confirmPassword: '', role: 'user', enabled: true }; showPass = false; showConfirm = false;"
                        style="padding: 12px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">Cancel</button>
                    <button type="submit"
                        :disabled="addForm.password !== addForm.confirmPassword || addForm.password.length < 6"
                        :style="{ opacity: (addForm.password !== addForm.confirmPassword || addForm.password.length < 6) ? '0.5' : '1', cursor: (addForm.password !== addForm.confirmPassword || addForm.password.length < 6) ? 'not-allowed' : 'pointer' }"
                        style="padding: 12px 0; border-radius: 12px; border: none; background: linear-gradient(170deg, #1f6a4d, #0f3d2e); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #f5f2e8; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i data-lucide="plus" class="h-3.5 w-3.5"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT USER MODAL -->
    <div x-show="editingUser !== null" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="editingUser = null">
        <div
            style="width: 400px; background: rgba(255,255,255,0.96); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden;">
            <div
                style="display: flex; flex-direction: column; align-items: center; padding-bottom: 24px; padding-top: 32px; background: linear-gradient(170deg, #0f3d2e, #082820); position: relative;">
                <button @click="editingUser = null"
                    style="position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.1); border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #f5f2e8;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" />
                    </svg>
                </button>

                <div style="width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-family: var(--font-sans); font-size: 22px; font-weight: 700; color: #f5f2e8; border: 3px solid rgba(255,255,255,0.15); margin-bottom: 12px;"
                    :style="{ background: editingUser?.accesslevel === 'admin' ? 'linear-gradient(135deg, #b08a4a, #8a6a34)' : 'linear-gradient(135deg, #1f6a4d, #0f3d2e)' }">
                    <span x-text="initials(editingUser?.fullname)"></span>
                </div>
                <p style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #f5f2e8; margin-bottom: 2px;"
                    x-text="editingUser?.fullname"></p>
                <p style="font-family: var(--font-sans); font-size: 12px; color: rgba(245,242,232,0.6);"
                    x-text="editingUser?.email"></p>
                <p style="font-family: var(--font-sans); font-size: 10px; color: rgba(245,242,232,0.4); margin-top: 4px; letter-spacing: 1px;"
                    x-text="'JOINED ' + (editingUser?.reg_date ? editingUser.reg_date.split(' ')[0] : '')"></p>
            </div>

            <form action="admin_Users.php" method="POST" style="padding: 24px 28px 28px;">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="email" :value="editingUser?.email">
                <input type="hidden" name="role" x-model="editForm.role">
                <input type="hidden" name="enabled" x-model="editForm.enabled">

                <div style="margin-bottom: 16px;">
                    <p
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 10px;">
                        Assign Role</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button type="button" @click="editForm.role = 'user'"
                            :style="{ background: editForm.role === 'user' ? 'linear-gradient(135deg, #1f6a4d, #0f3d2e)' : 'rgba(15,61,46,0.05)', color: editForm.role === 'user' ? '#f5f2e8' : '#6b7268' }"
                            style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 0; border-radius: 12px; border: none; cursor: pointer; font-family: var(--font-sans); font-size: 13px; font-weight: 500; transition: all 0.15s;">
                            <i data-lucide="user-circle-2" class="h-4 w-4"></i> User
                        </button>
                        <button type="button" @click="editForm.role = 'admin'"
                            :style="{ background: editForm.role === 'admin' ? 'linear-gradient(135deg, #b08a4a, #8a6a34)' : 'rgba(15,61,46,0.05)', color: editForm.role === 'admin' ? '#f5f2e8' : '#6b7268' }"
                            style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 11px 0; border-radius: 12px; border: none; cursor: pointer; font-family: var(--font-sans); font-size: 13px; font-weight: 500; transition: all 0.15s;">
                            <i data-lucide="shield-check" class="h-4 w-4"></i> Admin
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <p
                        style="font-family: var(--font-sans); font-size: 9px; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 10px;">
                        Account Status</p>
                    <button type="button" @click="editForm.enabled = !editForm.enabled"
                        :style="{ border: editForm.enabled ? '1.5px solid rgba(31,106,77,0.25)' : '1.5px solid rgba(192,57,43,0.25)', background: editForm.enabled ? 'rgba(31,106,77,0.06)' : 'rgba(192,57,43,0.06)' }"
                        style="width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-radius: 12px; cursor: pointer;">
                        <span x-text="editForm.enabled ? 'Account Enabled' : 'Account Disabled'"
                            :style="{ color: editForm.enabled ? '#1f6a4d' : '#c0392b' }"
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 500;"></span>
                        <template x-if="editForm.enabled">
                            <i data-lucide="toggle-right" class="h-6 w-6 text-[#1f6a4d]"></i>
                        </template>
                        <template x-if="!editForm.enabled">
                            <i data-lucide="toggle-left" class="h-6 w-6 text-[#c0392b]"></i>
                        </template>
                    </button>
                    <template x-if="!editForm.enabled">
                        <p style="font-family: var(--font-sans); font-size: 11px; color: #c0392b; margin-top: 6px;">This
                            user will not be able to sign in while disabled.</p>
                    </template>
                </div>

                <template x-if="editForm.confirmDelete">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <p
                            style="font-family: var(--font-sans); font-size: 12px; color: #c0392b; text-align: center; margin-bottom: 4px;">
                            Permanently remove this user?</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <button type="button" @click="editForm.confirmDelete = false"
                                style="padding: 11px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">Cancel</button>
                            <button type="button" @click="$refs.deleteFormInsideEdit.submit()"
                                style="padding: 11px 0; border-radius: 12px; border: none; background: #c0392b; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #fff; cursor: pointer;">Yes,
                                Delete</button>
                        </div>
                    </div>
                </template>
                <template x-if="!editForm.confirmDelete">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <button type="button" @click="editForm.confirmDelete = true"
                            style="padding: 11px 0; border-radius: 12px; border: 1px solid rgba(192,57,43,0.25); background: rgba(192,57,43,0.06); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #c0392b; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i data-lucide="trash-2" class="h-3.5 w-3.5"></i> Delete
                        </button>
                        <button type="submit"
                            style="padding: 11px 0; border-radius: 12px; border: none; background: linear-gradient(170deg, #1f6a4d, #0f3d2e); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #f5f2e8; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <i data-lucide="check" class="h-3.5 w-3.5"></i> Save Changes
                        </button>
                    </div>
                </template>
            </form>
            <form x-ref="deleteFormInsideEdit" action="admin_Users.php" method="POST" style="display: none;">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="email" :value="editingUser?.email">
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRM MODAL -->
    <div x-show="deletingUser !== null" class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none; background: rgba(8,40,32,0.45); backdrop-filter: blur(6px);"
        @click.self="deletingUser = null">
        <div
            style="width: 380px; background: rgba(255,255,255,0.97); border-radius: 28px; box-shadow: 0 32px 80px rgba(8,40,32,0.22); overflow: hidden; display: flex; flex-direction: column; align-items: center;">
            <div
                style="width: 100%; background: linear-gradient(170deg, #7f1d1d, #c0392b); padding: 32px 28px 28px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                <div
                    style="width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.12); border: 2px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="trash-2" class="h-6 w-6" style="color: #fff;"></i>
                </div>
                <div style="text-align: center;">
                    <p
                        style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #fff; margin-bottom: 4px;">
                        Delete User Account</p>
                    <p
                        style="font-family: var(--font-sans); font-size: 12px; color: rgba(255,255,255,0.7); line-height: 18px;">
                        This action is permanent and cannot be undone.</p>
                </div>
            </div>

            <div style="padding: 24px 28px 0; width: 100%;">
                <div
                    style="display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 14px; background: rgba(192,57,43,0.05); border: 1px solid rgba(192,57,43,0.12);">
                    <div style="width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-family: var(--font-sans); font-size: 13px; font-weight: 700; color: #f5f2e8;"
                        :style="{ background: deletingUser?.accesslevel === 'admin' ? 'linear-gradient(135deg, #b08a4a, #8a6a34)' : 'linear-gradient(135deg, #1f6a4d, #0f3d2e)' }">
                        <span x-text="initials(deletingUser?.fullname)"></span>
                    </div>
                    <div>
                        <p style="font-family: var(--font-serif); font-size: 15px; font-weight: 500; color: #082820;"
                            x-text="deletingUser?.fullname"></p>
                        <p style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;"
                            x-text="deletingUser?.email"></p>
                    </div>
                    <span
                        style="margin-left: auto; font-family: var(--font-sans); font-size: 10px; font-weight: 500; padding: 4px 10px; border-radius: 20px;"
                        :style="{ color: deletingUser?.accesslevel === 'admin' ? '#b08a4a' : '#1f6a4d', background: deletingUser?.accesslevel === 'admin' ? 'rgba(176,138,74,0.1)' : 'rgba(15,61,46,0.07)' }"
                        x-text="deletingUser?.accesslevel === 'admin' ? 'Administrator' : 'User'"></span>
                </div>

                <p
                    style="font-family: var(--font-sans); font-size: 13px; color: #6b7268; line-height: 20px; margin-top: 16px; text-align: center;">
                    Are you sure you want to permanently remove <strong style="color: #082820;"
                        x-text="deletingUser?.fullname"></strong> from the system? They will lose all access
                    immediately.
                </p>
            </div>

            <form action="admin_Users.php" method="POST"
                style="padding: 20px 28px 28px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="email" :value="deletingUser?.email">

                <button type="button" @click="deletingUser = null"
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