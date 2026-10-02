<?php
include_once '_cms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'delete') {
    $id = (int)$_POST['id'];
    $conn->query("DELETE FROM comments WHERE id = $id");
    flash("Đã xóa bình luận #$id", 'warning');
    redirect_self();
}

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $conn->prepare("SELECT c.*, p.tieude FROM comments c LEFT JOIN posts p ON p.id = c.post_id WHERE c.nguoidung LIKE ? OR c.traloi LIKE ? ORDER BY c.id DESC LIMIT 100");
$st->bind_param("ss", $like, $like); $st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

cms_header('Bình luận');
?>
<form class="form-inline mb-3"><input class="form-control mr-2" name="q" value="<?= h($q) ?>" placeholder="Tìm người dùng / nội dung"><button class="btn btn-main">Tìm</button></form>
<div class="table-responsive"><table class="table table-sm table-hover bg-white shadow-sm">
<tr><th>ID</th><th>Bài viết</th><th>Người dùng</th><th>Nội dung</th><th>Ngày</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><a href="/bai-viet?id=<?= $r['post_id'] ?>" target="_blank"><?= h($r['tieude'] ?? '(đã xóa)') ?></a></td>
  <td><?= h($r['nguoidung']) ?><?= $r['admin'] ? ' <span class="badge badge-danger">Admin</span>' : '' ?></td>
  <td style="max-width:500px"><?= h(mb_strimwidth(strip_tags($r['traloi']), 0, 200, '…')) ?></td>
  <td><small><?= h($r['created_at']) ?></small></td>
  <td><form method="post" onsubmit="return confirm('Xóa bình luận này?')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></form></td>
</tr>
<?php endforeach; ?>
</table></div>
<?php cms_footer();
