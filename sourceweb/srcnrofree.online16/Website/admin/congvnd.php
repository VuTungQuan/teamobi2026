<?php
include_once '_cms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $amount = (int)($_POST['amount'] ?? 0);
    $tongnap = !empty($_POST['tongnap']);
    if ($amount === 0) {
        flash('Số tiền phải khác 0 (số âm để trừ).', 'danger');
    } else {
        $sql = $tongnap
            ? "UPDATE account SET vnd = vnd + ?, tongnap = tongnap + ? WHERE username = ?"
            : "UPDATE account SET vnd = vnd + ? WHERE username = ?";
        $st = $conn->prepare($sql);
        $tongnap ? $st->bind_param("iis", $amount, $amount, $username) : $st->bind_param("is", $amount, $username);
        $st->execute();
        if ($st->affected_rows === 0) {
            flash("Không tìm thấy tài khoản '$username'.", 'danger');
        } else {
            $st2 = $conn->prepare("SELECT vnd FROM account WHERE username = ?");
            $st2->bind_param("s", $username); $st2->execute();
            $vnd = $st2->get_result()->fetch_row()[0];
            flash(($amount > 0 ? 'Đã cộng ' : 'Đã trừ ') . number_format(abs($amount)) . " VNĐ cho $username" . ($tongnap ? ' (tính vào tổng nạp)' : '') . '. Số dư hiện tại: ' . number_format($vnd) . ' VNĐ');
        }
    }
    redirect_self();
}

$users = $conn->query("SELECT username, vnd FROM account ORDER BY username")->fetch_all(MYSQLI_ASSOC);
$recent = $conn->query("SELECT username, vnd, tongnap, last_time_login FROM account ORDER BY update_time DESC, id DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

cms_header('Cộng VNĐ');
?>
<div class="row"><div class="col-lg-5">
<div class="card shadow-sm"><div class="card-body">
  <form method="post"><?= csrf_field() ?>
    <div class="form-group"><label>Tài khoản <small class="text-muted">(gõ để tìm)</small></label>
      <input class="form-control" list="userlist" name="username" required autocomplete="off">
      <datalist id="userlist"><?php foreach ($users as $u): ?><option value="<?= h($u['username']) ?>"><?= number_format($u['vnd']) ?> VNĐ</option><?php endforeach; ?></datalist></div>
    <div class="form-group"><label>Số tiền (VNĐ)</label><input class="form-control" name="amount" type="number" step="1000" required placeholder="VD: 50000, nhập số âm để trừ"></div>
    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="tongnap" id="tongnap"><label class="form-check-label" for="tongnap">Tính vào tổng nạp (ảnh hưởng top nạp)</label></div>
    <button class="btn btn-main">Cộng tiền</button>
  </form>
</div></div>
</div>
<div class="col-lg-7">
<div class="card shadow-sm"><div class="card-header bg-main">Tài khoản thay đổi gần đây</div>
<table class="table table-sm mb-0"><tr><th>Tài khoản</th><th class="text-right">VNĐ</th><th class="text-right">Tổng nạp</th><th>Đăng nhập cuối</th></tr>
<?php foreach ($recent as $r): ?><tr><td><a href="/admin/accounts?q=<?= urlencode($r['username']) ?>"><?= h($r['username']) ?></a></td><td class="text-right"><?= number_format($r['vnd']) ?></td><td class="text-right"><?= number_format($r['tongnap']) ?></td><td><small><?= h($r['last_time_login']) ?></small></td></tr><?php endforeach; ?>
</table></div>
</div></div>
<?php cms_footer();
