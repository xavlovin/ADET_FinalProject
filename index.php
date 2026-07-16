<?php
include 'includes/config.php';

// Fetch products from database
$query = "SELECT * FROM inventory";
$result = mysqli_query($conn, $query);
$db_products = array();
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $img = $row['image_file'];
        if (strpos($img, 'http') !== 0 && strpos($img, 'assets/img/uploads/') !== 0 && strpos($img, 'assets/') !== 0 && !empty($img)) {
            // Assume it's a bare filename in assets/images if not a full URL or already having a folder path
            $img = 'assets/images/' . $img;
        }

        $db_products[] = [
            'id' => $row['id'],
            'name' => $row['product_name'],
            'category' => $row['category'],
            'price' => (float) $row['price'],
            'stock' => (int) $row['stock'],
            'tagline' => $row['tagline'],
            'image' => $img
        ];
    }
}

// Fetch categories from database
$catQuery = "SELECT name, icon FROM categories ORDER BY name ASC";
$catResult = mysqli_query($conn, $catQuery);
$db_categories = [];
if ($catResult) {
    while ($c = mysqli_fetch_assoc($catResult)) {
        $db_categories[] = [
            'key' => $c['name'],
            'icon' => $c['icon']
        ];
    }
}

include 'includes/header.php';
?>
<div x-data="storeData()" x-cloak>
    <div class="px-4 md:px-8">
        <!-- HERO -->
        <section class="relative mx-auto mt-6 overflow-hidden rounded-[40px]"
            style="max-width: 1440px; height: 710px; background-image: linear-gradient(153.754deg, rgb(245, 242, 232) 0%, rgb(236, 235, 226) 55%, rgb(230, 224, 208) 100%);">
            <!-- organic glow -->
            <div class="absolute pointer-events-none"
                style="inset: 0; filter: blur(40px); background-image: url(&quot;data:image/svg+xml;utf8,<svg viewBox='0 0 2016 3084.8' xmlns='http://www.w3.org/2000/svg'><rect x='0' y='0' height='100%' width='100%' fill='url(%23g1)' opacity='1'/><defs><radialGradient id='g1' gradientUnits='userSpaceOnUse' cx='0' cy='0' r='10' gradientTransform='matrix(0 -232 -232 0 605 1234)'><stop stop-color='rgba(31,106,77,0.15)' offset='0'/><stop stop-color='rgba(0,0,0,0)' offset='0.45'/></radialGradient></defs></svg>&quot;), url(&quot;data:image/svg+xml;utf8,<svg viewBox='0 0 2016 3084.8' xmlns='http://www.w3.org/2000/svg'><rect x='0' y='0' height='100%' width='100%' fill='url(%23g2)' opacity='1'/><defs><radialGradient id='g2' gradientUnits='userSpaceOnUse' cx='0' cy='0' r='10' gradientTransform='matrix(0 -232 -232 0 1411 1851)'><stop stop-color='rgba(176,138,74,0.12)' offset='0'/><stop stop-color='rgba(0,0,0,0)' offset='0.45'/></radialGradient></defs></svg>&quot;);">
            </div>

            <!-- Left content -->
            <div class="absolute flex flex-col items-start" style="left: 64px; top: 64px; width: 640px;">
                <!-- badge -->
                <div class="flex items-center gap-2 rounded-full"
                    style="background: rgba(255,255,255,0.5); border: 1px solid rgba(15,61,46,0.15); padding: 7px 17px;">
                    <span class="rounded-full"
                        style="width: 6px; height: 6px; background: #1f6a4d; display: inline-block;"></span>
                    <span
                        style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #082820;">Autumn
                        Collection · 2026</span>
                </div>

                <!-- heading -->
                <div class="mt-5"
                    style="line-height: 77.52px; font-family: var(--font-serif); font-size: 76px; font-weight: 500; letter-spacing: -1.9px;">
                    <span style="color: #082820;">Elevate Your </span>
                    <span
                        style="background-image: linear-gradient(155.48deg, rgb(212,176,120) 0%, rgb(176,138,74) 50%, rgb(138,106,52) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">Daily</span><br>
                    <span style="color: #082820;">Wellness Ritual.</span>
                </div>

                <!-- description -->
                <p class="mt-6"
                    style="font-family: var(--font-sans); font-size: 16px; font-weight: 400; line-height: 26px; color: #6b7268; max-width: 512px;">
                    Sixteen artisanal formulations, distilled from clinically studied botanicals and precision-chelated
                    minerals. Third-party verified, small-batch bottled.
                </p>

                <!-- CTA row -->
                <div class="flex items-center gap-4 mt-8">
                    <button class="flex items-center gap-3 rounded-full transition-transform hover:-translate-y-0.5"
                        style="background-image: linear-gradient(167.597deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); padding: 16px 28px; box-shadow: 0px 10px 40px 0px rgba(31,106,77,0.6);">
                        <span
                            style="font-family: var(--font-sans); font-size: 14px; font-weight: 500; letter-spacing: 0.28px; color: #f5f2e8;">Explore
                            The Collection</span>
                        <svg class="h-4 w-4 text-[#f5f2e8]" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-3">
                        <div class="flex" style="gap: -4px;">
                            <div class="rounded-full border-2 border-white"
                                style="width: 24px; height: 24px; background: #0f3d2e; margin-left: 0;"></div>
                            <div class="rounded-full border-2 border-white"
                                style="width: 24px; height: 24px; background: #b08a4a; margin-left: -4px;"></div>
                            <div class="rounded-full border-2 border-white"
                                style="width: 24px; height: 24px; background: #9ab89a; margin-left: -4px;"></div>
                        </div>
                        <span
                            style="font-family: var(--font-sans); font-size: 12px; font-weight: 400; color: #6b7268;">Trusted
                            by 12,400+ wellness practitioners</span>
                    </div>
                </div>
            </div>

            <!-- Right image -->
            <div class="absolute overflow-hidden"
                style="left: 759px; top: 64px; width: 616px; height: 582px; border-radius: 14px;">
                <img src="assets/img/landingPage.webp" alt="Artisanal supplement collection"
                    class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0"
                    style="background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0) 40%, rgba(8,40,32,0.75) 100%);">
                </div>
            </div>
        </section>

        <!-- FILTER DASHBOARD -->
        <section class="mx-auto mt-10" style="max-width: 1440px;">
            <div class="relative rounded-3xl"
                style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px 0 rgba(15,61,46,0.06); padding: 25px;">
                <div class="flex items-center gap-5">
                    <!-- search -->
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute" xmlns="http://www.w3.org/2000/svg"
                            style="left: 20px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #1f6a4d;"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input x-model="query" @input="page = 1"
                            placeholder="Search formulations, benefits, ingredients…"
                            class="w-full rounded-full outline-none transition-all"
                            :class="query ? 'ring-[3px] ring-[#1f6a4d]/15' : ''"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.15); padding: 15px 21px 15px 49px; font-family: var(--font-sans); font-size: 14px; font-weight: 400; color: rgba(8,40,32,0.9);">
                    </div>

                    <!-- sort dropdown -->
                    <div class="relative shrink-0" style="width: 288px;" @click.outside="sortOpen = false">
                        <button @click="sortOpen = !sortOpen"
                            class="flex w-full items-center justify-between gap-3 rounded-full"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.15); padding: 15px 17px 15px 21px;">
                            <span
                                style="font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 0.225px; text-transform: uppercase; color: #b08a4a;">Sort</span>
                            <span x-text="sortLabels[sort]"
                                style="flex: 1; text-align: left; font-family: var(--font-serif); font-size: 13px; color: #082820;"></span>
                            <svg class="shrink-0 transition-transform" :class="{'rotate-180': sortOpen}"
                                xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px; color: #1f6a4d;"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <div x-show="sortOpen" x-transition
                            style="display: none; width: 288px; background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.1); padding: 6px;"
                            class="absolute right-0 top-full z-20 mt-2 overflow-hidden rounded-2xl">
                            <template x-for="(label, key) in sortLabels" :key="key">
                                <button @click="sort = key; sortOpen = false"
                                    class="flex w-full items-center justify-between rounded-xl px-4 py-3 transition-colors hover:bg-white/80"
                                    style="color: #082820;">
                                    <span x-text="label"
                                        style="font-family: var(--font-serif); font-size: 13px;"></span>
                                    <svg x-show="sort === key" xmlns="http://www.w3.org/2000/svg"
                                        style="width: 14px; height: 14px; color: #b08a4a;" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- category chips row -->
                <div class="mt-5 flex gap-2 overflow-x-auto pb-1 no-scrollbar" style="height: 46px;">
                    <button @click="cat = 'All'; page = 1"
                        class="flex shrink-0 items-center gap-2 rounded-full h-full transition-all"
                        :style="cat === 'All' ? 'background: #082820; color: #f5f2e8; border: 1px solid transparent; box-shadow: 0px 6px 10px rgba(31,106,77,0.5); padding: 11px 17px;' : 'background: rgba(255,255,255,0.6); color: #082820; border: 1px solid rgba(15,61,46,0.12); padding: 11px 17px;'">
                        <span style="font-family: var(--font-sans); font-size: 13px; font-weight: 500;">✧</span>
                        <span
                            style="font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.24px; white-space: nowrap;">All
                            Collections</span>
                    </button>
                    <template x-for="c in categories" :key="c.key">
                        <button @click="cat = c.key; page = 1"
                            class="flex shrink-0 items-center gap-2 rounded-full h-full transition-all"
                            :style="cat === c.key ? 'background: #082820; color: #f5f2e8; border: 1px solid transparent; box-shadow: 0px 6px 10px rgba(31,106,77,0.5); padding: 11px 17px;' : 'background: rgba(255,255,255,0.6); color: #082820; border: 1px solid rgba(15,61,46,0.12); padding: 11px 17px;'">
                            <span x-text="c.icon" style="font-size: 14px; line-height: 1;"></span>
                            <span x-text="c.key"
                                style="font-family: var(--font-sans); font-size: 12px; font-weight: 500; letter-spacing: 0.24px; white-space: nowrap;"></span>
                        </button>
                    </template>
                </div>
            </div>
        </section>

        <!-- PRODUCT GRID -->
        <section class="mx-auto mt-10" style="max-width: 1440px;">
            <div class="mb-6 flex items-end justify-between">
                <div>
                    <p
                        style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #b08a4a;">
                        The Collection</p>
                    <p class="mt-1"
                        style="font-family: var(--font-serif); font-size: 32px; font-weight: 500; line-height: 48px; color: #082820;">
                        <span x-text="filteredProducts.length"></span> formulations
                    </p>
                </div>
                <p style="font-family: var(--font-sans); font-size: 12px; font-weight: 400; color: #6b7268;">
                    Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <template x-for="p in pagedProducts" :key="p.id">
                    <article
                        class="relative overflow-hidden rounded-[28px] bg-white transition-all hover:-translate-y-1"
                        style="border: 1px solid rgba(15,61,46,0.06); box-shadow: 0px 1px 3px 0px rgba(15,61,46,0.04), 0px 20px 40px -20px rgba(15,61,46,0.15);">
                        <div class="relative overflow-hidden" style="height: 340px;">
                            <div class="absolute inset-0"
                                style="background: linear-gradient(to bottom, #f5f2e8, #ecebe2);"></div>
                            <img :src="getImage(p)" :alt="p.name"
                                class="absolute inset-0 h-full w-full object-cover transition-all duration-300"
                                :style="p.stock <= 0 ? 'filter: grayscale(100%); opacity: 0.5;' : ''"
                                @error="$event.target.style.display='none'">
                            <div class="absolute flex items-center gap-1.5 rounded-full"
                                style="left: 12px; top: 12px; background: rgba(255,255,255,0.75); padding: 4px 12px;">
                                <span x-text="getCatIcon(p.category)" style="font-size: 12px; line-height: 1;"></span>
                                <span x-text="p.category"
                                    style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 0.5px; color: #082820;"></span>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 p-5">
                            <div>
                                <p x-text="p.name"
                                    style="font-family: var(--font-serif); font-size: 17px; font-weight: 500; line-height: 23.375px; color: #082820;">
                                </p>
                                <p x-text="p.tagline" class="mt-1"
                                    style="font-family: var(--font-sans); font-size: 12px; font-weight: 400; line-height: 18px; color: #6b7268; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">
                                </p>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="relative" style="height: 36px; min-width: 60px;">
                                    <span
                                        style="font-family: var(--font-serif); font-size: 24px; font-weight: 600; line-height: 36px; background-image: linear-gradient(137.291deg, rgb(212,176,120) 0%, rgb(176,138,74) 50%, rgb(138,106,52) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">
                                        ₱<span x-text="p.price"></span>
                                    </span>
                                    <span class="absolute"
                                        style="font-family: var(--font-sans); font-size: 11px; font-weight: 400; line-height: 16.5px; color: #6b7268; bottom: 0; right: -26px;">PHP</span>
                                </div>
                                <template x-if="p.stock > 0">
                                    <button @click="collect(p)"
                                        class="flex items-center gap-2 rounded-full transition-all"
                                        :style="added === p.id ? 'background: #b08a4a; padding: 10px 16px; box-shadow: 0px 4px 10px rgba(31,106,77,0.5); color: #f5f2e8;' : 'background: #082820; padding: 10px 16px; box-shadow: 0px 4px 10px rgba(31,106,77,0.5); color: #f5f2e8;'">
                                        <template x-if="added === p.id">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px;"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 6 9 17l-5-5" />
                                            </svg>
                                        </template>
                                        <template x-if="added !== p.id">
                                            <svg width="14" height="14" viewBox="0 0 13.9931 13.9931" fill="none">
                                                <path d="M2.91523 6.99655H11.0779" stroke="#F5F2E8"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.16609" />
                                                <path d="M6.99655 2.91523V11.0779" stroke="#F5F2E8"
                                                    stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.16609" />
                                            </svg>
                                        </template>
                                        <span
                                            style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; letter-spacing: 0.55px;"
                                            x-text="added === p.id ? 'ADDED' : 'COLLECT'"></span>
                                    </button>
                                </template>
                                <template x-if="p.stock <= 0">
                                    <div class="flex items-center gap-2 rounded-full transition-all"
                                        style="background: #e2e8f0; padding: 10px 16px; color: #64748b; cursor: not-allowed; opacity: 0.8;">
                                        <span
                                            style="font-family: var(--font-sans); font-size: 11px; font-weight: 500; letter-spacing: 0.55px;">
                                            NO STOCK
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            <!-- PAGINATION -->
            <div class="mt-12 flex items-center justify-center gap-2">
                <button @click="page--" :disabled="currentPage === 1"
                    class="flex items-center justify-center rounded-full border transition-all disabled:opacity-30"
                    style="width: 44px; height: 44px; border-color: rgba(15,61,46,0.15); color: #082820;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>
                <template x-for="n in pagesArray()" :key="n">
                    <button @click="page = n" class="flex items-center justify-center rounded-full transition-all"
                        :style="currentPage === n ? 'width: 44px; height: 44px; background: #082820; color: #f5f2e8; border: 1px solid transparent; box-shadow: 0 0 0 3px rgba(31,106,77,0.15), 0 8px 24px -8px rgba(31,106,77,0.5); font-family: var(--font-sans); font-size: 13px;' : 'width: 44px; height: 44px; background: transparent; color: #082820; border: 1px solid rgba(15,61,46,0.15); font-family: var(--font-sans); font-size: 13px;'"
                        x-text="n"></button>
                </template>
                <button @click="page++" :disabled="currentPage === totalPages"
                    class="flex items-center justify-center rounded-full border transition-all disabled:opacity-30"
                    style="width: 44px; height: 44px; border-color: rgba(15,61,46,0.15); color: #082820;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>
        </section>

        <!-- GUEST GATE MODAL -->
        <div x-show="showGuestModal"
            style="display: none; background: rgba(8,40,32,0.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);"
            class="fixed inset-0 z-[100] flex items-center justify-center" @click="showGuestModal = false">
            <div class="relative mx-4 flex flex-col items-center rounded-[32px] text-center"
                style="width: 100%; max-width: 460px; background: rgba(255,255,255,0.92); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 24px 64px rgba(8,40,32,0.25); padding: 48px 40px 40px;"
                @click.stop>
                <!-- Close -->
                <button @click="showGuestModal = false"
                    class="absolute right-5 top-5 flex h-8 w-8 items-center justify-center rounded-full transition-colors hover:bg-black/5"
                    style="color: #6b7268;">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.75"
                            stroke-linecap="round" />
                    </svg>
                </button>

                <!-- Icon -->
                <div class="flex items-center justify-center rounded-full"
                    style="width: 68px; height: 68px; background: linear-gradient(135deg, #0f3d2e, #1f6a4d); box-shadow: 0 0 0 8px rgba(31,106,77,0.1), 0 12px 32px rgba(15,61,46,0.35);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#f5f2e8" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>

                <!-- Copy -->
                <h2 class="mt-6"
                    style="font-family: var(--font-serif); font-size: 28px; font-weight: 500; color: #082820; line-height: 36px;">
                    Join to begin collecting.</h2>
                <p class="mt-3"
                    style="font-family: var(--font-sans); font-size: 14px; color: #6b7268; line-height: 22px; max-width: 340px;">
                    Please sign in or create an account to add items to your collection and continue shopping with us.
                </p>

                <!-- Bronze divider -->
                <div class="my-6 w-16"
                    style="height: 2px; background: linear-gradient(to right, #d4b078, #b08a4a); border-radius: 2px;">
                </div>

                <!-- Buttons -->
                <div class="flex w-full flex-col gap-3">
                    <button @click="goTo('login.php')"
                        class="flex w-full items-center justify-center gap-2.5 rounded-2xl py-4 transition-transform hover:-translate-y-0.5"
                        style="background-image: linear-gradient(173.83deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); box-shadow: 0 12px 20px rgba(31,106,77,0.45); font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.5px; color: #f5f2e8;">SIGN
                        IN</button>
                    <button @click="goTo('register.php')"
                        class="flex w-full items-center justify-center gap-2.5 rounded-2xl py-4 transition-all hover:-translate-y-0.5 hover:bg-black/5"
                        style="border: 1px solid rgba(15,61,46,0.18); background: transparent; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.5px; color: #0f3d2e;">CREATE
                        ACCOUNT</button>
                </div>
                <p class="mt-5" style="font-family: var(--font-sans); font-size: 11px; color: #9ab89a;">Tap outside to
                    dismiss</p>
            </div>
        </div>
    </div>
</div>

<script>
    const CATEGORIES = <?php echo json_encode($db_categories); ?>;

    const PRODUCTS = <?php echo json_encode($db_products); ?>;

    document.addEventListener('alpine:init', () => {
        Alpine.data('storeData', () => ({
            query: '',
            cat: 'All',
            sort: 'az',
            sortOpen: false,
            page: 1,
            perPage: 8,
            added: null,
            showGuestModal: false,
            categories: CATEGORIES,
            sortLabels: {
                az: 'Alphabetical (A–Z)',
                lowhigh: 'Price · Low to High',
                highlow: 'Price · High to Low',
            },

            get filteredProducts() {
                let items = PRODUCTS.filter(p => this.cat === 'All' ? true : p.category === this.cat);
                if (this.query.trim()) {
                    const q = this.query.toLowerCase();
                    items = items.filter(p => 
                        p.name.toLowerCase().includes(q) || 
                        p.category.toLowerCase().includes(q) || 
                        (p.tagline && p.tagline.toLowerCase().includes(q))
                    );
                }
                return items.sort((a, b) => {
                    if (this.sort === 'az') return a.name.localeCompare(b.name);
                    if (this.sort === 'lowhigh') return a.price - b.price;
                    return b.price - a.price;
                });
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.filteredProducts.length / this.perPage));
            },

            get currentPage() {
                return Math.min(this.page, this.totalPages);
            },

            get pagedProducts() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.filteredProducts.slice(start, start + this.perPage);
            },

            pagesArray() {
                return Array.from({ length: this.totalPages }, (_, i) => i + 1);
            },

            getCatIcon(category) {
                const cat = this.categories.find(c => c.key === category);
                return cat ? cat.icon : '';
            },

            getImage(product) {
                return product.image ? product.image : 'assets/img/landingPage.webp'; // fallback if no image
            },

            collect(product) {
                if (!this.isLoggedIn) {
                    this.showGuestModal = true;
                    return;
                }

                fetch('cart_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: 'add', id: product.id, qty: 1 })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.cartCount = data.cartCount;
                            this.added = product.id;
                            setTimeout(() => {
                                if (this.added === product.id) this.added = null;
                            }, 1200);
                        }
                    })
                    .catch(err => console.error('Error adding to cart:', err));
            },

            goTo(url) {
                window.location.href = url;
            }
        }))
    })
</script>
<?php include 'includes/footer.php'; ?>
