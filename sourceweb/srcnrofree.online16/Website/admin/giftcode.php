<?php
include_once '_cms.php';

// Gom các dòng phần thưởng từ form thành JSON đúng định dạng server đọc.
function parse_rewards() {
    $detail = [];
    foreach ($_POST['item_id'] ?? [] as $i => $raw) {
        if (!preg_match('/^(-?\d+)/', trim($raw), $m)) continue;
        $row = ['id' => (int)$m[1], 'quantity' => (int)$_POST['qty'][$i], 'options' => []];
        if ($row['quantity'] <= 0) continue;
        if ($row['id'] > 0 && $_POST['opt'][$i] !== '') $row['options'][] = ['id' => (int)$_POST['opt'][$i], 'param' => (int)$_POST['param'][$i]];
        $detail[] = $row;
    }
    return $detail;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $do = $_POST['do'] ?? '';
    if ($do === 'create' || $do === 'save') {
        $code = trim($_POST['code']);
        $count = (int)$_POST['count_left'];
        $expired = str_replace('T', ' ', $_POST['expired'] ?: '2030-01-01 00:00');
        $detail = parse_rewards();
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $code)) { flash('Code chỉ gồm chữ, số, gạch dưới.', 'danger'); redirect_self(); }
        if (!$detail) { flash('Cần ít nhất 1 phần thưởng.', 'danger'); redirect_self(); }
        $json = json_encode($detail, JSON_UNESCAPED_UNICODE);
        if ($do === 'create') {
            $st = $conn->prepare("INSERT INTO giftcode (code, count_left, detail, datecreate, expired) VALUES (?, ?, ?, NOW(), ?)");
            $st->bind_param("siss", $code, $count, $json, $expired);
        } else {
            $st = $conn->prepare("UPDATE giftcode SET code = ?, count_left = ?, detail = ?, expired = ? WHERE id = ?");
            $st->bind_param("sissi", $code, $count, $json, $expired, $id);
        }
        $st->execute()
            ? flash(($do === 'create' ? 'Đã tạo' : 'Đã cập nhật') . " code $code. Server game cần khởi động lại để nhận thay đổi.")
            : flash('Lỗi: ' . $conn->error, 'danger');
    } elseif ($do === 'delete') {
        $conn->query("DELETE FROM giftcode WHERE id = $id");
        flash("Đã xóa code #$id", 'warning');
    }
    redirect_self();
}

$rows = $conn->query("SELECT * FROM giftcode ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$items = $conn->query("SELECT id, NAME FROM item_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$options = $conn->query("SELECT id, NAME FROM item_option_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$names = ['-1' => 'Vàng', '-2' => 'Ngọc', '-3' => 'Ngọc khóa'];
foreach ($items as $it) $names[$it['id']] = $it['NAME'];

// Form tạo/sửa dùng chung. $g = null khi tạo mới.
function code_form($g, $options, $names) {
    $detail = $g ? (json_decode($g['detail'], true) ?: []) : [['id' => '', 'quantity' => 1, 'options' => []]];
    ?>
<form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="<?= $g ? 'save' : 'create' ?>"><input type="hidden" name="id" value="<?= $g['id'] ?? 0 ?>">
  <div class="form-row">
    <div class="col-md-4 form-group"><label>Code</label><input class="form-control" name="code" required value="<?= h($g['code'] ?? '') ?>" placeholder="VD: tanthu2026"></div>
    <div class="col-md-4 form-group"><label>Số lượt còn lại (-1 = vô hạn)</label><input class="form-control" name="count_left" type="number" value="<?= $g['count_left'] ?? 100 ?>" required></div>
    <div class="col-md-4 form-group"><label>Hết hạn</label><input class="form-control" name="expired" type="datetime-local" value="<?= $g ? date('Y-m-d\TH:i', strtotime($g['expired'])) : '2030-01-01T00:00' ?>"></div>
  </div>
  <label>Phần thưởng</label>
  <table class="table table-sm rewards"><thead><tr><th style="width:45%">Vật phẩm (gõ tên/ID; -1 Vàng, -2 Ngọc, -3 Ngọc khóa)</th><th>Số lượng</th><th>Chỉ số</th><th>Giá trị</th><th></th></tr></thead>
  <tbody><?php foreach ($detail as $d): $o = $d['options'][0] ?? null; ?><tr>
    <td><input class="form-control form-control-sm" list="itemlist" name="item_id[]" autocomplete="off" value="<?= $d['id'] === '' ? '' : $d['id'] . ' - ' . h($names[$d['id']] ?? '') ?>"></td>
    <td><input class="form-control form-control-sm" name="qty[]" type="number" value="<?= (int)$d['quantity'] ?>"></td>
    <td><select class="form-control form-control-sm" name="opt[]"><option value="">Không</option><?php foreach ($options as $op): ?><option value="<?= $op['id'] ?>"<?= $o && $o['id'] == $op['id'] ? ' selected' : '' ?>><?= $op['id'] ?> - <?= h($op['NAME']) ?></option><?php endforeach; ?></select></td>
    <td><input class="form-control form-control-sm" name="param[]" type="number" value="<?= (int)($o['param'] ?? 0) ?>"></td>
    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">&times;</button></td>
  </tr><?php endforeach; ?></tbody></table>
  <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addRow(this)"><i class="fa fa-plus"></i> Thêm dòng</button><br>
  <button class="btn btn-success"><?= $g ? 'Lưu' : 'Tạo code' ?></button>
</form>
<?php }

cms_header('Quản lý code');
?>
<div class="alert alert-info py-2">Server game chỉ đọc bảng giftcode lúc khởi động. Tạo, sửa hoặc xóa code xong cần khởi động lại server. Số lượt = -1 nghĩa là không giới hạn.</div>
<button class="btn btn-success mb-3" data-toggle="collapse" data-target="#new"><i class="fa fa-plus"></i> Tạo code mới</button>
<div class="collapse mb-3" id="new"><div class="card shadow-sm"><div class="card-body"><?php code_form(null, $options, $names); ?></div></div></div>
<datalist id="itemlist"><option value="-1 - Vàng"><option value="-2 - Ngọc"><option value="-3 - Ngọc khóa"><?php foreach ($items as $it): ?><option value="<?= $it['id'] ?> - <?= h($it['NAME']) ?>"><?php endforeach; ?></datalist>

<div class="table-responsive"><table class="table table-sm table-hover bg-white shadow-sm">
<tr><th>ID</th><th>Code</th><th>Còn lại</th><th>Phần thưởng</th><th>Tạo</th><th>Hết hạn</th><th></th></tr>
<?php foreach ($rows as $r): $d = json_decode($r['detail'], true) ?: []; ?>
<tr>
  <td><?= $r['id'] ?></td><td><b><?= h($r['code']) ?></b></td>
  <td><?= $r['count_left'] == -1 ? '∞' : number_format($r['count_left']) ?></td>
  <td><small><?php foreach ($d as $x): ?><?= h($names[$x['id']] ?? ('#' . $x['id'])) ?> x<?= (int)$x['quantity'] ?><?= !empty($x['options']) ? ' (opt ' . implode(',', array_map(fn($o) => $o['id'] . ':' . $o['param'], $x['options'])) . ')' : '' ?><br><?php endforeach; ?></small></td>
  <td><small><?= h($r['datecreate']) ?></small></td>
  <td><small class="<?= strtotime($r['expired']) < time() ? 'text-danger' : '' ?>"><?= h($r['expired']) ?></small></td>
  <td class="text-nowrap"><button class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#e<?= $r['id'] ?>"><i class="fa fa-edit"></i></button>
    <form class="d-inline" method="post" onsubmit="return confirm('Xóa code <?= h($r['code']) ?>?')"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></form></td>
</tr>
<tr class="collapse" id="e<?= $r['id'] ?>"><td colspan="7" class="bg-light"><?php code_form($r, $options, $names); ?></td></tr>
<?php endforeach; ?>
</table></div>
<script>
function addRow(btn) {
  var tb = btn.closest('form').querySelector('.rewards tbody');
  var tr = tb.rows.length ? tb.rows[0].cloneNode(true) : null;
  if (!tr) { location.reload(); return; }
  tr.querySelectorAll('input').forEach(i => i.value = i.name === 'qty[]' ? 1 : (i.type === 'number' ? 0 : ''));
  tr.querySelector('select').value = '';
  tb.appendChild(tr);
}
</script>
<?php cms_footer();
