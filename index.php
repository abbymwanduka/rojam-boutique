<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Home';

// Filters
$search      = trim($_GET['search'] ?? '');
$categoryId  = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : 0;

// Categories for the dropdown
$categories = $pdo->query('SELECT category_id, category_name FROM categories ORDER BY category_name')->fetchAll();

// Products (join category name), with optional filters
$sql = 'SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity,
               c.category_name, c.category_id
        FROM products p
        JOIN categories c ON c.category_id = p.category_id
        WHERE 1 = 1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (p.product_name LIKE ? OR p.description LIKE ? OR c.category_name LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($categoryId > 0) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}
$sql .= ' ORDER BY p.product_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<main>
  <section class="hero">
    <div class="container">
      <div>
        <h1>Shop Rojam Boutique clothing online.</h1>
        <p>Browse available products, add items to your cart, and place orders from anywhere.</p>
                <div class="actions">
          <a class="btn" href="#products">View Products</a>
          <?php if (current_user()): ?>
            <a class="btn secondary" href="my-orders.php">My Orders</a>
          <?php else: ?>
            <a class="btn secondary" href="register.php">Create Account</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="section" id="products">
    <div class="container">
      <div class="section-title">
        <div>
          <h2>Available Products</h2>
          <p>Search, filter by category, and add clothing items to your cart.</p>
        </div>
      </div>

      <form class="filters" method="get" action="index.php">
        <input name="search" type="search" placeholder="Search by product name or description" value="<?= e($search) ?>">
        <select name="category" onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['category_id'] ?>" <?= $categoryId === (int) $c['category_id'] ? 'selected' : '' ?>>
              <?= e($c['category_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if (!$products): ?>
        <div class="card empty">No products match your search.</div>
      <?php else: ?>
        <div class="grid product-grid">
          <?php foreach ($products as $p): ?>
            <article class="card product-card">
              <a class="product-image" href="product.php?id=<?= (int) $p['product_id'] ?>" style="text-decoration:none;"><?= e(mb_substr($p['product_name'], 0, 1)) ?></a>
              <div class="product-meta">
                <span><?= e($p['category_name']) ?></span>
                <span class="<?= $p['stock_quantity'] <= 5 ? 'low-stock' : '' ?>">Stock: <?= (int) $p['stock_quantity'] ?></span>
              </div>
              <h3><a href="product.php?id=<?= (int) $p['product_id'] ?>" style="text-decoration:none;color:inherit;"><?= e($p['product_name']) ?></a></h3>
              <p><?= e($p['description']) ?></p>
              <div class="product-meta"><span class="price"><?= money($p['price']) ?></span></div>
              <form method="post" action="cart.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $p['product_id'] ?>">
                <input type="hidden" name="redirect" value="index.php#products">
                <button class="btn" type="submit" <?= $p['stock_quantity'] <= 0 ? 'disabled' : '' ?>>Add to Cart</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<footer class="site-footer">
  <div class="container">
    <p style="margin:0 0 6px;"><strong>Contact us:</strong> Rojam Boutique, Kitengela, Kajiado County &nbsp;|&nbsp; Phone: 0700111222 &nbsp;|&nbsp; Email: info@rojam.co.ke</p>
    &copy; <?= date('Y') ?> Rojam Boutique Online Clothing Management System.
  </div>
</footer>
</body>
</html>
