<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'My Orders';
$user = current_user();

$stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY order_id DESC');
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

// Preload items per order
$itemsByOrder = [];
if ($orders) {
    $ids = implode(',', array_map(fn($o) => (int) $o['order_id'], $orders));
    $result = $pdo->query(
        "SELECT od.order_id, od.quantity, od.unit_price, od.subtotal, p.product_name
         FROM order_details od JOIN products p ON p.product_id = od.product_id
         WHERE od.order_id IN ($ids)"
    );
    foreach ($result as $row) { $itemsByOrder[(int) $row['order_id']][] = $row; }
}

include __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="section-title"><div><h1>My Orders</h1><p>Track placed orders and their current status.</p></div></div>

    <?php if (!$orders): ?>
      <div class="card empty">No orders found for this account.</div>
    <?php else: foreach ($orders as $o): ?>
      <article class="card" style="margin-bottom:18px;">
        <div class="section-title" style="margin-bottom:10px;">
          <div><h2>Order #<?= (int) $o['order_id'] ?></h2><p><?= e($o['created_at']) ?></p></div>
          <span class="badge"><?= e($o['order_status']) ?></span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($itemsByOrder[(int) $o['order_id']] ?? [] as $it): ?>
                <tr><td><?= e($it['product_name']) ?></td><td><?= money($it['unit_price']) ?></td><td><?= (int) $it['quantity'] ?></td><td><?= money($it['subtotal']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <h3 style="text-align:right;color:var(--primary);">Total: <?= money($o['total_amount']) ?></h3>
      </article>
    <?php endforeach; endif; ?>
  </div>
</main>
</body>
</html>
