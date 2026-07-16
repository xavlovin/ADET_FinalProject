<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }

    $action = $data['action'] ?? '';
    $product_id = $data['id'] ?? null;
    $qty = isset($data['qty']) ? (int) $data['qty'] : 1;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    $cartCount = 0;
    foreach ($_SESSION['cart'] as $q) {
        $cartCount += $q;
    }

    if ($action === 'add' && $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
        $cartCount += $qty;
        echo json_encode(['status' => 'success', 'cart' => $_SESSION['cart'], 'cartCount' => $cartCount]);
    } elseif ($action === 'remove' && $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            $cartCount -= $_SESSION['cart'][$product_id];
            unset($_SESSION['cart'][$product_id]);
        }
        echo json_encode(['status' => 'success', 'cart' => $_SESSION['cart'], 'cartCount' => $cartCount]);
    } elseif ($action === 'update' && $product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            $cartCount -= $_SESSION['cart'][$product_id];
        }
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
            $cartCount += $qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
        echo json_encode(['status' => 'success', 'cart' => $_SESSION['cart'], 'cartCount' => $cartCount]);
    } elseif ($action === 'clear') {
        $_SESSION['cart'] = array();
        echo json_encode(['status' => 'success', 'cart' => [], 'cartCount' => 0]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
