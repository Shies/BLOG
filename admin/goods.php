<?PHP require('cpanel.php');?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="keywords" content="商品列表">
<meta name="description" content="商品列表" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>管理商品列表</title>
<script type="text/javascript" src="../js/jquery.js"></script>
</head>

<body>
<div id='content'>
<?php if ($_REQUEST['act'] == 'lists') { ?>
	<div id='search'> <a href='goods.php?act=add'>添加商品</a>
		<form action='goods.php' method='get'>
			商品名称 <input type='text' name='goodsname' id='goodsname' value='<?=$goods[2]['goodsname']?>' />
			<input type='submit' name='submit' value='搜索' />
		</form>
	</div>

	<table name='lists' id='lists'>
		<tr>
			<th width='5%'>编号</th>
			<th width='15%'>商品名称</th>
			<th width='15%'>商品货号</th>
			<th width='30%'>商品库存</th>
			<th width='10%'>商品价格</th>
			<th width='15%'>是否上架</th>
			<th width='10%'>操作</th>
		</tr>
		<?php if (empty($goods[0])) { ?>
			<tr>
				<td>暂无商品信息记录 ...</td>
			</tr>
		<?php } else { ?>
			<?php foreach ($goods[0] AS $key => $value): ?>
			<tr align='center'>
				<td><?=$value['goods_id']?></td>
				<td><?=$value['goods_name']?></td>
				<td><?=$value['goods_sn']?></td>
				<td><?=$value['goods_number']?></td>
				<td><?=$value['shop_price']?></td>
				<td><?=$value['is_on_sale'] == 1 ? '是' : '否'?></td>
				<td>
					<a href='goods.php?act=edit&id=<?=$value['goods_id']?>'>编辑</a>
					<a href='goods.php?act=dele&id=<?=$value['goods_id']?>'>删除</a>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php } ?>
	</table>
	<div id='pager'><?=empty($goods[1]['pager'])?'':$goods[1]['pager']?></div>
<?php } elseif ($_REQUEST['act'] == 'add') { ?>
	<form action='goods.php?act=add' method='post'>
		商品名称 <input type='text' name='goods_name' id='goods_name' value='' />
		商品货号 <input type='text' name='goods_sn' id='goods_sn' value='' />
		商品库存 <input type='text' name='goods_number' id='goods_number' value='' />
		商品价格 <input type='text' name='shop_price' id='shop_price' value='' />
		商品简介 <textarea name='goods_desc' id='goods_desc'></textarea>
		<input type='submit' name='submit' value='提交' />
	</form>
<?php } elseif ($_REQUEST['act'] == 'edit') { ?>
	<form action='goods.php?act=edit' method='post'>
		商品名称 <input type='text' name='goods_name' id='goods_name' value='<?=$goods['goods_name']?>' />
		商品货号 <input type='text' name='goods_sn' id='goods_sn' value='<?=$goods['goods_sn']?>' />
		商品库存 <input type='text' name='goods_number' id='goods_number' value='<?=$goods['goods_number']?>' />
		商品价格 <input type='text' name='shop_price' id='shop_price' value='<?=$goods['shop_price']?>' />
		商品简介 <textarea name='goods_desc' id='goods_desc'><?=$goods['goods_desc']?></textarea>
				<input type='hidden' value='<?=$goods['goods_id']?>' name='goods_id' id='goods_id' />
		<input type='submit' name='submit' value='提交' />
	</form>
<?php } ?>
</div>
</body>
</html>