<?php
include 'includes/config.php';

// Session Lock
if (!isset($_SESSION['accesslevel']) || $_SESSION['accesslevel'] !== 'user') {
  header("Location: login.php");
  exit();
}
include 'includes/header.php';

// Process the order
if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
  $cartTotal = 0;
  $ids = array_keys($_SESSION['cart']);
  $ids_string = implode(',', array_map('intval', $ids));

  // Calculate total
  $query = "SELECT id, price FROM inventory WHERE id IN ($ids_string)";
  $result = mysqli_query($conn, $query);
  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $cartTotal += $row['price'] * $_SESSION['cart'][$row['id']];
    }
  }

  $shipping = 12;
  $tax = round($cartTotal * 0.07);
  $totalAmount = $cartTotal + $shipping + $tax;
  $user_id = $_SESSION['user_id'];

  // Insert into orders
  $insertOrderQuery = "INSERT INTO orders (user_id, total_amount, status) VALUES ('$user_id', '$totalAmount', 'Pending')";
  if (mysqli_query($conn, $insertOrderQuery)) {
    $order_id = mysqli_insert_id($conn);

    // Fetch full product details for order items
    $productQuery = "SELECT id, price, stock FROM inventory WHERE id IN ($ids_string)";
    $productResult = mysqli_query($conn, $productQuery);

    while ($prod = mysqli_fetch_assoc($productResult)) {
      $pid = $prod['id'];
      $price = $prod['price'];
      $qty = $_SESSION['cart'][$pid];

      // Insert order item
      $insertItemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$pid', '$qty', '$price')";
      mysqli_query($conn, $insertItemQuery);

      // Deduct stock
      $newStock = max(0, $prod['stock'] - $qty);
      $updateStockQuery = "UPDATE inventory SET stock = '$newStock' WHERE id = '$pid'";
      mysqli_query($conn, $updateStockQuery);
    }
  }

  // Clear the cart on successful payment
  unset($_SESSION['cart']);
}
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

    <!-- ── SUCCESS STATE ── -->
    <div class="flex flex-col items-center justify-center rounded-3xl py-20 text-center"
      style="background: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.6); box-shadow: 0 8px 32px rgba(15,61,46,0.06);">
      <div class="flex items-center justify-center rounded-full"
        style="width: 72px; height: 72px; background: linear-gradient(173.83deg, rgb(31,106,77), rgb(15,61,46)); box-shadow: 0 0 0 4px rgba(31,106,77,0.15), 0 12px 40px rgba(31,106,77,0.4);">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
          <path d="M5 13l4 4L19 7" stroke="#f5f2e8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <h2 class="mt-6" style="font-family: var(--font-serif); font-size: 36px; font-weight: 500; color: #082820;">
        Purchase confirmed.
      </h2>
      <p class="mt-3"
        style="font-family: var(--font-sans); font-size: 14px; color: #6b7268; max-width: 400px; margin-left: auto; margin-right: auto;">
        Your collection is being prepared. A concierge will be in touch with your tracking details shortly.
      </p>
      <button onclick="window.location.href='index.php'"
        class="mt-8 rounded-2xl px-8 py-4 transition-transform hover:-translate-y-0.5"
        style="background-image: linear-gradient(173.83deg, rgb(31,106,77), rgb(15,61,46)); color: #f5f2e8; font-family: var(--font-sans); font-size: 13px; font-weight: 500; letter-spacing: 1.5px; box-shadow: 0 12px 20px rgba(31,106,77,0.45); border: none; cursor: pointer;">
        RETURN TO STORE
      </button>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>