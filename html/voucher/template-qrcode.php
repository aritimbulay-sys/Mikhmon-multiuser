<?php
// Copy Paste ke template editor [Settings -> Template Editor].

if (substr($timelimit, -1) == "d" && strlen($timelimit) > 3) {
  $timelimit = "Durasi:" . ((intval(substr($timelimit, 0, -1)) * 7) + intval(substr($timelimit, 2, 1))) . " HARI";
} else if (substr($timelimit, -1) == "d") {
  $timelimit = "Durasi:" . substr($timelimit, 0, -1) . " HARI";
} else if (substr($timelimit, -1) == "h") {
  $timelimit = "Durasi:" . substr($timelimit, 0, -1) . "Jam";
} else if (substr($timelimit, -1) == "w") {
  $timelimit = "Durasi:" . (intval(substr($timelimit, 0, -1)) * 7) . " HARI";
}

if (substr($validity, -1) == "d") {
  $validity = "Masa Aktif : " . substr($validity, 0, -1) . " HARI";
} else if (substr($validity, -1) == "h") {
  $validity = "Masa Aktif : " . substr($validity, 0, -1) . "Jam";
}

/*
Sesuaikan harga dan warna masing-masing.
Nilai warna sama dengan template standar & small.
*/
if($getprice == "3000"){ $color = "#03DAC6";}
elseif($getprice == "1000"){ $color = "#FF1493";}
elseif($getprice == "2000"){ $color = "#8B008B";}
elseif($getprice == "500000"){ $color = "#FFDE03";}
elseif($getprice == "5000"){ $color = "#FF4500";}
elseif($getprice == "10000"){ $color = "#E65100";}
elseif($getprice == "15000"){ $color = "#FF69B4";}
elseif($getprice == "25000"){ $color = "#0000FF";}
elseif($getprice == "8000"){ $color = "#FF00FF";}
elseif($getprice == "50000"){ $color = "#E60C00";}
elseif($getprice == "400000"){ $color = "#1A237E";}
elseif($getprice == "4000"){ $color = "#00BCD4";}
elseif($getprice == "6000"){ $color = "#9C27B0";}
elseif($getprice == "7000"){ $color = "#4CAF50";}
elseif($getprice == "9000"){ $color = "#FFC107";}
elseif($getprice == "55000"){ $color = "#D81B60";}
elseif($getprice == "60000"){ $color = "#8E24AA";}
elseif($getprice == "65000"){ $color = "#5E35B1";}
elseif($getprice == "70000"){ $color = "#3949AB";}
elseif($getprice == "75000"){ $color = "#1E88E5";}
elseif($getprice == "80000"){ $color = "#039BE5";}
elseif($getprice == "85000"){ $color = "#00ACC1";}
elseif($getprice == "90000"){ $color = "#00897B";}
elseif($getprice == "95000"){ $color = "#43A047";}
elseif($getprice == "100000"){ $color = "#7CB342";}
elseif($getprice == "105000"){ $color = "#C0CA33";}
elseif($getprice == "110000"){ $color = "#FDD835";}
elseif($getprice == "115000"){ $color = "#FFB300";}
elseif($getprice == "120000"){ $color = "#FB8C00";}
elseif($getprice == "125000"){ $color = "#F4511E";}
elseif($getprice == "130000"){ $color = "#6D4C41";}
elseif($getprice == "135000"){ $color = "#546E7A";}
elseif($getprice == "140000"){ $color = "#AD1457";}
elseif($getprice == "145000"){ $color = "#6A1B9A";}
elseif($getprice == "150000"){ $color = "#4527A0";}
elseif($getprice == "155000"){ $color = "#283593";}
elseif($getprice == "160000"){ $color = "#1565C0";}
elseif($getprice == "165000"){ $color = "#0277BD";}
elseif($getprice == "170000"){ $color = "#00838F";}
elseif($getprice == "175000"){ $color = "#00695C";}
elseif($getprice == "180000"){ $color = "#2E7D32";}
elseif($getprice == "185000"){ $color = "#558B2F";}
elseif($getprice == "190000"){ $color = "#9E9D24";}
elseif($getprice == "195000"){ $color = "#F9A825";}
elseif($getprice == "200000"){ $color = "#FF8F00";}
elseif($getprice == "205000"){ $color = "#EF6C00";}
elseif($getprice == "210000"){ $color = "#D84315";}
elseif($getprice == "215000"){ $color = "#4E342E";}
elseif($getprice == "220000"){ $color = "#37474F";}
elseif($getprice == "225000"){ $color = "#C2185B";}
elseif($getprice == "230000"){ $color = "#7B1FA2";}
elseif($getprice == "235000"){ $color = "#512DA8";}
elseif($getprice == "240000"){ $color = "#303F9F";}
elseif($getprice == "245000"){ $color = "#1976D2";}
elseif($getprice == "250000"){ $color = "#0288D1";}
elseif($getprice == "255000"){ $color = "#0097A7";}
elseif($getprice == "260000"){ $color = "#00796B";}
elseif($getprice == "265000"){ $color = "#388E3C";}
elseif($getprice == "270000"){ $color = "#689F38";}
elseif($getprice == "275000"){ $color = "#AFB42B";}
elseif($getprice == "280000"){ $color = "#FBC02D";}
elseif($getprice == "285000"){ $color = "#FFA000";}
elseif($getprice == "290000"){ $color = "#F57C00";}
elseif($getprice == "295000"){ $color = "#E64A19";}
elseif($getprice == "300000"){ $color = "#5D4037";}
elseif($getprice == "305000"){ $color = "#455A64";}
elseif($getprice == "310000"){ $color = "#EC407A";}
elseif($getprice == "315000"){ $color = "#AB47BC";}
elseif($getprice == "320000"){ $color = "#7E57C2";}
elseif($getprice == "325000"){ $color = "#5C6BC0";}
elseif($getprice == "330000"){ $color = "#42A5F5";}
elseif($getprice == "335000"){ $color = "#29B6F6";}
elseif($getprice == "340000"){ $color = "#26C6DA";}
elseif($getprice == "345000"){ $color = "#26A69A";}
elseif($getprice == "350000"){ $color = "#66BB6A";}
elseif($getprice == "355000"){ $color = "#9CCC65";}
elseif($getprice == "360000"){ $color = "#D4E157";}
elseif($getprice == "365000"){ $color = "#FFEE58";}
elseif($getprice == "370000"){ $color = "#FFCA28";}
elseif($getprice == "375000"){ $color = "#FFA726";}
elseif($getprice == "380000"){ $color = "#FF7043";}
elseif($getprice == "385000"){ $color = "#8D6E63";}
elseif($getprice == "390000"){ $color = "#78909C";}
elseif($getprice == "395000"){ $color = "#F06292";}
elseif($getprice == "405000"){ $color = "#BA68C8";}
elseif($getprice == "410000"){ $color = "#9575CD";}
elseif($getprice == "415000"){ $color = "#7986CB";}
elseif($getprice == "420000"){ $color = "#64B5F6";}
elseif($getprice == "425000"){ $color = "#4FC3F7";}
elseif($getprice == "430000"){ $color = "#4DD0E1";}
elseif($getprice == "435000"){ $color = "#4DB6AC";}
elseif($getprice == "440000"){ $color = "#81C784";}
elseif($getprice == "445000"){ $color = "#AED581";}
elseif($getprice == "450000"){ $color = "#DCE775";}
elseif($getprice == "455000"){ $color = "#FFF176";}
elseif($getprice == "460000"){ $color = "#FFD54F";}
elseif($getprice == "465000"){ $color = "#FFB74D";}
elseif($getprice == "470000"){ $color = "#FF8A65";}
elseif($getprice == "475000"){ $color = "#A1887F";}
elseif($getprice == "480000"){ $color = "#90A4AE";}
elseif($getprice == "485000"){ $color = "#E91E63";}
elseif($getprice == "490000"){ $color = "#9C27B0";}
elseif($getprice == "495000"){ $color = "#673AB7";}
elseif($getprice == "505000"){ $color = "#3F51B5";}
elseif($getprice == "510000"){ $color = "#2196F3";}
elseif($getprice == "515000"){ $color = "#03A9F4";}
elseif($getprice == "520000"){ $color = "#00BCD4";}
elseif($getprice == "525000"){ $color = "#009688";}
elseif($getprice == "530000"){ $color = "#4CAF50";}
elseif($getprice == "535000"){ $color = "#8BC34A";}
elseif($getprice == "540000"){ $color = "#CDDC39";}
elseif($getprice == "545000"){ $color = "#FFEB3B";}
elseif($getprice == "550000"){ $color = "#FFC107";}
elseif($getprice == "555000"){ $color = "#FF9800";}
elseif($getprice == "560000"){ $color = "#FF5722";}
elseif($getprice == "565000"){ $color = "#795548";}
elseif($getprice == "570000"){ $color = "#607D8B";}
elseif($getprice == "575000"){ $color = "#AD1457";}
elseif($getprice == "580000"){ $color = "#4A148C";}
elseif($getprice == "585000"){ $color = "#1A237E";}
elseif($getprice == "590000"){ $color = "#004D40";}
elseif($getprice == "595000"){ $color = "#BF360C";}
elseif($getprice == "600000"){ $color = "#212121";}
else{ $color = "#BA68C8";}

$vinfo = trim($timelimit . " " . $datalimit);
if ($vinfo == "") {
  $vinfo = $validity;
}
?>
<style>
.qrcode {
  width: 115px;
  height: 115px;
  display: block;
  margin: 2px auto;
}
</style>
<table class="voucher" style="width: 170px; border-collapse: collapse; border: 1px solid <?= $color; ?>; margin: 2.5px; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
  <tbody>
    <tr>
      <td style="padding: 4px 5px; border-bottom: 1px solid <?= $color; ?>; -webkit-print-color-adjust: exact;">
        <table style="width: 100%; border-collapse: collapse;">
          <tbody>
            <tr>
              <td style="text-align: left; vertical-align: middle;">
                <img src="<?= $logo; ?>" alt="logo" style="max-width: 75px; max-height: 20px; display: block;">
              </td>
              <td style="text-align: right; vertical-align: middle; font-weight: bold; font-size: 13px; font-family: Tahoma, Arial, sans-serif; color: <?= $color; ?>; white-space: nowrap;">
                <?= $price; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
    <tr>
      <td style="text-align: center; padding: 4px 5px 2px 5px;">
        <div style="font-size: 9px; font-weight: bold; color: #333333; letter-spacing: 0.4px; margin-bottom: 2px;">SCAN QR VOUCHER</div>
        <div style="background: #FFFFFF; padding: 2px; display: inline-block; border: 1px solid <?= $color; ?>;">
          <?= $qrcode ?>
        </div>
        <div style="font-size: 9px; font-weight: bold; color: #111111; margin-top: 2px; word-break: break-all;"><?= $dnsname; ?></div>
      </td>
    </tr>
    <tr>
      <td style="background-color: <?= $color; ?>; color: #FFFFFF; padding: 3px 5px; font-size: 9px; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
        <span><?= $vinfo; ?></span>
        <span id="num"><?= " [$num]"; ?></span>
      </td>
    </tr>
  </tbody>
</table>
