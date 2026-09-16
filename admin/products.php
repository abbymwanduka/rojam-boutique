<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin('../');
$pageTitle = 'Products';

$categories = $pdo->query('SELECT category_id, category_name FROM categories ORDER BY category_name')->fetchAll();
$editing = null;

// Handle POST (save or delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $pid = (int) ($_POST['product_id'] ?? 0);
        try {
            $stmt = $pdo->prepare('DELETE FROM products WHERE product_id = ?');
            $stmt->execute([$pid]);
            flash_set('Product deleted.');
        } catch (PDOException $e) {
            flash_set('Cannot delete: this product is referenced by existing orders.', 'error');
        }
        header('Location: products.php');
        exit;
    }

    if ($action === 'save') {
        $pid        = (int) ($_POST['product_id'] ?? 0);
        $name       = trim($_POST['product_name'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $price      = (float) ($_POST['price'] ?? 0);
        $stock      = (int) ($_POST['stock'] ?? 0);
        $desc       = trim($_POST['description'] ?? '');

        // Product name validation
        $error = validate_product_name($name);
        if ($error === '' && $categoryId <= 0) {
            $error = 'Please choose a category.';
        }
        if ($error === '' && $price < 0) {
            $error = 'Price cannot be negative.';
        }
        if ($error === '' && $stock < 0) {
            $error = 'Stock cannot be negative.';
        }
        // Duplicate name check (excluding the row being edited)
        if ($error === '') {
            $chk = $pdo->prepare('SELECT product_id FROM products WHERE product_name = ? AND product_id <> ?');
            $chk->execute([$name, $pid]);
            if ($chk->fetch()) {
                $error = 'A product with this name already exists.';
            }
        }

        if ($error !== '') {
            flash_set($error, 'error');
            // keep form values by falling through to render with $_POST
        } else {
            if ($pid > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE products SET product_name = ?, category_id = ?, price = ?, stock_quantity = ?, description = ? WHERE product_id = ?'
                );
                $stmt->execute([$name, $categoryId, $price, $stock, $desc, $pid]);
                flash_set('Product updated.');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO products (product_name, category_id, price, stock_quantity, description) VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$name, $categoryId, $price, $stock, $desc]);
                flash_set('Product added.');
            }
            header('Location: products.php');
            exit;
        }
    }
}

// Load product for editing
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$products = $pdo->query(
    'SELECT p.*, c.category_name FROM products p JOIN categories c ON c.category_id = p.category_id ORDER BY p.product_name'
)->fetchAll();

// Values to prefill the form (edit row, or rejected POST, or blank)
$fname  = $editing['product_name'] ?? ($_POST['product_name'] ?? '');
$fcat   = (int) ($editing['category_id'] ?? ($_POST['category_id'] ?? 0));
$fprice = $editing['price'] ?? ($_POST['price'] ?? '');
$fstock = $editing['stock_quantity'] ?? ($_POST['stock'] ?? '');
$fdesc  = $editing['description'] ?? ($_POST['description'] ?? '');
$fid    = (int) ($editing['product_id'] ?? ($_POST['product_id'] ?? 0));

include __DIR__ . '/../includes/admin_header.php';
?>
<main class="section admin">
  <div class="container">
    <h1>Product Management</h1>

    <section class="card admin-panel">
      <h2><?= $fid ? 'Edit Product' : 'Add Product' ?></h2>
      <form method="post" action="products.php">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="product_id" value="<?= $fid ?>">
        <div class="form-row">
          <div class="form-group">
            <label for="product_name">Product Name</label>
            <input id="product_name" name="product_name" required minlength="2" maxlength="120" value="<?= e($fname) ?>">
          </div>
          <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
              <option value="">Select category</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= (int) $c['category_id'] ?>" <?= $fcat === (int) $c['category_id'] ? 'selected' : '' ?>><?= e($c['category_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="price">Price (KES)</label>
            <input id="price" name="price" type="number" min="0" step="0.01" required value="<?= e($fprice) ?>">
          </div>
          <div class="form-group">
            <label for="stock">Stock Quantity</label>
            <input id="stock" name="stock" type="number" min="0" required value="<?= e($fstock) ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="3"><?= e($fdesc) ?></textarea>
        </div>
        <div class="actions">
          <button class="btn" type="submit">Save Product</button>
          <?php if ($fid): ?><a class="btn secondary" href="products.php">Cancel</a><?php endif; ?>
        </div>
      </form>
    </section>

    <section class="card admin-panel">
      <h2>All Products</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($products as $p): ?>
              <tr>
                <td><?= (int) $p['product_id'] ?></td>
                <td><?= e($p['product_name']) ?></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= money($p['price']) ?></td>
                <td class="<?= $p['stock_quantity'] <= 5 ? 'low-stock' : '' ?>"><?= (int) $p['stock_quantity'] ?></td>
                <td>
                  <a class="btn small secondary" href="products.php?edit=<?= (int) $p['product_id'] ?>">Edit</a>
                  <form method="post" action="products.php" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="<?= (int) $p['product_id'] ?>">
                    <button class="btn small danger" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</main>
</body>
</html>
