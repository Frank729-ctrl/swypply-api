<?php
require_once __DIR__ . '/auth.php';
adminAuth();
require_once __DIR__ . '/../api/lib/DB.php';

$stats = [
    'total_users'  => DB::find('SELECT COUNT(*) AS c FROM users')['c'],
    'free_users'   => DB::find("SELECT COUNT(*) AS c FROM users WHERE plan = 'free'")['c'],
    'basic_users'  => DB::find("SELECT COUNT(*) AS c FROM users WHERE plan = 'basic'")['c'],
    'pro_users'    => DB::find("SELECT COUNT(*) AS c FROM users WHERE plan = 'pro'")['c'],
    'total_apps'   => DB::find('SELECT COUNT(*) AS c FROM applications')['c'],
    'apps_today'   => DB::find("SELECT COUNT(*) AS c FROM applications WHERE created_at::date = CURRENT_DATE")['c'],
    'new_today'    => DB::find("SELECT COUNT(*) AS c FROM users WHERE created_at::date = CURRENT_DATE")['c'],
];

$recent = DB::findAll('SELECT id, name, email, plan, ai_used, ai_limit, created_at FROM users ORDER BY created_at DESC LIMIT 10');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard — Swypply Admin</title>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>
<main>
  <h1>Dashboard</h1>

  <div class="stats-grid">
    <div class="stat"><div class="stat-val"><?= $stats['total_users'] ?></div><div class="stat-lbl">Total Users</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['new_today'] ?></div><div class="stat-lbl">New Today</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['free_users'] ?></div><div class="stat-lbl">Free Plan</div></div>
    <div class="stat"><div class="stat-val orange"><?= $stats['basic_users'] ?></div><div class="stat-lbl">Basic Plan</div></div>
    <div class="stat"><div class="stat-val orange"><?= $stats['pro_users'] ?></div><div class="stat-lbl">Pro Plan</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['total_apps'] ?></div><div class="stat-lbl">Applications</div></div>
    <div class="stat"><div class="stat-val"><?= $stats['apps_today'] ?></div><div class="stat-lbl">Applied Today</div></div>
  </div>

  <h2>Recent Sign-ups</h2>
  <div class="table-wrap">
  <table>
    <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Plan</th><th>AI Used</th><th>Joined</th></tr></thead>
    <tbody>
    <?php foreach ($recent as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="badge <?= $u['plan'] !== 'free' ? 'paid' : '' ?>"><?= $u['plan'] ?></span></td>
      <td><?= $u['ai_used'] ?> / <?= $u['ai_limit'] ?></td>
      <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <a href="/admin/users.php" class="btn">View All Users →</a>
</main>
</body>
</html>
