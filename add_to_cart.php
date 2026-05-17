<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Support both POST (new UI) and GET (legacy/fallback)
$item_id = 0;
$quantity = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
} else {
    $item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $quantity = isset($_GET['quantity']) ? (int)$_GET['quantity'] : 1;
}

if ($item_id > 0) {
    // Initialize cart if empty
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or increment quantity
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id] += $quantity;
    } else {
        $_SESSION['cart'][$item_id] = $quantity;
    }

    $total_items = array_sum($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'status' => 'success', // For UI compatibility
        'new_count' => $total_items
    ]);
} else {
    echo json_encode([
        'success' => false,
        'status' => 'error',
        'message' => 'Invalid product ID'
    ]);
}
