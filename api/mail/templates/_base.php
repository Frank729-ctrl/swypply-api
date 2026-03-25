<?php
/**
 * Base email layout helper — wraps all templates in the shared shell.
 *
 * @param string $preheader  Short preview text shown in inbox before opening
 * @param string $bodyHtml   Inner HTML for the card body
 * @return string            Full HTML email string
 */
function baseEmail(string $preheader, string $bodyHtml): string {
    $year = date('Y');
    return "<!DOCTYPE html>
<html lang=\"en\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0\">
  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
  <title>Swypply</title>
  <style>
    body{margin:0;padding:0;background:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;}
    table{border-collapse:collapse;}a{color:#FF6B35;}
    @media only screen and (max-width:600px){
      .wrapper{width:100%!important;padding:0!important;}
      .card{border-radius:0!important;}
    }
  </style>
</head>
<body>
<div style=\"display:none;max-height:0;overflow:hidden;\">$preheader &nbsp;&#8204;&nbsp;&#8204;&nbsp;&#8204;</div>
<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f4f4f5;padding:32px 16px;\">
<tr><td align=\"center\">
<table class=\"wrapper\" width=\"560\" cellpadding=\"0\" cellspacing=\"0\">

  <!-- Wordmark -->
  <tr><td align=\"center\" style=\"padding-bottom:20px;\">
    <span style=\"font-size:20px;font-weight:900;letter-spacing:4px;color:#FF6B35;\">SWYPPLY</span>
  </td></tr>

  <!-- Card -->
  <tr><td class=\"card\" style=\"background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,0.07);\">
    $bodyHtml
  </td></tr>

  <!-- Footer -->
  <tr><td style=\"padding:24px 16px;text-align:center;\">
    <p style=\"margin:0 0 4px;font-size:11px;color:#9ca3af;\">You received this because you have a Swypply account.</p>
    <p style=\"margin:0;font-size:11px;color:#9ca3af;\">&copy; $year Swypply. All rights reserved.</p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>";
}
