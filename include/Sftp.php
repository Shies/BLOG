<?PHP
/* *
 *	首先通过　$ftps->connect($host,$username,$password,$post,$pasv,$ssl,$timeout);进行FTP服务器连接。
 *	通过具体的函数进行FTP的操作。
 *	$ftps->mkdir() 创建目录，可以创建多级目录以“/abc/def/higk”的形式进行多级目录的创建。
 *	$ftps->put() 上传文件
 *	$ftps->rmdir() 删除目录
 *	$ftps->f_delete() 删除文件
 *	$ftps->nlist() 列出指定目录的文件
 *	$ftps->chdir() 变更当前文件夹
 *	$ftps->get_error() 获取错误信息
 */
class Sftp {

	private $conn;
	private $error;
	private $errno;
	
	public  $conntime;
	public	$ftpnode;

	function Sftp() {
		;
	}
	
	/* *
	 *	@param 	$host 服务器地址
	 *	@param	$user 用户名
	 *	@param	$pass 密码
	 *	@param	$port 端口
	 *	@param	$pasv 开启被动模式
	 *	@param	$ssl  开始ssl连接
	 *	@param	$timeout	超时时间	
	 *
	 *	@return
	 */
	function connect($host, $user, $pass, $port, $pasv = false, $ssl = false, $timeout = 10) {
		$start = time();
		if (false !== $ssl) {
			if (!$this->conn = ftp_ssl_connect($host, $port, $timeout)) {
				$this->errno = 1;
				return false;
			}
		} else {
			if (!$this->conn = ftp_connect($host, $port, $timeout)) {
				$this->errno = 1;
				return false;
			}
		}
		
		if (@ftp_login($this->conn, $user, $pass)) {
			if ($pasv) {
				ftp_pasv($this->conn, true);
			}
			$this->conntime = time() - $start;
			return true;
		} else {
			$this->errno = 1;
			return false;
		}
		register_shutdown_function(array(&$this, 'close'));
	}
	

	# 创建目录 #
	function mkdir($path) {
		if (!$this->conn) {
			$this->errno = 2;
			return false;
		}
		
		$dirname = $this->checkpath($path);
		$path = '';
		$res = true;
		for ($i = 0; $i < count($dirname) - 1; $i++) {
			$path .= '/'.$dirname[$i];
			if (!$this->chdir($path)) {
				$this->chdir('/');
				if (!ftp_mkdir($this->conn, $path)) {
					$res = false;
					break;
				}
			}
		}
		
		return $res;
	}
	
	# 删除目录 #
	function rmdir($path, $force = false) {
		if (!$this->conn) {
			$this->error = 2;
			return false;
		}
		
		$list = $this->nlist($path);
		if ($list && $force) {
			$this->chdir($path);
			foreach ($list AS $val) {
				$this->delete($val);
			}
		} elseif ($list && !$force) {
			$this->errno = 3;
			return false;
		}
		@ftp_rmdir($this->conn, $path);
		return true;
	}
	
	# 上传文件 remote 远程目录 和 local 本地目录 #
	function put($remote, $local) {
		if (!$this->conn) {
			$this->errno = 2;
			return false;
		}
		
		$path = pathinfo($remote, PATHINFO_DIRNAME);
		if (!$this->chdir($path)) {
			$this->mkdir($path);
		}
		
		if (ftp_put($this->conn, $remote, $local, $this->ftpmode)) {
			return true;
		} else {
			$this->errno = 7;
			return false;
		}
	}
	
	# 删除指定文件 #
	function delete($filename) {
		if (!$this->conn) {
			$this->errno = 2;
			return false;
		}
		
		if (ftp_delete($this->conn, $filename)) {
			return true;
		} else {
			$this->errno = 4;
			return false;
		}
	}
	
	# 返回指定目录下的列表 #
	function nlist($path) {
		if (!$this->conn) {
			$this->errno = 2;
			return false;
		}
		
		if ($list = @ftp_nlist($this->conn, $path)) {
			return $list;
		} else {
			$this->errno = 5;
			return false;
		}
	}
	
	# 切换目录 #
	function chdir($path) {
		if (!$this->conn) {
			$this->errno = 2;
			return false;
		}
		
		if (ftp_chdir($this->conn, $path)) {
			return true;
		} else {
			$this->errno = 6;
			return false;
		}
	}
	
	# 获取错误信息 #
	function get_error() {
		if (!$this->errno) return false;
		$this->error = array(
			'1'=>'Server can not connect',
			'2'=>'Not connect to server',
			'3'=>'Can not delete non-empty folder',
			'4'=>'Can not delete file',
			'5'=>'Can not get file list',
			'6'=>'Can not change the current directory on the server',
			'7'=>'Can not upload files'
		);
		return $this->error[$this->errno];
	}
	
	# 过滤路径空值 #
	function checkpath($path) {
		return array_values(array_filter(split('/', str_replace('\\', '/', $path)), 'strval'));
	}
	
	function close() {
		return @ftp_close($this->conn);
	}
}

$sftp = new Sftp;
$dirname = $sftp->checkpath('/abc/e/d');
print_r($dirname);
exit;