<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv='content-type' content='text/html' charset='utf8' />
<title>登陆与注册</title>
<script type="text/javascript" src='js/jquery.js'></script>
<style type='text/css'>
.reg .login {width:200px; margin:0 auto;}
</style>
</head>
<?php if ($_GET['act'] == 'register'): ?>
<div class="reg">
	<?php if ($this->_var['submit'] == ''): ?>
	<form id='myform' name='myform' action='<?php echo $this->_var['register']; ?>' method='post'>
		用户名：<input type='text' name='username' id='username' /> <br />
		邮箱：<input type='text' name='email' id='email' /> <br />
		密码：<input type='password' name='password' id='password' />
			<input type="submit" name='submit' value='注册' />
	</form>
	<?php endif; ?>
</div>
<?php elseif ($_GET['act'] == 'login'): ?>
<div class="login">
	<?php if ($this->_var['submit'] == ''): ?>
	<form id="myfrom" name="myfrom" action='<?php echo $this->_var['login']; ?>' method='post'>
		用户名：<input type='text' name='username' id='username' /> <br />
		密码：<input type='password' name='password' id='password' />
			  <input type='submit' name='submit' value='登陆' />
	</form>
	<?php endif; ?>
</div>
<?php endif; ?>
</html>