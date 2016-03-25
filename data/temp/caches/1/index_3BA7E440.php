<?php exit;?>a:3:{s:8:"template";a:1:{i:0;s:24:"./view/default/index.htm";}s:7:"expires";i:1361774087;s:8:"maketime";i:1361770487;}<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="content-type" content="text/html" charset="utf-8" />
<title>video</title>
<script type='text/javascript' src='js/jquery.js'></script>
<style type='text/css'>
.all_seller {width:960px; margin:0 auto;}
</style>
</head>
<body>
<a href="index.php?app=userpwd&act=logout">退出</a>
<div class='all_seller'>
	<table width="200" border="1">
	  	  <tr>
		<td><a href='javascript:;'>1111111</a></td>
		<td><a href='javascript:;'><img src='./data/video_logo/4819ce97fdview.gif' /></a></td>
		<td><a href='javascript:;' onclick='vote(6)'>投票</a></td>
		<td><a href='javascript:;' onclick='copyUrl()'>拉票</a></td>
	  </tr>
	  	  <tr>
		<td><a href='javascript:;'>language1111</a></td>
		<td><a href='javascript:;'><img src='./data/video_logo/9fe46977baf81dc5572efc801f6085343f6a5e3134ershou_10.gif' /></a></td>
		<td><a href='javascript:;' onclick='vote(5)'>投票</a></td>
		<td><a href='javascript:;' onclick='copyUrl()'>拉票</a></td>
	  </tr>
	  	</table>
	<table width="200" border="1">
	  	  <tr>
		<td><a href='javascript:;'><img src='./data/seller_logo/783634452ba494d269e0141b5cd872b4290022eeeebgt_24_24.gif' /></a></td>
	  </tr>
	  	  <tr>
		<td><a href='javascript:;'><img src='./data/seller_logo/bf2d19c779f81dc5572efc801f6085343f6a5e3134ershou_10.gif' /></a></td>
	  </tr>
	  	</table>
</div>
</body>
</html>
<script language='javascript'>
function vote(vid)
{
	$.ajax({
		type : 'POST',
		url  : 'index.php?app=userpwd&act=vote',
		data : {
			vid : vid
		},
		dataType : 'text',
		cache    : false,
		success  : function(data)
		{
		  alert(data);
		}
	});
}
function copyUrl()
{
     var test = $.trim(this.location.href);
     if (!window.ActiveXObject)
	 {
         prompt('按Ctrl+C复制投票地址',test);
     }
     else
	 {
         window.clipboardData.setData("Text",test);
         alert("投票地址复制，分享给好友");
     }
 }
</script>