<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Shopping Cart';

// Handle cart actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $cart      = cart_items();

    if ($action === 'add' && $productId > 0) {
        $stmt = $pdo->prepare('SELECT stock_quantity FROM products WHERE product_id = ?');
        $stmt->execute([$productId]);
        $stock = (int) ($stmt->fetchColumn() ?: 0);
        $have = $cart[$productId] ?? 0;
        if ($have + 1 > $stock) {
            flash_set('Requested quantity exceeds available stock.', 'error');
        } else {
            $cart[$productId] = $have + 1;
            flash_set('Item added to cart.');
        }
    } elseif ($action === 'increase' && $productId > 0) {
        $stmt = $pdo->prepare('SELECT stock_quantity FROM products WHERE product_id = ?');
        $stmt->execute([$productId]);
        $stock = (int) ($stmt->fetchColumn() ?: 0);
        $have = $cart[$productId] ?? 0;
        if ($have + 1 > $stock) {
            flash_set('Requested quantity exceeds available stock.', 'error');
        } else {
            $cart[$productId] = $have + 1;
        }
    } elseif ($action === 'decrease' && $productId > 0) {
        $have = $cart[$productId] ?? 0;
        if ($have - 1 <= 0) { unset($cart[$productId]); }
        else { $cart[$productId] = $have - 1; }
    } elseif ($action === 'remove' && $productId > 0) {
        unset($cart[$productId]);
    }

    $_SESSION['cart'] = $cart;

    // After "add" from the catalogue or product page, return to that page
    // (with a confirmation message) instead of jumping to the cart.
    if ($action === 'add') {
        $back = $_POST['redirect'] ?? 'index.php';
        // Safety: only allow local relative paths, never external URLs
        if (strpos($back, '://') !== false || substr($back, 0, 2) === '//') {
            $back = 'index.php';
        }
        header('Location: ' . $back);
        exit;
    }

    header('Location: cart.php');
    exit;
}

// Build cart rows
$cart = cart_items();
$rows = [];
$total = 0;
if ($cart) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = $pdo->query("SELECT product_id, product_name, price FROM products WHERE product_id IN ($ids)");
    foreach ($result as $p) {
        $pid = (int) $p['product_id'];
        $qty = (int) $cart[$pid];
        $subtotal = (float) $p['price'] * $qty;
        $total += $subtotal;
        $rows[] = ['id' => $pid, 'name' => $p['product_name'], 'price' => $p['price'], 'qty' => $qty, 'subtotal' => $subtotal];
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <div class="section-title"><div><h1>Shopping Cart</h1><p>Review selected products and place an order.</p></div></div>

    <?php if (!$rows): ?>
      <div class="card empty"><p>Your cart is empty.</p><a class="btn" href="index.php#products">Browse Products</a></div>
    <?php else: ?>
      <div class="table-wrap card">
        <table>
          <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><strong><?= e($r['name']) ?></strong></td>
                <td><?= money($r['price']) ?></td>
                <td>
                  <div class="qty-control">
                    <form method="post" action="cart.php" style="display:inline;">
                      <input type="hidden" name="action" value="decrease">
                      <input type="hidden" name="product_id" value="<?= $r['id'] ?>">
                      <button type="submit">&minus;</button>
                    </form>
                    <span><?= $r['qty'] ?></span>
                    <form method="post" action="cart.php" style="display:inline;">
                      <input type="hidden" name="action" value="increase">
                      <input type="hidden" name="product_id" value="<?= $r['id'] ?>">
                      <button type="submit">+</button>
                    </form>
                  </div>
                </td>
                <td><?= money($r['subtotal']) ?></td>
                <td>
                  <form method="post" action="cart.php">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?= $r['id'] ?>">
                    <button class="btn danger small" type="submit">Remove</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="card" style="margin-top:18px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h2 style="margin:0;color:var(--primary);">Total: <?= money($total) ?></h2>
        <form method="post" action="place-order.php">
          <button class="btn" type="submit">Place Order</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
