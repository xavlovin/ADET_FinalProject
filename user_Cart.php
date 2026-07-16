<?php
include 'includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'user') {
    header("Location: login.php");
    exit();
}

$cart_data = [];
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $ids));

    $query = "SELECT * FROM inventory WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $img = $row['image_file'];
            if (strpos($img, 'http') !== 0 && strpos($img, 'assets/img/uploads/') !== 0 && strpos($img, 'assets/') !== 0 && !empty($img)) {
                $img = 'assets/images/' . $img;
            }

            $stock = (int) $row['stock'];
            $qty = $_SESSION['cart'][$row['id']];
            if ($qty > $stock) {
                $qty = $stock;
                $_SESSION['cart'][$row['id']] = $qty; // Update session if it exceeded stock
            }

            $cart_data[] = [
                'product' => [
                    'id' => $row['id'],
                    'name' => $row['product_name'],
                    'tagline' => $row['tagline'],
                    'category' => $row['category'],
                    'price' => (float) $row['price'],
                    'image' => $img,
                    'stock' => $stock
                ],
                'qty' => $qty
            ];
        }
    }
}

include 'includes/header.php';
?>
<!-- inline script for cart logic for presentation -->
<script>
    function cartData() {
        return {
            cart: <?php echo json_encode($cart_data); ?>,
            get cartTotal() {
                return this.cart.reduce((total, item) => total + (item.product.price * item.qty), 0);
            },
            setQty(id, qty) {
                const item = this.cart.find(i => i.product.id == id);
                if (item) {
                    if (qty > item.product.stock) qty = item.product.stock;
                    if (qty < 1) return;
                    item.qty = qty;
                    fetch('cart_action.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'update', id: id, qty: qty })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.cartCount = data.cartCount;
                            }
                        });
                }
            },
            removeFromCart(id) {
                this.cart = this.cart.filter(i => i.product.id != id);
                fetch('cart_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'remove', id: id })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.cartCount = data.cartCount;
                        }
                    });
            }
        }
    }
</script>

<div class="px-4 md:px-8" x-data="cartData()">
    <div class="mx-auto mt-6" style="max-width: 1440px;">

        <!-- ── PAGE HEADER ── -->
        <div class="mb-8 flex items-end justify-between">
            <div>
                <p
                    style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #b08a4a;">
                    Your Collection
                </p>
                <h1 class="mt-2"
                    style="font-family: var(--font-serif); font-size: 52px; font-weight: 500; line-height: 78px; color: #082820;">
                    Cart &amp; Checkout
                </h1>
            </div>
            <div class="text-right">
                <p style="font-family: var(--font-sans); font-size: 12px; font-weight: 400; color: #6b7268;">
                    Items curated
                </p>
                <p style="font-family: var(--font-serif); font-size: 28px; font-weight: 500; line-height: 42px; color: #082820;"
                    x-text="cart.reduce((s, l) => s + l.qty, 0)">
                </p>
            </div>
        </div>

        <!-- ── TWO-COLUMN LAYOUT ── -->
        <div style="display: flex; gap: 24px; align-items: flex-start;">

            <!-- LEFT — cart items -->
            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 16px;">

                <template x-if="cart.length === 0">
                    <div class="flex flex-col items-center justify-center py-16 text-center"
                        style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 16px;">
                        <p style="font-family: var(--font-serif); font-size: 22px; color: #082820;">Your cart is quiet.
                        </p>
                        <p class="mt-2" style="font-family: var(--font-sans); font-size: 13px; color: #6b7268;">
                            Begin curating from the collection.
                        </p>
                        <button onclick="window.location.href='index.php'"
                            class="mt-6 transition-transform hover:-translate-y-0.5"
                            style="background: #0f3d2e; color: #f5f2e8; font-family: var(--font-sans); font-size: 13px; border-radius: 12px; padding: 10px 24px;">
                            Browse Formulations
                        </button>
                    </div>
                </template>

                <template x-for="line in cart" :key="line.product.id">
                    <div
                        style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 16px; padding: 17px; display: flex; gap: 20px; align-items: flex-start;">

                        <!-- Product image -->
                        <div
                            style="width: 96px; height: 96px; flex-shrink: 0; border-radius: 20px; background: #ecebe2; overflow: hidden; position: relative;">
                            <img :src="line.product.image" :alt="line.product.name"
                                class="absolute inset-0 h-full w-full object-cover"
                                @error="$event.target.style.display='none'">
                        </div>

                        <!-- Product info -->
                        <div style="flex: 1; display: flex; flex-direction: column; gap: 0; align-self: stretch;">
                            <!-- Top row -->
                            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                                <div>
                                    <p style="font-family: var(--font-sans); font-size: 9px; font-weight: 400; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a; line-height: 13.5px;"
                                        x-text="line.product.category"></p>
                                    <p class="mt-1"
                                        style="font-family: var(--font-serif); font-size: 16px; font-weight: 500; line-height: 24px; color: #082820;"
                                        x-text="line.product.name"></p>
                                    <p style="font-family: var(--font-sans); font-size: 12px; font-weight: 400; line-height: 18px; color: #6b7268;"
                                        x-text="line.product.tagline"></p>
                                </div>
                                <button @click="removeFromCart(line.product.id)"
                                    style="padding: 6px; background: none; border: none; cursor: pointer; flex-shrink: 0;">
                                    <svg width="16" height="16" viewBox="0 0 15.9896 15.9896" fill="none">
                                        <path d="M3.33117 3.33117L12.6584 12.6584" stroke="#6B7268"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33247" />
                                        <path d="M12.6584 3.33117L3.33117 12.6584" stroke="#6B7268"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="1.33247" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Bottom row -->
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 10px;">
                                <!-- Qty pill -->
                                <div
                                    style="display: flex; align-items: center; gap: 4px; background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 9999px; padding: 1px;">
                                    <button
                                        @click="line.qty > 1 ? setQty(line.product.id, line.qty - 1) : removeFromCart(line.product.id)"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: none; border: none; cursor: pointer; border-radius: 9999px;">
                                        <svg width="14" height="14" viewBox="0 0 13.9931 13.9931" fill="none">
                                            <path d="M2.91523 6.99655H11.0779" stroke="#082820" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="1.16609" />
                                        </svg>
                                    </button>
                                    <span
                                        style="width: 32px; text-align: center; font-family: var(--font-serif); font-size: 14px; font-weight: 400; color: #082820; line-height: 21px;"
                                        x-text="line.qty"></span>
                                    <button
                                        @click="line.qty < line.product.stock ? setQty(line.product.id, line.qty + 1) : null"
                                        :class="line.qty >= line.product.stock ? 'opacity-30' : ''"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; background: none; border: none; border-radius: 9999px;"
                                        :style="line.qty >= line.product.stock ? { cursor: 'not-allowed' } : { cursor: 'pointer' }">
                                        <svg width="14" height="14" viewBox="0 0 13.9931 13.9931" fill="none">
                                            <path d="M2.91523 6.99655H11.0779" stroke="#082820" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="1.16609" />
                                            <path d="M6.99655 2.91523V11.0779" stroke="#082820" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="1.16609" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Price -->
                                <span
                                    style="font-family: var(--font-serif); font-size: 20px; font-weight: 600; line-height: 30px; background-image: linear-gradient(135.939deg, rgb(212,176,120) 0%, rgb(176,138,74) 50%, rgb(138,106,52) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;"
                                    x-text="'₱' + (line.product.price * line.qty)">
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- RIGHT — Order Summary -->
            <div
                style="width: 412px; flex-shrink: 0; background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); border-radius: 24px; padding: 28px;">
                <h2
                    style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; line-height: 30px; color: #082820;">
                    Order Summary
                </h2>

                <div class="mt-6" style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: #6b7268;">Subtotal</span>
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: #082820;"
                            x-text="'₱' + cartTotal"></span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: #6b7268;">Concierge
                            shipping</span>
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: #082820;"
                            x-text="cart.length ? '₱12' : '₱0'"></span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: #6b7268;">Estimated
                            tax</span>
                        <span
                            style="font-family: var(--font-sans); font-size: 13px; font-weight: 400; line-height: 19.5px; color: #082820;"
                            x-text="'₱' + Math.round(cartTotal * 0.07)"></span>
                    </div>
                </div>

                <div style="margin: 20px 0; border-top: 1.111px dashed rgba(15,61,46,0.15);"></div>

                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <p
                        style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2px; text-transform: uppercase; color: #082820;">
                        Total Due</p>
                    <p style="font-family: var(--font-serif); font-size: 32px; font-weight: 600; line-height: 48px; background-image: linear-gradient(147.381deg, rgb(212,176,120) 0%, rgb(176,138,74) 50%, rgb(138,106,52) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;"
                        x-text="'₱' + (cartTotal + (cart.length ? 12 : 0) + Math.round(cartTotal * 0.07))"></p>
                </div>

                <button onclick="window.location.href='user_Checkout.php'" :disabled="cart.length === 0"
                    class="transition-transform hover:-translate-y-0.5 disabled:opacity-40"
                    style="margin-top: 24px; width: 100%; display: flex; align-items: center; justify-content: center; background-image: linear-gradient(173.83deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); box-shadow: 0 12px 20px rgba(31,106,77,0.55); border-radius: 16px; padding: 16px; border: none; cursor: pointer; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.95px; color: #f5f2e8;">
                    PROCEED TO CHECKOUT
                </button>

                <button onclick="window.location.href='index.php'"
                    style="margin-top: 16px; width: 100%; text-align: center; font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #0f3d2e; background: none; border: none; cursor: pointer; text-decoration: underline; text-underline-offset: 3px;">
                    Continue Shopping
                </button>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>