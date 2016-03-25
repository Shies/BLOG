<?PHP
# 统一管理平台管理员验证类 #
class Cookie {
	
	// 个人web的domain
	private $domain = '.myhome.cn';
	// 加密截取偏移量
	private	$encryoffset = 0;
	// 加密截取长度值
	private $encrylength = 6;
	// 存放客服端cookie的值
	private $cookval;
	// 登录用户名
	private $username;
	// 登录用户ID
	private $user_id;
	
	function __construct() {
		if (!is_integer(strpos($_SERVER['HTTP_HOST'], 'myhome.cn'))) {
			$this->domain = '';
		}
		
		if (!in_array($_SERVER['HTTP_HOST'], array('www.myhome.cn', 'test.myhome.cn'))) {
			$this->encryoffset = 3;
			$this->encrylength = 6;
		}
	}
	
	function clear() {
		@setcookie('USERKEY', null, -86400, '/', $this->domain);
	}
	
	function setvalue($user_id, $username, $lifetime = 86400) {
		$this->user_id = $user_id;
		$this->username = $username;
		
		$parts = $user_id.'|'.$username;
		$encry = substr(md5($user_id), $this->encryoffset, $this->encrylength);
		$this->cookval = $this->encrypt($parts, $encry).$encry;
		@setcookie('USERKEY', $this->cookval, TIMESTAMP + $lifetime, '/', $this->domain);
	}
	
	function get_userid() {
		if ($this->user_id <= 0) {
			$this->get_userkey();
		}
		return $this->user_id;
	}
	
	function get_username() {
		if ($this->user_id <= 0) {
			$this->get_userkey();
		}
		return $this->username;
	}
	
	function get_userkey() {
		$this->user_id = 0;
		$this->username = '';
		$this->cookval = isset($_COOKIE['USERKEY']) ? $_COOKIE['USERKEY'] : '';
		
		if ($this->cookval == '') {
			return;
		}
		
		$encry = substr($this->cookval, -$this->encrylength);
		$parts = $this->decrypt(substr($this->cookval, 0, -$this->encrylength), $encry);
		$value = explode('|', $parts);
		
		if (!is_array($value) || sizeof($value) != 2) {
			$this->user_id = 0;
			$this->username = '';
			return;
		}
		
		if (strtolower($encry) != strtolower(substr(md5(reset($value)), $this->encryoffset, $this->encrylength))) {
			$this->user_id = 0;
			$this->username = '';
			return;
		}
		
		list($this->user_id, 
			$this->username) = $value;
	}
	
	function check() {
		$this->get_userkey();
		return $this->user_id > 0 ? true : false;
	}
	
	function encrypt($str, $key) {
		if ($str == "" || $key == "") {
			return;
		}
		
		$result = '';
		for ($i = 0; $i < ceil(strlen($str)/strlen($key)); $i ++) {
			$result .= bin2hex(substr($str, $i*strlen($key), ($i+1)*strlen($key))^$key);
		}
		
		return $result;
	}
	
	function decrypt($str, $key) {
		if ($str == "" || $key == "") {
			return;
		}
		
		$result = ''; $j = 0;
		for ($i = 0; $i < strlen($str) / 2;  $i ++) {
			if ($j >= strlen($key)) $j = 0;
			$result .= chr((hexdec(substr($str, $i*2, 2))))^substr($key, $j, 1);
			$j ++;
		}
		
		return $result;
	}
}