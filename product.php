<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Product';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare(
    'SELECT p.*, c.category_name
     FROM products p JOIN categories c ON c.category_id = p.category_id
     WHERE p.product_id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

include __DIR__ . '/includes/header.php';
?>
<main class="section">
  <div class="container">
    <?php if (!$product): ?>
      <div class="card empty"><p>Product not found.</p><a class="btn" href="index.php#products">Back to Products</a></div>
    <?php else:
      $stock = (int) $product['stock_quantity'];
      if ($stock <= 0) { $stockText = 'Out of stock'; $stockClass = 'stock-out'; }
      elseif ($stock <= 5) { $stockText = "Low stock ($stock left)"; $stockClass = 'stock-low'; }
      else { $stockText = "In stock ($stock available)"; $stockClass = 'stock-ok'; }
    ?>
      <div class="card detail-grid">
        <div class="detail-image"><?= e(mb_substr($product['product_name'], 0, 1)) ?></div>
        <div class="detail-info">
          <span class="badge"><?= e($product['category_name']) ?></span>
          <h1><?= e($product['product_name']) ?></h1>
          <p><?= e($product['description']) ?></p>
          <div class="detail-price"><?= money($product['price']) ?></div>
          <p class="<?= $stockClass ?>"><?= e($stockText) ?></p>
          <div class="actions">
            <form method="post" action="cart.php">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
              <input type="hidden" name="redirect" value="product.php?id=<?= (int) $product['product_id'] ?>">
              <button class="btn" type="submit" <?= $stock <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
            </form>
            <a class="btn secondary" href="index.php#products">Back to Products</a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
