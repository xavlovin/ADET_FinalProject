<?php
include 'includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Protect route
if (!isset($_SESSION['user_id']) || $_SESSION['accesslevel'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_pw = $_POST['current_pw'];
    $new_pw = $_POST['new_pw'];
    $confirm_pw = $_POST['confirm_pw'];

    // Fetch user to check password
    $query = "SELECT password FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if (password_verify($current_pw, $user['password']) || $current_pw == $user['password']) {
        if ($new_pw === $confirm_pw) {
            if (strlen($new_pw) >= 6) { // basic validation
                $hashed_pw = password_hash($new_pw, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = '$hashed_pw' WHERE id = '$user_id'";
                if (mysqli_query($conn, $update_query)) {
                    $message = "Password updated successfully!";
                } else {
                    $error = "Database error updating password.";
                }
            } else {
                $error = "New password must be at least 6 characters long.";
            }
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

include 'includes/header.php';
?>

<div class="px-4 md:px-8">
    <div class="mx-auto mt-6" style="max-width: 1440px;">

        <!-- Page header -->
        <div>
            <p
                style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #b08a4a;">
                Your Account
            </p>
            <h1
                style="font-family: var(--font-serif); font-size: 52px; font-weight: 500; line-height: 62px; color: #082820; margin-top: 8px;">
                Security Settings
            </h1>
        </div>

        <!-- Body: sidebar + content -->
        <form method="POST" action="">
            <div class="mt-8 flex items-start gap-6">

                <!-- LEFT SIDEBAR -->
                <?php include 'includes/settings_sidebar.php'; ?>

                <!-- RIGHT CONTENT -->
                <div class="flex-1 min-w-0 flex flex-col gap-4">

                    <?php if ($message): ?>
                        <div
                            style="background: rgba(15, 61, 46, 0.1); border: 1px solid #1f6a4d; border-radius: 14px; padding: 14px 19px; color: #1f6a4d; font-family: var(--font-sans); font-size: 14px; margin-bottom: 16px;">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div
                            style="background: rgba(212, 24, 61, 0.1); border: 1px solid #d4183d; border-radius: 14px; padding: 14px 19px; color: #d4183d; font-family: var(--font-sans); font-size: 14px; margin-bottom: 16px;">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Password panel -->
                    <div
                        style="background: rgba(255,255,255,0.55); border: 1.111px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 24px;">
                        <div
                            style="border-bottom: 1.111px solid rgba(15,61,46,0.07); padding: 28px 33px 17px; margin-bottom: 0;">
                            <p
                                style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #082820;">
                                Change Password</p>
                            <p
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; color: #6b7268; margin-top: 4px;">
                                Leave blank to keep your current password.
                            </p>
                        </div>

                        <div class="flex flex-col gap-4" style="padding: 20px 33px 29px;">
                            <div class="flex flex-col items-start w-full">
                                <p
                                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                    Current Password
                                </p>
                                <div class="relative w-full" style="height: 49px;">
                                    <input type="password" name="current_pw" placeholder="••••••••" required
                                        class="absolute inset-0 w-full outline-none focus:border-[#1f6a4d]"
                                        style="background: rgba(255,255,255,0.7); border: 1.111px solid rgba(15,61,46,0.12); border-radius: 14px; padding: 14px 19px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: #082820; transition: border-color 0.15s, box-shadow 0.15s;"
                                        onfocus="this.style.borderColor='#1f6a4d'; this.style.boxShadow='0 0 0 3px rgba(31,106,77,0.1)'"
                                        onblur="this.style.borderColor='rgba(15,61,46,0.12)'; this.style.boxShadow='none'">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="flex flex-col items-start w-full">
                                    <p
                                        style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                        New Password
                                    </p>
                                    <div class="relative w-full" style="height: 49px;">
                                        <input type="password" name="new_pw" placeholder="••••••••" required
                                            class="absolute inset-0 w-full outline-none focus:border-[#1f6a4d]"
                                            style="background: rgba(255,255,255,0.7); border: 1.111px solid rgba(15,61,46,0.12); border-radius: 14px; padding: 14px 19px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: #082820; transition: border-color 0.15s, box-shadow 0.15s;"
                                            onfocus="this.style.borderColor='#1f6a4d'; this.style.boxShadow='0 0 0 3px rgba(31,106,77,0.1)'"
                                            onblur="this.style.borderColor='rgba(15,61,46,0.12)'; this.style.boxShadow='none'">
                                    </div>
                                </div>

                                <div class="flex flex-col items-start w-full">
                                    <p
                                        style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                        Confirm New Password
                                    </p>
                                    <div class="relative w-full" style="height: 49px;">
                                        <input type="password" name="confirm_pw" placeholder="••••••••" required
                                            class="absolute inset-0 w-full outline-none focus:border-[#1f6a4d]"
                                            style="background: rgba(255,255,255,0.7); border: 1.111px solid rgba(15,61,46,0.12); border-radius: 14px; padding: 14px 19px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: #082820; transition: border-color 0.15s, box-shadow 0.15s;"
                                            onfocus="this.style.borderColor='#1f6a4d'; this.style.boxShadow='0 0 0 3px rgba(31,106,77,0.1)'"
                                            onblur="this.style.borderColor='rgba(15,61,46,0.12)'; this.style.boxShadow='none'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ACTION ROW -->
            <div class="mt-4 flex items-center justify-end gap-6" style="padding-left: 271px;">
                <a href="index.php"
                    style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #0f3d2e; text-decoration: underline; text-underline-offset: 3px;">
                    Back to Store
                </a>
                <button type="submit" class="transition-transform hover:-translate-y-0.5"
                    style="background-image: linear-gradient(178.231deg, rgb(31,106,77) 4.35%, rgb(15,61,46) 95.65%); box-shadow: 0 12px 10px rgba(31,106,77,0.45); border-radius: 16px; padding: 16px 32px; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.5px; color: #f5f2e8; border: none; cursor: pointer;">
                    SAVE CHANGES
                </button>
            </div>
        </form>

        <div class="h-16"></div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>