<?php
require_once __DIR__ . '/auth.php';
adminAuth();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../api/lib/Mailer.php';

$result = null;
$error  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $to = trim($_POST['to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        try {
            $mailer = new Mailer();
            $mailer->send(
                $to,
                'Test Recipient',
                'Swypply SMTP Test',
                '<h2 style="font-family:sans-serif">✅ SMTP is working!</h2>
                 <p style="font-family:sans-serif">This is a test email from your Swypply admin panel.</p>
                 <p style="font-family:sans-serif;color:#888">Sent via ' . htmlspecialchars(SMTP_HOST) . ':' . SMTP_PORT . '</p>'
            );
            $result = 'Email sent to ' . htmlspecialchars($to) . ' — check your inbox.';
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Test Mail — Swypply Admin</title>
<?php require __DIR__ . '/partials/head.php'; ?>
</head>
<body>
<?php require __DIR__ . '/partials/nav.php'; ?>
<main>
  <h1>SMTP Test</h1>

  <div style="background:#1a1a1a;border:1px solid #2a2a2a;border-radius:12px;padding:24px;max-width:480px;margin-bottom:28px">
    <h3 style="margin-bottom:16px;font-size:14px;color:#aaa;text-transform:uppercase;letter-spacing:1px">Current Config</h3>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <tr><td style="color:#888;padding:4px 0;width:120px">Host</td><td><?= htmlspecialchars(SMTP_HOST) ?></td></tr>
      <tr><td style="color:#888;padding:4px 0">Port</td><td><?= htmlspecialchars(SMTP_PORT) ?></td></tr>
      <tr><td style="color:#888;padding:4px 0">User</td><td><?= htmlspecialchars(SMTP_USER) ?></td></tr>
      <tr><td style="color:#888;padding:4px 0">From</td><td><?= htmlspecialchars(SMTP_FROM_EMAIL) ?></td></tr>
      <tr><td style="color:#888;padding:4px 0">Password</td><td><?= SMTP_PASS ? '✓ Set (' . strlen(SMTP_PASS) . ' chars)' : '<span style="color:#f87171">✗ Not set</span>' ?></td></tr>
    </table>
  </div>

  <?php if ($result): ?>
    <div style="background:#14532d;border:1px solid #166534;color:#86efac;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px">
      ✅ <?= htmlspecialchars($result) ?>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div style="background:#450a0a;border:1px solid #7f1d1d;color:#fca5a5;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:13px;word-break:break-all">
      ✗ <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <form method="POST" style="max-width:480px">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
    <label style="display:block;font-size:13px;color:#aaa;margin-bottom:8px">Send test email to</label>
    <div style="display:flex;gap:10px">
      <input type="email" name="to" value="<?= htmlspecialchars($_POST['to'] ?? '') ?>"
             placeholder="your@email.com" style="flex:1">
      <button type="submit" class="btn" style="margin-top:0;padding:10px 20px">Send</button>
    </div>
  </form>
</main>
</body>
</html>
