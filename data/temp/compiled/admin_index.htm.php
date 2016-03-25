<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv='content-type' content='text/html' charset='utf-8' />
<title>欢迎页面</title>
<style type='text/css'>
.seller_manger, .video_manger {width:960px; height:500px; margin:0 auto;}
</style>
</head>

<body>
<div class='seller_manger'><a href='<?php echo $this->_var['seller_manger_url']; ?>'>商家管理</a><br />
<a href='<?php echo $this->_var['add_seller_url']; ?>'>添加商家</a>
<?php if ($_GET['app'] == 'seller'): ?>
	<?php if ($_GET['act'] == 'seller_list'): ?>
	<div class="seller_list">
	  <table width="960" border="1" style='text-align:center;'>
		  <tr>
			<td>商家I  D</td>
			<td>商家名称</td>
			<td>商家描述</td>
			<td>商家logo</td>
			<td>注册日期</td>
			<td>其他信息</td>
		  </tr>
	  <?php $_from = $this->_var['seller_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
		  <tr>
			<td><?php echo $this->_var['list']['seller_id']; ?></td>
			<td><?php echo $this->_var['list']['seller_name']; ?></td>
			<td><?php echo $this->_var['list']['seller_desc']; ?></td>
			<td><img src='<?php echo $this->_var['list']['seller_logo']; ?>' alt='<?php echo $this->_var['list']['seller_name']; ?>' /></td>
			<td><?php echo $this->_var['list']['reg_time']; ?></td>
			<td><?php echo $this->_var['list']['ext_info']; ?></td>
			<td><a href="admin.php?app=seller&act=edit&seller_id=<?php echo $this->_var['list']['seller_id']; ?>">编辑</a></td>
			<td><a href="admin.php?app=seller&act=delete&seller_id=<?php echo $this->_var['list']['seller_id']; ?>">删除</a></td>
		  </tr>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	  </table>
	  <?php echo $this->_var['pager']; ?>
	</div>
	<?php elseif ($_GET['act'] == 'add' || $_GET['act'] == 'edit'): ?>
	<div class="seller_list">
	  <form action='<?php if ($_GET['act'] == "add"): ?><?php echo $this->_var['insert_submit_url']; ?><?php else: ?><?php echo $this->_var['update_submit_url']; ?><?php endif; ?>' method='post' enctype='multipart/form-data'>
		  <table width="960" border="1" style='text-align:center;'>
			  <tr>
				<td>商家名称：<input type='text' name='seller_name' id='seller_name' value="<?php echo $this->_var['seller_info']['seller_name']; ?>" /></td>
				<td>商家描述：<textarea name="seller_desc" id="seller_desc"><?php echo $this->_var['seller_info']['seller_desc']; ?></textarea></td>
				<td>商家logo：<input type='file' name='seller_logo' id='seller_logo' /><img src="<?php echo $this->_var['seller_info']['seller_logo']; ?>" /></td>
				<td>其他信息：<textarea name='ext_info' id='ext_info'><?php echo $this->_var['seller_info']['ext_info']; ?></textarea></td>
				<td><input type='submit' name='submit' value='提交' /><input type='hidden' name='seller_id' value='<?php echo $this->_var['seller_info']['seller_id']; ?>' /></td>
			  </tr>
		  </table>
	  </form>
	</div>
	<?php endif; ?>
<?php endif; ?>
</div>
<div class='video_manger'><a href='<?php echo $this->_var['video_manger_url']; ?>'>视频管理</a>
<br /><a href='<?php echo $this->_var['add_video_url']; ?>'>添加视频</a>
<?php if ($_GET['app'] == 'video'): ?>
	<?php if ($_GET['act'] == 'video_list'): ?>
	<div class="video_list">
	  <table width="960" border="1" style='text-align:center;'>
		  <tr>
			<td>视频I  D</td>
			<td>商家名称</td>
			<td>视频名称</td>
			<td>视频描述</td>
			<td>视频logo</td>
			<td>视频链接</td>
			<td>投票统计</td>
			<td>点击统计</td>
			<td>拉票统计</td>
			<td>添加时间</td>
			<td>其他信息</td>
		  </tr>
	  <?php $_from = $this->_var['video_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
		  <tr>
			<td><?php echo $this->_var['list']['video_id']; ?></td>
			<td><?php echo $this->_var['list']['seller_name']; ?></td>
			<td><?php echo $this->_var['list']['video_name']; ?></td>
			<td><?php echo $this->_var['list']['video_desc']; ?></td>
			<td><img src='<?php echo $this->_var['list']['video_logo']; ?>' alt='<?php echo $this->_var['list']['video_name']; ?>' /></td>
			<td><?php echo $this->_var['list']['target_url']; ?></td>
			<td><?php echo $this->_var['list']['vote_count']; ?></td>
			<td><?php echo $this->_var['list']['click_count']; ?></td>
			<td><?php echo $this->_var['list']['canvass_count']; ?></td>
			<td><?php echo $this->_var['list']['add_time']; ?></td>
			<td><?php echo $this->_var['list']['ext_info']; ?></td>
			<td><a href="admin.php?app=video&act=edit&video_id=<?php echo $this->_var['list']['video_id']; ?>">编辑</a></td>
			<td><a href="admin.php?app=video&act=delete&video_id=<?php echo $this->_var['list']['video_id']; ?>">删除</a></td>
		  </tr>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	  </table>
	  <?php echo $this->_var['pager']; ?>
	</div>
	<?php elseif ($_GET['act'] == 'add' || $_GET['act'] == 'edit'): ?>
	<div class="seller_list">
	  <form action='<?php if ($_GET['act'] == "add"): ?><?php echo $this->_var['insert_submit_url']; ?><?php else: ?><?php echo $this->_var['update_submit_url']; ?><?php endif; ?>' method='post' enctype='multipart/form-data'>
		  <table width="960" border="1" style='text-align:center;'>
			  <tr>
				<td>
					<select name='seller_id' id='seller_id'>
						<?php $_from = $this->_var['all_seller']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'seller');if (count($_from)):
    foreach ($_from AS $this->_var['seller']):
?>
							<option value='<?php echo $this->_var['seller']['seller_id']; ?>' <?php if ($this->_var['video_info']['seller_id'] == $this->_var['seller']['seller_id']): ?>selected<?php endif; ?>><?php echo $this->_var['seller']['seller_name']; ?></option>
						<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
					</select>
				</td>
				<td>视频名称：<input type='text' name='video_name' id='video_name' value="<?php echo $this->_var['video_info']['video_name']; ?>" /></td>
				<td>视频描述：<textarea name="video_desc" id="video_desc"><?php echo $this->_var['video_info']['video_desc']; ?></textarea></td>
				<td>视频logo：<input type='file' name='video_logo' id='video_logo' /><img src="<?php echo $this->_var['video_info']['video_logo']; ?>" /></td>
				<td>视频url：<input type='text' name='target_url' id='target_url' value='<?php echo $this->_var['video_info']['target_url']; ?>' /></td>
				<td>其他信息：<textarea name='ext_info' id='ext_info'><?php echo $this->_var['video_info']['ext_info']; ?></textarea></td>
				<td><input type='submit' name='submit' value='提交' /><input type='hidden' name='video_id' value='<?php echo $this->_var['video_info']['video_id']; ?>' /></td>
			  </tr>
		  </table>
	  </form>
	</div>
	<?php endif; ?>
<?php endif; ?>
</div>
</body>

</html>