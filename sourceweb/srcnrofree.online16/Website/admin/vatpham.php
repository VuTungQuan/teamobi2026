<?php
include_once '_cms.php';

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_name = trim($_POST['player_name'] ?? '');
    $st = $conn->prepare("SELECT id, items_bag FROM player WHERE name = ? LIMIT 1");
    $st->bind_param("s", $player_name); $st->execute();
    $p = $st->get_result()->fetch_assoc();
    if (!$p) {
        flash('Tên nhân vật không tồn tại.', 'danger');
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $soluong = (int)($_POST['soluong'] ?? 0);
        $params = (array)($_POST['param'] ?? []);
        $opts = [];
        foreach ((array)($_POST['option'] ?? []) as $i => $o) $opts[] = '[' . (int)$o . ',' . (int)($params[$i] ?? 0) . ']';
        if (!$opts) $opts = ['[73,1]'];
        if ($id <= 0 || $soluong <= 0) {
            flash('ID vật phẩm và số lượng phải lớn hơn 0.', 'danger');
        } else {
            // Ghi vào ô trống đầu tiên trong items_bag. Định dạng chuỗi giữ nguyên theo game server.
            $q = "\\\\\\\\\\\"";
            $replacement = "[$id, $soluong,\\\"[$q" . implode("$q,$q", $opts) . "$q]\\\"";
            $bag = preg_replace('/\[-1,0,\\\"\[\]\\\"/', $replacement, $p['items_bag'], 1, $count);
            if ($count === 0 || empty($bag)) {
                flash('Hành trang đầy hoặc không tìm thấy ô trống.', 'danger');
            } else {
                $st = $conn->prepare("UPDATE player SET items_bag = ? WHERE id = ?");
                $st->bind_param("si", $bag, $p['id']); $st->execute();
                flash("Đã buff x$soluong vật phẩm #$id (" . count($opts) . " chỉ số) cho $player_name. Người chơi cần thoát game trước khi buff.");
            }
        }
    }
    redirect_self();
}

$items = $conn->query("SELECT id, NAME, TYPE FROM item_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);
// Tên loại theo cột TYPE của item_template (đặt theo tên vật phẩm trong DB).
$typeNames = [0 => 'Áo', 1 => 'Quần', 2 => 'Găng', 3 => 'Giày', 4 => 'Rada', 5 => 'Avatar / vật phẩm thường', 6 => 'Đậu thần', 7 => 'Sách kỹ năng', 8 => 'Đồ ăn / nhiệm vụ', 9 => 'Vàng', 10 => 'Ngọc', 11 => 'Ngọc rồng Namek', 12 => 'Ngọc rồng', 13 => 'Bùa', 14 => 'Đá cường hóa', 17 => 'Đai lưng', 22 => 'Vệ tinh', 23 => 'Thú cưỡi', 24 => 'Thú cưỡi VIP', 25 => 'Sách tuyệt kỹ', 27 => 'Đồ ăn / capsule', 28 => 'Cờ', 29 => 'Thuốc / buff', 30 => 'Sao pha lê', 31 => 'Trung thu', 32 => 'Giáp tập luyện', 33 => 'Mảnh thú', 36 => 'Danh hiệu', 37 => 'Sách kỹ năng 2', 75 => 'Vật phẩm đặc biệt'];
$types = array_unique(array_column($items, 'TYPE')); sort($types);
$options = $conn->query("SELECT id, NAME FROM item_option_template WHERE NAME <> '' ORDER BY id")->fetch_all(MYSQLI_ASSOC);

// Các chỉ số tiềm năng / sức mạnh có tham số, ghim lên đầu danh sách cho dễ chọn.
$tnsm = array_filter($options, fn($o) => strpos($o['NAME'], '#') !== false && preg_match('/tiềm năng|\bTN\b/iu', $o['NAME']));

cms_header('Buff vật phẩm');
?>
<div class="row"><div class="col-lg-6">
<div class="card shadow-sm"><div class="card-body">
  <form method="post" onsubmit="return pickId()"><?= csrf_field() ?>
    <div class="form-group"><label>Tên nhân vật</label><input class="form-control" name="player_name" required></div>
    <div class="form-group"><label>Vật phẩm <small class="text-muted">(gõ tên hoặc ID để tìm, <?= count($items) ?> vật phẩm)</small></label>
      <select class="form-control mb-2" id="item_type" onchange="fillItems()"><option value="">Tất cả loại</option><?php foreach ($types as $t): ?><option value="<?= $t ?>"><?= $t ?> - <?= h($typeNames[$t] ?? 'Loại ' . $t) ?></option><?php endforeach; ?></select>
      <input class="form-control" list="itemlist" id="item_pick" placeholder="VD: Thỏi vàng" autocomplete="off" required>
      <input type="hidden" name="id" id="id">
      <datalist id="itemlist"></datalist>
    </div>
    <div class="form-group"><label>Số lượng</label><input class="form-control" name="soluong" type="number" min="1" value="1" required></div>
    <div class="form-group"><label>Chỉ số <small class="text-muted">(không thêm dòng nào = không chỉ số)</small></label>
      <div id="optRows"></div>
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addOpt()">+ Thêm chỉ số</button>
    </div>
    <template id="optTpl"><div class="form-row mb-2">
      <div class="col-7"><select class="form-control" name="option[]">
        <optgroup label="Tiềm năng, sức mạnh"><?php foreach ($tnsm as $o): ?><option value="<?= $o['id'] ?>"><?= $o['id'] ?> - <?= h($o['NAME']) ?></option><?php endforeach; ?></optgroup>
        <optgroup label="Tất cả chỉ số"><?php foreach ($options as $o): ?><option value="<?= $o['id'] ?>"><?= $o['id'] ?> - <?= h($o['NAME']) ?></option><?php endforeach; ?></optgroup></select></div>
      <div class="col-4"><input class="form-control" name="param[]" type="number" value="10" title="Giá trị chỉ số (VD: 10 = 10%)" required></div>
      <div class="col-1"><button type="button" class="btn btn-outline-danger" onclick="this.closest('.form-row').remove()">&times;</button></div>
    </div></template>
    <button class="btn btn-main">Buff</button>
  </form>
</div></div>
</div>
<div class="col-lg-6"><div class="alert alert-warning">
  <b>Lưu ý</b><br>- Người chơi phải thoát game trước khi buff, nếu không server sẽ ghi đè hành trang.<br>- Chỉ dùng chỉ số thực sự có, chọn sai gây lỗi vật phẩm.<br>- Ví dụ: Thỏi vàng là ID 457.
</div></div></div>
<script>
var ITEMS = <?= json_encode(array_map(fn($i) => [(int)$i['id'], $i['NAME'], (int)$i['TYPE']], $items), JSON_UNESCAPED_UNICODE) ?>;
function fillItems() {
  var t = document.getElementById('item_type').value, dl = document.getElementById('itemlist'), html = '';
  ITEMS.forEach(function (i) { if (t === '' || i[2] == t) html += '<option value="' + i[0] + ' - ' + i[1].replace(/"/g, '&quot;') + '">'; });
  dl.innerHTML = html;
}
fillItems();
function addOpt() { document.getElementById('optRows').appendChild(document.getElementById('optTpl').content.cloneNode(true)); }
function pickId() {
  var v = document.getElementById('item_pick').value.trim();
  var m = v.match(/^(\d+)/);
  if (!m) { alert('Chọn vật phẩm trong danh sách (dạng "ID - Tên")'); return false; }
  document.getElementById('id').value = m[1];
  return true;
}
</script>
<?php cms_footer();
