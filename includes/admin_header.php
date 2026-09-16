<?php
$user = current_user();
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin | Rojam Boutique</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="brand"><span class="brand-mark">RB</span><span>Rojam Boutique Admin</span></a>
      <nav class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="categories.php">Categories</a>
        <a href="orders.php">Orders</a>
        <a href="reports.php">Reports</a>
        <a href="../index.php">View Site</a>
        <a href="../logout.php">Logout</a>
      </nav>
    </div>
  </header>
  <?php if ($flash): ?>
    <div class="container" style="margin-top:14px;">
      <div class="message <?= e($flash['type']) ?> show"><?= e($flash['message']) ?></div>
    </div>
  <?php endif; ?>
