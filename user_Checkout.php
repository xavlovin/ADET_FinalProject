<?php
include 'includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'user') {
    header("Location: login.php");
    exit();
}
include 'includes/header.php';
// Calculate total dynamically from session cart
$cartTotal = 0;
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    $ids = array_keys($_SESSION['cart']);
    $ids_string = implode(',', array_map('intval', $ids));
    $query = "SELECT id, price FROM inventory WHERE id IN ($ids_string)";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cartTotal += $row['price'] * $_SESSION['cart'][$row['id']];
        }
    }
}
$shipping = 12;
$tax = round($cartTotal * 0.07);
$total = $cartTotal + $shipping + $tax;
?>
<div class="px-4 md:px-8">
    <div class="mx-auto mt-6" style="max-width: 1440px;">
        <!-- ── PAGE HEADER ── -->
        <div class="mb-8">
            <p
                style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 2.5px; text-transform: uppercase; color: #b08a4a;">
                Your Collection
            </p>
            <h1 class="mt-2"
                style="font-family: var(--font-serif); font-size: 52px; font-weight: 500; line-height: 78px; color: #082820;">
                Shipping Details
            </h1>
        </div>

        <!-- ── SHIPPING FORM ── -->
        <div class="rounded-3xl"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); padding: 28px 32px;">
            <div class="mb-6 flex items-center justify-between">
                <h2
                    style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; line-height: 30px; color: #082820;">
                    Shipping Address
                </h2>
            </div>

            <form action="user_Payment.php" method="POST" class="flex flex-col gap-4">
                <div>
                    <label
                        style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                        Full Name
                    </label>
                    <input type="text" name="name" required placeholder="Amara Whitfield"
                        class="w-full outline-none transition-all"
                        style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                </div>

                <div>
                    <label
                        style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                        Street Address
                    </label>
                    <input type="text" name="address" required placeholder="123 Wellness Blvd"
                        class="w-full outline-none transition-all"
                        style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                            City
                        </label>
                        <input type="text" name="city" required placeholder="Beverly Hills"
                            class="w-full outline-none transition-all"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                    </div>
                    <div>
                        <label
                            style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                            ZIP Code
                        </label>
                        <input type="text" name="zip" required placeholder="90210"
                            class="w-full outline-none transition-all"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                    </div>
                </div>

                <button type="submit"
                    class="mt-4 flex w-full items-center justify-center transition-transform hover:-translate-y-0.5"
                    style="background-image: linear-gradient(173.83deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); box-shadow: 0 12px 20px rgba(31,106,77,0.55); border-radius: 16px; padding: 16px; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.95px; color: #f5f2e8; border: none; cursor: pointer;">
                    CONTINUE TO PAYMENT
                </button>

                <button type="button" onclick="window.location.href='user_Cart.php'"
                    class="w-full text-center transition-opacity hover:opacity-70 mt-2"
                    style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #0f3d2e; background: none; border: none; cursor: pointer; text-decoration: underline; text-underline-offset: 3px;">
                    Back to Cart
                </button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>