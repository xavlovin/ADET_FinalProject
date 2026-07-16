<?php
// Global Header
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Assume basic user data for now if logged in
$isLoggedIn = isset($_SESSION['user_id']) ? 'true' : 'false';
if (isset($_SESSION['fullname'])) {
    $userName = $_SESSION['fullname'];
} else {
    $userName = '';
}
if (isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];
} else {
    $userEmail = '';
}
if (isset($_SESSION['profile_picture'])) {
    $userProfilePic = $_SESSION['profile_picture'];
} else {
    $userProfilePic = '';
}

if ($isLoggedIn === 'true' && empty($userProfilePic) && isset($conn)) {
    $header_user_id = mysqli_real_escape_string($conn, $_SESSION['user_id']);
    $header_query = "SELECT profile_picture FROM users WHERE id = '$header_user_id'";
    $header_result = mysqli_query($conn, $header_query);
    if ($header_result && $header_row = mysqli_fetch_assoc($header_result)) {
        if (!empty($header_row['profile_picture'])) {
            $userProfilePic = $header_row['profile_picture'];
            $_SESSION['profile_picture'] = $userProfilePic;
        }
    }
}

// Fix legacy paths that were stored before moving uploads to assets/img
if (!empty($userProfilePic) && strpos($userProfilePic, 'uploads/') === 0) {
    $userProfilePic = 'assets/img/' . $userProfilePic;
}


$cartTotalItems = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cartTotalItems += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriLife+</title>

    <!-- Alpine.js for Reactivity -->
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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --font-size: 16px;
            --font-serif: 'Playfair Display', ui-serif, Georgia, serif;
            --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            --background: #fbfaf6;
            --foreground: #1a201c;
            --primary: #0f3d2e;
            --primary-foreground: #f5f2e8;
            --secondary: #ecebe2;
            --secondary-foreground: #0f3d2e;
            --muted: #f1efe6;
            --muted-foreground: #6b7268;
            --accent: #b08a4a;
            --accent-foreground: #ffffff;
            --emerald: #0f3d2e;
            --emerald-deep: #082820;
            --emerald-glow: #1f6a4d;
            --sage: #9ab89a;
            --bronze: #b08a4a;
            --bronze-light: #d4b078;
            --cream: #f5f2e8;
            --taupe: #e6e0d0;
            --charcoal: #1a201c;
            --destructive: #d4183d;
        }

        body {
            background-color: var(--background);
            color: var(--foreground);
            font-family: var(--font-sans);
            background-image:
                radial-gradient(ellipse at 15% 10%, rgba(154, 184, 154, 0.18), transparent 55%),
                radial-gradient(ellipse at 85% 90%, rgba(176, 138, 74, 0.10), transparent 60%),
                radial-gradient(ellipse at 50% 50%, rgba(15, 61, 46, 0.04), transparent 70%);
            background-attachment: fixed;
        }

        .glass {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(18px) saturate(1.2);
            -webkit-backdrop-filter: blur(18px) saturate(1.2);
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 32px rgba(15, 61, 46, 0.06);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen pt-6 pb-20" x-data="shellData()" x-cloak>
    <header class="sticky top-0 z-50 px-4 pt-4 md:px-8">
        <div
            class="glass mx-auto flex max-w-[1440px] items-center justify-between rounded-full px-5 py-3 md:px-8 md:py-4">
            <a href="index.php" class="group">
                <div style="display: flex; align-items: center; gap: 12.8px;">
                    <div style="width: 40px; height: 40px; flex-shrink: 0; position: relative;">
                        <svg class="absolute block inset-0 size-full" style="width: 100%; height: 100%;" fill="none"
                            preserveAspectRatio="none" viewBox="0 0 40 40">
                            <g>
                                <path
                                    d="M0 20C0 8.95431 8.95431 0 20 0V0C31.0457 0 40 8.95431 40 20V20C40 31.0457 31.0457 40 20 40V40C8.95431 40 0 31.0457 0 20V20Z"
                                    fill="url(#paint0_linear)" />
                                <path
                                    d="M7.79433 8L9.02675 9.75054C9.42591 10.3164 10.2224 11.3954 12.1998 12.2452C14.2103 13.1067 17.5065 13.7607 22.931 13.279C26.6467 12.9501 29.2421 14.2777 30.8884 16.3259C32.4997 18.331 33.1233 20.9313 33.1233 23.0657C33.1233 23.5147 33.1067 23.9938 33.0736 24.5029C31.076 22.58 28.2985 21.6167 25.5632 21.1075C21.8549 20.5436 17.8597 20.2362 15.2808 17.1032C15.0196 16.786 14.5414 16.9035 14.5837 17.3225C14.7823 19.2513 16.6144 20.8863 17.9756 21.8418C22.335 24.9082 28.5928 21.6108 31.9553 27.8552C32.6457 29.0195 33.7061 31.374 34.489 33.7862C33.5797 34.6978 33.0871 35.1439 32.1954 35.8515C31.9422 34.0704 31.4608 32.3421 30.6437 30.763C28.8429 32.6329 25.8851 33.8352 21.167 33.8352C17.7567 33.8352 15.0601 32.77 12.9815 31.0253C10.914 29.2885 9.50684 26.9231 8.58529 24.4011C6.74586 19.3844 6.74586 13.561 7.38966 10.1461L7.79433 8Z"
                                    fill="#F5F2E8" />
                            </g>
                            <defs>
                                <linearGradient id="paint0_linear" x1="0" x2="40" y1="0" y2="40"
                                    gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#0F3D2E" />
                                    <stop offset="1" stop-color="#1F6A4D" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <div style="display: flex; flex-direction: column; line-height: 1;">
                        <span
                            style="font-family: var(--font-serif); font-size: 20.8px; font-weight: 600; letter-spacing: 0.02em; color: var(--emerald-deep, #082820);">NutriLife+</span>
                        <span
                            style="font-family: var(--font-sans); font-size: 8.8px; font-weight: 500; letter-spacing: 0.22em; text-transform: uppercase; color: var(--bronze, #b08a4a); margin-top: 2.4px;">PURE.BALANCED.BETTER</span>
                    </div>
                </div>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden items-center gap-1 md:flex">
                <template x-for="n in navItems" :key="n.url">
                    <a :href="n.url" class="group relative rounded-full px-5 py-2 transition-all"
                        :style="currentPage === n.url ? 'color: var(--cream); background: var(--emerald-deep);' : 'color: var(--emerald-deep); background: transparent;'">
                        <span class="relative z-10 tracking-wide" style="font-size: 13px;" x-text="n.label"></span>
                    </a>
                </template>
            </nav>

            <div class="flex items-center gap-2">
                <!-- User dropdown -->
                <div class="relative" @click.outside="userOpen = false">
                    <button @click="userOpen = !userOpen"
                        class="flex h-10 w-10 items-center justify-center rounded-full transition-all hover:bg-black/5 overflow-hidden"
                        :style="isLoggedIn ? 'color: var(--emerald-deep); border: 2px solid rgba(176,138,74,0.5);' : 'color: var(--emerald-deep); border: none;'"
                        aria-label="Account">
                        <template x-if="isLoggedIn">
                            <div class="flex h-full w-full items-center justify-center rounded-full overflow-hidden"
                                style="background: linear-gradient(135deg, #0f3d2e, #1f6a4d);">
                                <template x-if="userProfile.picture">
                                    <div class="w-full h-full">
                                        <img :src="userProfile.picture"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                        <span class="w-full h-full items-center justify-center"
                                            style="display:none; font-size: 14px; font-weight: 600; color: #f5f2e8;"
                                            x-text="initials"></span>
                                    </div>
                                </template>
                                <template x-if="!userProfile.picture">
                                    <span style="font-size: 14px; font-weight: 600; color: #f5f2e8;"
                                        x-text="initials"></span>
                                </template>
                            </div>
                        </template>
                        <template x-if="!isLoggedIn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </template>
                    </button>

                    <div x-show="userOpen" x-transition
                        style="display: none; width: 200px; background: rgba(255,255,255,0.9); backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 12px 40px rgba(15,61,46,0.14);"
                        class="absolute right-0 top-[calc(100%+8px)] z-50 overflow-hidden rounded-2xl">
                        <template x-if="isLoggedIn">
                            <div>
                                <div class="flex items-center gap-3 px-4 py-3"
                                    style="border-bottom: 1px solid rgba(15,61,46,0.07);">
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-full"
                                        style="background: linear-gradient(135deg, #0f3d2e, #1f6a4d);">
                                        <template x-if="userProfile.picture">
                                            <div class="w-full h-full">
                                                <img :src="userProfile.picture"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                    alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                                <span class="w-full h-full items-center justify-center"
                                                    style="display:none; font-size: 12px; font-weight: 600; color: #f5f2e8;"
                                                    x-text="initials"></span>
                                            </div>
                                        </template>
                                        <template x-if="!userProfile.picture">
                                            <span style="font-size: 12px; font-weight: 600; color: #f5f2e8;"
                                                x-text="initials"></span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <p style="font-size: 13px; font-weight: 600; color: #082820; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                            x-text="userProfile.name || 'User'"></p>
                                        <p style="font-size: 11px; color: #6b7268; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                            x-text="userProfile.email"></p>
                                    </div>
                                </div>
                                <a href="user_ProfileSettings.php"
                                    class="flex w-full items-center gap-3 px-4 py-3 transition-colors hover:bg-black/5">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1f6a4d"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3" />
                                        <path
                                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                                    </svg>
                                    <span style="font-size: 13px; font-weight: 500; color: #082820;">Account
                                        Settings</span>
                                </a>
                                <button type="button" @click="showLogoutModal = true"
                                    class="flex w-full items-center gap-3 px-4 py-3 transition-colors hover:bg-red-50"
                                    style="border-top: 1px solid rgba(15,61,46,0.05); cursor: pointer;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d4183d"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" y1="12" x2="9" y2="12" />
                                    </svg>
                                    <span style="font-size: 13px; font-weight: 500; color: #d4183d;">Sign Out</span>
                                </button>
                            </div>
                        </template>
                        <template x-if="!isLoggedIn">
                            <div>
                                <div class="px-4 py-2.5" style="border-bottom: 1px solid rgba(15,61,46,0.07);">
                                    <p
                                        style="font-size: 10px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: var(--bronze);">
                                        Account</p>
                                </div>
                                <a href="login.php"
                                    class="flex w-full items-center gap-3 px-4 py-3 transition-colors hover:bg-black/5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0"
                                        style="color: var(--emerald-glow);" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                        <polyline points="10 17 15 12 10 7" />
                                        <line x1="15" y1="12" x2="3" y2="12" />
                                    </svg>
                                    <span style="font-size: 13px; font-weight: 500; color: var(--emerald-deep);">Sign
                                        In</span>
                                </a>
                                <a href="register.php"
                                    class="flex w-full items-center gap-3 px-4 py-3 transition-colors hover:bg-black/5"
                                    style="border-top: 1px solid rgba(15,61,46,0.05);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0"
                                        style="color: var(--emerald-glow);" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <line x1="19" y1="8" x2="19" y2="14" />
                                        <line x1="22" y1="11" x2="16" y2="11" />
                                    </svg>
                                    <span style="font-size: 13px; font-weight: 500; color: var(--emerald-deep);">Sign
                                        Up</span>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Cart button -->
                <a href="user_Cart.php"
                    class="group relative flex items-center gap-2 rounded-full py-2 pl-3 pr-4 transition-all hover:pl-4"
                    style="background: linear-gradient(135deg, #0f3d2e, #082820); color: var(--cream);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                        <path d="M3 6h18" />
                        <path d="M16 10a4 4 0 0 1-8 0" />
                    </svg>
                    <span style="font-size: 13px; font-weight: 500;" x-text="cartCount"></span>
                    <template x-if="cartCount > 0">
                        <span class="absolute -right-1 -top-1 flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full"
                                style="background: var(--bronze-light);"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full"
                                style="background: var(--bronze);"></span>
                        </span>
                    </template>
                </a>

                <!-- Mobile menu toggle -->
                <button @click="menuOpen = !menuOpen" class="rounded-full p-2 md:hidden"
                    style="color: var(--emerald-deep);">
                    <template x-if="menuOpen">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </template>
                    <template x-if="!menuOpen">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12" />
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <line x1="3" y1="18" x2="21" y2="18" />
                        </svg>
                    </template>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="menuOpen" x-transition style="display: none;"
            class="glass mx-auto mt-2 max-w-[1440px] rounded-2xl p-3 md:hidden">
            <template x-for="n in navItems" :key="n.url">
                <a :href="n.url" class="block w-full rounded-xl px-4 py-3 text-left transition-colors"
                    :style="currentPage === n.url ? 'color: var(--cream); background: var(--emerald-deep); font-size: 14px;' : 'color: var(--emerald-deep); background: transparent; font-size: 14px;'"
                    x-text="n.label">
                </a>
            </template>
            <div style="border-top: 1px solid rgba(15,61,46,0.08); margin-top: 4px; padding-top: 4px;">
                <template x-if="isLoggedIn">
                    <div>
                        <a href="user_ProfileSettings.php"
                            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left transition-colors hover:bg-black/5"
                            style="font-size: 14px; color: var(--emerald-deep);">
                            Account Settings
                        </a>
                        <button type="button" @click="showLogoutModal = true"
                            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left transition-colors hover:bg-red-50"
                            style="font-size: 14px; color: #d4183d; cursor: pointer;">
                            Sign Out
                        </button>
                    </div>
                </template>
                <template x-if="!isLoggedIn">
                    <div>
                        <a href="login.php"
                            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left transition-colors hover:bg-black/5"
                            style="font-size: 14px; color: var(--emerald-deep);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                                <polyline points="10 17 15 12 10 7" />
                                <line x1="15" y1="12" x2="3" y2="12" />
                            </svg>
                            Sign In
                        </a>
                        <a href="register.php"
                            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left transition-colors hover:bg-black/5"
                            style="font-size: 14px; color: var(--emerald-deep);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <line x1="19" y1="8" x2="19" y2="14" />
                                <line x1="22" y1="11" x2="16" y2="11" />
                            </svg>
                            Sign Up
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </header>

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
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </div>
                <div style="text-align: center;">
                    <p
                        style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; color: #fff; margin-bottom: 4px;">
                        Sign Out</p>
                    <p
                        style="font-family: var(--font-sans); font-size: 12px; color: rgba(255,255,255,0.7); line-height: 18px;">
                        Are you sure you want to log out of your session?</p>
                </div>
            </div>

            <div
                style="padding: 20px 28px 28px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button type="button" @click="showLogoutModal = false"
                    style="padding: 12px 0; border-radius: 12px; border: 1px solid rgba(15,61,46,0.15); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #6b7268; cursor: pointer;">
                    Cancel
                </button>
                <a href="logout.php"
                    style="padding: 12px 0; border-radius: 12px; border: none; background: linear-gradient(135deg, #c0392b, #7f1d1d); font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg> Yes, Sign Out
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('shellData', () => ({
                isLoggedIn: <?= $isLoggedIn ?>,
                userProfile: {
                    name: "<?= htmlspecialchars($userName) ?>",
                    email: "<?= htmlspecialchars($userEmail) ?>",
                    picture: "<?= htmlspecialchars($userProfilePic) ?>"
                },
                cartCount: <?= $cartTotalItems ?>,
                menuOpen: false,
                userOpen: false,
                showLogoutModal: false,
                navItems: [
                    { url: 'index.php', label: 'Store' },
                    { url: 'about.php', label: 'Visionaries' }
                ],
                currentPage: window.location.pathname.split('/').pop() || 'index.php',

                get initials() {
                    return this.userProfile.name
                        ? this.userProfile.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
                        : '?';
                }
            }))
        });
    </script>