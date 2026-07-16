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
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);

    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $full_address = $address . ($city ? ", " . $city : "");

    $update_query = "UPDATE users SET address = '$full_address', contact = '$contact' WHERE id = '$user_id'";
    if (mysqli_query($conn, $update_query)) {
        $message = "Contact information updated successfully!";
        // update local user object so it reflects on reload
        $user['address'] = $full_address;
        $user['contact'] = $contact;
    } else {
        $error = "Database error updating contact information.";
    }
}

// Split the address back to Address and City for the UI if there is a comma
$address_parts = explode(', ', $user['address'] ?? '');
$display_address = $address_parts[0] ?? '';
$display_city = $address_parts[1] ?? '';

if (count($address_parts) > 2) {
    // If there were more commas, just put everything back together reasonably
    $display_city = array_pop($address_parts);
    $display_address = implode(', ', $address_parts);
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
                Address Settings
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

                    <!-- Address panel -->
                    <div
                        style="background: rgba(255,255,255,0.55); border: 1.111px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 24px;">
                        <div
                            style="border-bottom: 1.111px solid rgba(15,61,46,0.07); padding: 28px 33px 17px; margin-bottom: 0;">
                            <p
                                style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #082820;">
                                Address &amp; Contact Info</p>
                            <p
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; color: #6b7268; margin-top: 4px;">
                                Update your delivery address and contact number.
                            </p>
                        </div>

                        <div class="flex flex-col gap-4" style="padding: 20px 33px 29px;">
                            <div class="flex flex-col items-start w-full">
                                <p
                                    style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                    Complete Address
                                </p>
                                <div class="relative w-full" style="height: 49px;">
                                    <input type="text" name="address" value="<?= htmlspecialchars($display_address) ?>"
                                        placeholder="77 Cedar Row, District 04" required
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
                                        City / Region
                                    </p>
                                    <div class="relative w-full" style="height: 49px;">
                                        <input type="text" name="city" value="<?= htmlspecialchars($display_city) ?>"
                                            placeholder="New York, NY" required
                                            class="absolute inset-0 w-full outline-none focus:border-[#1f6a4d]"
                                            style="background: rgba(255,255,255,0.7); border: 1.111px solid rgba(15,61,46,0.12); border-radius: 14px; padding: 14px 19px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: #082820; transition: border-color 0.15s, box-shadow 0.15s;"
                                            onfocus="this.style.borderColor='#1f6a4d'; this.style.boxShadow='0 0 0 3px rgba(31,106,77,0.1)'"
                                            onblur="this.style.borderColor='rgba(15,61,46,0.12)'; this.style.boxShadow='none'">
                                    </div>
                                </div>

                                <div class="flex flex-col items-start w-full">
                                    <p
                                        style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; margin-bottom: 6px;">
                                        Contact Number
                                    </p>
                                    <div class="relative w-full" style="height: 49px;">
                                        <input type="tel" name="contact"
                                            value="<?= htmlspecialchars($user['contact'] ?? '') ?>"
                                            placeholder="+1 (555) 214-8890" required
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