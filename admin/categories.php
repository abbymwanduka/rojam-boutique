<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin('../');
$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['category_name'] ?? '');
        if ($name === '' || mb_strlen($name) < 2) {
            flash_set('Category name must be at least 2 characters.', 'error');
        } else {
            $chk = $pdo->prepare('SELECT category_id FROM categories WHERE category_name = ?');
            $chk->execute([$name]);
            if ($chk->fetch()) {
                flash_set('That category already exists.', 'error');
            } else {
                $stmt = $pdo->prepare('INSERT INTO categories (category_name) VALUES (?)');
                $stmt->execute([$name]);
                flash_set('Category added.');
            }
        }
        header('Location: categories.php');
        exit;
    }

    if ($action === 'delete') {
        $cid = (int) ($_POST['category_id'] ?? 0);
        try {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE category_id = ?');
            $stmt->execute([$cid]);
            flash_set('Category deleted.');
        } catch (PDOException $e) {
            flash_set('Cannot delete: products are still assigned to this category.', 'error');
        }
        header('Location: categories.php');
        exit;
    }
}

// Categories with product counts
$categories = $pdo->query(
    'SELECT c.category_id, c.category_name, COUNT(p.product_id) AS product_count
     FROM categories c LEFT JOIN products p ON p.category_id = c.category_id
     GROUP BY c.category_id, c.category_name
     ORDER BY c.category_name'
)->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<main class="section admin">
  <div class="container">
    <h1>Category Management</h1>

    <section class="card admin-panel">
      <h2>Add New Category</h2>
      <form method="post" action="categories.php">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
          <label for="category_name">Category Name</label>
          <input id="category_name" name="category_name" required minlength="2" maxlength="80" placeholder="e.g. Accessories">
        </div>
        <button class="btn" type="submit">Add Category</button>
      </form>
    </section>

    <section class="card admin-panel">
      <h2>All Categories</h2>
      <div class="table-wrap">
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Products</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($categories as $c): ?>
              <tr>
                <td><?= (int) $c['category_id'] ?></td>
                <td><?= e($c['category_name']) ?></td>
                <td><?= (int) $c['product_count'] ?></td>
                <td>
                  <form method="post" action="categories.php" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" value="<?= (int) $c['category_id'] ?>">
                    <button class="btn small danger" type="submit" <?= $c['product_count'] > 0 ? 'disabled title="Reassign products first"' : '' ?>>Delete</button>
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
