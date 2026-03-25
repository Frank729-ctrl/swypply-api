<?php require_once __DIR__ . '/_base.php'; ?>
<?php
// Variables: $name (string), $message (string), $ctaText (string), $ctaUrl (string)

$body = '
<!-- Header -->
<div style="background:#FF6B35;padding:32px 32px 24px;text-align:center;">
  <span style="font-size:18px;font-weight:900;letter-spacing:3px;color:#fff;">SWYPPLY</span>
</div>

<!-- Body -->
<div style="padding:32px;">
  <p style="margin:0 0 16px;font-size:15px;color:#1a1a1a;">
    Hi <strong>' . htmlspecialchars($name) . '</strong>,
  </p>
  <div style="font-size:14px;color:#374151;line-height:1.7;margin-bottom:28px;">
    ' . nl2br(htmlspecialchars($message)) . '
  </div>

  <!-- CTA -->
  <div style="text-align:center;">
    <a href="' . htmlspecialchars($ctaUrl) . '"
       style="display:inline-block;background:#FF6B35;color:#fff;font-weight:700;font-size:14px;
              text-decoration:none;padding:13px 32px;border-radius:999px;">
      ' . htmlspecialchars($ctaText) . '
    </a>
  </div>
</div>';

echo baseEmail($message, $body);
