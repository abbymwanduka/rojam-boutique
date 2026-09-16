<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin('../');
$pageTitle = 'Sales Reports';

// Read filters
$from       = trim($_GET['from'] ?? '');
$to         = trim($_GET['to'] ?? '');
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : 0;

// Build the sales query with filters
function build_report(PDO $pdo, $from, $to, $categoryId) {
    $sql = "SELECT p.product_name, c.category_name,
                   SUM(od.quantity) AS quantity_sold,
                   SUM(od.subtotal) AS revenue
            FROM order_details od
            JOIN products p   ON p.product_id = od.product_id
            JOIN categories c ON c.category_id = p.category_id
            JOIN orders o     ON o.order_id   = od.order_id
            WHERE o.order_status <> 'Cancelled'";
    $params = [];
    if ($from !== '') { $sql .= ' AND DATE(o.created_at) >= ?'; $params[] = $from; }
    if ($to !== '')   { $sql .= ' AND DATE(o.created_at) <= ?'; $params[] = $to; }
    if ($categoryId > 0) { $sql .= ' AND p.category_id = ?'; $params[] = $categoryId; }
    $sql .= ' GROUP BY p.product_id, p.product_name, c.category_name ORDER BY revenue DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// CSV export (must run before any HTML output)
if (($_GET['export'] ?? '') === 'csv') {
    $rows = build_report($pdo, $from, $to, $categoryId);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sales_report_' . date('Ymd_His') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Product', 'Category', 'Quantity Sold', 'Revenue (KES)']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['product_name'], $r['category_name'], (int) $r['quantity_sold'], number_format((float) $r['revenue'], 2, '.', '')]);
    }
    fclose($out);
    exit;
}

$rows = build_report($pdo, $from, $to, $categoryId);
$grandTotal = 0;
foreach ($rows as $r) { $grandTotal += (float) $r['revenue']; }

$categories = $pdo->query('SELECT category_id, category_name FROM categories ORDER BY category_name')->fetchAll();

// Preserve filters in the export link
$exportQuery = http_build_query(['export' => 'csv', 'from' => $from, 'to' => $to, 'category' => $categoryId ?: '']);

include __DIR__ . '/../includes/admin_header.php';
?>
<main class="section admin">
  <div class="container">
    <h1>Sales Reports</h1>
    <p>Filter by date range and category, then export to CSV.</p>

    <section class="card admin-panel">
      <form method="get" action="reports.php" class="form-row" style="align-items:end;">
        <div class="form-group">
          <label for="from">From Date</label>
          <input id="from" name="from" type="date" value="<?= e($from) ?>">
        </div>
        <div class="form-group">
          <label for="to">To Date</label>
          <input id="to" name="to" type="date" value="<?= e($to) ?>">
        </div>
        <div class="form-group">
          <label for="category">Category</label>
          <select id="category" name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['category_id'] ?>" <?= $categoryId === (int) $c['category_id'] ? 'selected' : '' ?>><?= e($c['category_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>&nbsp;</label>
          <div style="display:flex;gap:8px;">
            <button class="btn" type="submit">Apply Filter</button>
            <a class="btn secondary" href="reports.php?<?= e($exportQuery) ?>">Export CSV</a>
          </div>
        </div>
      </form>
    </section>

    <section class="card admin-panel">
      <h2>Results</h2>
      <?php if (!$rows): ?>
        <div class="empty">No sales match the selected filters.</div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Category</th><th>Quantity Sold</th><th>Revenue</th></tr></thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?= e($r['product_name']) ?></td>
                  <td><?= e($r['category_name']) ?></td>
                  <td><?= (int) $r['quantity_sold'] ?></td>
                  <td><?= money($r['revenue']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr><th colspan="3" style="text-align:right;">Grand Total</th><th><?= money($grandTotal) ?></th></tr>
            </tfoot>
          </table>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
</body>
</html>
