<?php require_once __DIR__ . '/_base.php'; ?>
<?php
// Variables: $name (string)
$appUrl = defined('APP_URL') ? APP_URL : 'https://swypply.com';

$steps = [
    ['icon' => '👤', 'title' => 'Complete your profile',   'body' => 'Add your desired role, location, and work type so we show you the right jobs.'],
    ['icon' => '📄', 'title' => 'Fill in your CV details', 'body' => 'Your details power the AI tailoring. The more you add, the better the match.'],
    ['icon' => '⟶',  'title' => 'Start swiping',           'body' => 'Swipe right to apply, left to skip, up to see the full job details.'],
    ['icon' => '✦',  'title' => 'Export tailored PDFs',    'body' => 'On your Applied page, export a Claude-tailored CV and cover letter for any job.'],
];

$stepsHtml = '';
foreach ($steps as $i => $step) {
    $num = $i + 1;
    $stepsHtml .= '
    <tr>
      <td style="padding:0 0 20px;">
        <table cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="48" valign="top" style="padding-right:14px;">
              <div style="width:40px;height:40px;background:#fff7f3;border-radius:10px;
                          text-align:center;line-height:40px;font-size:18px;">'
                . $step['icon'] .
              '</div>
            </td>
            <td valign="top">
              <p style="margin:0 0 3px;font-size:14px;font-weight:700;color:#1a1a1a;">'
                . htmlspecialchars($step['title']) .
              '</p>
              <p style="margin:0;font-size:13px;color:#4b5563;line-height:1.5;">'
                . htmlspecialchars($step['body']) .
              '</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>';
}

$body = '
<!-- Header -->
<div style="background:#FF6B35;padding:40px 32px;text-align:center;">
  <h1 style="margin:0 0 6px;font-size:26px;font-weight:900;color:#fff;">Welcome aboard! 🎉</h1>
  <p style="margin:0;font-size:14px;color:rgba(255,255,255,0.85);">Your AI-powered job search starts now</p>
</div>

<!-- Body -->
<div style="padding:36px 32px;">
  <p style="margin:0 0 24px;font-size:15px;color:#1a1a1a;">
    Hi <strong>' . htmlspecialchars($name) . '</strong>, great to have you on Swypply!
  </p>

  <p style="margin:0 0 24px;font-size:14px;color:#4b5563;line-height:1.6;">
    Here\'s how to get the most out of it:
  </p>

  <table cellpadding="0" cellspacing="0" width="100%">
    ' . $stepsHtml . '
  </table>

  <!-- CTA -->
  <div style="text-align:center;margin-top:8px;">
    <a href="' . htmlspecialchars($appUrl) . '"
       style="display:inline-block;background:#FF6B35;color:#fff;font-weight:700;font-size:15px;
              text-decoration:none;padding:14px 36px;border-radius:999px;">
      Start swiping jobs →
    </a>
  </div>
</div>';

echo baseEmail("Welcome to Swypply, {$name}! Here's how to get started.", $body);
