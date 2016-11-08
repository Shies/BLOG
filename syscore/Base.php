<?PHP
// 系统开发中
define('IN_SYSTEM', true);

define('SYS_CORE', strtolower(substr(dirname(__FILE__), -7))); // 核心目录
define('SYS_BASE_DEBUG', false); // 状态码
unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);

// ------------------------------------ //
	// Base::bmstart('#run#app#');
// ------------------------------------ //

set_exception_handler(array('Base', 'handleexception'));

if (SYS_BASE_DEBUG) {
	set_error_handler(array('Base', 'handleerror'));
	register_shutdown_function(array('Base', 'handleshutdown'));
}

if (function_exists('spl_autoload_register')) {
	spl_autoload_register(array('Base', 'autoload'));
} else {
	function __autoload($class) {
		return Base::autoload($class);
	}
}

Base::createapp();

// ------------------------------------ //
	// print_r(Base::bmstop('#run#app#'));
	// exit;
// ------------------------------------ //

class Base {
	
	private static $_app;
	
	static function app() {
		return self::$_app;
	}
	
	static function createapp() {
		if (empty(self::$_app)) {
			self::$_app =& Application::get_instance();
			self::loaded('app', get_class(self::$_app));
		}
		return self::$_app;
	}
	
	static function run() {
		Router::parseUrl();
		$directory = Router::fetchDirectory();
		$className = Router::fetchClass();
		$methodName = Router::fetchMethod();
		$params = Router::fetchParams();
		
		$control = Base::load_class(ucfirst($className).'Action', 'control/'.$directory);
		if (method_exists($control, $methodName)) {
			if ($methodName{0} != '_') {
				call_user_func_array(array($control, $methodName), $params);
				exit;
			}
		}
		exit('Action not found!');
	}
	
	static function loaded($var = '', $class = '') {
		static $loaded = array();
		if ($var !== '')
		{
			$loaded[strtolower($var)] = $class;
		}

		return $loaded;
	}
	
	static function load_config($file, $part = 'data', $return = false, $key = '') {
		static $configs = array();
		if (!isset($configs[$file])) {
			$path = ROOT_PATH.$part.'/'.$file.'.php';
			if (file_exists($path)) {
				if ($return) {
					$configs[$file] = include $path;
				} else {
					include $path;
					$configs[$file] = true;
				}
			}
		}
		
		if (empty($key{0}) == true) {
			return $configs[$file];
		} else {
			if (!isset($configs[$file][$key])) {
				return $configs[$file];
			}
			
			return $configs[$file][$key];
		}
	}
	
	static function load_class($class, $part = '', $args = '', $alias = '') {
		if (get_class(self::$_app) 
				=== ucfirst(strtolower($class))) {
			return self::$_app;
		}

		static $classes = array();
		if (isset($classes[$class])) {
			return $classes[$class];
		}
		
		if (null != $part) {
			$part = rtrim(str_replace('\\', '/', $part), '/');
		} else {
			$part = 'include';
		}
		$pathname = ROOT_PATH.$part.'/'.$class.'.php';
		if (file_exists($pathname)) {
			require $pathname;
		} else {
			require ROOT_PATH.'control/'.$class.'.php';
		}
		
		if (null != $args) {
			$classes[$class] = new $class($args);
		} else {
			$classes[$class] = new $class();
		}
		self::loaded($alias ? $alias : $class, $class);
		
		return $classes[$class];
	}
	
	# Model装载到env里，而不是赋给C的指针, 为了防止类名冲突, 或者意想不到的exception #
	static function load_model($model, $base = null) {
		if (!empty($_ENV[$model])) {
			return $_ENV[$model];
		}
	
		$pathname = 'model/'.ucfirst($model).'Model.php';
		if (file_exists(ROOT_PATH.$pathname)) {
			require ROOT_PATH.$pathname;
		} else {
			require rtrim(ROOT_PATH, '/').'/'.$pathname;
		}
		
		eval('$_ENV[$model] = new '.
			ucfirst($model).'Model(
			$base !== null ? $base : null);'
		);
	}
	
	static function handleexception($exception) {
		Syserror::exception($exception);
	}
	
	static function handleerror($errno, $errmsg, $errfile, $errline) {
		if ($errno & SYS_BASE_DEBUG) {
			Syserror::error($errmsg, false, true, false);
		}
	}
	
	static function handleshutdown() {
		if (($error = error_get_last()) && $error['type'] & SYS_BASE_DEBUG) {
			Syserror::error($error['message'], false, true, false);
		}
	}
	
	static function autoload($class) {
		$class = ucfirst(strtolower($class));
		if (strpos($class, ' ') !== false) {
			$pathname = str_replace(' ', '', $class);
		} else {
			$pathname = $class;
		}
		
		try {
			self::load_config($pathname, SYS_CORE, false);
			unset($class, $pathname);
		} catch (Exception $e) {
			$trace = $e->getTrace();
			foreach ($trace AS $log) {
				if (!$log['class'] && $log['function'] == 'class_exists') {
					return false;
				} 
			}
			Syserror::exception($e);
		}
	}
	
	static function bmstart($name) {
		$key = 'other';
		if ($name[0] === '#') {
			list(, $key, $name) = explode('#', $name);
		}
		
		if (!isset($_ENV['bm'])) {
			$_ENV['bm'] = array();
		}
		if (!isset($_ENV['bm'][$key])) {
			$_ENV['bm'][$key] = array();
			$_ENV['bm'][$key]['sum'] = 0;
		}
		
		$_ENV['bm'][$key][$name]['start'] = microtime(true);
		$_ENV['bm'][$key][$name]['start_memory_get_usage'] = memory_get_usage();
		$_ENV['bm'][$key][$name]['start_memory_get_real_usage'] = memory_get_usage(true);
		$_ENV['bm'][$key][$name]['start_memory_get_peak_usage'] = memory_get_peak_usage();
		$_ENV['bm'][$key][$name]['start_memory_get_peak_real_usage'] = memory_get_peak_usage(true);
	}
	
	static function bmstop($name) {
		$key = 'other';
		if ($name[0] === '#') {
			list(, $key, $name) = explode('#', $name);
		}
		
		if (isset($_ENV['bm'][$key][$name]['start'])) {
			$diff = round((microtime(true) - $_ENV['bm'][$key][$name]['start']) * 1000, 5);
			$_ENV['bm'][$key][$name]['time'] = $diff;
			$_ENV['bm'][$key]['sum'] += $diff;			
			unset($_ENV['bm'][$key][$name]['start']);
			
			$_ENV['bm'][$key][$name]['stop_memory_get_usage'] = memory_get_usage();
			$_ENV['bm'][$key][$name]['stop_memory_get_real_usage'] = memory_get_usage(true);
			$_ENV['bm'][$key][$name]['stop_memory_get_peak_usage'] = memory_get_peak_usage();
			$_ENV['bm'][$key][$name]['stop_memory_get_peak_real_usage'] = memory_get_peak_usage(true);
		}
		
		return $_ENV['bm'][$key][$name];
	}
}