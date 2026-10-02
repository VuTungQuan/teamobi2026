<?php
include_once '_cms.php';

$cfg = SERVER_DIR . '/Config.properties';
$exists = is_file($cfg);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $exists) {
    $rate = max(1, min(127, (int)$_POST['expserver'])); // server đọc bằng Byte.parseByte
    $txt = file_get_contents($cfg);
    $new = preg_replace('/^server\.expserver=.*$/m', "server.expserver=$rate", $txt, 1, $n);
    if ($n === 0) $new = rtrim($txt) . "\nserver.expserver=$rate\n";
    file_put_contents($cfg, $new) === false
        ? flash('Không ghi được Config.properties', 'danger')
        : flash("Đã đặt kinh nghiệm server = x$rate. Khởi động lại server game để áp dụng.");
    redirect_self();
}

$props = [];
if ($exists) foreach (file($cfg, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
    if ($l[0] !== '#' && strpos($l, '=') !== false) { [$k, $v] = explode('=', $l, 2); $props[trim($k)] = trim($v); }
}
$running = @fsockopen('127.0.0.1', (int)($props['server.port'] ?? 14445), $e, $s, 1);
if ($running) fclose($running);

cms_header('Kinh nghiệm server');
?>
<div class="row"><div class="col-lg-5">
<div class="card shadow-sm"><div class="card-body">
<?php if (!$exists): ?>
  <div class="alert alert-danger">Không tìm thấy <code><?= h($cfg) ?></code>. Sửa hằng SERVER_DIR trong admin/_cms.php.</div>
<?php else: ?>
  <form method="post"><?= csrf_field() ?>
    <div class="form-group"><label>Tỷ lệ kinh nghiệm (server.expserver)</label>
      <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">x</span></div>
      <input class="form-control" name="expserver" type="number" min="1" max="127" value="<?= (int)($props['server.expserver'] ?? 1) ?>" required></div>
      <small class="text-muted">Nhân với tiềm năng nhận được khi đánh quái. x1 = gốc, x3 = gấp 3.</small></div>
    <button class="btn btn-main">Lưu</button>
  </form>
<?php endif; ?>
</div></div>
</div>
<div class="col-lg-7">
<div class="card shadow-sm"><div class="card-header bg-main">Trạng thái server game</div><div class="card-body">
  <p>Cổng <?= h($props['server.port'] ?? '?') ?>: <?= $running ? '<span class="badge badge-success">Đang chạy</span>' : '<span class="badge badge-danger">Không chạy</span>' ?></p>
  <table class="table table-sm mb-3"><?php foreach (['server.name', 'server.ip', 'server.port', 'server.maxplayer', 'server.maxperip', 'server.waitlogin', 'server.expserver'] as $k): ?>
    <tr><td><code><?= $k ?></code></td><td><?= h($props[$k] ?? '') ?></td></tr><?php endforeach; ?></table>
  <div class="alert alert-warning mb-0">Server chỉ đọc file cấu hình lúc khởi động. Sau khi lưu, tắt cửa sổ server và chạy lại <code>run.bat</code> trong <code><?= h(SERVER_DIR) ?></code>.</div>
</div></div>
</div></div>
<?php cms_footer();
