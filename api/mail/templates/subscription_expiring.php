<?php require_once __DIR__ . '/_base.php'; ?>
<?php
// Variables: $name (string), $plan (string), $daysLeft (int)
$appUrl = defined('APP_URL') ? APP_URL : 'https://swypply.com';

$urgencyColor = $daysLeft <= 1 ? '#DC2626' : ($daysLeft <= 3 ? '#D97706' : '#FF6B35');
$urgencyText  = $daysLeft === 1
    ? 'expires <strong>tomorrow</strong>'
    : "expires in <strong>{$daysLeft} days</strong>";

$body = '
<!-- Header -->
<div style="background:' . $urgencyColor . ';padding:36px 32px;text-align:center;">
  <div style="font-size:36px;margin-bottom:8px;">⏳</div>
  <h1 style="margin:0 0 6px;font-size:22px;font-weight:900;color:#fff;">Your subscription is expiring</h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.9);">
    ' . htmlspecialchars($plan) . ' plan · ' . $urgencyText . '
  </p>
</div>

<!-- Body -->
<div style="padding:36px 32px;">
  <p style="margin:0 0 16px;font-size:15px;color:#1a1a1a;">
    Hi <strong>' . htmlspecialchars($name) . '</strong>,
  </p>
  <p style="margin:0 0 24px;font-size:14px;color:#4b5563;line-height:1.6;">
    Your <strong>' . htmlspecialchars($plan) . ' plan</strong> ' . $urgencyText . '.
    Once it expires your account will revert to the free plan (3 AI applications/month).
  </p>

  <!-- What you\'ll lose -->
  <div style="background:#fef2f2;border-left:3px solid ' . $urgencyColor . ';border-radius:8px;padding:16px 20px;margin-bottom:28px;">
    <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:' . $urgencyColor . ';">What you\'ll lose if you don\'t renew:</p>
    <ul style="margin:0;padding-left:18px;font-size:13px;color:#374151;line-height:1.8;">
      <li>Unlimited AI tailoring → capped at 3/month</li>
      <li>Tailored CV &amp; cover letter PDF export</li>
      ' . (strtolower($plan) === 'pro' ? '<li>General CV generator</li>' : '') . '
    </ul>
  </div>

  <!-- CTA -->
  <div style="text-align:center;">
    <a href="' . htmlspecialchars($appUrl) . '"
       style="display:inline-block;background:' . $urgencyColor . ';color:#fff;font-weight:700;font-size:15px;
              text-decoration:none;padding:14px 36px;border-radius:999px;">
      Renew now →
    </a>
  </div>

  <p style="margin:24px 0 0;font-size:12px;color:#9ca3af;text-align:center;">
    Open the Swypply app and go to Profile → Subscription to renew.
  </p>
</div>';

echo baseEmail("Your Swypply {$plan} plan expires soon — renew to keep your benefits.", $body);
