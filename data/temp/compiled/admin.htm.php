<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv='content-type' content='text/html' charset='utf8' />
<title>后台管理登陆</title>
<script type="text/javascript" src='js/jquery.js'></script>
<style type='text/css'>
.login {width:200px; margin:0 auto;}
</style>
</head>
<div class="login">
	<form id="myfrom" name="myfrom" action='<?php echo $this->_var['login_submit_url']; ?>' method='post'>
		账号：<input type='text' name='username' id='username' /> <br />
		密码：<input type='password' name='password' id='password' />
			  <input type='submit' name='submit' value='登陆' />
	</form>
</div>
</html>
<script language='javascript'>
function validlogin()
{
	var username = $.trim($('#username').val());
	var password = $.trim($('#password').val());
	
	if (username.length < 3 || username.length > 16)
	{
		alert('用户名最少3个字符');
		return false;
	}
	else if (password.length < 6 || password.length > 16)
	{
		alert('密码最少6个字符');
		return false;
	}
	return true;
}
</script>