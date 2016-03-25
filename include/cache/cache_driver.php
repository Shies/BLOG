<?PHP
// 缓存工厂类调用驱动器
final class cache_driver {
		
	private static $cache_drivers;
	
	protected $cache_config;
	
	protected $cache_list;
	
	function __construct() {
		
	}
	
	static function get_instance($cache_config = '') {
		if (empty(self::$cache_drivers) || $cache_config != '') {
			self::$cache_drivers = new self();
			if ($cache_config != '') {
				self::$cache_drivers->cache_config = $cache_config;
			}
		}
		return self::$cache_drivers;
	}
	
	function get_cache($name = '') {
		if (empty($this->cache_list[$name]) || !is_object($this->cache_list[$name])) {
			$this->cache_list[$name] = $this->load_cache($name);
		}
		return $this->cache_list[$name];
	}
	
	function load_cache($name = '') {
		$object = null;
		if (isset($this->cache_config[$name]['type'])) {
			switch ($this->cache_config[$name]['type']) {
				case 'file' :
					$object =& Base::load_class('cache_file', 'include/cache');
				break;
				case 'redis' : 
				case 'memcache' : 
					$object =& Base::load_class('memcache_cache', 'include/cache');
				break;
				case 'apc' : 
					$object =& Base::load_class('apc_file', 'include/cache');
				break;
				default : 
					$object =& Base::loadclass('cache_file', 'include/cache');
			}
		} else {
			$object =& Base::load_class('cache_file', 'include/cache');
		}
		return $object;
	}
}

// print_r(cache_drivers::get_instance()->get_cache('tpl'));