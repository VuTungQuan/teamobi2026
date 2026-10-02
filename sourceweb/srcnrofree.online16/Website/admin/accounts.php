<?php
include_once '_cms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['do'] ?? '') {
        case 'save':
            $st = $conn->prepare("UPDATE account SET vnd = ?, admin = ?, ban = ?, active = ?, email = ? WHERE id = ?");
            $vnd = (int)$_POST['vnd']; $adm = (int)!empty($_POST['admin']); $ban = (int)!empty($_POST['ban']); $act = (int)$_POST['active']; $email = trim($_POST['email']);
            $st->bind_param("iiiisi", $vnd, $adm, $ban, $act, $email, $id);
            $st->execute();
            if (!empty($_POST['password'])) {
                $st = $conn->prepare("UPDATE account SET password = ? WHERE id = ?");
                $st->bind_param("si", $_POST['password'], $id);
                $st->execute();
            }
            flash("Đã cập nhật tài khoản #$id");
            break;
        case 'delete':
            if ($id === (int)$cms_admin['id']) { flash('Không thể xóa chính mình', 'danger'); break; }
            $conn->query("DELETE FROM player WHERE account_id = $id");
            $conn->query("DELETE FROM account WHERE id = $id");
            flash("Đã xóa tài khoản #$id", 'warning');
            break;
    }
    redirect_self();
}

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1)); $per = 30;
$like = "%$q%";
$st = $conn->prepare("SELECT COUNT(*) FROM account WHERE username LIKE ? OR email LIKE ? OR ip_address LIKE ?");
$st->bind_param("sss", $like, $like, $like); $st->execute();
$total = $st->get_result()->fetch_row()[0];
$off = ($page - 1) * $per;
$st = $conn->prepare("SELECT a.*, (SELECT GROUP_CONCAT(name) FROM player p WHERE p.account_id = a.id) AS players
    FROM account a WHERE username LIKE ? OR email LIKE ? OR ip_address LIKE ? ORDER BY a.id DESC LIMIT ?, ?");
$st->bind_param("sssii", $like, $like, $like, $off, $per); $st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

cms_header('Tài khoản');
?>
<form class="form-inline mb-3"><input class="form-control mr-2" name="q" value="<?= h($q) ?>" placeholder="Tìm tài khoản / email / IP"><button class="btn btn-main">Tìm</button>
  <span class="ml-3 text-muted"><?= number_format($total) ?> tài khoản</span></form>
<div class="table-responsive"><table class="table table-sm table-hover bg-white shadow-sm">
<tr><th>ID</th><th>Tài khoản</th><th>Nhân vật</th><th>VNĐ</th><th>Tổng nạp</th><th>Admin</th><th>Khóa</th><th>Đăng nhập cuối</th><th>IP</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= $r['id'] ?></td><td><b><?= h($r['username']) ?></b><br><small class="text-muted"><?= h($r['email']) ?></small></td>
  <td><?= h($r['players']) ?></td><td><?= number_format($r['vnd']) ?></td><td><?= number_format($r['tongnap']) ?></td>
  <td><?= $r['admin'] ? '<span class="badge badge-danger">Admin</span>' : '' ?></td>
  <td><?= $r['ban'] ? '<span class="badge badge-dark">Bị khóa</span>' : '<span class="badge badge-success">OK</span>' ?></td>
  <td><small><?= h($r['last_time_login']) ?></small></td><td><small><?= h($r['ip_address']) ?></small></td>
  <td class="text-nowrap"><button class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#e<?= $r['id'] ?>"><i class="fa fa-edit"></i></button>
    <form class="d-inline" method="post" onsubmit="return confirm('Xóa tài khoản <?= h($r['username']) ?> và toàn bộ nhân vật?')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></form></td>
</tr>
<tr class="collapse" id="e<?= $r['id'] ?>"><td colspan="10" class="bg-light">
  <form method="post" class="form-inline"><?= csrf_field() ?><input type="hidden" name="do" value="save"><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <label class="mr-1">VNĐ</label><input class="form-control form-control-sm mr-3" name="vnd" type="number" value="<?= $r['vnd'] ?>">
    <label class="mr-1">Email</label><input class="form-control form-control-sm mr-3" name="email" value="<?= h($r['email']) ?>">
    <label class="mr-1">Mật khẩu mới</label><input class="form-control form-control-sm mr-3" name="password" placeholder="(để trống nếu không đổi)">
    <label class="mr-1">Active</label><select class="form-control form-control-sm mr-3" name="active"><option value="1"<?= $r['active']==1?' selected':'' ?>>Đã kích hoạt</option><option value="0"<?= $r['active']==0?' selected':'' ?>>Chưa kích hoạt</option><option value="-1"<?= $r['active']==-1?' selected':'' ?>>Đang bị khóa</option></select>
    <div class="form-check mr-3"><input class="form-check-input" type="checkbox" name="admin" id="a<?= $r['id'] ?>"<?= $r['admin']?' checked':'' ?>><label class="form-check-label" for="a<?= $r['id'] ?>">Admin</label></div>
    <div class="form-check mr-3"><input class="form-check-input" type="checkbox" name="ban" id="b<?= $r['id'] ?>"<?= $r['ban']?' checked':'' ?>><label class="form-check-label" for="b<?= $r['id'] ?>">Khóa</label></div>
    <button class="btn btn-sm btn-success">Lưu</button>
  </form></td></tr>
<?php endforeach; ?>
</table></div>
<?php if ($total > $per): ?><nav><ul class="pagination">
<?php for ($i = 1; $i <= ceil($total / $per); $i++): ?><li class="page-item<?= $i == $page ? ' active' : '' ?>"><a class="page-link" href="?q=<?= urlencode($q) ?>&page=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?>
</ul></nav><?php endif; ?>
<?php cms_footer();
