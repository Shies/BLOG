<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Rocky BLOG</title>
<script type='text/javascript' src='static/js/jquery.js'></script>
</head>
<body class="bg">

<div id="body" class="ah">
  <div class="w9">
    <div class="box"><p class="title">帐号登录</p>
		<div class="cr"></div>
		<div class="panes fms" id="fm1">
			<form action='index.php?app=passport&act=login' name='myform' method='post' onsubmit='return userLogin()'>
				  <p><span>用&nbsp;&nbsp;&nbsp;户：</span><input type="text" name='username' id='username' class="ipt9" /></p>
				  <p><span>密&nbsp;&nbsp;&nbsp;码：</span><input type="password" name='password' id='password' class="ipt9" /> <a href="index.php?app=passport&act=get_password">
				  忘记密码?</a></p>
				  <p><span>验证码：</span><input type='text' name='captcha' id='captcha' size="4" class="ipt9" /><img onclick="this.src='index.php?app=passport&act=captcha'+'&'+Math.random()" src='index.php?app=passport&act=captcha&<?php echo $this->_var['random']; ?>' alt='加载中' title='加载中' /></p>
				  <p><span>两周之内自动登录：</span><input type='checkbox' name='remember' value='1' /> <a href='index.php?app=passport&act=register'>还没有用户注册?</a></p>
				  <p><span>&nbsp;</span><input type="submit" name='submit' value="登录" class="bt1" /></p>
			</form>
		</div>
    
    </div>
  </div>
</div>

</body>
</html>
<script language='javascript'>
function userLogin()
{
  var frm      = document.forms['myform'];
  var username = $.trim(frm.elements['username'].value);
  var password = $.trim(frm.elements['password'].value);
  var captcha  = $.trim(frm.elements['captcha'].value);
  var msg = '';

  if (username.length == 0)
  {
    msg += '用户名不能为空' + '\n';
  }

  if (password.length == 0)
  {
    msg += '密码不能为空' + '\n';
  }
  
  if (captcha.length == 0) {
	msg += '验证码不能为空' + '\n';
  }

  if (msg.length > 0)
  {
    alert(msg);
    return false;
  }
  else
  {
    return true;
  }
}
</script>