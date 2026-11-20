<?php
/*
 *  Login page for Mikhmon multi-user.
 */
if (substr($_SERVER["REQUEST_URI"], -9) == "login.php") { header("Location:./"); };
?>
<style>
  .mk-auth {
    position: fixed; inset: 0; z-index: 9999; overflow: auto; display: flex; align-items: center; justify-content: center;
    padding: 24px; box-sizing: border-box; font-family: -apple-system, "Segoe UI", Inter, Roboto, sans-serif; font-size: 14px;
    color: var(--mk-text); background:
      radial-gradient(circle at 20% 20%, rgba(34,211,238,.12), transparent 40%),
      radial-gradient(circle at 80% 80%, rgba(59,130,246,.15), transparent 45%),
      linear-gradient(var(--mk-line) 1px, transparent 1px) 0 0 / 44px 44px,
      linear-gradient(90deg, var(--mk-line) 1px, transparent 1px) 0 0 / 44px 44px,
      var(--mk-bg);
  }
  .mk-auth * { box-sizing: border-box; }
  .mk-box {
    display: flex; width: 100%; max-width: 900px; min-height: 480px;
    background: var(--mk-panel); border: 1px solid var(--mk-line); border-radius: 16px; overflow: hidden;
    box-shadow: 0 30px 60px rgba(0,0,0,.25);
  }
  .mk-side {
    flex: 1; display: flex; flex-direction: column; gap: 12px; padding: 40px 36px;
    background: linear-gradient(160deg, #0b1d3a 0%, #1e3a8a 60%, #0e7490 100%);
  }
  .mk-auth .mk-side h3 { font-size: 26px; margin: 26px 0 4px; color: #fff !important; }
  .mk-side p { font-size: 14px; color: #c7d7ee; line-height: 1.6; margin: 0; }
  .mk-side ul { padding-left: 18px; margin: 4px 0 0; color: #c7d7ee; line-height: 1.6; }
  .mk-side-foot { margin-top: auto; font-size: 13px; color: #c7d7ee; }
  .mk-main { flex: 1; display: flex; align-items: center; padding: 36px; }
  .mk-form { width: 100%; }
  .mk-brand { display: flex; align-items: center; gap: 12px; }
  .mk-brand-name { font-size: 18px; font-weight: 700; letter-spacing: .3px; color: var(--mk-text); }
  .mk-brand-name span { color: var(--mk-accent); }
  .mk-brand-sub { font-size: 12px; color: var(--mk-muted); }
  .mk-form h2 { font-size: 22px; margin: 26px 0 4px; color: var(--mk-text); }
  .mk-sub { color: var(--mk-muted); font-size: 13px; margin: 0 0 20px; }
  .mk-field { margin-bottom: 14px; }
  .mk-field label { display: block; font-size: 12px; color: var(--mk-muted); margin-bottom: 5px; }
  .mk-field input {
    width: 100%; padding: 9px 11px 9px 38px; background-color: var(--mk-bg2); color: var(--mk-text); font: inherit;
    border: 1px solid var(--mk-line); border-radius: 7px; outline: none; background-repeat: no-repeat; background-position: 12px center; background-size: 16px;
  }
  .mk-field input:focus { border-color: var(--mk-accent); box-shadow: 0 0 0 3px rgba(34,211,238,.15); }
  .mk-field input[name=user] { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%237d8db0' stroke-width='2' stroke-linecap='round'%3E%3Ccircle cx='12' cy='8' r='4'/%3E%3Cpath d='M4 21c0-4 4-6 8-6s8 2 8 6'/%3E%3C/svg%3E"); }
  .mk-field input[name=pass] { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%237d8db0' stroke-width='2' stroke-linecap='round'%3E%3Crect x='4' y='11' width='16' height='10' rx='2'/%3E%3Cpath d='M8 11V7a4 4 0 0 1 8 0v4'/%3E%3C/svg%3E"); }
  .mk-btn {
    width: 100%; padding: 11px; margin-top: 6px; border: 0; border-radius: 7px; cursor: pointer; font: inherit; font-weight: 600;
    background: linear-gradient(135deg, #3b82f6, #22d3ee); color: #04111f;
  }
  .mk-btn:hover { filter: brightness(1.08); }
  .mk-error {
    margin-bottom: 14px; padding: 10px 12px; border-radius: 8px; font-size: 13px;
    background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25); color: #dc2626;
  }
  .mk-error i { margin-right: 4px; }
  .mk-auth-theme { position: absolute; top: 16px; right: 16px; }
  .mk-auth-theme .mk-theme-cycle { width: auto !important; margin: 0 !important; padding: 7px 12px !important; font-size: 13px !important; }
  @media (max-width: 760px) {
    .mk-side { display: none; }
    .mk-main { padding: 28px 22px; }
  }
</style>
<div class="mk-auth">
  <div class="mk-auth-theme">
    <button type="button" class="mk-theme-cycle" data-mk-cycle><i class="fa fa-moon-o"></i> <span>Gelap</span></button>
  </div>
  <div class="mk-box">
    <aside class="mk-side">
      <div class="mk-brand">
        <svg viewBox="0 0 40 40" fill="none" width="34" height="34"><rect width="40" height="40" rx="10" fill="#0f1830" stroke="#1f2d4d"/><path d="M20 12 L12 26 M20 12 L28 26 M12 26 L28 26" stroke="#22d3ee" stroke-width="1.8" stroke-linecap="round"/><circle cx="20" cy="12" r="3.2" fill="#22d3ee"/><circle cx="12" cy="26" r="3.2" fill="#3b82f6"/><circle cx="28" cy="26" r="3.2" fill="#3b82f6"/></svg>
        <span class="mk-brand-name" style="color:#fff;">MIK<span style="color:#67e8f9;">HMON</span></span>
      </div>
      <h3>Selamat datang</h3>
      <p>Kelola voucher dan pantau router MikroTik hotspot Anda dari satu tempat.</p>
      <ul>
        <li>Setiap user hanya melihat router miliknya</li>
        <li>Cetak voucher dan laporan penjualan</li>
        <li>Pantau user aktif dan log hotspot</li>
      </ul>
      <div class="mk-side-foot">Hubungi admin jika belum memiliki akun.</div>
    </aside>
    <div class="mk-main">
      <form class="mk-form" autocomplete="off" method="post" action="">
        <div class="mk-brand">
          <div>
            <div class="mk-brand-name">MIK<span>HMON</span></div>
            <div class="mk-brand-sub">MikroTik Hotspot Monitor</div>
          </div>
        </div>
        <h2><?= isset($_please_login) ? $_please_login : 'Masuk ke dashboard'; ?></h2>
        <p class="mk-sub">Silakan masuk untuk melanjutkan.</p>
        <?php if (isset($error) && $error != "") { ?>
          <div class="mk-error"><?= $error; ?></div>
        <?php } ?>
        <div class="mk-field">
          <label><?= isset($_user_name) ? $_user_name : 'Username'; ?></label>
          <input type="text" name="user" placeholder="<?= isset($_user_name) ? $_user_name : 'Username'; ?>" required="1" autofocus />
        </div>
        <div class="mk-field">
          <label><?= isset($_password) ? $_password : 'Password'; ?></label>
          <input type="password" name="pass" placeholder="<?= isset($_password) ? $_password : 'Password'; ?>" required="1" />
        </div>
        <button class="mk-btn" type="submit" name="login" value="1"><?= isset($_login) ? $_login : 'Login'; ?></button>
      </form>
    </div>
  </div>
</div>
