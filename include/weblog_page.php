<?php
function wl_geo_label($geo) {
  $names = array('ID' => 'Indonesia', 'MY' => 'Malaysia', 'SG' => 'Singapura', 'US' => 'Amerika Serikat', 'GB' => 'Inggris', 'AU' => 'Australia', 'JP' => 'Jepang', 'CN' => 'Tiongkok', 'IN' => 'India', 'NL' => 'Belanda', 'DE' => 'Jerman', 'FR' => 'Prancis', 'TH' => 'Thailand', 'VN' => 'Vietnam', 'PH' => 'Filipina', 'KR' => 'Korea Selatan', 'SA' => 'Arab Saudi', 'CA' => 'Kanada', 'HK' => 'Hong Kong', 'TW' => 'Taiwan');
  $parts = explode(', ', $geo);
  $code = array_pop($parts);
  $label = isset($names[$code]) ? $names[$code] : $code;
  return implode(', ', array_merge($parts, array($label)));
}
?>
<?php
/*
 *  Halaman log aktivitas web (hanya admin).
 */
if (!isset($_SESSION["mikhmon"]) || !um_is_admin($_SESSION["mikhmon"])) {
  echo "<script>window.location='./admin.php?id=sessions'</script>";
  exit;
}
$wl_rows = wl_read(500);
$wl_aksi_label = array(
  'login' => 'Login',
  'login_gagal' => 'Login gagal',
  'logout' => 'Logout',
  'tambah_user' => 'Tambah user',
  'ubah_user' => 'Ubah user',
  'hapus_user' => 'Hapus user',
  'ganti_password' => 'Ganti password',
  'simpan_router' => 'Simpan router',
  'hapus_router' => 'Hapus router',
  'upload_logo' => 'Upload logo',
  'hapus_logo' => 'Hapus logo',
  'hapus_laporan' => 'Hapus laporan',
);
?>
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-list-alt"></i> Log Web</h3>
      </div>
      <div class="card-body">
        <div style="margin-bottom:10px;">
          <input id="wlSearch" type="text" class="form-control" placeholder="Cari user, aksi, detail, IP, atau browser" style="max-width:320px;">
          <div class="muted" style="font-size:12px;margin-top:6px;">Menampilkan <?= count($wl_rows); ?> aktivitas terbaru (maksimal 500).</div>
        </div>
        <div class="box-bordered" style="max-height:70vh;overflow:auto;">
        <table class="table table-bordered table-hover text-nowrap" id="wlTable">
          <thead class="thead-light">
            <tr>
              <th>Waktu</th>
              <th>User</th>
              <th>Aksi</th>
              <th>Detail</th>
              <th>IP &amp; Lokasi</th>
              <th>Perangkat</th>
              <th>Browser</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($wl_rows as $r) {
            $aksi = isset($wl_aksi_label[$r['aksi']]) ? $wl_aksi_label[$r['aksi']] : $r['aksi'];
          ?>
            <tr>
              <td><?= htmlspecialchars($r['t']); ?></td>
              <td><?= htmlspecialchars($r['user']); ?></td>
              <td><?= htmlspecialchars($aksi); ?></td>
              <td><?= htmlspecialchars($r['detail']); ?></td>
              <td>
                <div style="font-family:monospace;"><?= htmlspecialchars($r['ip']); ?></div>
                <div class="muted" style="font-size:12px;"><?= !empty($r['geo']) ? htmlspecialchars(wl_geo_label($r['geo'])) : '-'; ?></div>
              </td>
              <td><?= htmlspecialchars(wl_parse_ua(isset($r['ua']) ? $r['ua'] : '')['perangkat']); ?></td>
              <td><?= htmlspecialchars(wl_parse_ua(isset($r['ua']) ? $r['ua'] : '')['browser']); ?></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
  $("#wlSearch").on("keyup", function(){
    var q = $(this).val().toLowerCase();
    $("#wlTable tbody tr").each(function(){
      $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1);
    });
  });
});
</script>
