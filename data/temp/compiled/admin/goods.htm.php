<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>商品列表</title>
<meta name="keywords" content="商品列表">
<meta name="description" content="商品列表" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="js/jquery.js"></script>
</head>
<body>
<div class="container">
<?php if ($_GET['act'] == 'detail'): ?>
	<div class='goodsinfo'>
		<ul>
			<?php if ($this->_var['goods'] == ''): ?>
				<li>暂无商品详情显示 ...</li>
			<?php else: ?>
				<li>商品名称：<?php echo $this->_var['goods']['goods_name']; ?></a></li>
				<li>商品货号：<?php echo $this->_var['goods']['goods_sn']; ?></li>
				<li>商品价格：<?php echo $this->_var['goods']['shop_price']; ?></li>
				<li>商品库存：<?php echo $this->_var['goods']['goods_number']; ?></li>
				<li>商品详情：<?php echo $this->_var['goods']['goods_desc']; ?></li>
			<?php endif; ?>
		</ul>
	</div>
<?php else: ?>
	<div class='goodslist'>
		<ul>
			<?php if ($this->_var['goods'] == ''): ?>
				<li>暂无商品信息显示 ...</li>
			<?php else: ?>
				<?php $_from = $this->_var['goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
					<li><a href='index.php?app=goods&act=detail&id=<?php echo $this->_var['item']['goods_id']; ?>' target='_blank'><?php echo $this->_var['item']['goods_name']; ?></a></li>
				<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
			<?php endif; ?>
		</ul>
	</div>
	<div id='pager'><?php if ($this->_var['pager'] == ''): ?><?php else: ?><?php echo $this->_var['pager']['pager']; ?><?php endif; ?></div>
<?php endif; ?>
</div>
</body>
</html>