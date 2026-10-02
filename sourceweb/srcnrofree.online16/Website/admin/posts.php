<?php
include_once '_cms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['do'] ?? '') {
        case 'create':
            $st = $conn->prepare("INSERT INTO posts (tieude, noidung, username, ghimbai) VALUES (?, ?, ?, ?)");
            $pin = (int)!empty($_POST['ghimbai']);
            $st->bind_param("sssi", $_POST['tieude'], $_POST['noidung'], $cms_admin['username'], $pin);
            $st->execute();
            flash('Đã đăng bài mới');
            break;
        case 'save':
            $st = $conn->prepare("UPDATE posts SET tieude = ?, noidung = ?, ghimbai = ? WHERE id = ?");
            $pin = (int)!empty($_POST['ghimbai']);
            $st->bind_param("ssii", $_POST['tieude'], $_POST['noidung'], $pin, $id);
            $st->execute();
            flash("Đã cập nhật bài #$id");
            break;
        case 'pin':
            $conn->query("UPDATE posts SET ghimbai = 1 - ghimbai WHERE id = $id");
            flash("Đã đổi trạng thái ghim bài #$id");
            break;
        case 'delete':
            $conn->query("DELETE FROM comments WHERE post_id = $id");
            $conn->query("DELETE FROM posts WHERE id = $id");
            flash("Đã xóa bài #$id", 'warning');
            break;
    }
    redirect_self();
}

$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $conn->prepare("SELECT p.*, (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS cmt FROM posts p WHERE tieude LIKE ? OR username LIKE ? ORDER BY ghimbai DESC, id DESC LIMIT 100");
$st->bind_param("ss", $like, $like); $st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

cms_header('Bài viết');
?>
<div class="d-flex mb-3">
  <form class="form-inline"><input class="form-control mr-2" name="q" value="<?= h($q) ?>" placeholder="Tìm tiêu đề / người đăng"><button class="btn btn-main">Tìm</button></form>
  <button class="btn btn-success ml-auto" data-toggle="collapse" data-target="#new"><i class="fa fa-plus"></i> Đăng bài mới</button>
</div>
<div class="collapse mb-3" id="new"><div class="card shadow-sm"><div class="card-body">
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="create">
    <input class="form-control mb-2" name="tieude" placeholder="Tiêu đề" required>
    <textarea class="form-control mb-2" name="noidung" rows="6" placeholder="Nội dung (cho phép HTML)" required></textarea>
    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="ghimbai" id="newpin"><label class="form-check-label" for="newpin">Ghim bài</label></div>
    <button class="btn btn-success">Đăng</button>
  </form></div></div></div>
<div class="table-responsive"><table class="table table-sm table-hover bg-white shadow-sm">
<tr><th>ID</th><th>Tiêu đề</th><th>Người đăng</th><th>Bình luận</th><th>Ngày</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td>
  <td><?= $r['ghimbai'] ? '<i class="fa fa-thumbtack text-danger"></i> ' : '' ?><a href="/bai-viet?id=<?= $r['id'] ?>" target="_blank"><?= h($r['tieude']) ?></a></td>
  <td><?= h($r['username']) ?></td><td><?= $r['cmt'] ?></td><td><small><?= h($r['created_at']) ?></small></td>
  <td class="text-nowrap">
    <form class="d-inline" method="post"><?= csrf_field() ?><input type="hidden" name="do" value="pin"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-secondary" title="Ghim / bỏ ghim"><i class="fa fa-thumbtack"></i></button></form>
    <button class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#e<?= $r['id'] ?>"><i class="fa fa-edit"></i></button>
    <form class="d-inline" method="post" onsubmit="return confirm('Xóa bài viết này và toàn bộ bình luận?')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></form>
  </td>
</tr>
<tr class="collapse" id="e<?= $r['id'] ?>"><td colspan="6" class="bg-light">
  <form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <input class="form-control mb-2" name="tieude" value="<?= h($r['tieude']) ?>" required>
    <textarea class="form-control mb-2" name="noidung" rows="8"><?= h($r['noidung']) ?></textarea>
    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="ghimbai" id="p<?= $r['id'] ?>"<?= $r['ghimbai']?' checked':'' ?>><label class="form-check-label" for="p<?= $r['id'] ?>">Ghim bài</label></div>
    <button class="btn btn-sm btn-success">Lưu</button>
  </form></td></tr>
<?php endforeach; ?>
</table></div>
<?php cms_footer();
