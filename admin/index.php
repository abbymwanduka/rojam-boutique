<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin('../');
$pageTitle = 'Dashboard';

$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders   = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pending       = (int) $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$lowStock      = (int) $pdo->query('SELECT COUNT(*) FROM products WHERE stock_quantity <= 5')->fetchColumn();
$salesValue    = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE order_status <> 'Cancelled'")->fetchColumn();

include __DIR__ . '/../includes/admin_header.php';
?>
<main class="section admin">
  <div class="container">
    <h1>Admin Dashboard</h1>
    <p>Manage products, categories, orders, and sales reports.</p>

    <div class="grid summary-grid">
      <div class="card summary-card"><strong>Total Products</strong><span><?= $totalProducts ?></span></div>
      <div class="card summary-card"><strong>Pending Orders</strong><span><?= $pending ?></span></div>
      <div class="card summary-card"><strong>Total Orders</strong><span><?= $totalOrders ?></span></div>
      <div class="card summary-card"><strong>Low Stock Items</strong><span><?= $lowStock ?></span></div>
      <div class="card summary-card"><strong>Sales Value</strong><span><?= money($salesValue) ?></span></div>
    </div>

    <div class="section">
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <a class="card" href="products.php" style="text-decoration:none;"><h2 style="color:var(--primary);margin:0 0 6px;">Products</h2><p style="margin:0;color:var(--muted);">Add, edit, delete products.</p></a>
        <a class="card" href="categories.php" style="text-decoration:none;"><h2 style="color:var(--primary);margin:0 0 6px;">Categories</h2><p style="margin:0;color:var(--muted);">Add or remove categories.</p></a>
        <a class="card" href="orders.php" style="text-decoration:none;"><h2 style="color:var(--primary);margin:0 0 6px;">Orders</h2><p style="margin:0;color:var(--muted);">Update status, add items.</p></a>
        <a class="card" href="reports.php" style="text-decoration:none;"><h2 style="color:var(--primary);margin:0 0 6px;">Reports</h2><p style="margin:0;color:var(--muted);">Filter and export sales.</p></a>
      </div>
    </div>
  </div>
</main>
</body>
</html>
