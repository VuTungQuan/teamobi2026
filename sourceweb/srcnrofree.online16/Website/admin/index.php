<?php
include_once '_cms.php';

$stats = [];
foreach ([
    'Tài khoản' => "SELECT COUNT(*) FROM account",
    'Nhân vật' => "SELECT COUNT(*) FROM player",
    'Bài viết' => "SELECT COUNT(*) FROM posts",
    'Bình luận' => "SELECT COUNT(*) FROM comments",
    'Tài khoản bị khóa' => "SELECT COUNT(*) FROM account WHERE ban = 1",
    'Tổng nạp (VNĐ)' => "SELECT COALESCE(SUM(tongnap),0) FROM account",
    'Thẻ nạp thành công' => "SELECT COUNT(*) FROM payments WHERE is_credited = 1",
    'Chuyển khoản thành công' => "SELECT COUNT(*) FROM bank_transfers WHERE is_credited = 1",
] as $label => $sql) {
    $stats[$label] = $conn->query($sql)->fetch_row()[0];
}
$recent_acc = $conn->query("SELECT username, create_time, ip_address FROM account ORDER BY id DESC LIMIT 8");
$recent_posts = $conn->query("SELECT id, tieude, username, created_at FROM posts ORDER BY id DESC LIMIT 8");

cms_header('Tổng quan');
?>
<div class="row">
<?php foreach ($stats as $label => $v): ?>
  <div class="col-6 col-md-3 mb-3"><div class="card shadow-sm"><div class="card-body">
    <div class="text-muted small"><?= $label ?></div>
    <div class="h4 mb-0 text-main"><?= number_format((float)$v) ?></div>
  </div></div></div>
<?php endforeach; ?>
</div>
<div class="row">
  <div class="col-md-6 mb-3"><div class="card shadow-sm"><div class="card-header bg-main">Tài khoản mới</div>
    <table class="table table-sm mb-0"><tr><th>Tài khoản</th><th>Ngày tạo</th><th>IP</th></tr>
    <?php while ($r = $recent_acc->fetch_assoc()): ?>
      <tr><td><a href="/admin/accounts?q=<?= urlencode($r['username']) ?>"><?= h($r['username']) ?></a></td><td><?= h($r['create_time']) ?></td><td><?= h($r['ip_address']) ?></td></tr>
    <?php endwhile; ?></table></div></div>
  <div class="col-md-6 mb-3"><div class="card shadow-sm"><div class="card-header bg-main">Bài viết mới</div>
    <table class="table table-sm mb-0"><tr><th>Tiêu đề</th><th>Người đăng</th><th>Ngày</th></tr>
    <?php while ($r = $recent_posts->fetch_assoc()): ?>
      <tr><td><a href="/bai-viet?id=<?= $r['id'] ?>" target="_blank"><?= h($r['tieude']) ?></a></td><td><?= h($r['username']) ?></td><td><?= h($r['created_at']) ?></td></tr>
    <?php endwhile; ?></table></div></div>
</div>
<?php cms_footer();
