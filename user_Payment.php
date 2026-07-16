<?php
include 'includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'user') {
    header("Location: login.php");
    exit();
}
include 'includes/header.php';

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
                Payment
            </h1>
        </div>

        <!-- ── PAYMENT FORM ── -->
        <div class="rounded-3xl"
            style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06); padding: 28px 32px;">
            <div class="mb-6 flex items-center justify-between">
                <h2
                    style="font-family: var(--font-serif); font-size: 20px; font-weight: 500; line-height: 30px; color: #082820;">
                    Payment Details
                </h2>
                <div class="flex items-center gap-1.5">
                    <svg width="12" height="12" viewBox="0 0 11.9965 11.9965" fill="none">
                        <path d="M3.499 7.497h4.998V9.996a.5.5 0 0 1-.5.5H3.999a.5.5 0 0 1-.5-.5V7.497Z"
                            stroke="#1F6A4D" stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9997" />
                        <path d="M3.999 7.497V5.498a1.999 1.999 0 1 1 3.998 0v1.999" stroke="#1F6A4D"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="0.9997" />
                    </svg>
                    <span
                        style="font-family: var(--font-sans); font-size: 10px; font-weight: 400; letter-spacing: 1.5px; text-transform: uppercase; color: #1f6a4d;">
                        Encrypted
                    </span>
                </div>
            </div>

            <form action="user_PaymentSuccess.php" method="POST" class="flex flex-col gap-4">
                <div>
                    <label
                        style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                        Cardholder
                    </label>
                    <input type="text" name="cardholder" required placeholder="Amara Whitfield"
                        class="w-full outline-none transition-all"
                        style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                </div>

                <div>
                    <label
                        style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                        Card Number
                    </label>
                    <div class="relative">
                        <input type="text" name="cardNumber" required placeholder="4242 4242 4242 4242" maxlength="19"
                            class="w-full outline-none transition-all"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 44px 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                        <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2">
                            <svg width="16" height="16" viewBox="0 0 15.9896 15.9896" fill="none">
                                <path
                                    d="M1.332 3.994h13.325a.666.666 0 0 1 .666.666v6.66a.666.666 0 0 1-.666.665H1.332a.666.666 0 0 1-.666-.665v-6.66a.666.666 0 0 1 .666-.666Z"
                                    stroke="#B08A4A" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="1.33247" />
                                <path d="M1.332 6.662h13.325" stroke="#B08A4A" stroke-linecap="round"
                                    stroke-linejoin="round" stroke-width="1.33247" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label
                            style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                            Expiry
                        </label>
                        <input type="text" name="expiry" required placeholder="09 / 28" maxlength="7"
                            class="w-full outline-none transition-all"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                    </div>
                    <div>
                        <label
                            style="display: block; margin-bottom: 6px; font-family: var(--font-sans); font-size: 9px; font-weight: 500; letter-spacing: 1.8px; text-transform: uppercase; color: #b08a4a;">
                            CVV
                        </label>
                        <input type="password" name="cvv" required placeholder="•••" maxlength="4"
                            class="w-full outline-none transition-all"
                            style="background: rgba(255,255,255,0.7); border: 1px solid rgba(15,61,46,0.12); border-radius: 20px; padding: 13px 17px; font-family: var(--font-sans); font-size: 13px; color: rgba(8,40,32,0.8);" />
                    </div>
                </div>

                <!-- Order total reminder -->
                <div class="flex items-center justify-between rounded-2xl px-5 py-3 mt-2"
                    style="background: rgba(15,61,46,0.04); border: 1px solid rgba(15,61,46,0.08);">
                    <span style="font-family: var(--font-sans); font-size: 12px; color: #6b7268;">Total due today</span>
                    <span
                        style="font-family: var(--font-serif); font-size: 20px; font-weight: 600; background-image: linear-gradient(147.381deg, rgb(212,176,120) 0%, rgb(176,138,74) 50%, rgb(138,106,52) 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">
                        ₱<?= $total ?>
                    </span>
                </div>

                <button type="submit"
                    class="mt-2 flex w-full items-center justify-center transition-transform hover:-translate-y-0.5"
                    style="background-image: linear-gradient(173.83deg, rgb(31,106,77) 0%, rgb(15,61,46) 100%); box-shadow: 0 12px 20px rgba(31,106,77,0.55); border-radius: 16px; padding: 16px; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.95px; color: #f5f2e8; border: none; cursor: pointer;">
                    CONFIRM PURCHASE &amp; COLLECTION
                </button>

                <button type="button" onclick="window.location.href='user_Checkout.php'"
                    class="w-full text-center transition-opacity hover:opacity-70 mt-2"
                    style="font-family: var(--font-sans); font-size: 13px; font-weight: 500; color: #0f3d2e; background: none; border: none; cursor: pointer; text-decoration: underline; text-underline-offset: 3px;">
                    Back to Shipping
                </button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>