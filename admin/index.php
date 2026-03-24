<?php
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    adminRateLimit(); // 5 attempts per 15 min per IP
    if (hash_equals(ADMIN_PASSWORD, $_POST['password'] ?? '')) {
        adminRateLimitClear();
        session_regenerate_id(true); // prevent session fixation
        $_SESSION['admin'] = true;
        header('Location: /admin/dashboard.php');
        exit;
    }
    $error = 'Wrong password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Swypply Admin</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { background: #0f0f0f; color: #f1f1f1; font-family: system-ui, sans-serif;
         display: flex; align-items: center; justify-content: center; min-height: 100vh; }
  .card { background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 12px;
          padding: 40px; width: 100%; max-width: 380px; }
  .logo { font-size: 24px; font-weight: 700; color: #f97316; margin-bottom: 8px; }
  .sub { color: #888; font-size: 14px; margin-bottom: 32px; }
  label { display: block; font-size: 13px; color: #aaa; margin-bottom: 6px; }
  input[type=password] { width: 100%; padding: 12px 14px; background: #111; border: 1px solid #333;
          border-radius: 8px; color: #fff; font-size: 15px; outline: none; }
  input:focus { border-color: #f97316; }
  button { width: 100%; margin-top: 20px; padding: 13px; background: #f97316;
           border: none; border-radius: 8px; color: #fff; font-size: 15px;
           font-weight: 600; cursor: pointer; }
  button:hover { background: #ea6c0a; }
  .error { margin-top: 14px; color: #f87171; font-size: 13px; text-align: center; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">Swypply</div>
  <div class="sub">Admin Panel</div>
  <form method="POST">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
    <label>Password</label>
    <input type="password" name="password" placeholder="Enter admin password" autofocus autocomplete="current-password">
    <button type="submit">Sign In</button>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </form>
</div>
</body>
</html>
