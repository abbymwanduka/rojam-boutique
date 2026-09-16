<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin('../');
$pageTitle = 'Orders';

$statuses = ['Pending', 'Processing', 'Ready for Pickup', 'Completed', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'status') {
        $oid = (int) ($_POST['order_id'] ?? 0);
        $status = $_POST['order_status'] ?? '';
        if (in_array($status, $statuses, true)) {
            $stmt = $pdo->prepare('UPDATE orders SET order_status = ? WHERE order_id = ?');
            $stmt->execute([$status, $oid]);
            flash_set('Order #' . $oid . ' updated to ' . $status . '.');
        }
        header('Location: orders.php');
        exit;
    }

    if ($action === 'add_item') {
        $oid = (int) ($_POST['order_id'] ?? 0);
        $pid = (int) ($_POST['product_id'] ?? 0);
        $qty = (int) ($_POST['quantity'] ?? 0);

        try {
            if ($qty < 1) { throw new Exception('Quantity must be at least 1.'); }

            $pdo->beginTransaction();

            // Lock product, check stock
            $stmt = $pdo->prepare('SELECT product_name, price, stock_quantity FROM products WHERE product_id = ? FOR UPDATE');
            $stmt->execute([$pid]);
            $product = $stmt->fetch();
            if (!$product) { throw new Exception('Product not found.'); }
            if ($qty > (int) $product['stock_quantity']) {
                throw new Exception('Insufficient stock for ' . $product['product_name'] . '.');
            }

            $price = (float) $product['price'];
            $subtotal = $price * $qty;

            // Add line
            $stmt = $pdo->prepare('INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$oid, $pid, $qty, $price, $subtotal]);

            // Deduct stock, update order total
            $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?')->execute([$qty, $pid]);
            $pdo->prepare('UPDATE orders SET total_amount = total_amount + ? WHERE order_id = ?')->execute([$subtotal, $oid]);

            $pdo->commit();
            flash_set('Added ' . $qty . ' x ' . $product['product_name'] . ' to order #' . $oid . '.');
        } catch (Exception $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            flash_set($e->getMessage(), 'error');
        }
        header('Location: orders.php#order-' . $oid);
        exit;
    }
}

// Load orders (newest first) with customer name
$orders = $pdo->query(
    'SELECT o.*, c.first_name, c.last_name, c.email
     FROM orders o JOIN customers c ON c.customer_id = o.customer_id
     ORDER BY o.order_id DESC'
)->fetchAll();

// Items per order
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

// Products for the add-item dropdown
$productList = $pdo->query('SELECT product_id, product_name, price, stock_quantity FROM products ORDER BY product_name')->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<main class="section admin">
  <div class="container">
    <h1>Order Management</h1>
    <p>Update order status and add product lines to an existing order.</p>

    <?php if (!$orders): ?>
      <div class="card empty">No orders have been placed yet.</div>
    <?php else: foreach ($orders as $o): $oid = (int) $o['order_id']; ?>
      <article class="card admin-panel" id="order-<?= $oid ?>">
        <div class="section-title" style="margin-bottom:10px;">
          <div>
            <h2>Order #<?= $oid ?></h2>
            <p><?= e($o['first_name'] . ' ' . $o['last_name']) ?> &middot; <?= e($o['email']) ?> &middot; <?= e($o['created_at']) ?></p>
          </div>
          <span class="badge"><?= e($o['order_status']) ?></span>
        </div>

        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
              <?php foreach ($itemsByOrder[$oid] ?? [] as $it): ?>
                <tr><td><?= e($it['product_name']) ?></td><td><?= money($it['unit_price']) ?></td><td><?= (int) $it['quantity'] ?></td><td><?= money($it['subtotal']) ?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <h3 style="text-align:right;color:var(--primary);">Total: <?= money($o['total_amount']) ?></h3>

        <div class="form-row" style="align-items:end;">
          <!-- Update status -->
          <form method="post" action="orders.php" class="form-group">
            <label>Update Status</label>
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="order_id" value="<?= $oid ?>">
            <div style="display:flex;gap:8px;">
              <select name="order_status">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= e($s) ?>" <?= $s === $o['order_status'] ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn small" type="submit">Save</button>
            </div>
          </form>

          <!-- Add product line -->
          <form method="post" action="orders.php" class="form-group">
            <label>Add Product to Order</label>
            <input type="hidden" name="action" value="add_item">
            <input type="hidden" name="order_id" value="<?= $oid ?>">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
              <select name="product_id" required>
                <option value="">Select product</option>
                <?php foreach ($productList as $p): ?>
                  <option value="<?= (int) $p['product_id'] ?>"><?= e($p['product_name']) ?> (<?= money($p['price']) ?>, stock <?= (int) $p['stock_quantity'] ?>)</option>
                <?php endforeach; ?>
              </select>
              <input type="number" name="quantity" min="1" value="1" style="width:90px;">
              <button class="btn small" type="submit">Add</button>
            </div>
          </form>
        </div>
      </article>
    <?php endforeach; endif; ?>
  </div>
</main>
</body>
</html>
