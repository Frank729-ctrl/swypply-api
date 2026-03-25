<?php require_once __DIR__ . '/_base.php'; ?>
<?php
// Variables: $name (string), $plan (string) — already ucfirst'd
$appUrl = defined('APP_URL') ? APP_URL : 'https://swypply.com';

$perks = [
    'basic' => [
        '20 AI-tailored applications per month',
        'Tailored CV + cover letter PDFs',
        'Full job feed from all sources',
        'Applied & Saved job tracking',
    ],
    'pro' => [
        'Unlimited AI-tailored applications',
        'Tailored CV + cover letter PDFs',
        'Generate a professional general CV',
        'Full job feed from all sources',
        'Priority AI processing',
    ],
];

$planKey  = strtolower($plan);
$planPerks = $perks[$planKey] ?? $perks['basic'];

$perksHtml = '';
foreach ($planPerks as $perk) {
    $perksHtml .= '
    <tr>
      <td style="padding:6px 0;">
        <table cellpadding="0" cellspacing="0"><tr>
          <td style="padding-right:10px;color:#FF6B35;font-size:15px;font-weight:700;">✓</td>
          <td style="font-size:13px;color:#374151;">' . htmlspecialchars($perk) . '</td>
        </tr></table>
      </td>
    </tr>';
}

$body = '
<!-- Header -->
<div style="background:#FF6B35;padding:40px 32px;text-align:center;">
  <div style="font-size:40px;margin-bottom:10px;">🎉</div>
  <h1 style="margin:0 0 6px;font-size:24px;font-weight:900;color:#fff;">You\'re on ' . htmlspecialchars($plan) . '!</h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.85);">Your subscription is now active</p>
</div>

<!-- Body -->
<div style="padding:36px 32px;">
  <p style="margin:0 0 20px;font-size:15px;color:#1a1a1a;">
    Hi <strong>' . htmlspecialchars($name) . '</strong>,
  </p>
  <p style="margin:0 0 24px;font-size:14px;color:#4b5563;line-height:1.6;">
    Thank you for subscribing. Here\'s what\'s unlocked for you:
  </p>

  <!-- Perks -->
  <div style="background:#fff7f3;border-radius:12px;padding:20px 24px;margin-bottom:28px;">
    <table cellpadding="0" cellspacing="0" width="100%">
      ' . $perksHtml . '
    </table>
  </div>

  <!-- CTA -->
  <div style="text-align:center;">
    <a href="' . htmlspecialchars($appUrl) . '"
       style="display:inline-block;background:#FF6B35;color:#fff;font-weight:700;font-size:15px;
              text-decoration:none;padding:14px 36px;border-radius:999px;">
      Go to Swypply →
    </a>
  </div>
</div>';

echo baseEmail("Your {$plan} plan is active — here's what you've unlocked.", $body);
