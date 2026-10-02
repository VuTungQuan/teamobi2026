<?php
include_once '_cms.php';

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $conn->prepare("SELECT * FROM payments WHERE name LIKE ? ORDER BY id DESC LIMIT 100");
$st->bind_param("s", $like); $st->execute();
$cards = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st = $conn->prepare("SELECT * FROM bank_transfers WHERE username LIKE ? ORDER BY id DESC LIMIT 100");
$st->bind_param("s", $like); $st->execute();
$banks = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$top = $conn->query("SELECT username, tongnap FROM account WHERE tongnap > 0 ORDER BY tongnap DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

cms_header('Nạp tiền');
?>
<form class="form-inline mb-3"><input class="form-control mr-2" name="q" value="<?= h($q) ?>" placeholder="Tìm theo tài khoản"><button class="btn btn-main">Tìm</button></form>
<div class="row">
<div class="col-lg-8">
  <div class="card shadow-sm mb-3"><div class="card-header bg-main">Nạp thẻ cào (100 gần nhất)</div>
  <div class="table-responsive"><table class="table table-sm mb-0">
  <tr><th>ID</th><th>Tài khoản</th><th>Nhà mạng</th><th>Mệnh giá</th><th>Thực nhận</th><th>Serial</th><th>Trạng thái</th><th>Cộng tiền</th><th>Ngày</th></tr>
  <?php foreach ($cards as $r): ?>
  <tr><td><?= $r['id'] ?></td><td><?= h($r['name']) ?></td><td><?= h($r['card_telco']) ?></td><td><?= number_format($r['declared_amount']) ?></td><td><?= number_format($r['detected_value']) ?></td>
    <td><small><?= h($r['card_serial']) ?></small></td><td><small><?= h($r['status_text']) ?></small></td>
    <td><?= $r['is_credited'] ? '<span class="badge badge-success">Đã cộng</span>' : '<span class="badge badge-secondary">Chưa</span>' ?></td><td><small><?= h($r['date']) ?></small></td></tr>
  <?php endforeach; ?>
  </table></div></div>
  <div class="card shadow-sm mb-3"><div class="card-header bg-main">Chuyển khoản ngân hàng (100 gần nhất)</div>
  <div class="table-responsive"><table class="table table-sm mb-0">
  <tr><th>ID</th><th>Tài khoản</th><th>Số tiền</th><th>Nội dung</th><th>Ngân hàng</th><th>Trạng thái</th><th>Cộng tiền</th><th>Ngày</th></tr>
  <?php foreach ($banks as $r): ?>
  <tr><td><?= $r['id'] ?></td><td><?= h($r['username']) ?></td><td><?= number_format($r['amount']) ?></td><td><small><?= h($r['description']) ?></small></td><td><?= h($r['sender_bank_name']) ?></td><td><?= h($r['status']) ?></td>
    <td><?= $r['is_credited'] ? '<span class="badge badge-success">Đã cộng</span>' : '<span class="badge badge-secondary">Chưa</span>' ?></td><td><small><?= h($r['created_at']) ?></small></td></tr>
  <?php endforeach; ?>
  </table></div></div>
</div>
<div class="col-lg-4">
  <div class="card shadow-sm"><div class="card-header bg-main">Top nạp</div>
  <table class="table table-sm mb-0"><?php foreach ($top as $i => $r): ?><tr><td><?= $i + 1 ?></td><td><?= h($r['username']) ?></td><td class="text-right"><?= number_format($r['tongnap']) ?></td></tr><?php endforeach; ?></table></div>
</div>
</div>
<?php cms_footer();
