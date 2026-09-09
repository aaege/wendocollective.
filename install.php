<?php
// One-time setup: creates the first admin account. Refuses to run once one exists.
// Delete this file after you've used it.
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$existing = db()->query('SELECT COUNT(*) AS c FROM admin_users')->fetch()['c'];
$error = '';
$done = false;

if ($existing > 0) {
    $error = 'An admin account already exists. Delete install.php. Setup is complete.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $stmt = db()->prepare('INSERT INTO admin_users (email, password_hash) VALUES (?, ?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
        $done = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head><meta charset="UTF-8"><title>Wendo CMS: Setup</title>
<style>
  body{ font-family:system-ui,sans-serif; background:#1A0505; color:#FAF3BA; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
  .card{ background:#2A0A0A; border:1px solid rgba(250,243,186,.2); padding:2rem; max-width:380px; width:100%; }
  h1{ font-size:1.3rem; margin:0 0 1rem; }
  label{ display:block; font-size:.85rem; margin:1rem 0 .3rem; }
  input{ width:100%; padding:.7rem; border:1px solid rgba(250,243,186,.3); background:#1A0505; color:#FAF3BA; box-sizing:border-box; }
  button{ margin-top:1.5rem; width:100%; padding:.8rem; background:#F8DF01; color:#241209; font-weight:700; border:none; cursor:pointer; }
  .msg{ background:rgba(255,255,255,.08); padding:.75rem 1rem; margin-bottom:1rem; font-size:.9rem; }
</style>
</head>
<body>
  <div class="card">
    <h1>Wendo CMS: First-time Setup</h1>
    <?php if ($done): ?>
      <p class="msg">Admin account created. <a href="admin/login.php" style="color:#F8DF01;">Go to login →</a></p>
      <p style="font-size:.8rem;opacity:.7;">Now delete install.php from the server.</p>
    <?php else: ?>
      <?php if ($error): ?><p class="msg"><?= e($error) ?></p><?php endif; ?>
      <?php if ($existing == 0): ?>
      <form method="post">
        <?= csrf_field() ?>
        <label for="email">Admin email</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password (min 8 characters)</label>
        <input type="password" id="password" name="password" required minlength="8">
        <button type="submit">Create Admin Account</button>
      </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>
</html>
