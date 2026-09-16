<?php
$user = current_user();
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Rojam Boutique</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
  <header class="site-header">
    <div class="container navbar">
      <a href="index.php" class="brand"><span class="brand-mark">RB</span><span>Rojam Boutique</span></a>
      <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="cart.php" class="cart-pill">Cart (<?= cart_count() ?>)</a>
        <a href="my-orders.php">My Orders</a>
        <?php if (is_admin()): ?>
          <a href="admin/index.php">Admin</a>
        <?php endif; ?>
        <?php if ($user): ?>
          <span style="padding:9px 4px;color:var(--muted);">Hi, <?= e($user['name']) ?></span>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="register.php">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <?php if ($flash): ?>
    <div class="container" style="margin-top:14px;">
      <div class="message <?= e($flash['type']) ?> show"><?= e($flash['message']) ?></div>
    </div>
  <?php endif; ?>
