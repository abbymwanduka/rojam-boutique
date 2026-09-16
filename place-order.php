<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
if ($user['role'] !== 'customer') {
    flash_set('Only customers can place orders.', 'error');
    header('Location: index.php');
    exit;
}

$cart = cart_items();
if (!$cart) {
    flash_set('Your cart is empty.', 'error');
    header('Location: cart.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Lock product rows, verify stock
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $stmt = $pdo->query("SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id IN ($ids) FOR UPDATE");
    $products = [];
    foreach ($stmt as $p) { $products[(int) $p['product_id']] = $p; }

    $total = 0;
    foreach ($cart as $pid => $qty) {
        $pid = (int) $pid; $qty = (int) $qty;
        if (!isset($products[$pid])) { throw new Exception('A product in your cart no longer exists.'); }
        if ($qty > (int) $products[$pid]['stock_quantity']) {
            throw new Exception('Insufficient stock for ' . $products[$pid]['product_name'] . '.');
        }
        $total += (float) $products[$pid]['price'] * $qty;
    }

    // Create order
    $stmt = $pdo->prepare('INSERT INTO orders (customer_id, order_status, total_amount) VALUES (?, "Pending", ?)');
    $stmt->execute([$user['id'], $total]);
    $orderId = (int) $pdo->lastInsertId();

    // Insert details + deduct stock
    $detail = $pdo->prepare('INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
    $deduct = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?');
    foreach ($cart as $pid => $qty) {
        $pid = (int) $pid; $qty = (int) $qty;
        $price = (float) $products[$pid]['price'];
        $detail->execute([$orderId, $pid, $qty, $price, $price * $qty]);
        $deduct->execute([$qty, $pid]);
    }

    $pdo->commit();
    $_SESSION['cart'] = [];
    header('Location: order-confirmation.php?id=' . $orderId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    flash_set($e->getMessage(), 'error');
    header('Location: cart.php');
    exit;
}
