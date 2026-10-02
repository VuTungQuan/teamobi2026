<?php
include_once '_cms.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['do'] ?? '') {
        case 'create':
            $boss = trim($_POST['boss_name']);
            if (!preg_match('/^(\d+)/', trim($_POST['item_id']), $m)) { flash('Chọn vật phẩm trong danh sách.', 'danger'); break; }
            $item = (int)$m[1]; $qmin = max(1, (int)$_POST['qmin']); $qmax = max($qmin, (int)$_POST['qmax']); $ratio = max(1, min(100, (int)$_POST['ratio'])); $note = trim($_POST['note']);
            if ($boss === '') { flash('Chọn boss.', 'danger'); break; }
            $st = $conn->prepare("INSERT INTO boss_reward (boss_name, item_id, quantity_min, quantity_max, ratio, note) VALUES (?, ?, ?, ?, ?, ?)");
            $st->bind_param("siiiis", $boss, $item, $qmin, $qmax, $ratio, $note); $st->execute();
            flash("Đã thêm phần thưởng cho $boss");
            break;
        case 'delete':
            $conn->query("DELETE FROM boss_reward WHERE id = $id");
            flash("Đã xóa phần thưởng #$id", 'warning');
            break;
    }
    redirect_self();
}

// Tên boss lấy từ source server (new BossData("Tên", ...)); nếu không có source thì nhập tay.
$bosses = [];
$dir = SERVER_DIR . '/src/nro/models/boss';
if (is_dir($dir)) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $f) {
        if (substr($f, -5) === '.java' && preg_match_all('/new BossData\(\s*"([^"]+)"/', file_get_contents($f), $m)) $bosses = array_merge($bosses, $m[1]);
    }
    $bosses = array_unique($bosses); sort($bosses, SORT_LOCALE_STRING);
}

$q = trim($_GET['q'] ?? ''); $like = "%$q%";
$st = $conn->prepare("SELECT r.*, i.NAME AS item_name FROM boss_reward r LEFT JOIN item_template i ON i.id = r.item_id WHERE r.boss_name LIKE ? ORDER BY r.boss_name, r.id");
$st->bind_param("s", $like); $st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$items = $conn->query("SELECT id, NAME FROM item_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);

cms_header('Phần thưởng diệt boss');
?>
<div class="alert alert-warning py-2">Bảng này chỉ có tác dụng khi server game được build lại kèm file <code>BossRewardDB.java</code> (đã đặt sẵn trong source server). Server hiện tại vẫn dùng phần thưởng hardcode trong code Java.</div>
<div class="d-flex mb-3">
  <form class="form-inline"><input class="form-control mr-2" name="q" value="<?= h($q) ?>" placeholder="Lọc theo boss"><button class="btn btn-main">Lọc</button></form>
  <button class="btn btn-success ml-auto" data-toggle="collapse" data-target="#new"><i class="fa fa-plus"></i> Thêm phần thưởng</button>
</div>
<div class="collapse mb-3" id="new"><div class="card shadow-sm"><div class="card-body">
<form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="create">
  <div class="form-row">
    <div class="col-md-3 form-group"><label>Boss (<?= count($bosses) ?>)</label><input class="form-control" list="bosslist" name="boss_name" required autocomplete="off"><datalist id="bosslist"><?php foreach ($bosses as $b): ?><option value="<?= h($b) ?>"><?php endforeach; ?></datalist></div>
    <div class="col-md-3 form-group"><label>Vật phẩm</label><input class="form-control" list="itemlist" name="item_id" required autocomplete="off" placeholder="gõ tên hoặc ID"><datalist id="itemlist"><?php foreach ($items as $it): ?><option value="<?= $it['id'] ?> - <?= h($it['NAME']) ?>"><?php endforeach; ?></datalist></div>
    <div class="col-md-1 form-group"><label>SL từ</label><input class="form-control" name="qmin" type="number" value="1"></div>
    <div class="col-md-1 form-group"><label>SL đến</label><input class="form-control" name="qmax" type="number" value="1"></div>
    <div class="col-md-1 form-group"><label>Tỷ lệ %</label><input class="form-control" name="ratio" type="number" value="100" min="1" max="100"></div>
    <div class="col-md-3 form-group"><label>Ghi chú</label><input class="form-control" name="note"></div>
  </div>
  <button class="btn btn-success">Thêm</button>
</form></div></div></div>
<div class="table-responsive"><table class="table table-sm table-hover bg-white shadow-sm">
<tr><th>ID</th><th>Boss</th><th>Vật phẩm</th><th>Số lượng</th><th>Tỷ lệ</th><th>Ghi chú</th><th></th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= $r['id'] ?></td><td><b><?= h($r['boss_name']) ?></b></td><td>#<?= $r['item_id'] ?> <?= h($r['item_name'] ?? '(không có trong item_template)') ?></td>
  <td><?= $r['quantity_min'] == $r['quantity_max'] ? $r['quantity_min'] : $r['quantity_min'] . ' - ' . $r['quantity_max'] ?></td><td><?= $r['ratio'] ?>%</td><td><?= h($r['note']) ?></td>
  <td><form method="post" onsubmit="return confirm('Xóa?')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></form></td></tr>
<?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-center text-muted">Chưa có phần thưởng nào</td></tr><?php endif; ?>
</table></div>
<?php cms_footer();
