<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Login';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        // Try admin first
        $stmt = $pdo->prepare('SELECT admin_id, full_name, password_hash FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['user'] = [
                'id'    => (int) $admin['admin_id'],
                'name'  => $admin['full_name'],
                'email' => $email,
                'role'  => 'admin',
            ];
            flash_set('Welcome back, ' . $admin['full_name'] . '.');
            header('Location: admin/index.php');
            exit;
        }

        // Then customer
        $stmt = $pdo->prepare('SELECT customer_id, first_name, last_name, password_hash FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        $cust = $stmt->fetch();

        if ($cust && password_verify($password, $cust['password_hash'])) {
            $_SESSION['user'] = [
                'id'    => (int) $cust['customer_id'],
                'name'  => $cust['first_name'] . ' ' . $cust['last_name'],
                'email' => $email,
                'role'  => 'customer',
            ];
            flash_set('Login successful.');
            header('Location: index.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <section class="card form-card">
    <h1>Login</h1>
    <p>Log in as a customer, or as the boutique administrator.</p>
    <?php if ($error): ?><div class="message error show"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="login.php">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>
      </div>
      <button class="btn" type="submit">Login</button>
    </form>
    <p style="margin-top:14px;">No account? <a href="register.php">Register here</a>.</p>
  </section>
</main>
</body>
</html>
