<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Register';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirmPassword'] ?? '';

    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    if (!preg_match('/^0\d{9}$/', $phone)) $errors[] = 'Enter a valid Kenyan phone, e.g. 0712345678.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        // Unique email check
        $stmt = $pdo->prepare('SELECT customer_id FROM customers WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO customers (first_name, last_name, phone, email, password_hash)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$firstName, $lastName, $phone, $email, $hash]);

            $_SESSION['user'] = [
                'id'    => (int) $pdo->lastInsertId(),
                'name'  => $firstName . ' ' . $lastName,
                'email' => $email,
                'role'  => 'customer',
            ];
            flash_set('Registration successful. Welcome!');
            header('Location: index.php');
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main class="container">
  <section class="card form-card">
    <h1>Create Customer Account</h1>
    <p>Register to place orders and track order progress.</p>
    <?php if ($errors): ?>
      <div class="message error show"><?= e(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
      <div class="form-row">
        <div class="form-group">
          <label for="firstName">First Name</label>
          <input id="firstName" name="firstName" required value="<?= e($_POST['firstName'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label for="lastName">Last Name</label>
          <input id="lastName" name="lastName" required value="<?= e($_POST['lastName'] ?? '') ?>">
        </div>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input id="phone" name="phone" required placeholder="e.g. 0712345678" value="<?= e($_POST['phone'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="password">Password</label>
                    <input id="password" name="password" type="password" required minlength="6" oninput="checkStrength()">
          <div style="height:6px;background:#eee;border-radius:3px;margin-top:6px;overflow:hidden;">
            <div id="strengthBar" style="height:100%;width:0;background:#ccc;transition:width .2s;"></div>
          </div>
          <small id="strengthText" style="color:#777;"></small>
        </div>
        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input id="confirmPassword" name="confirmPassword" type="password" required minlength="6">
        </div>
      </div>
      <button class="btn" type="submit">Register</button>
    </form>
    <p style="margin-top:14px;">Already registered? <a href="login.php">Login here</a>.</p>
  </section>
</main>
<script>
function checkStrength() {
  var p = document.getElementById('password').value;
  var score = 0;
  if (p.length >= 6) score++;
  if (p.length >= 10) score++;
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++;
  if (/[0-9]/.test(p)) score++;
  if (/[^A-Za-z0-9]/.test(p)) score++;
  var bar = document.getElementById('strengthBar');
  var text = document.getElementById('strengthText');
  var levels = [
    {w:'20%', c:'#c0392b', t:'Very weak'},
    {w:'40%', c:'#e67e22', t:'Weak'},
    {w:'60%', c:'#f1c40f', t:'Fair'},
    {w:'80%', c:'#27ae60', t:'Good'},
    {w:'100%', c:'#1e8449', t:'Strong'}
  ];
  if (p.length === 0) { bar.style.width='0'; text.textContent=''; return; }
  var lvl = levels[Math.min(score, 5) - 1] || levels[0];
  bar.style.width = lvl.w;
  bar.style.background = lvl.c;
  text.textContent = 'Password strength: ' + lvl.t;
}
</script>
</body>
</html>
