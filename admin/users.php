<?php
require_once __DIR__ . '/auth.php';
adminAuth();
require_once __DIR__ . '/../api/lib/DB.php';

// Handle plan change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['plan'])) {
    csrfVerify();
    $limits = ['free' => 3, 'basic' => 20, 'pro' => 9999];
    $plan   = $_POST['plan'];
    if (isset($limits[$plan])) {
        DB::query('UPDATE users SET plan = ?, ai_limit = ? WHERE id = ?',
            [$plan, $limits[$plan], (int) $_POST['user_id']]);
    }
    header('Location: /admin/users.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
if ($search) {
    $users = DB::findAll(
        'SELECT id, name, email, plan, ai_used, ai_limit, push_token, created_at FROM users
          WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC',
        ["%$search%", "%$search%"]
    );
} else {
    $users = DB::findAll(
        'SELECT id, name, email, plan, ai_used, ai_limit, push_token, created_at FROM users ORDER BY created_at DESC'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Users — Swypply Admin</title>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>
<main>
  <h1>Users <span style="color:#888;font-size:16px;font-weight:400">(<?= count($users) ?>)</span></h1>

  <form method="GET" style="margin-bottom:24px">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email…" style="max-width:320px">
    <button type="submit" class="btn" style="margin-left:8px;padding:10px 20px">Search</button>
    <?php if ($search): ?><a href="/admin/users.php" class="btn" style="margin-left:8px;padding:10px 20px;background:#333">Clear</a><?php endif; ?>
  </form>

  <div class="table-wrap">
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Plan</th><th>AI Used</th><th>Push Token</th><th>Joined</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="badge <?= $u['plan'] !== 'free' ? 'paid' : '' ?>"><?= $u['plan'] ?></span></td>
      <td><?= $u['ai_used'] ?> / <?= $u['ai_limit'] ?></td>
      <td style="font-size:11px;color:#666"><?= $u['push_token'] ? '✓' : '—' ?></td>
      <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td>
        <form method="POST" style="display:flex;gap:6px">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
          <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
          <select name="plan" style="padding:4px 8px;background:#222;border:1px solid #333;color:#fff;border-radius:6px">
            <?php foreach (['free','basic','pro'] as $p): ?>
              <option value="<?= $p ?>" <?= $u['plan'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn" style="padding:4px 12px;font-size:12px">Save</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</main>
</body>
</html>
