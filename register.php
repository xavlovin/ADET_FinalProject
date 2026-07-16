<?php
include 'includes/config.php';

$auth_error = null;
$register_success = false;
$user_first_name = '';

if (isset($_SESSION['accesslevel']) && $_SESSION['accesslevel'] == 'user') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    if ($password !== $confirm_password) {
        $auth_error = "Passwords do not match.";
    } else {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        if (mysqli_num_rows($result) > 0) {
            $auth_error = "Email already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (fullname, email, password, address, contact, reg_date) VALUES ('$name', '$email', '$hashed_password', '$address', '$phone', NOW())";
            if (mysqli_query($conn, $query)) {
                $user_id = mysqli_insert_id($conn);
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $email;
                $_SESSION['fullname'] = $name;
                $_SESSION['accesslevel'] = 'user';

                $register_success = true;
                $name_parts = explode(' ', trim($name));
                $user_first_name = $name_parts[0];
            } else {
                $auth_error = "Error creating account. Please try again.";
            }
        }
    }
}
include 'includes/header.php';
?>
<div class="px-4 md:px-8">
    <!-- Outer cream card -->
    <div class="mx-auto mt-6 overflow-hidden rounded-[40px]"
        style="max-width: 1440px; background-image: linear-gradient(152.802deg, rgb(245, 242, 232) 0%, rgb(236, 235, 226) 100%); padding: 40px;">
        <!-- Fixed height so switching modes never resizes the card -->
        <div class="flex overflow-hidden rounded-[32px]" style="height: 660px;">
            <!-- ── LEFT SIDEBAR (dark green) ── -->
            <div class="relative flex flex-col overflow-hidden"
                style="flex: 0 0 50%; background-image: linear-gradient(to bottom, #0f3d2e, #082820); padding: 148px 40px 48px 40px; border-radius: 32px 0 0 32px;">
                <!-- "Encrypted Onboarding" badge -->
                <div class="inline-flex w-fit items-center gap-2 rounded-full"
                    style="border: 1px solid rgba(176,138,74,0.35); padding: 7px 15px;">
                    <svg width="14" height="14" viewBox="0 0 13.9931 13.9931" fill="none">
                        <path
                            d="M11.6609 7.5796C11.6609 10.4948 9.62026 11.9524 7.19479 12.7979C7.06778 12.8409 6.92981 12.8388 6.80415 12.792C4.37284 11.9524 2.33218 10.4948 2.33218 7.5796V3.49828C2.33218 3.34364 2.39361 3.19534 2.50295 3.086C2.6123 2.97666 2.7606 2.91523 2.91523 2.91523C4.08132 2.91523 5.53894 2.21557 6.55344 1.32934C6.67696 1.22381 6.83409 1.16583 6.99655 1.16583C7.15901 1.16583 7.31614 1.22381 7.43967 1.32934C8.46 2.2214 9.91178 2.91523 11.0779 2.91523C11.2325 2.91523 11.3808 2.97666 11.4901 3.086C11.5995 3.19534 11.6609 3.34364 11.6609 3.49828V7.5796Z"
                            stroke="#D4B078" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.16609" />
                    </svg>
                    <span
                        style="font-family: var(--font-sans); font-size: 9px; font-weight: 400; letter-spacing: 2.25px; text-transform: uppercase; color: #d4b078;">
                        <span>Encrypted Onboarding</span>
                    </span>
                </div>

                <!-- Heading -->
                <div class="mt-6"
                    style="font-family: var(--font-serif); font-size: 46px; font-weight: 500; line-height: 48.3px; color: #f5f2e8;">
                    <span>Begin your <span style="color: #d4b078;">ritual</span>.</span>
                </div>

                <!-- Description -->
                <p class="mt-5"
                    style="font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 22.75px; color: rgba(245,242,232,0.75); max-width: 471px;">
                    <span>Your G·Health account grants access to concierge dosing consultations, subscription intervals
                        synced to your lunar cycle, and priority allocation of small-batch releases.</span>
                </p>

                <!-- Bullet list -->
                <ul class="mt-7 space-y-4">
                    <div>
                        <li class="flex items-start gap-3 mb-4">
                            <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full"
                                style="width: 20px; height: 20px; background: rgba(176,138,74,0.15); border: 1px solid rgba(176,138,74,0.35);">
                                <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                                    <path d="M9.99708 2.99913L4.49869 8.49752L1.99942 5.99825" stroke="#D4B078"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="0.999708" />
                                </svg>
                            </div>
                            <span
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: rgba(245,242,232,0.85);">End-to-end
                                256-bit encryption</span>
                        </li>
                        <li class="flex items-start gap-3 mb-4">
                            <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full"
                                style="width: 20px; height: 20px; background: rgba(176,138,74,0.15); border: 1px solid rgba(176,138,74,0.35);">
                                <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                                    <path d="M9.99708 2.99913L4.49869 8.49752L1.99942 5.99825" stroke="#D4B078"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="0.999708" />
                                </svg>
                            </div>
                            <span
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: rgba(245,242,232,0.85);">Concierge
                                onboarding within 24 hours</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full"
                                style="width: 20px; height: 20px; background: rgba(176,138,74,0.15); border: 1px solid rgba(176,138,74,0.35);">
                                <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                                    <path d="M9.99708 2.99913L4.49869 8.49752L1.99942 5.99825" stroke="#D4B078"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="0.999708" />
                                </svg>
                            </div>
                            <span
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: rgba(245,242,232,0.85);">Adaptive
                                privacy controls</span>
                        </li>
                    </div>
                </ul>

                <!-- Bronze glow blob -->
                <div class="pointer-events-none absolute rounded-full"
                    style="width: 160px; height: 160px; right: -60px; bottom: 40px; background: #b08a4a; opacity: 0.4; filter: blur(64px);">
                </div>
            </div>

            <!-- ── RIGHT FORM PANEL (glass) ── -->
            <div class="relative flex flex-col overflow-hidden <?= $register_success ? 'justify-center' : '' ?>"
                style="flex: 0 0 50%; background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px 0 rgba(15,61,46,0.06); border-radius: 0 32px 32px 0; padding: 32px 40px;">

                <?php if ($register_success): ?>
                    <!-- Success state -->
                    <div class="flex flex-1 flex-col items-center justify-center text-center">
                        <div class="flex items-center justify-center rounded-full"
                            style="width: 64px; height: 64px; background: #0f3d2e; box-shadow: 0 0 0 3px rgba(31,106,77,0.15), 0 10px 40px rgba(31,106,77,0.4);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="h-6 w-6" style="color: #f5f2e8;">
                                <path d="M20 6 9 17l-5-5"></path>
                            </svg>
                        </div>
                        <h4 class="mt-6"
                            style="font-family: var(--font-serif); font-size: 30px; font-weight: 500; color: #082820;">
                            Welcome, <?= htmlspecialchars($user_first_name) ?>.
                        </h4>
                        <p class="mt-3 max-w-sm" style="font-family: var(--font-sans); font-size: 14px; color: #6b7268;">
                            Your account has been secured. A concierge will reach out within the next 24 hours.
                        </p>
                        <button onclick="window.location.href='index.php'" class="mt-8 rounded-2xl px-6 py-3"
                            style="background: #0f3d2e; color: #f5f2e8; font-family: var(--font-sans); font-size: 13px;">
                            Enter the Collection
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Panel header row -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #b08a4a;">
                                Step 01 / 01</p>
                            <h3 class="mt-2"
                                style="font-family: var(--font-serif); font-size: 30px; font-weight: 500; line-height: 45px; color: #082820;">
                                Registration & Security</h3>
                        </div>
                        <div class="overflow-hidden rounded-full"
                            style="width: 96px; height: 6px; background: rgba(15,61,46,0.1);">
                            <div
                                style="width: 85%; height: 100%; background-image: linear-gradient(to right, #1f6a4d, #b08a4a);">
                            </div>
                        </div>
                    </div>

                    <?php if (isset($auth_error)): ?>
                        <div class="rounded-xl px-4 py-3 mt-4"
                            style="background: rgba(192,57,43,0.08); border: 1px solid rgba(192,57,43,0.2);">
                            <p style="font-family: var(--font-sans); font-size: 12px; color: #c0392b; text-align: center;">
                                <?= htmlspecialchars($auth_error) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form class="mt-5 flex flex-col" style="gap: 12px;" method="POST" action="register.php">

                        <div class="grid grid-cols-2" style="gap: 12px;">
                            <div class="col-span-2">
                                <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                                    <input type="text" name="name" required placeholder="Complete Name" x-model="val"
                                        @focus="focused = true" @blur="focused = false"
                                        class="absolute inset-0 w-full outline-none transition-all"
                                        :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
                                </div>
                            </div>
                            <div class="col-span-2">
                                <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                                    <input type="email" name="email" required placeholder="E-mail Address" x-model="val"
                                        @focus="focused = true" @blur="focused = false"
                                        class="absolute inset-0 w-full outline-none transition-all"
                                        :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
                                </div>
                            </div>
                            <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                                <input type="password" name="password" required placeholder="Password" x-model="val"
                                    @focus="focused = true" @blur="focused = false"
                                    class="absolute inset-0 w-full outline-none transition-all"
                                    :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
                            </div>
                            <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                                <input type="password" name="confirm_password" required placeholder="Confirm Password"
                                    x-model="val" @focus="focused = true" @blur="focused = false"
                                    class="absolute inset-0 w-full outline-none transition-all"
                                    :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
                            </div>
                            <div class="col-span-2">
                                <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                                    <input type="text" name="address" required placeholder="Complete Address" x-model="val"
                                        @focus="focused = true" @blur="focused = false"
                                        class="absolute inset-0 w-full outline-none transition-all"
                                        :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
                                </div>
                            </div>
                            <div class="col-span-2">
                                <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                                    <input type="tel" name="phone" required placeholder="Contact Numbers" x-model="val"
                                        @focus="focused = true" @blur="focused = false"
                                        class="absolute inset-0 w-full outline-none transition-all"
                                        :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
                                </div>
                            </div>
                        </div>

                        <!-- Submit button -->
                        <button type="submit" name="register"
                            class="flex w-full items-center justify-center gap-3 transition-transform hover:-translate-y-0.5"
                            style="padding: 14px 16px; background-image: linear-gradient(175.03deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); box-shadow: 0px 12px 20px rgba(31,106,77,0.55); border-radius: 16px;">
                            <svg width="16" height="16" viewBox="0 0 13.9931 13.9931" fill="none">
                                <path
                                    d="M11.6609 7.5796C11.6609 10.4948 9.62026 11.9524 7.19479 12.7979C7.06778 12.8409 6.92981 12.8388 6.80415 12.792C4.37284 11.9524 2.33218 10.4948 2.33218 7.5796V3.49828C2.33218 3.34364 2.39361 3.19534 2.50295 3.086C2.6123 2.97666 2.7606 2.91523 2.91523 2.91523C4.08132 2.91523 5.53894 2.21557 6.55344 1.32934C6.67696 1.22381 6.83409 1.16583 6.99655 1.16583C7.15901 1.16583 7.31614 1.22381 7.43967 1.32934C8.46 2.2214 9.91178 2.91523 11.0779 2.91523C11.2325 2.91523 11.3808 2.97666 11.4901 3.086C11.5995 3.19534 11.6609 3.34364 11.6609 3.49828V7.5796Z"
                                    stroke="#F5F2E8" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.16609" />
                            </svg>
                            <span
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.95px; color: #f5f2e8;">SECURE
                                REGISTER & CONFIRM</span>
                        </button>

                        <!-- Terms -->
                        <p class="text-center"
                            style="font-family: var(--font-sans); font-size: 11px; font-weight: 400; line-height: 16.5px; color: #6b7268;">
                            By registering you agree to our study terms · this project is for educational use.</p>

                        <!-- Toggle link -->
                        <p class="text-center" style="font-family: var(--font-sans); font-size: 13px; color: #6b7268;">
                            <span>Already have an account?</span>
                            <button type="button" onclick="window.location.href = 'login.php'"
                                class="transition-opacity hover:opacity-70"
                                style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #0f3d2e; text-decoration: underline; background: none; border: none; cursor: pointer;">Sign
                                in</button>
                        </p>

                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>