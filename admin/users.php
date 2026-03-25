<?php
require_once __DIR__ . '/auth.php';
adminAuth();
require_once __DIR__ . '/../api/lib/DB.php';

// ── Handle POST actions ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $uid    = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'set_plan' && $uid) {
        $limits = ['free' => 3, 'basic' => 20, 'pro' => 9999];
        $plan   = $_POST['plan'] ?? '';
        if (isset($limits[$plan])) {
            $expires = $plan !== 'free' ? date('c', strtotime('+30 days')) : null;
            DB::query(
                'UPDATE users SET plan = ?, ai_limit = ?, subscription_expires_at = ? WHERE id = ?',
                [$plan, $limits[$plan], $expires, $uid]
            );
        }
    }

    if ($action === 'verify_email' && $uid) {
        DB::query(
            'UPDATE users SET email_verified_at = NOW(),
                              email_verification_code = NULL,
                              email_verification_expires_at = NULL
             WHERE id = ?',
            [$uid]
        );
    }

    if ($action === 'reset_ai' && $uid) {
        DB::query('UPDATE users SET ai_used = 0 WHERE id = ?', [$uid]);
    }

    if ($action === 'delete' && $uid) {
        DB::query('DELETE FROM users WHERE id = ?', [$uid]);
    }

    header('Location: /admin/users.php' . ($_GET['q'] ? '?q=' . urlencode($_GET['q']) : ''));
    exit;
}

// ── Query ─────────────────────────────────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
if ($search) {
    $users = DB::findAll(
        'SELECT id, name, email, plan, ai_used, ai_limit, push_token,
                email_verified_at, subscription_expires_at, created_at
         FROM users WHERE name ILIKE ? OR email ILIKE ? ORDER BY created_at DESC',
        ["%$search%", "%$search%"]
    );
} else {
    $users = DB::findAll(
        'SELECT id, name, email, plan, ai_used, ai_limit, push_token,
                email_verified_at, subscription_expires_at, created_at
         FROM users ORDER BY created_at DESC'
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
<style>
.actions { display:flex; gap:6px; flex-wrap:wrap; align-items:center; }
.btn-sm { padding:4px 10px; font-size:12px; border-radius:6px; border:none;
          cursor:pointer; font-weight:600; color:#fff; text-decoration:none; display:inline-block; }
.btn-orange { background:#f97316; }
.btn-green  { background:#16a34a; }
.btn-red    { background:#dc2626; }
.btn-gray   { background:#444; }
.btn-sm:hover { opacity:0.85; }
</style>
</head>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>
<main>
  <h1>Users <span style="color:#888;font-size:16px;font-weight:400">(<?= count($users) ?>)</span></h1>

  <form method="GET" style="margin-bottom:24px;display:flex;gap:8px;align-items:center">
    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email…" style="max-width:320px">
    <button type="submit" class="btn" style="margin-top:0;padding:10px 20px">Search</button>
    <?php if ($search): ?>
      <a href="/admin/users.php" class="btn" style="margin-top:0;padding:10px 20px;background:#333">Clear</a>
    <?php endif; ?>
  </form>

  <div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Name</th><th>Email</th><th>Verified</th>
        <th>Plan</th><th>AI Used</th><th>Subscription</th><th>Push</th><th>Joined</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
    <?php
      $isVerified  = !empty($u['email_verified_at']);
      $hasExpiry   = !empty($u['subscription_expires_at']);
      $daysLeft    = $hasExpiry ? (int) ceil((strtotime($u['subscription_expires_at']) - time()) / 86400) : null;
      $expiryColor = $daysLeft !== null ? ($daysLeft <= 7 ? '#f87171' : ($daysLeft <= 14 ? '#fbbf24' : '#4ade80')) : '#555';
    ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td>
        <?php if ($isVerified): ?>
          <span style="color:#4ade80" title="<?= date('d M Y', strtotime($u['email_verified_at'])) ?>">✓ Verified</span>
        <?php else: ?>
          <span style="color:#f87171">✗ Pending</span>
        <?php endif; ?>
      </td>
      <td><span class="badge <?= $u['plan'] !== 'free' ? 'paid' : '' ?>"><?= $u['plan'] ?></span></td>
      <td><?= $u['ai_used'] ?> / <?= $u['ai_limit'] ?></td>
      <td>
        <?php if ($daysLeft !== null): ?>
          <span style="color:<?= $expiryColor ?>"><?= $daysLeft ?>d left</span>
        <?php else: ?>
          <span style="color:#555">—</span>
        <?php endif; ?>
      </td>
      <td style="color:#666"><?= $u['push_token'] ? '<span style="color:#4ade80">✓</span>' : '—' ?></td>
      <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
      <td>
        <div class="actions">
          <?php $csrf = htmlspecialchars(csrfToken()); ?>

          <!-- Set plan -->
          <form method="POST" style="display:flex;gap:4px;align-items:center">
            <input type="hidden" name="_csrf"    value="<?= $csrf ?>">
            <input type="hidden" name="user_id"  value="<?= $u['id'] ?>">
            <input type="hidden" name="action"   value="set_plan">
            <select name="plan" style="padding:4px 6px;background:#222;border:1px solid #333;color:#fff;border-radius:6px;font-size:12px">
              <?php foreach (['free','basic','pro'] as $p): ?>
                <option value="<?= $p ?>" <?= $u['plan'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sm btn-orange">Set</button>
          </form>

          <!-- Verify email (only if not yet verified) -->
          <?php if (!$isVerified): ?>
          <form method="POST">
            <input type="hidden" name="_csrf"   value="<?= $csrf ?>">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action"  value="verify_email">
            <button type="submit" class="btn-sm btn-green">Verify</button>
          </form>
          <?php endif; ?>

          <!-- Reset AI uses -->
          <form method="POST">
            <input type="hidden" name="_csrf"   value="<?= $csrf ?>">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action"  value="reset_ai">
            <button type="submit" class="btn-sm btn-gray">Reset AI</button>
          </form>

          <!-- Delete -->
          <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($u['name'])) ?>? This cannot be undone.')">
            <input type="hidden" name="_csrf"   value="<?= $csrf ?>">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="hidden" name="action"  value="delete">
            <button type="submit" class="btn-sm btn-red">Delete</button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</main>
</body>
</html>
