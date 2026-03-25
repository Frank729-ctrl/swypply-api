<?php require_once __DIR__ . '/_base.php'; ?>
<?php
// Variables: $name (string), $code (string)
$digits = str_split($code);

$digitBoxes = '';
foreach ($digits as $d) {
    $digitBoxes .= '<td style="padding:0 4px;">
      <div style="width:44px;height:54px;background:#fff7f3;border:2px solid #FF6B35;border-radius:10px;
                  text-align:center;line-height:54px;font-size:26px;font-weight:800;color:#1a1a1a;">'
        . htmlspecialchars($d) .
      '</div></td>';
}

$body = '
<!-- Orange header bar -->
<div style="background:#FF6B35;padding:36px 32px;text-align:center;">
  <div style="font-size:36px;margin-bottom:8px;">✉</div>
  <h1 style="margin:0;font-size:22px;font-weight:800;color:#fff;letter-spacing:0.5px;">Verify your email</h1>
</div>

<!-- Body -->
<div style="padding:36px 32px;">
  <p style="margin:0 0 10px;font-size:16px;color:#1a1a1a;">Hi <strong>' . htmlspecialchars($name) . '</strong>,</p>
  <p style="margin:0 0 28px;font-size:14px;color:#4b5563;line-height:1.6;">
    Use the code below to verify your email address. It expires in <strong>10 minutes</strong>.
  </p>

  <!-- Code boxes -->
  <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
    <tr>' . $digitBoxes . '</tr>
  </table>

  <p style="margin:0 0 28px;font-size:13px;color:#9ca3af;text-align:center;">
    Didn\'t request this? You can safely ignore this email.
  </p>

  <hr style="border:none;border-top:1px solid #f0f0f0;margin:0 0 20px;">
  <p style="margin:0;font-size:12px;color:#9ca3af;">
    For your security, never share this code with anyone. Swypply staff will never ask for it.
  </p>
</div>';

echo baseEmail('Your Swypply verification code — expires in 10 minutes', $body);
