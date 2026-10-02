<?php
include_once '_cms.php';

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_name = trim($_POST['player_name'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    $soluong = (int)($_POST['soluong'] ?? 0);
    $has_option = ($_POST['option_type'] ?? '') === 'has_option';
    $option = $has_option ? (int)$_POST['option'] : 73;
    $param = $has_option ? (int)$_POST['param'] : 1;

    $st = $conn->prepare("SELECT id, items_bag FROM player WHERE name = ? LIMIT 1");
    $st->bind_param("s", $player_name); $st->execute();
    $p = $st->get_result()->fetch_assoc();
    if (!$p) {
        flash('Tên nhân vật không tồn tại.', 'danger');
    } elseif ($id <= 0 || $soluong <= 0) {
        flash('ID vật phẩm và số lượng phải lớn hơn 0.', 'danger');
    } else {
        // Ghi vào ô trống đầu tiên trong items_bag. Định dạng chuỗi giữ nguyên theo game server.
        $replacement = "[$id, $soluong,\\\"[\\\\\\\\\\\"[$option,$param]\\\\\\\\\\\"]\\\"";
        $bag = preg_replace('/\[-1,0,\\\"\[\]\\\"/', $replacement, $p['items_bag'], 1, $count);
        if ($count === 0 || empty($bag)) {
            flash('Hành trang đầy hoặc không tìm thấy ô trống.', 'danger');
        } else {
            $st = $conn->prepare("UPDATE player SET items_bag = ? WHERE id = ?");
            $st->bind_param("si", $bag, $p['id']); $st->execute();
            flash("Đã buff x$soluong vật phẩm #$id cho $player_name. Người chơi cần thoát game trước khi buff.");
        }
    }
    redirect_self();
}

$items = $conn->query("SELECT id, NAME FROM item_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$options = $conn->query("SELECT id, NAME FROM item_option_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);

cms_header('Buff vật phẩm');
?>
<div class="row"><div class="col-lg-6">
<div class="card shadow-sm"><div class="card-body">
  <form method="post" onsubmit="return pickId()"><?= csrf_field() ?>
    <div class="form-group"><label>Tên nhân vật</label><input class="form-control" name="player_name" required></div>
    <div class="form-group"><label>Vật phẩm <small class="text-muted">(gõ tên hoặc ID để tìm, <?= count($items) ?> vật phẩm)</small></label>
      <input class="form-control" list="itemlist" id="item_pick" placeholder="VD: Thỏi vàng" autocomplete="off" required>
      <input type="hidden" name="id" id="id">
      <datalist id="itemlist"><?php foreach ($items as $it): ?><option value="<?= $it['id'] ?> - <?= h($it['NAME']) ?>"><?php endforeach; ?></datalist>
    </div>
    <div class="form-group"><label>Số lượng</label><input class="form-control" name="soluong" type="number" min="1" value="1" required></div>
    <div class="form-group"><label>Chỉ số</label>
      <select class="form-control" name="option_type" onchange="document.getElementById('optionFields').style.display = this.value === 'has_option' ? '' : 'none'">
        <option value="no_option">Không chọn chỉ số</option><option value="has_option">Có chỉ số</option></select></div>
    <div id="optionFields" style="display:none">
      <div class="form-group"><label>Loại chỉ số</label>
        <select class="form-control" name="option"><?php foreach ($options as $o): ?><option value="<?= $o['id'] ?>"><?= $o['id'] ?> - <?= h($o['NAME']) ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label>Giá trị chỉ số (VD: 10 = 10%)</label><input class="form-control" name="param" type="number" value="10"></div>
    </div>
    <button class="btn btn-main">Buff</button>
  </form>
</div></div>
</div>
<div class="col-lg-6"><div class="alert alert-warning">
  <b>Lưu ý</b><br>- Người chơi phải thoát game trước khi buff, nếu không server sẽ ghi đè hành trang.<br>- Chỉ dùng chỉ số thực sự có, chọn sai gây lỗi vật phẩm.<br>- Ví dụ: Thỏi vàng là ID 457.
</div></div></div>
<script>
function pickId() {
  var v = document.getElementById('item_pick').value.trim();
  var m = v.match(/^(\d+)/);
  if (!m) { alert('Chọn vật phẩm trong danh sách (dạng "ID - Tên")'); return false; }
  document.getElementById('id').value = m[1];
  return true;
}
</script>
<?php cms_footer();
