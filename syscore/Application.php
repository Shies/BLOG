<?PHP
// 大多数功能考虑差不多了，分离Base和Application，为了更好模块化
class Application extends Object {
	
	public $supervars = array('GLOBALS' => 1, '_GET' => 1, '_POST' => 1, '_REQUEST' => 1, '_COOKIE' => 1, '_SERVER' => 1, '_ENV' => 1, '_FILES' => 1);
	public $vars;
	
	public $config;
	
	public $lang;
	public $sessctrl;
	public $cache;
	
	// 是否初始化过? 如果编译过, 下次则不编译
	private $initated;
	
	static function &get_instance() {
		static $instance;
		if (empty($instance)) {
			$instance = new self();
		}
		return $instance;
	} 

	function __construct() {
		$this->_ini_env();
		$this->_ini_config();
		$this->_ini_input();
		$this->_ini_output();
	}
	
	function init($isadmin = false) {	
		if (!$this->initated) {
			$this->ini_var();
			$this->ini_db();
			$this->ini_template($isadmin);
		}
		$this->initated = true;
	}
	
	private function _ini_env() {
		// error_reporting(E_ERROR | E_WARNING | E_PARSE); // 致命错误、严重错误、解析错误 停止code执行
		// 关闭入库\转移
		PHP_VERSION < '5.3.0' AND set_magic_quotes_runtime(0); 
		
		define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
		define('ICONV_ENABLE', function_exists('iconv'));
		define('MB_ENABLE', function_exists('mb_convert_encoding'));
		define('EXT_OBGZIP', function_exists('ob_gzhandler'));
		define('IS_POST', 	$_SERVER['REQUEST_METHOD'] == 'POST');
		
		# 定义时间戳和设置时区 #
		$this->set_time_zone();
		
		# 载入公共配置文件 #		
		if (!include(ROOT_PATH.SYS_CORE.'/Common.php')) {
			Syserror::error('include common.func.php is missing');
		}
		
		# 限制内存最大使用范围 #
		if (function_exists('ini_get')) {
			$memorylimit = @ini_get('memory_limit');
			if ($memorylimit && return_bytes($memorylimit) < 10000000 && function_exists('ini_set')) {
				ini_set('memory_limit', '64m');
			}
		}
		
		// 检查是否是机器人访问
		define('IS_ROBOT', is_robot());
		
		// 当启动register_global, 生成一个干净的全局Globals变量 
		foreach (isset($GLOBALS) ? $GLOBALS : array() AS $key => $val) {
			if (!isset($this->supervars[$key])) {
				$GLOBALS[$key] = null; 
				unset($GLOBALS[$key]);
			}
		}
		
		global $_G;
		$_G = array(
			'remoteport' => $_SERVER['REMOTE_PORT'],
			'starttime'  => microtime(true), 
		);
		
		// 当前执行PHP的url及file
		$_G['PHP_SELF'] = $this->get_script_url();
		$_G['basename'] = basename($_G['PHP_SELF']);
		
		// 判断是否是https请求方式
		$_G['IS_HTTPS'] = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off' ? true : false;
		
		// 当前站点PHP执行url分析
		$sitepath = substr($_G['PHP_SELF'], 0, strrpos($_G['PHP_SELF'], '/'));
		$_G['siteurl'] = ($_G['IS_HTTPS'] ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$sitepath.'/';
		
		$parts = parse_url($_G['siteurl']);
		$_G['sitepath'] = isset($parts['path']) ? $parts['path'] : '';
		$_G['siteport'] = empty($_SERVER['SERVER_PORT']) 
			|| $_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443 ? '' : ':'.$_SERVER['SERVER_PORT'];
		
		$this->vars =& $_G;
	}
	
	private function _ini_config() {
		static $_config = array();
		
		// 初始化系统必要的一些配置
		if (empty($_config)) {
			if (!include(ROOT_PATH.'data/config.php')) {
				Syserror::error('system config not found');
			}
		}
		
		if (empty($_config['security']['authkey'])) {
			$_config['security']['authkey'] = md5($_config['cookie']['cookiepre'].$_config['db'][1]['dbname']);
		}
		
		if ($_config['debug'] && !file_exists(ROOT_PATH.'/helper/debug.php')) {
			define('SYS_DEBUG', false);
			error_reporting(0);
		} elseif ($_config['debug'] === 1 || $_config['debug'] === 2 || !empty($_REQUEST['debug']) && $_REQUEST['debug'] == $_config['debug']) {
			define('SYS_DEBUG', true);
			error_reporting(E_ERROR);
			if ($_config['debug'] === 2) {
				error_reporting(E_ALL);
			}
		} else {
			define('SYS_DEBUG', false);
			error_reporting(0);
		}
		
		define('STATIC_URL', !empty($_config['output']['staticurl']) ? $_config['output']['staticurl'] : ROOT_PATH.'static');
		$this->vars['staticurl'] = STATIC_URL;
		
		$this->config =& $_config;
		$this->vars['config'] =& $_config;
		
		if (substr($_config['cookie']['cookiepath'], 0, 1) != '/') {
			$this->vars['config']['cookie']['cookiepath'] = '/'.$this->vars['config']['cookie']['cookiepath'];
		}
		
		$this->vars['config']['cookie']['cookiepre'] = $this->vars['config']['cookie']['cookiepre'].substr(md5($this->vars['config']['cookie']['cookiepath'].'|'.$this->vars['config']['cookie']['cookiedomain']), 0, 4).'_';
	}
	
	private function _ini_input() {
		if ($_GET['GLOBALS'] || $_POST['GLOBALS'] || $_COOKIE['GLOBALS'] || $_FILES['GLOBALS']) {
			exit('sorry, request\'s url invalid');
		}
		
		$prelength = strlen($this->vars['cookie']['cookiepre']);
		foreach ($_COOKIE AS $key => $val) {
			if (substr($key, 0, $prelength) == $this->vars['cookie']['cookiepre']) {
				$this->vars['cookie'][substr($key, $prelength)] = $val;
			}
		}
		
		if (!empty($_POST) && $_SERVER['REQUEST_METHOD'] == 'POST') {
			$_GET = array_merge($_POST, $_GET);
		}
		
		foreach (array('_GET', '_POST', 
			'_COOKIE', '_SERVER', '_FILES', '_REQUEST') AS $val)
		{
			global $$val;
			if (in_array($val, array('_SERVER', '_FILES'))) {
				$$val = daddslashes($$val);
				continue;
			}
			$$val = daddslashes($$val, 1, TRUE);
		}
		
		$this->vars['isajax'] = empty($_GET['isajax']) ? 0 : (empty($this->vars['config']['output']['ajaxvalidate']) ? 1 : ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || $_SERVER['REQUEST_METHOD'] == 'POST' ? 1 : 0));
	}
	
	private function _ini_output() {
		if ($this->config['security']['attackevasive']) {
			require_once(ROOT_PATH.'helper/security.php');
		}
	
		if (!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && 
			FALSE === strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$this->config['output']['gzip'] = false;
		}

		$allowgzip = false;
		if ($this->config['output']['gzip']) {
			if (empty($this->vars['isajax']) && EXT_OBGZIP) {
				$allowgzip = true;
			}
		}
		$GLOBALS['_G']['gzip'] = $allowgzip;
		if (!ob_start($allowgzip ? 'ob_gzhandler' : null)) {
			ob_start();
		}
		
		if (!$this->config['output']['charset']) {
			if (defined('RK_CHARSET')) {
				$this->config['output']['charset'] = RK_CHARSET;
				$GLOBALS['_G']['charset'] = RK_CHARSET;
			}
		}
		
		if ($this->config['output']['forceheader']) {
			@header('Content-Type: text/html; charset='.$this->config['output']['charset']);
		}
	}
	
	function get_script_url() {
		if (!isset($this->vars['PHP_SELF'])) {
			$this->gen_script_url($this->vars['PHP_SELF']);
		}
		
		return $this->vars['PHP_SELF'];
	}
	
	function gen_script_url(&$phpself) {
		$scriptname = basename($_SERVER['SCRIPT_FILENAME']);
		
		if (basename($_SERVER['SCRIPT_NAME']) === $scriptname) {
			$phpself = $_SERVER['SCRIPT_NAME'];
		} elseif (basename($_SERVER['PHP_SELF']) === $scriptname) {
			$phpself = $_SERVER['PHP_SELF'];
		} elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptname) {
			// ORIG_SCRIPT_NAME 如果PHP通过cgi来运行为/php/php.exe, 如果apache将PHP作为模块来运行为/phpinfo.php
			$phpself = $_SERVER['ORIG_SCRIPT_NAME'];
		} elseif ($pos = strpos($_SERVER['PHP_SELF'], '/'.$scriptname) !== false) {
			$phpself = substr($_SERVER['SCRIPT_NAME'], 0, $pos).'/'.$scriptname;
		} elseif (isset($_SERVER['DOCUMENT_ROOT']) && 
			strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
		{
			$phpself = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
			$phpself[0] != '/' && $phpself = '/'.$phpself;
		} 
		else {
			exit('sorry, request\'s url invalid');
		}
	}
	
	function checkrobot() {
		if (IS_ROBOT) {
			exit(header("HTTP/1.1 403 Forbidden"));
		}
	}
	
	function checkxss() {
		static $check = array('"', '\'', '<', '>', '(', ')', 'CONTENT-TRANSFER-ENCODING');
		
		if (isset($_GET['formhash']) && $_GET['formhash'] !== formhash()) {
			Syserror::error('hacker attack');
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$temp = $_SERVER['REQUEST_URI'];
		} elseif (empty($_GET['formhash'])) {
			$temp = $_SERVER['REQUEST_URI'].file_get_contents('php://input');
		} else {
			$temp = null;
		}
		
		if (null !== $temp) {
			$temp = strtoupper(urldecode(urldecode($temp)));
			foreach ($check AS $char) {
				if (strpos($temp, $char) !== false) {
					Syserror::error('hacker attack');
				}
			}
		}
		
		return true;
	}
	
	function set_time_zone($timezone = 0) {
		define('TIMESTAMP', $_SERVER['REQUEST_TIME']);
		if (function_exists('date_default_timezone_set') && @date_default_timezone_set()) {
			@date_default_timezone_set('Etc/GTM'.$timezone > 0 ? '-' : '+'.abs($timezone));
		}
	}
	
	function ini_var() {
		$this->vars['time'] = TIMESTAMP;

		if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$this->vars['ip'] = getenv('HTTP_CLIENT_IP');
		} elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$this->vars['ip'] = getenv('HTTP_X_FORWARDED_FOR');
		} elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$this->vars['ip'] = getenv('REMOTE_ADDR');
		} elseif ($_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$this->vars['ip'] = $_SERVER['REMOTE_ADDR'];
		}
		
		preg_match('/[\d\.]{7,15}/', $this->vars['ip'], $match);
		$this->vars['ip'] = $match[0] ? $match[0] : 'unknown';
		unset($match);
		
		require ROOT_PATH . 'include/Lang.php';
		$this->lang =& $lang;
	}
	
	function ini_db() {
		try {
			$C =& get_instance();
			
			$C->db =& Base::load_class('Mysql', null, 
				array(DB_HOST, DB_USER, DB_PASS, DB_NAME, RK_CHARSET), 'db');
		} catch (Exception $e) {
			print $e->getMessage() . 
						'------' . $e->getCode();
			exit;
		}
	}
	
	function ini_template($isadmin = false) {
		$C =& get_instance();
	
		$C->view =& Base::load_class('View', 'syscore');
		if ($isadmin) {
			$C->view->template_dir = ROOT_PATH . 'view/admin';
			$C->view->compile_dir = ROOT_PATH . 'data/temp/compiled/admin';
		} else {
			header('Cache-control: private');
			header('Content-type: text/html; charset=' . RK_CHARSET);
			
			$C->view->cache_lifetime = 3600;
			$C->view->cache_dir = ROOT_PATH . 'data/temp/caches';
			$C->view->template_dir = ROOT_PATH . 'view/default';
			$C->view->compile_dir = ROOT_PATH . 'data/temp/compiled';
		}
	}
	
	function ini_mobile($mobile = 'ios') {
		return $this->vars['mobile'] = $mobile;
	}
	
	function ini_sess($sess = 'session') {
		static $sessctrl = array();
		if (is_array($sessctrl)) {
			if (!isset($sessctrl[$sess]) || !is_object($sessctrl[$sess])) {
				$sessctrl[$sess] =& Base::load_class($sess, 'include/sessctrl');
			}
			
			return $sessctrl[$sess];
		} else {
			$sessctrl = array(
				'cookie', 'session', 'sess_file', 'sess_mem', 'sess_mysql'
			);
			
			return $this->ini_sess($sess);
		}
	}
	
	function ini_cache($cache = 'cache_file') {
		static $caches = array();
		if ($caches && is_array($caches)) {
			if (!isset($caches[$cache]) || !is_object($caches[$cache])) {
				$caches[$cache] =& Base::load_class($cache, 'include/cache');
			}
			
			return $caches[$cache];
		} else {
			$caches = array(
				'cache_memcache', 'cache_redis', 'cache_apc', 'cache_file'
			);
			
			return $this->ini_cache($cache);
		}
	}
}