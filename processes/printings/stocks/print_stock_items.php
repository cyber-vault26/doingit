<?php
session_start();
if (!isset($_SESSION['userid'])) {
    echo "<script>alert('Unauthorized'); window.close();</script>";
    exit();
}
ob_start();

require_once '../../../vendor/autoload.php';
require_once '../../../configs.php';
require_once '../../../functions/min_sub_functions.php';
require_once '../../../functions/utilities_functions.php';

use Mpdf\Mpdf;

$conn  = openConn();
$items = selectAllMinSub($conn);

$logoPath   = __DIR__ . '/../../../dist/img/logo.png';
$logoBase64 = file_exists($logoPath)
    ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
    : '';
$logoTag = $logoBase64 ? '<img src="' . $logoBase64 . '" style="height:72px;width:auto;" />' : '';

ob_end_clean();

// ── mPDF instance ──────────────────────────────
$mpdf = new Mpdf([
    'mode'          => 'utf-8',
    'format'        => 'A4',
    'margin_top'    => 8,
    'margin_bottom' => 8,
    'margin_left'   => 10,
    'margin_right'  => 10,
]);
$mpdf->SetTitle('Stock Items List');
$mpdf->SetAuthor('Mangowo Trading and Supplies');

// ── CHUNK 1: styles + header ───────────────────
$mpdf->WriteHTML('
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, sans-serif; }
  table { border-collapse:collapse; width:100%; }
</style>
<table style="width:100%;background:#162171;">
  <tr>
    <td style="padding:10px 14px;width:90px;vertical-align:middle;">' . $logoTag . '</td>
    <td style="padding:10px;text-align:center;vertical-align:middle;">
      <span style="font-size:20px;font-weight:bold;color:#ffffff;letter-spacing:1px;">MANGOWO TRADING AND SUPPLIES</span>
    </td>
  </tr>
</table>
<div style="background:#FFC107;height:5px;"></div>
<div style="background:#F5F7FF;text-align:center;padding:5px;font-size:8px;color:#444;font-style:italic;">
  Kariakoo, Narung\'ombe Na Sikukuu &nbsp;|&nbsp; P.O. Box xxx, Dar es Salaam, Tanzania &nbsp;|&nbsp; Tel: +255 785 182 121 / +255 759 266 699 / +255 655 606 676
</div>
<div style="background:#0E1650;text-align:center;padding:8px;font-size:14px;font-weight:bold;color:#FFC107;letter-spacing:2px;margin-top:2px;">
  STOCK ITEMS LIST
</div>
<div style="text-align:right;padding:3px 8px;font-size:8px;color:#888;font-style:italic;">
  Generated: ' . date('d-M-Y H:i') . '
</div>
<div style="height:6px;"></div>
<table style="width:100%;border-collapse:collapse;">
<thead>
  <tr style="background:#162171;">
    <th style="color:#fff;padding:8px 4px;text-align:center;border:1px solid #FFC107;font-size:10px;width:5%;">#</th>
    <th style="color:#fff;padding:8px 4px;text-align:center;border:1px solid #FFC107;font-size:10px;width:16%;">Image</th>
    <th style="color:#fff;padding:8px 4px;text-align:center;border:1px solid #FFC107;font-size:10px;width:14%;">Code</th>
    <th style="color:#fff;padding:8px 4px;text-align:left;border:1px solid #FFC107;font-size:10px;width:43%;">Item Name</th>
    <th style="color:#fff;padding:8px 4px;text-align:right;border:1px solid #FFC107;font-size:10px;width:16%;">Price (TZS)</th>
  </tr>
</thead>
<tbody>');

// ── CHUNK per row (avoids giant string) ────────
$num = 0;
if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $num++;
        $bg    = ($num % 2 === 0) ? '#E8ECFF' : '#FFFFFF';
        $code  = !empty($item['code']) ? htmlspecialchars($item['code']) : '—';
        $name  = htmlspecialchars($item['name']);
        $price = (isset($item['price']) && $item['price'] !== '')
                 ? number_format((float)$item['price'], 2) : '—';

        $imgHtml = '<span style="color:#aaa;font-size:8px;font-style:italic;">No Image</span>';
        $imgFile = __DIR__ . '/../../../uploads/items/' . ($item['image_url'] ?? '');
        if (!empty($item['image_url']) && file_exists($imgFile)) {
            $ext     = strtolower(pathinfo($imgFile, PATHINFO_EXTENSION));
            $mime    = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
            $b64     = base64_encode(file_get_contents($imgFile));
            $imgHtml = '<img src="data:' . $mime . ';base64,' . $b64
                     . '" style="height:58px;width:auto;max-width:76px;display:block;margin:0 auto;border-radius:3px;" />';
        }

        $mpdf->WriteHTML('
        <tr style="background:' . $bg . ';">
          <td style="text-align:center;padding:6px 4px;border:1px solid #ddd;font-size:10px;vertical-align:middle;">' . $num . '</td>
          <td style="text-align:center;padding:4px;border:1px solid #ddd;vertical-align:middle;">' . $imgHtml . '</td>
          <td style="text-align:center;padding:6px 4px;border:1px solid #ddd;font-size:10px;font-weight:bold;color:#162171;vertical-align:middle;">' . $code . '</td>
          <td style="padding:6px 8px;border:1px solid #ddd;font-size:10px;font-weight:bold;color:#1a1a2e;vertical-align:middle;">' . $name . '</td>
          <td style="text-align:right;padding:6px 8px;border:1px solid #ddd;font-size:10px;font-weight:bold;color:#0a6e0a;vertical-align:middle;">' . $price . '</td>
        </tr>');
    }
}

// ── CHUNK 3: footer ────────────────────────────
$mpdf->WriteHTML('
</tbody>
</table>
<div style="background:#FFC107;text-align:center;padding:7px;font-size:11px;font-weight:bold;color:#162171;margin-top:0;">
  TOTAL ITEMS: ' . $num . '
</div>
<div style="background:#FFC107;height:5px;margin-top:4px;"></div>
<div style="background:#162171;text-align:center;padding:8px;font-size:8px;font-weight:bold;color:#ffffff;">
  MANGOWO TRADING AND SUPPLIES &nbsp;|&nbsp; Dar es Salaam, Tanzania &nbsp;|&nbsp; Tel: +255 785 182 121 / +255 759 266 699 / +255 655 606 676
</div>');

$mpdf->Output('stock_items_' . date('Ymd_His') . '.pdf', 'D');
exit;