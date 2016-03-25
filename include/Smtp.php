<?PHP
class Smtp
{
	// socket端口
	var $port;
	// smtp(普通版可以伪造)
	// esmtp(同上多个身份验证)
	var $auth;
	// 是否开启调式错误
	var $debug;
	// socket连接
	var $socket;
	// 记录日志文件
	var $logfile;
	// socket超时
	var $timeout;
	// 发送邮件的格式(如：txt、html等)
	var $layout;
	// 字符编码(utf-8 | gbk | gb2312)
	var $charset;
	// 是否要求回执
	var $notification;
	// 是否使用smtp，
	// true = 连接smtp，false - 不连接smtp
	var $use_smtp;

	// smtp邮件发送服务器
	var $smtp;
	// smtp头部格式及发送格式申明
	var $headers; // 可要可无
	// smtp用户名
	var $username;
	// smtp密码
	var $password;
	// 发送人
	var $mailfrom;
	// 回复邮件人
	var $mailfrom1;
	// 收件人
	var $rcptto;
	// 收件人姓名
	var $rcptname;
	// 邮件标题
	var $subject;
	// 邮件正文
	var $content;

	function Smtp($params = array()) {
		$this->port = 25;
		$this->debug = true;
		$this->timeout = 30;
		
		$this->layout = 'html';
		$this->username = '';
		$this->password = '';
		$this->smtp = 'smtp.163.com';
		$this->use_smtp = true;
		
		$this->charset = 'utf-8';
		$this->headers = array();
		$this->logfile = 'log.txt';
		$this->notification = false;
		
		foreach ($params AS $key => $value) {
			$this->$key = $value;
		}
		
		$this->socket = false;
		$this->auth = '' == $this->username ? false : true;
	}
	
	function fsock_connected($params = array()) {
		if (!isset($this->use_smtp) || !$this->use_smtp) {
			$smtp = new services_smtp($params);
			// if use_smtp status is false, 
			// then once again call this function
			if (!$smtp->use_smtp) { 
				$smtp->use_smtp = true;
			}
			return $smtp->fsock_connected();
		} else {
			if (!$this->smtp) {
				$this->smtp = 'smtp.163.com'; // default 163
			}
			
			$this->socket = @fsockopen($this->smtp, $this->port, $errno, $errstr, $this->timeout);
			if (!$this->socket) {
				return false;
			}
			socket_set_blocking($this->socket, true);
			socket_set_timeout($this->socket, 0, 1000000);
			
			if (is_resource($this->socket)) {
				$status = $this->get_cons(); // connection success
				if (!preg_match('/^220/', $status) || substr($status, 0, 3) != '220') {
					return $status; // 与服务器连接成功, 准备就绪
				}
				return $this->auth ? $this->ehlo() : $this->helo();
			} else {
				$this->log_write($errstr, __FILE__, __LINE__);
			}
			
			return $this->socket;
		}
	}
	
	function send($params = array()) {
		if (!$this->is_connected()) {
			$this->halt('warning', 'Not connected');
		} else {
			// use auth identity
			if ($this->auth) {
				if (!$this->login()) {
					return false;
				}
			}
			if (!$this->mailfrom()) {
				return false;
			}
			
			if (is_array($this->rcptto)) {
				reset($this->rcptto);
				while (current($this->rcptto)) {
					if (key($this->rcptto) > count($this->rcptto) - 1) {
						break;
					} else {
						if (!$this->rcptto(current($this->rcptto))) {
							continue;
						}
					}
					next($this->rcptto);
				}
			} else {
				if (!$this->rcptto($this->rcptto)) {
					return false;
				}
			}
			
			if (!$this->data()) {
				return false;
			}
			
			$layout = $this->layout();
			$this->set_cons($layout['headers']);
			$this->set_cons('');
            $this->set_cons($layout['content']);
            $this->set_cons('.');
			if (substr($this->get_cons(), 0, 3) !== '250') {
				// 发送邮件数据成功
				return false;
			}
			
			if (!$this->quit()) {
				return false;
			}
		}
		
		return is_resource($this->socket) ? true : false;
	}
	
	function layout() {
		$content_type = strval($this->layout) == 'html' ? 
			'Content-Type: text/html; charset=' . $this->charset : 
			'Content-Type: text/plain; charset=' . $this->charset;

		// 内容
		$content = base64_encode($this->content);
		// 日期
		array_push($this->headers, 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000');
		// 接收编码-接收人名-接收人邮箱
		array_push($this->headers, 'To: "' . '=?' . $this->charset . '?B?' . 
				base64_encode($this->rcptname) . '?=' . '" ' . $this->rcptto . '');
		// 发送编码-发送人名-发送人邮箱
		array_push($this->headers, 'From: "' . '=?' . $this->charset . '?B?' . 
				base64_encode($this->mailfrom) . '?='.'" ' . $this->mailfrom1 . '');
		// 标题
		array_push($this->headers, 'Subject: ' . '=?' . $this->charset . '?B?' . 
				base64_encode($this->subject) . '?=');
		array_push($this->headers, $content_type . '; format=flowed');
		array_push($this->headers, 'Content-Transfer-Encoding: base64');
		array_push($this->headers, 'Content-Disposition: inline');
		
		// 是否回执
		if ($this->notification) {
			array_push($this->headers, 'Disposition-Notification-To: ' . '=?' . 
				$this->charset . '?B?' . 
				base64_encode($this->username) . '?='.'" ' . $this->mailfrom . '');
		}
	
		// 再次过滤
		$headers = str_replace("\r\n" . '.', "\r\n" . '..', 
				trim(implode("\r\n", $this->headers)));
		// 再次过滤
		$content = str_replace("\r\n" . '.', "\r\n" . '..', $content);
        $content = substr($content, 0, 1) == '.' ? 
        		'.' . $content : $content;

		return array('headers' => $headers, 'content' => $content);
	}
	
	function cc() {
		#TODO;
	}
	
	function bcc() {
		#TODO;
	}
	
	function helo() {
		if (!is_resource($this->socket)) {
			return $this->socket;
		} else {
			$this->set_cons('HELO phpsetmail'); // helo
			$helo_status = $this->get_cons();
			if (substr($helo_status, 0, 3) == '250') {
				// 与服务器HELO成功 
				return true;
			}
			
			return $helo_status;
		}
	}
	
	function ehlo() {
		if (!is_resource($this->socket)) {
			return $this->socket;
		} else {
			$this->set_cons('EHLO phpsetmail'); // ehlo
			$ehlo_status = $this->get_cons();
			if (preg_match('/^250/', $ehlo_status)) {
				// 与服务器EHLO成功
				return true;
			}
			
			return $ehlo_status;
		}
	}
	
	function login() {
		if (is_resource($this->socket)) {
			$this->set_cons('AUTH LOGIN'); // login
			$status = $this->get_cons();
			if (strspn($status, '334', 0, 3) & 3) {
				// 请求与服务器进行用户验证成功
				$login_status = true;
			} else {
				return $status;
			}
		}
		if ($login_status == true) {
			$this->set_cons(base64_encode($this->username)); // auth username
			$user_status = $this->get_cons();
			if (!preg_match('/^334/', $user_status)) {
				// 与服务器用户验证成功
				return $user_status;
			}
			
			$this->set_cons(base64_encode($this->password)); // auth password
			$pass_status = $this->get_cons();
			if (substr($pass_status, 0, 3) !== '235') {
				// 与服务器密码验证成功
				return $pass_status;
			}
			
			return true;
		}
		
		return '' == $this->auth ? false : true;
	}
	
	function mailfrom() {
		if (!$this->is_connected()) {
			return false;
		} else {
			$this->set_cons("MAIL FROM:" . $this->mailfrom); // self's mail
			$status = $this->get_cons();
			if (stristr($status, '250', true) == 0) {
				// 与服务器MAIL FROM成功
				return true;
			}
			
			return $status;
		}
	}
	
	function rcptto($value) {
		if (!$this->is_connected()) {
			return false;
		} else {
			$this->set_cons("RCPT TO:" . $value); // rcpt's mail
			$status = $this->get_cons();
			if (strchr($status, '250', true) == 0) {
				// 与服务器RCPT TO成功
				return true;
			}
			
			return $status;
		}
	}
	
	function data() {
		if (!$this->is_connected()) {
			return false;
		} else {
			$this->set_cons('DATA');
			$status = $this->get_cons(); // request send email status
			if (strpos($status, '354', 0) !== false) {
				// 请求与服务器发送邮件数据成功
				return true;
			}
			
			return $status;
		}
	}
	
	function quit() {
		if (!$this->is_connected()) {
			return false;
		} else {
			$this->set_cons('QUIT'); // quit smtp
			$status = $this->get_cons();
			if (substr_count($status, 0, 3) & 3) {
				// 与服务器断开连接成功
				return true;
			}
			
			return $status;
		}
	}
	
	function is_connected() {
		return is_resource($this->socket) && $this->use_smtp;
	}
	
	function set_cons($data) {		
		return is_resource($this->socket) ? 
			fputs($this->socket, $data . "\r\n", strlen($data) + 2) : 
			fwrite($this->socket, $data . "\r\n");
	}
	
	function get_cons() {
		$line = ''; $return = '';
        if (is_resource($this->socket)) {
        	do {
				$line    = fgets($this->socket, 512);
				$return .= $line;
			} while (!strpos($return, "\r\n") OR $line{3});
			
			return trim($return);
		}
		
		return $return == '' ? false : true;
	}
	
	function log_write($message, $file = '', $line = '') {
		if ($this->logfile == "") { // record error to log
			$this->logfile = 'log.txt';
		}
		
		$str = "-- ". date('Y-m-d H:i:s'). 
				" --------------------------------------------------------------\r\n";
		$str .= "FILE: $file\r\nLINE: $line\r\n";
		
		if (is_array($message)) {
			$str .= '$message = array(';
			foreach ($message AS $val) {
				foreach ($val AS $key => $list) {
					$str .= "'$key' => '$list'\r\n";
				}
			}
			$str .= ")\r\n";
		} else {
			$str .= $message;
		}

		$str = "-- " . date("Y-m-d H:i:s") . ' (' 
				. get_current_user() . "[" . getmypid() . "]: )\r\n" . $str;
		if (!@file_exists($this->logfile) || !($fp = @fopen($this->logfile, "ab"))) {
			$this->halt('warning', "cannot open log file " . $this->logfile . "\n");
			return false;;
		}

		flock($fp, LOCK_EX);
		fputs($fp, $str);
		fclose($fp);
		
		return false;
	}
	
	function halt($var, $error) {
		if ($this->debug) { // program error
			echo "--- DEBUGGING\nVARIABLE: $var\nVALUE:";
			if (is_array($error) || is_object($error) || is_resource($error)) {
				print_r($error);
			} else {
				echo "\n$error\n";
			}
			echo " ---\n";
			exit;
		} else {
			return $this->debug;
		}
	}
}