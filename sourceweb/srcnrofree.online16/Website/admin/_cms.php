<?php
// Guard + layout dùng chung cho CMS. Mọi trang admin include file này đầu tiên.
if (session_status() == PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../connect.php';
// Thư mục source/chạy của server game (Config.properties, src/...). Đổi nếu chuyển máy.
const SERVER_DIR = 'D:/Code/teamobi2026/Teamobi2026/SRC';

$cms_user = $_SESSION['username'] ?? $_SESSION['account'] ?? null;
$cms_admin = null;
if ($cms_user) {
    $st = $conn->prepare("SELECT id, username, admin FROM account WHERE username = ? LIMIT 1");
    $st->bind_param("s", $cms_user);
    $st->execute();
    $cms_admin = $st->get_result()->fetch_assoc();
    $st->close();
}
if (!$cms_admin || (int)$cms_admin['admin'] !== 1) {
    header('Location: /app/login');
    exit;
}
// Các tool admin cũ (set.php) đọc $_SESSION["account"]; đồng bộ để chúng nhận đăng nhập.
$_SESSION["account"] = $cms_admin["username"];
$_SESSION["id"] = $cms_admin["id"];

if (empty($_SESSION['cms_csrf'])) $_SESSION['cms_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['cms_csrf'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !hash_equals($csrf, $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('CSRF token không hợp lệ');
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash($msg, $type = 'success') { $_SESSION['cms_flash'] = [$msg, $type]; }
function redirect_self() { header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . (empty($_GET['q']) ? '' : '?q=' . urlencode($_GET['q']))); exit; }

function cms_header($title) {
    global $csrf, $cms_admin;
    $nav = ['index' => 'Tổng quan', 'accounts' => 'Tài khoản', 'posts' => 'Bài viết', 'comments' => 'Bình luận', 'payments' => 'Nạp tiền', 'vatpham' => 'Buff vật phẩm', 'chiso' => 'Cộng chỉ số', 'congvnd' => 'Cộng VNĐ', 'boss' => 'Boss', 'giftcode' => 'Giftcode', 'exp' => 'Kinh nghiệm'];
    $cur = basename($_SERVER['PHP_SELF'], '.php');
    ?><!DOCTYPE html>
<html lang="vi"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title) ?> - CMS</title>
<link href="/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<link href="/assets/main.css" rel="stylesheet">
<style>body{background:#f4f5f7}.table td,.table th{vertical-align:middle}.form-inline .form-control{width:auto}</style>
</head><body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background:#924c31">
  <a class="navbar-brand font-weight-bold" href="/admin/">CMS</a>
  <button class="navbar-toggler" data-toggle="collapse" data-target="#nav"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="nav"><ul class="navbar-nav mr-auto">
  <?php foreach ($nav as $f => $label): ?>
    <li class="nav-item<?= $cur === $f ? ' active' : '' ?>"><a class="nav-link" href="/admin/<?= $f ?>"><?= $label ?></a></li>
  <?php endforeach; ?>
  </ul>
  <span class="navbar-text text-white mr-3"><i class="fa fa-user"></i> <?= h($cms_admin['username']) ?></span>
  <a class="btn btn-outline-light btn-sm" href="/forum">Về diễn đàn</a>
  </div>
</nav>
<div class="container-fluid py-4">
<h4 class="mb-3"><?= h($title) ?></h4>
<?php if (!empty($_SESSION['cms_flash'])): [$m, $t] = $_SESSION['cms_flash']; unset($_SESSION['cms_flash']); ?>
  <div class="alert alert-<?= $t ?> alert-dismissible"><?= h($m) ?><button class="close" data-dismiss="alert">&times;</button></div>
<?php endif;
}

function cms_footer() { ?>
</div>
<script src="/assets/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body></html><?php
}

function csrf_field() { global $csrf; return '<input type="hidden" name="csrf" value="' . $csrf . '">'; }
