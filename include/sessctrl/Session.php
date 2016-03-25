<?PHP
class Session {
	
	private $sess_cookie_secure;
	private $sess_cookie_domain;
	private $sess_cookie_path;
	
	private $sess_expire;
	private $maxlifetime = 3600;
	
	private $sesskeys;
	private $sessid;
	private $sessname;
	
	private $sesstable;
	private $ctrl;
	
	private $ip;
	private $time;

	function __construct($sesstable = 'ld_sessions', $sessname = 'TestId', $sessid = '') {
		$this->open($sesstable, $sessname, $sessid);
	}
	
	public function open($sesstable, $sessname, $sessid) {
		$_SESSION = array();
		
		$_SESS =& Base::load_config('session', 'data', true);
		$this->sess_cookie_domain = isset($_SESS['cookie_domain']) ? $_SESS['cookie_domain'] : '';
		$this->sess_cookie_path = isset($_SESS['cookie_path']) ? $_SESS['cookie_path'] : '/';
		$this->sess_cookie_secure = isset($_SESS['cookie_secure']) ? $_SESS['cookie_secure'] : FALSE;		
		$this->sessname = $sessname;		
		$this->sesstable = $sesstable;
		
		$this->ctrl =& get_instance();
		$this->ip = $this->ctrl->app->vars['ip'];
		
		if (!$sessid && !empty($_COOKIE[$this->sessname])) {
			$this->sessid = $_COOKIE[$this->sessname];
		} else {
			$this->sessid = $sessid;
		}
		
		if ($this->sessid) {
			$tmpsessid = substr($this->sessid, 0, 32);
			if ($this->gen_sess_key($tmpsessid) == substr($this->sessid, 32)) {
				$this->sessid = $tmpsessid;
			} else {
				$this->sessid = '';
			}
		}
		$this->time = $this->ctrl->app->vars['time'];
		
		if ($this->sessid) {
			$this->sess_read(); // 存在直接读取
		} else {
			// 生成新的sessid并入库
			$this->gen_sess_id();
			setcookie($this->sessname, $this->sessid.$this->gen_sess_key($this->sessid), 
			0, $this->sess_cookie_path, $this->sess_cookie_domain, $this->sess_cookie_secure);
		}
		
		register_shutdown_function(array(&$this, 'sess_close'));
	}
	
	public function get_sess_id() {
		return $this->sessid;
	}
	
	public function gen_sess_id() {
		$this->sessid = md5(uniqid(mt_rand(), true));
		return $this->sess_create();
	}
	
	public function gen_sess_key($sessid) {
		static $ip = NULL;
		if (NULL === $ip) {
            $ip = sprintf('%u', ip2long($this->ip));
        }
		
        return sprintf('%06x', 
			crc32($ip.$sessid));
	}
	
	public function get_online_count() {
		return $this->ctrl->db->getOne("SELECT COUNT(*) FROM ".$this->sesstable);
	}
	
	public function sess_read() {
		$sess = $this->ctrl->db->getRow(
			"SELECT lastvisit, user_id, data FROM ".$this->sesstable." WHERE session_id = '$this->sessid'");
		if (empty($sess)) {
			# 当机器上存在sessid #
			// 但是db的sessdata可能被人工删除或者其它情况出现丢失sessdata, 
			// 这时则重新插入当前机器上的session入库, 保持和客户端建立会话
			$this->sess_create();
			
			$this->sess_expire = 0;
			$this->sesskeys = 'abcdefghijklmnopqrstuvwxyz';
			$_SESSION = array();
		
			return;
		}
		
		# sess已存活时间 = 当前时间 - sess入库时间 #
		// 如果lt sess最大生存时间, 则表明该sess未到期还处于活动状态
		$isexpire = $this->time - $sess['lastvisit'] <= $this->maxlifetime ? false : true;
		if (!empty($sess['data']) && !$isexpire) {
			$this->sess_expire = $sess['lastvisit'];
			$this->sesskeys = sha1($sess['data']);
		
			$_SESSION = unserialize($sess['data']);
			$_SESSION['user_id'] = $sess['user_id'];
		} else {
			$this->sess_expire = 0;
			$this->sesskeys = 'abcdefghijklmnopqrstuvwxyz';
			$_SESSION = array();
		}
	}
	
	public function sess_create() {
		$sess = array(
			'session_id' => $this->sessid,
			'user_id'    => 0,
			'ip'	     => $this->ip,
			'useragent'  => $_SERVER['HTTP_USER_AGENT'],
			'lastvisit'  => $this->time,
			'data'		 => 'a:0:{}'
		);
		return $this->ctrl->db->autoExecute(
			$this->sesstable, $sess, 'INSERT');
	}
	
	public function sess_update() {
		$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
		unset($_SESSION['user_id']); // 因为已经存在user_id字段, 防止data存在user_id
			
		$data = serialize($_SESSION);
		$this->time = $this->ctrl->app->vars['time'];
		
		// 防止频繁的刷新db, 设置个10秒为间隔时间、停顿
		if ($this->sesskeys == sha1($data) && $this->time < $this->sess_expire + 10) {
			// 如果从db读取到了sess和sess更新时一样并且当前时间lt sess入库时间+10
			return true;
		}
		
		$sess = array(
			'user_id'   => $user_id,
			'ip'	    => $this->ip,
			'useragent' => $_SERVER['HTTP_USER_AGENT'],
			'lastvisit' => $this->time,
			'data'		=> addslashes($data)
		);
		return $this->ctrl->db->autoExecute(
			$this->sesstable, $sess, 
			'UPDATE', "session_id='$this->sessid'"
		);
	}
	
	public function sess_destory() {
		if (isset($_SESSION)) $_SESSION = array();
		
		setcookie($this->sessname, $this->sessid, 1, 
			$this->sess_cookie_path, $this->sess_cookie_domain, $this->sess_cookie_secure
		);
		
		return $this->ctrl->db->query(
			"DELETE FROM ".$this->sesstable." 
			WHERE session_id = '$this->sessid'"
		);
	}
	
	public function sess_close() {
		# 当程序执行完成后 #
	
		// 如果设置过session或当前时间超出间隔时间, 立即更新session的值, 
		// 否则获取值与原有值保持一致并且当前时间lt sess入库时间+10, 则返回
		$this->sess_update();
		
		// 随机对session过期数据进行清理
		return $this->sess_gc();
	}
	
	public function sess_gc() {
		if ($this->time % 2 != 0) {
			return true;
		}
		
		$expiretime = $this->time - $this->maxlifetime;
		return $this->ctrl->db->query("DELETE FROM ".$this->sesstable." 
			WHERE lastvisit < '$expiretime'");
	}
}