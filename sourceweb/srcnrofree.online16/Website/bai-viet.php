<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'post_detail_logic.php';

?>
<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width,maximum-scale=1,user-scalable=no"/>
		<meta http-equiv="content-language" content="vi" />
        <title><?php echo $post_detail ? htmlspecialchars($post_detail['tieude']) : 'Bài viết không tồn tại'; ?> - Diễn Đàn</title>
		<meta name="keywords" content="Chú Bé Rồng Online - Ngọc Rồng Online - TÍNH NĂNG MỚI: ĐỆ TỬ MỚI, Chú Bé Rồng Online, Ngoc Rong Online, Ngọc Rồng Mobile, Ngoc Rong Dien Thoai, Dragon Ball Online, game ngoc rong, ngoc rong, ngoc rong online, ngoc rong mobile, game 7 vien ngoc rong, game ngọc rồng, ngọc rồng, game 7 viên ngọc rồng" />
		<meta name="description" content="Ngoc Rong Online, Ngọc Rồng Mobile, Ngoc Rong Dien Thoai, Dragon Ball Online" />
		<meta name="robots" content="INDEX,FOLLOW" />
		<link rel="apple-touch-icon" href="/images/favicon-48x48.ico" />
    <link rel="icon" href='/images/favicon-48x48.ico' type="image/x-icon" />
    <link rel="shortcut icon" href='/images/favicon-48x48.ico' type="image/x-icon" />
    <link rel="icon" href="/images/favicon-48x48.ico">
    <link rel="icon" type="image/png" href="/images/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/images/favicon-64x64.png" sizes="64x64">
    <link rel="icon" type="image/png" href="/images/favicon-128x128.png" sizes="128x128">
    <link rel="icon" type="image/png" href="/images/favicon-48x48.png" sizes="48x48">
    <script src="/view/static/js/disable_devtools.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="app/wiew/css/StyleSheet.css" />
		<link rel="stylesheet" href="app/view/css/w3.css">           
        <link rel="stylesheet" href="/view/static/css/template.css?v=1.11">        		
         <link rel="stylesheet" type="text/css" href="/view/static/css/eff.css" />
    </head>
    <body>
    <div class="snowEffect">
        <canvas id="snowcanvas" height="100%" width="100%"></canvas>
    </div>

    <div style="position: relative;" class="body_body">
        <a href="#" id="backTop"><img id='backTopimg' src='/images/favicon-32x32.png' alt='top' /> </a>

        <div class="div-12">
            <img height=12 src="/images/18-1.png" style="vertical-align: middle;" />
            <span style="vertical-align: middle;">Dành cho người chơi trên 18 tuổi. Chơi quá 180 phút mỗi ngày sẽ hại sức khỏe.
            </span>
        </div>
        <div class="left_top"></div>
        <div class="bg_top">
            <div class="right_top"></div>
        </div>
        <div class="body-content">
            <div class="bg-content2">
                <h1 class="a">
                    <a href="/" title="game bảy viên Chú Bé Rồng Online">
                        <img height=90 src="/images/logo_sk_he.png" alt="game bảy viên Chú Bé Rồng Online" /></a>
                </h1>
<div id="top">
		<div class="link-more">
		<div class="h">
		<div class="bg_tree"></div>
		<div class="bg_noel"></div>
				<div class="menu2" style="">
        <table width="100%" border="0" cellspacing="4">
			<tr class="menu">
				<td style="border: 3px solid #924C31;padding: 2px;"><a href="trang-chu">Trang Chủ</a></td>
				<td style="border: 3px solid #924C31;padding: 2px;"><a href="gioi-thieu">Giới Thiệu</a></td>
				<td id="selected" style="border: 3px solid #FFAF4D;padding: 2px;"><a href="forum">Diễn Đàn</a></td>
				<td style="border: 3px solid #924C31;padding: 2px;">
				<a href="https://www.facebook.com/share/19A5zVEZph/">Fanpage</a></td>
			</tr>
		</table>
		</div><div class="body">
<div id="box_login_ads">
	
	<div class="box_inputboxx" style="width:100%">
	<div class="box_button_login" style="width:100%;position: relative;text-align:center;"><a href="app/login.php">
		<button class="w3-button w3-red w3-small w3-hover-green">Đăng nhập</button></a>
		<a href="app/doi-mat-khau.php">
		<button class="w3-button w3-red w3-small w3-hover-green">Đổi mật khẩu</button></a></div>
		</div>
		<p>
	</p><br>	
	
	
	<div style="width:100%;float:left;">
	<table style="margin-left:auto;margin-right:auto;text-align:left;">
		<tr>
			<td>
							</td>
			
		</tr>
		
	</table>
	</div>
			
	<div style="text-align:center;    font-weight: bold;font-size:16px;">	
	</div>
</div>
<style>
	.w-40px{
		width: 40px !important;
	}
	.a-hv{
    cursor: pointer;
}
</style>
<div id="box_forums">
    <?php echo $_alert;?>
	<div class="box_list_parent">
								<div class="box_parent_list_next">
				<div class="box_phantrang">
					<div class="backlink">
						<a style="color:#fff;" href="forum">Quay lại</a>
					</div>
					
<div class="pagination">
    </div>				</div>
			</div>
			<form method="POST" name="UpdateHide">
				<div class="box_list_parent_next">
					<?php if ($post_detail): ?>
						<table cellpadding="0" cellspacing="0" width="99%" border="0" style="table-layout:fixed;word-wrap: break-word;">
							<tr>
								<td width="50px;" align="center" class="box_list_c_s">
									<img class="avatar" src="<?php echo htmlspecialchars($post_detail['author_avatar_path']); ?>" alt="<?php echo htmlspecialchars($post_detail['username']); ?>" />
									<div class="box_list_b_s" style="background-color: #FFAF4D;">
										<div class="box_list_ads">
											<div class="box_oxx_admin" style="border:none">
												<a style="font-size: 8px;text-decoration: none;" href="javascrip::viod(0)"><?php echo htmlspecialchars($post_detail['username']); ?><br><?php echo ($post_detail['author_is_admin'] == 1) ? 'Admin' : ''; ?></a>
											</div>
										</div>
									</div>
								</td>
								<td class="box_list_b_s">
									<div class="box_list_ads">
										<div class="box_oxx_admin">
											<span style="font-weight:normal;color:black;font-size:9px;"><i>
													<img style="vertical-align:middle;" title="<?php echo htmlspecialchars($post_detail['username']); ?> is offline" src="images/img/offline.png" border="0" />
														<?php echo htmlspecialchars($post_detail['created_at']); ?></i></span>
											</div>
										<div class="box_title_bviet"><?php echo htmlspecialchars($post_detail['tieude']); ?></div>
										<div class="box_ndung_bviet">
											<?php echo nl2br(htmlspecialchars($post_detail['noidung'])); ?>
											<?php if (!empty($post_detail['display_image_path'])): ?>
												<br /><center><img src='<?php echo htmlspecialchars($post_detail['display_image_path']); ?>' /></center>
											<?php endif; ?>
										</div>
										<div class="box_timee_bviet" style="padding:3px;">
											<span style="color:#333;"><span style="color:red">♥</span>
												1.000.000.000 người thích bài này.
											</span>
										</div>
									</div>
								</td>
							</tr>
						</table>
						<p><center><a href="https://cat.io.vn/forum" target="_blank"><img src="https://my.teamobi.com/images/new.gif"> Ngọc Rồng Private <img src="https://my.teamobi.com/images/new.gif"></a></center></p>
					<?php else: ?>
						<div class="box_list_parent_next" style="margin-top: 10px;">
                            <div class="box_list_c_s" style="padding: 10px; text-align: center;">
                                Bài viết không tồn tại hoặc đã bị xóa.
                            </div>
                        </div>
					<?php endif; ?>
				</div>
				<div class="box_parent_list_next" style="margin:0px;text-align:right;">
					<div class="box_phantrang">
						<div class="pagination"></div>
					</div>
				</div>
			</form>

            <?php if ($post_detail):?>
                <div class="box_list_parent_next" style="margin-top: 10px;">
                    <div class="box_list_c_s" style="padding: 10px;">
                        <div class="box_title_bviet">Bình luận</div>
                        <?php if ($_is_logged_in): ?>
                            <form method="POST" action="bai-viet.php?id=<?php echo htmlspecialchars($post_id); ?>">
                                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
                                <textarea name="comment_content" rows="4" placeholder="Nhập bình luận của bạn..." style="width: 98%; padding: 5px; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 3px;"></textarea>
                                <button type="submit" name="submit_comment" class="w3-button w3-blue w3-small w3-hover-green">Gửi bình luận</button>
                            </form>
                        <?php else: ?>
                            <p style="text-align: center;">Bạn cần <a href="app/login.php">đăng nhập</a> để bình luận.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($comments)): ?>
                <div class="box_list_parent_next" style="margin-top: 10px;">
                    <div class="box_list_c_s" style="padding: 10px;">
                        <div class="box_title_bviet">Các bình luận</div>
                        <?php foreach ($comments as $comment):
                            $comment_avatar_src = '/images/avatar/default_avatar.png';
                            if ($comment['admin'] == 0) {
                                if ($comment['gender'] == 0) {
                                    $comment_avatar_src = "/images/avatar/10.png";
                                } elseif ($comment['gender'] == 1) {
                                    $comment_avatar_src = "/images/avatar/11.png";
                                } elseif ($comment['gender'] == 2) {
                                    $comment_avatar_src = "/images/avatar/12.png";
                                } else {
                                    $comment_avatar_src = "/images/avatar/6101.gif";
                                }
                            } else {
                                if ($comment['comment_head_id'] > 0) {
                                    $comment_avatar_src = "/images/avatar/" . htmlspecialchars($comment['comment_head_id']) . ".png";
                                } else {
                                    if ($comment['gender'] == 0) {
                                        $comment_avatar_src = "/images/avatar/0.png";
                                    } elseif ($comment['gender'] == 1) {
                                        $comment_avatar_src = "/images/avatar/1.png";
                                    } elseif ($comment['gender'] == 2) {
                                        $comment_avatar_src = "/images/avatar/2.png";
                                    } else {
                                        $comment_avatar_src = "/images/avatar/default_avatar.png";
                                    }
                                }
                            }
                        ?>
                            <table cellpadding="0" cellspacing="0" width="99%" border="0" style="table-layout:fixed;word-wrap: break-word; margin-bottom: 5px; border: 1px solid #ddd; padding: 5px;">
                                <tr>
                                    <td width="50px;" align="center" class="box_list_c_s">
                                        <img class="avatar" src="<?php echo htmlspecialchars($comment_avatar_src); ?>" alt="<?php echo htmlspecialchars($comment['nguoidung']); ?>" />
                                        <div class="box_list_b_s" style="background-color: #FFAF4D;">
                                            <div class="box_list_ads">
                                                <div class="box_oxx_admin" style="border:none">
                                                    <a style="font-size: 8px;text-decoration: none;" href="javascrip::viod(0)">
                                                        <?php echo htmlspecialchars($comment['nguoidung']); ?><br>
                                                        <?php echo ($comment['admin'] == 1) ? 'Admin' : ''; ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="box_list_b_s">
                                        <div class="box_list_ads">
                                            <div class="box_oxx_admin">
                                                <span style="font-weight:normal;color:black;font-size:9px;"><i><?php echo htmlspecialchars($comment['created_at']); ?></i></span>
                                                <?php
                                                if ($logged_in_username === $comment['nguoidung'] || $is_admin):
                                                ?>
                                                    <span style="float:right;"><a href="bai-viet.php?id=<?php echo htmlspecialchars($post_id); ?>&delete_comment_id=<?php echo htmlspecialchars($comment['id']); ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này không?');" style="color: red; font-size: 9px;">Xóa</a></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="box_ndung_bviet"><?php echo nl2br(htmlspecialchars($comment['traloi'])); ?></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php elseif ($post_detail):?>
                <div class="box_list_parent_next" style="margin-top: 10px;">
                    <div class="box_list_c_s" style="padding: 10px; text-align: center;">
                        Chưa có bình luận nào cho bài viết này. Hãy là người đầu tiên!
                    </div>
                </div>
            <?php endif; ?>

	<div class="box_list_chuyenmuc">
			</div>
</div>
<div class="clearfix"></div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>

		<script src='https://forum.ngocrongonline.com/app/js/icon.js?v6' type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
</div>          
 <br></div>	
</div><br>
</div>

</div>
<style>
._51mz{
    margin: 0px auto !important;
}
			.copyright a{
				color: #000;
			}
		   </style>
<div class="left_b_bottom"><div class="right_b_bottom"><div style="height: 103px;" class="footer"><div class="left_bottom"></div><div class="right_bottom"></div></div></div></div>
 <div class="copyright" style="line-height: 7px">
 
  <p>Bản Quyền thuộc về Gomobi</p><p>
<a href="http://wap.teamobi.com/game/30" target="_blank">Avatar</a> -
 <a href="http://am.teamobi.com/" target="_blank">AVMK</a> -
 <a href="http://wap.teamobi.com/game/118" target="_blank">Mobi Army</a> -
 <a href="http://wap.teamobi.com/game/31" target="_blank">KPAH</a> -
 <a href="http://ninjaschool.vn/" target="_blank">Ninja</a> -
 <a href="http://knightageonline.com/" target="_blank">Knight</a> -
 <a href="http://haitactihon.com/" target="_blank">Hải Tặc</a></p>
 <div id="fb-root"></div>
 <script async defer crossorigin="anonymous" src="https://connect.facebook.com/vi_VN/sdk.js#xfbml=1&version=v18.0" nonce="WGx8ACST" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
 <center style="">
 <iframe name="f2190621c667f54" width="1000px" height="1000px" data-testid="fb:share_button Facebook Social Plugin" title="fb:share_button Facebook Social Plugin" frameborder="0" allowtransparency="true" allowfullscreen="true" scrolling="no" allow="encrypted-media" src="https://www.facebook.com/v18.0/plugins/share_button.php?app_id=&amp;channel=https%3A%2F%2Fstaticxx.facebook.com%2Fx%2Fconnect%2Fxd_arbiter%2F%3Fversion%3D46%23cb%3Df1b8ada27771c4%26domain%3Ddevelopers.facebook.com%26is_canvas%3Dfalse%26origin%3Dhttps%253A%252F%252Fdevelopers.facebook.com%252Ffd83f7cb4fa8b%26relation%3Dparent.parent&amp;container_width=734&amp;href=https%3A%2F%2Fwww.facebook.com%2Fngoc.rong.online.9&amp;layout=button_count&amp;locale=vi_VN&amp;sdk=joey" style="border: none; visibility: visible; width: 105px; height: 20px;" class=""></iframe>
                 </center>
</div>
</div>

<script type="d8583da729ad4da4fb7fe69d-text/javascript">
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-22738816-8', 'ngocrongonline.com');
  ga('send', 'pageview');

</script>
	<script src="/view/static/js/ThreeCanvas.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
<script src="/view/static/js/Snow3d.js" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
<script src="/view/static/js/animation.js?v4" type="d8583da729ad4da4fb7fe69d-text/javascript"></script>
<script type="d8583da729ad4da4fb7fe69d-text/javascript">

		(function($) {

			"use strict"

			$(function() {
				if ($('#backTop').length) {
					var scrollTrigger = 100, // px
						backToTop = function() {
							var scrollTop = $(window).scrollTop();
							if (scrollTop > scrollTrigger) {
								$('#backTop').addClass('show');
							} else {
								$('#backTop').removeClass('show');
							}
						};
					backToTop();
					$(window).on('scroll', function() {
						backToTop();
					});
					$('#backTop').on('click', function(e) {
						e.preventDefault();
						$('html,body').animate({
							scrollTop: 0
						}, 700);
					});
				}

			});

		})(jQuery);
	</script>

   
    <script src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="d8583da729ad4da4fb7fe69d-|49" defer></script></body>
</html>