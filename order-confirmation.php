<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Order Confirmation';

$user = current_user();
$orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
    'SELECT o.*, c.first_name, c.last_name, c.email, c.phone
     FROM orders o JOIN customers c ON c.customer_id = o.customer_id
     WHERE o.order_id = ?'
);
$stmt->execute([$orderId]);
$order = $stmt->fetch();

// Only owner (or admin) can view
if ($order && $user['role'] !== 'admin' && (int) $order['customer_id'] !== (int) $user['id']) {
    $order = null;
}

$items = [];
if ($order) {
    $stmt = $pdo->prepare(
        'SELECT od.quantity, od.unit_price, od.subtotal, p.product_name
         FROM order_details od JOIN products p ON p.product_id = od.product_id
         WHERE od.order_id = ?'
    );
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="section-title"><div><h1>Order Confirmation</h1><p>Your order receipt is shown below.</p></div></div>

    <?php if (!$order): ?>
      <div class="card empty"><p>Order not found.</p><a class="btn" href="index.php">Home</a></div>
    <?php else: ?>
      <div class="message success show">Your order has been placed successfully.</div>
      <div class="card">
        <div class="receipt-head">
          <div>
            <h2 style="margin:0;color:var(--primary);">Order Receipt</h2>
            <p style="margin:4px 0 0;">Order No: <strong>#<?= (int) $order['order_id'] ?></strong></p>
            <p style="margin:2px 0 0;"><?= e($order['created_at']) ?></p>
          </div>
          <div style="text-align:right;">
            <p style="margin:0;"><strong><?= e($order['first_name'] . ' ' . $order['last_name']) ?></strong></p>
            <p style="margin:2px 0 0;"><?= e($order['email']) ?></p>
            <p style="margin:2px 0 0;"><?= e($order['phone']) ?></p>
            <span class="badge"><?= e($order['order_status']) ?></span>
          </div>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><?= e($it['product_name']) ?></td>
                  <td><?= money($it['unit_price']) ?></td>
                  <td><?= (int) $it['quantity'] ?></td>
                  <td><?= money($it['subtotal']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <h3 style="text-align:right;color:var(--primary);">Total: <?= money($order['total_amount']) ?></h3>
        <div class="actions">
          <a class="btn" href="my-orders.php">View My Orders</a>
          <a class="btn secondary" href="index.php#products">Continue Shopping</a>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
