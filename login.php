<?php
include 'includes/config.php';

$auth_error = null;
$login_success = false;
$user_first_name = '';

if (isset($_SESSION['accesslevel']) && in_array($_SESSION['accesslevel'], ['user', 'admin'])) {
  $login_success = true;
  if (isset($_SESSION['fullname'])) {
    $fullname = $_SESSION['fullname'];
  } else {
    $fullname = 'Curator';
  }
  $name_parts = explode(' ', trim($fullname));
  $user_first_name = $name_parts[0];
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = $_POST['password'];

  $query = "SELECT * FROM users WHERE email = '$email'";
  $result = mysqli_query($conn, $query);

  if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    if (isset($user['status']) && strtolower($user['status']) === 'inactive') {
      $auth_error = "Your account has been disabled. You cannot log in.";
    } else {
      if (password_verify($password, $user['password']) || $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['fullname'] = $user['fullname'];
        if (isset($user['accesslevel'])) {
          $_SESSION['accesslevel'] = $user['accesslevel'];
        } else {
          $_SESSION['accesslevel'] = 'user';
        }
        $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;

        $login_success = true;
        $name_parts = explode(' ', trim($user['fullname']));
        $user_first_name = $name_parts[0];
      } else {
        $auth_error = "Invalid password.";
      }
    }
  } else {
    $admin_query = "SELECT * FROM admin_users WHERE email = '$email'";
    $admin_result = mysqli_query($conn, $admin_query);
    if ($admin_result && mysqli_num_rows($admin_result) > 0) {
      $admin = mysqli_fetch_assoc($admin_result);
      if (password_verify($password, $admin['password']) || $password == $admin['password']) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['email'] = $admin['email'];
        $_SESSION['fullname'] = 'System Administrator';
        $_SESSION['accesslevel'] = 'admin';

        $login_success = true;
        $user_first_name = 'System';
      } else {
        $auth_error = "Invalid password.";
      }
    } else {
      $auth_error = "User not found.";
    }
  }
}
include 'includes/header.php';
?>
<div class="px-4 md:px-8">
  <!-- Outer cream card -->
  <div class="mx-auto mt-6 overflow-hidden rounded-[40px]"
    style="max-width: 1440px; background-image: linear-gradient(152.802deg, rgb(245, 242, 232) 0%, rgb(236, 235, 226) 100%); padding: 40px;">
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
            <span>Secure Session</span>
          </span>
        </div>

        <!-- Heading -->
        <div class="mt-6"
          style="font-family: var(--font-serif); font-size: 46px; font-weight: 500; line-height: 48.3px; color: #f5f2e8;">
          <span>Welcome <span style="color: #d4b078;">back</span>.</span>
        </div>

        <!-- Description -->
        <p class="mt-5"
          style="font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 22.75px; color: rgba(245,242,232,0.75); max-width: 471px;">
          <span>Sign in to access your curated collection, manage subscriptions, and reconnect with your personal
            wellness concierge.</span>
        </p>

        <!-- Bullet list -->
        <ul class="mt-7 space-y-4">
          <div>
            <li class="flex items-start gap-3 mb-4">
              <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full"
                style="width: 20px; height: 20px; background: rgba(176,138,74,0.15); border: 1px solid rgba(176,138,74,0.35);">
                <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                  <path d="M9.99708 2.99913L4.49869 8.49752L1.99942 5.99825" stroke="#D4B078" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="0.999708" />
                </svg>
              </div>
              <span
                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: rgba(245,242,232,0.85);">Secure
                256-bit encrypted session</span>
            </li>
            <li class="flex items-start gap-3 mb-4">
              <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full"
                style="width: 20px; height: 20px; background: rgba(176,138,74,0.15); border: 1px solid rgba(176,138,74,0.35);">
                <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                  <path d="M9.99708 2.99913L4.49869 8.49752L1.99942 5.99825" stroke="#D4B078" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="0.999708" />
                </svg>
              </div>
              <span
                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: rgba(245,242,232,0.85);">Instant
                access to your collection</span>
            </li>
            <li class="flex items-start gap-3">
              <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full"
                style="width: 20px; height: 20px; background: rgba(176,138,74,0.15); border: 1px solid rgba(176,138,74,0.35);">
                <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                  <path d="M9.99708 2.99913L4.49869 8.49752L1.99942 5.99825" stroke="#D4B078" stroke-linecap="round"
                    stroke-linejoin="round" stroke-width="0.999708" />
                </svg>
              </div>
              <span
                style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: rgba(245,242,232,0.85);">Seamless
                concierge reconnection</span>
            </li>
          </div>
        </ul>

        <!-- Bronze glow blob -->
        <div class="pointer-events-none absolute rounded-full"
          style="width: 160px; height: 160px; right: -60px; bottom: 40px; background: #b08a4a; opacity: 0.4; filter: blur(64px);">
        </div>
      </div>

      <!-- ── RIGHT FORM PANEL (glass) ── -->
      <div class="relative flex flex-col overflow-hidden <?= $login_success ? 'justify-center' : 'justify-center' ?>"
        style="flex: 0 0 50%; background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px 0 rgba(15,61,46,0.06); border-radius: 0 32px 32px 0; padding: 32px 40px;">

        <?php if ($login_success): ?>
          <!-- Success state -->
          <div class="flex flex-1 flex-col items-center justify-center text-center">
            <div class="flex items-center justify-center rounded-full"
              style="width: 64px; height: 64px; background: #0f3d2e; box-shadow: 0 0 0 3px rgba(31,106,77,0.15), 0 10px 40px rgba(31,106,77,0.4);">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"
                style="color: #f5f2e8;">
                <path d="M20 6 9 17l-5-5"></path>
              </svg>
            </div>
            <h4 class="mt-6" style="font-family: var(--font-serif); font-size: 30px; font-weight: 500; color: #082820;">
              You're signed in.
            </h4>
            <p class="mt-3 max-w-sm" style="font-family: var(--font-sans); font-size: 14px; color: #6b7268;">
              <?php if ($_SESSION['accesslevel'] === 'admin'): ?>
                Session authenticated. Your admin console is ready.
              <?php else: ?>
                Session authenticated. Your collection and concierge are ready.
              <?php endif; ?>
            </p>
            <?php if ($_SESSION['accesslevel'] === 'admin'): ?>
              <button onclick="window.location.href='admin/admin_Users.php'"
                class="mt-8 rounded-2xl px-6 py-3 transition-transform hover:-translate-y-0.5"
                style="background: linear-gradient(135deg, #b08a4a, #8a6a34); color: #f5f2e8; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.5px; text-transform: uppercase;">
                Enter Admin Console
              </button>
            <?php else: ?>
              <button onclick="window.location.href='index.php'" class="mt-8 rounded-2xl px-6 py-3"
                style="background: #0f3d2e; color: #f5f2e8; font-family: var(--font-sans); font-size: 13px;">
                Enter the Collection
              </button>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <!-- Panel header row -->
          <div class="flex items-center justify-between">
            <div>
              <p
                style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #b08a4a;">
                Welcome Back</p>
              <h3 class="mt-2"
                style="font-family: var(--font-serif); font-size: 30px; font-weight: 500; line-height: 45px; color: #082820;">
                Sign In to G·Health</h3>
            </div>
          </div>

          <?php if (isset($auth_error)): ?>
            <div class="rounded-xl px-4 py-3 mt-4"
              style="background: rgba(192,57,43,0.08); border: 1px solid rgba(192,57,43,0.2);">
              <p style="font-family: var(--font-sans); font-size: 12px; color: #c0392b; text-align: center;">
                <?= htmlspecialchars($auth_error) ?></p>
            </div>
          <?php endif; ?>

          <!-- Form -->
          <form class="mt-5 flex flex-col" style="gap: 12px;" method="POST" action="login.php">

            <div class="flex flex-col" style="gap: 12px;">
              <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                <input type="email" name="email" required placeholder="E-mail Address" x-model="val"
                  @focus="focused = true" @blur="focused = false"
                  class="absolute inset-0 w-full outline-none transition-all"
                  :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
              </div>
              <div class="relative" style="height: 50px;" x-data="{ focused: false, val: '' }">
                <input type="password" name="password" required placeholder="Password" x-model="val"
                  @focus="focused = true" @blur="focused = false"
                  class="absolute inset-0 w-full outline-none transition-all"
                  :style="`background: rgba(255,255,255,0.7); border: 1px solid ${focused ? '#1f6a4d' : 'rgba(15,61,46,0.12)'}; border-radius: 14px; padding: 14px 20px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; line-height: 21px; color: ${val ? '#082820' : '#6b7268'}; ${focused ? 'box-shadow: 0 0 0 3px rgba(31,106,77,0.1);' : ''}`" />
              </div>
              <div class="flex justify-end">
                <button type="button"
                  style="font-family: var(--font-sans); font-size: 12px; color: #b08a4a; background: none; border: none; cursor: pointer;">
                  Forgot your password?
                </button>
              </div>
            </div>

            <!-- Submit button -->
            <button type="submit" name="login"
              class="flex w-full items-center justify-center gap-3 transition-transform hover:-translate-y-0.5"
              style="padding: 14px 16px; background-image: linear-gradient(175.03deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); box-shadow: 0px 12px 20px rgba(31,106,77,0.55); border-radius: 16px;">
              <svg width="16" height="16" viewBox="0 0 13.9931 13.9931" fill="none">
                <path
                  d="M11.6609 7.5796C11.6609 10.4948 9.62026 11.9524 7.19479 12.7979C7.06778 12.8409 6.92981 12.8388 6.80415 12.792C4.37284 11.9524 2.33218 10.4948 2.33218 7.5796V3.49828C2.33218 3.34364 2.39361 3.19534 2.50295 3.086C2.6123 2.97666 2.7606 2.91523 2.91523 2.91523C4.08132 2.91523 5.53894 2.21557 6.55344 1.32934C6.67696 1.22381 6.83409 1.16583 6.99655 1.16583C7.15901 1.16583 7.31614 1.22381 7.43967 1.32934C8.46 2.2214 9.91178 2.91523 11.0779 2.91523C11.2325 2.91523 11.3808 2.97666 11.4901 3.086C11.5995 3.19534 11.6609 3.34364 11.6609 3.49828V7.5796Z"
                  stroke="#F5F2E8" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.16609" />
              </svg>
              <span
                style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.95px; color: #f5f2e8;">SIGN
                IN SECURELY</span>
            </button>

            <!-- Terms -->
            <p class="text-center"
              style="font-family: var(--font-sans); font-size: 11px; font-weight: 400; line-height: 16.5px; color: #6b7268;">
              Protected by end-to-end encryption · educational project.</p>

            <!-- Toggle link -->
            <p class="text-center" style="font-family: var(--font-sans); font-size: 13px; color: #6b7268;">
              <span>Don't have an account?</span>
              <button type="button" onclick="window.location.href = 'register.php'"
                class="transition-opacity hover:opacity-70"
                style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #0f3d2e; text-decoration: underline; background: none; border: none; cursor: pointer;">Register</button>
            </p>

          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>