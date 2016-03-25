<!DOCTYPE HTML>
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
<a href="<?php echo $this->_var['logout']; ?>">退出</a>
<div class='all_seller'>
	<table width="200" border="1">
	  <?php $_from = $this->_var['all_video']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'video');if (count($_from)):
    foreach ($_from AS $this->_var['video']):
?>
	  <tr>
		<td><a href='javascript:;'><?php echo $this->_var['video']['video_name']; ?></a></td>
		<td><a href='javascript:;'><img src='<?php echo $this->_var['video']['video_logo']; ?>' /></a></td>
		<td><a href='javascript:;' onclick='vote(<?php echo $this->_var['video']['video_id']; ?>)'>投票</a></td>
		<td><a href='javascript:;' onclick='copyUrl()'>拉票</a></td>
	  </tr>
	  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	</table>
	<table width="200" border="1">
	  <?php $_from = $this->_var['all_seller']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'seller');if (count($_from)):
    foreach ($_from AS $this->_var['seller']):
?>
	  <tr>
		<td><a href='javascript:;'><img src='<?php echo $this->_var['seller']['seller_logo']; ?>' /></a></td>
	  </tr>
	  <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
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