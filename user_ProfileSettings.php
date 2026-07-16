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

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = mysqli_real_escape_string($conn, $_POST['fullname']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);

    $update_query = "UPDATE users SET fullname = '$new_name', email = '$new_email' WHERE id = '$user_id'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['fullname'] = $new_name;
        $_SESSION['email'] = $new_email;
        $user['fullname'] = $new_name;
        $user['email'] = $new_email;
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile.";
    }

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'assets/img/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $update_pic = "UPDATE users SET profile_picture = '$target_file' WHERE id = '$user_id'";
            if (mysqli_query($conn, $update_pic)) {
                $user['profile_picture'] = $target_file;
                $_SESSION['profile_picture'] = $target_file;
            }
        }
    }
}

include 'includes/header.php';

$initials = '?';
if (!empty($user['fullname'])) {
    $words = explode(' ', trim($user['fullname']));
    $initials = strtoupper(substr($words[0], 0, 1));
    if (count($words) > 1) {
        $initials .= strtoupper(substr($words[1], 0, 1));
    } else {
        $initials .= strtoupper(substr($words[0], 1, 1));
    }
}

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
                Profile Settings
            </h1>
        </div>

        <!-- Body: sidebar + content -->
        <form method="POST" action="" enctype="multipart/form-data">
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

                    <!-- Avatar panel -->
                    <div class="flex flex-col items-center"
                        style="background: rgba(255,255,255,0.55); border: 1.111px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 24px; padding: 41px 25px; margin-bottom: 16px;">
                        <div class="relative">
                            <div class="flex items-center justify-center overflow-hidden"
                                style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #0f3d2e, #1f6a4d); border: 2.222px solid rgba(176,138,74,0.4);">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture"
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                        onerror="this.outerHTML='<span style=\'font-family: var(--font-serif); font-size: 36px; font-weight: 600; color: #f5f2e8;\'><?= htmlspecialchars($initials) ?></span>'">
                                <?php else: ?>
                                    <span
                                        style="font-family: var(--font-serif); font-size: 36px; font-weight: 600; color: #f5f2e8;">
                                        <?= $initials ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="profile_pic_input" name="profile_picture" accept="image/*"
                                class="hidden" onchange="this.form.submit()">
                            <button type="button" onclick="document.getElementById('profile_pic_input').click()"
                                class="absolute flex items-center justify-center transition-transform hover:scale-110"
                                style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #b08a4a, #d4b078); box-shadow: 0 4px 6px rgba(176,138,74,0.45); border: 1.111px solid white; bottom: 0; right: 0;">
                                <svg width="16" height="16" viewBox="0 0 15.9896 15.9896" fill="none">
                                    <path
                                        d="M1.332 5.328a1.332 1.332 0 0 1 1.333-1.332h.888L4.664 2.664h6.66l1.11 1.332h.888a1.332 1.332 0 0 1 1.333 1.332v6.66a1.332 1.332 0 0 1-1.333 1.332H2.665A1.332 1.332 0 0 1 1.332 11.988V5.328Z"
                                        stroke="white" stroke-width="1.33" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <circle cx="7.994" cy="8.325" r="2.22" stroke="white" stroke-width="1.33"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>

                        <p class="mt-5"
                            style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #082820; text-align: center;">
                            <?= htmlspecialchars($user['fullname']) ?>
                        </p>
                        <p class="mt-1"
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; color: #6b7268; text-align: center;">
                            <?= htmlspecialchars($user['email']) ?>
                        </p>

                        <div class="my-6 w-full"
                            style="height: 1px; background: linear-gradient(to right, transparent, rgba(176,138,74,0.3), transparent);">
                        </div>

                        <p
                            style="font-family: var(--font-sans); font-size: 11px; font-weight: 400; color: #6b7268; text-align: center; line-height: 17px;">
                            Upload a photo to personalise your wellness profile. Accepted: JPG, PNG, WEBP.
                        </p>
                    </div>

                    <!-- Personal Information panel -->
                    <div
                        style="background: rgba(255,255,255,0.55); border: 1.111px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 24px; padding: 29px 33px;">
                        <div
                            style="border-bottom: 1.111px solid rgba(15,61,46,0.07); padding-bottom: 17px; margin-bottom: 20px;">
                            <p
                                style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #082820;">
                                Personal Information</p>
                            <p
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; color: #6b7268; margin-top: 4px;">
                                Update your name and email address.
                            </p>
                        </div>

                        <div class="flex flex-col gap-4">
                            <div class="flex flex-col items-start w-full">
                                <p
                                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                    Full Name
                                </p>
                                <div class="relative w-full" style="height: 49px;">
                                    <input type="text" name="fullname"
                                        value="<?= htmlspecialchars($user['fullname']) ?>" required
                                        class="absolute inset-0 w-full outline-none focus:border-[#1f6a4d]"
                                        style="background: rgba(255,255,255,0.7); border: 1.111px solid rgba(15,61,46,0.12); border-radius: 14px; padding: 14px 19px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: #082820; transition: border-color 0.15s, box-shadow 0.15s;"
                                        onfocus="this.style.borderColor='#1f6a4d'; this.style.boxShadow='0 0 0 3px rgba(31,106,77,0.1)'"
                                        onblur="this.style.borderColor='rgba(15,61,46,0.12)'; this.style.boxShadow='none'">
                                </div>
                            </div>

                            <div class="flex flex-col items-start w-full">
                                <p
                                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                    Email Address
                                </p>
                                <div class="relative w-full" style="height: 49px;">
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                                        required class="absolute inset-0 w-full outline-none focus:border-[#1f6a4d]"
                                        style="background: rgba(255,255,255,0.7); border: 1.111px solid rgba(15,61,46,0.12); border-radius: 14px; padding: 14px 19px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: #082820; transition: border-color 0.15s, box-shadow 0.15s;"
                                        onfocus="this.style.borderColor='#1f6a4d'; this.style.boxShadow='0 0 0 3px rgba(31,106,77,0.1)'"
                                        onblur="this.style.borderColor='rgba(15,61,46,0.12)'; this.style.boxShadow='none'">
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