<?PHP
// 与APC方法保持一直, 根据调用习惯而设计
class cache_memcache {

	private $_memcache;
	
	// 如果PECL memcache >= 2.0.0否则自行根据权重定义方法随机分配到指定的mem
	protected $_mem_conf = array(
		'default' => array(
			'defaulthost'	 => '127.0.0.1',
			'defaultport'	 => 11211,
			'defaultweight'  => 1
		)
	);
	
	function __construct() {
		if (!$this->is_supported()) {
			exit(-1);
		} 
		$this->setup_memcache();
	}
	
	function save($key, $var, $ttl = 300) {
		if (get_class($this->_memcache) == 'Memcached') {
			return $this->_memcache->set($key, array($var, time(), $ttl), $ttl);
		} elseif (get_class($this->_memcache) == 'Memcache') {
			return $this->_memcache->set($key, array($var, time(), $ttl), 0, $ttl);
		}
		return false;
	}
	
	function get($key) {
		$var = $this->_memcache->get($key);
		return is_array($var) ? $var[0] : null;
	}
	
	function delete($key) {
		return $this->_memcache->delete($key);
	}
	
	function clean() {
		return $this->_memcache->flush();
	}
	
	function cache_info($type = null) {
		return $this->_memcache->getStats();
	}
	
	function get_output($key) {
		$stored = $this->_memcache->get($key);
		if (count($stored) !== 3) {
			return false;
		}
		
		list($var, $time, $ttl) = $stored;
		
		return array(
			'expire' => $time + $ttl,
			'mtime'  => $time,
			'data'   => $var
		);
	}
	
	function setup_memcache() {
		if (FALSE != Base::load_config('memcache', 'data', false)) {
			if (isset($memconf) && 
				is_array($memconf) && sizeof($memconf) > 0) {
				$this->_mem_conf = null;
				foreach ($memconf AS $name => $conf) {
					$this->_mem_conf[$name] = $conf;
				}
				unset($memconf);
			}
		}

		$this->_memcache = extension_loaded('memcached') ? new Memcached : new Memcache;	
		foreach ($this->_mem_conf AS $name => $server) {
			if (!array_key_exists('hostname', $server)) {
				$server['hostname'] = $this->_mem_conf['default']['defaulthost'];
			}
			if (!array_key_exists('port', $server)) {
				$server['port'] = $this->_mem_conf['default']['defaultport'];
			}
			if (!array_key_exists('weight', $server)) {
				$server['weight'] = $this->_mem_conf['default']['defaultweight'];
			}
			
			if (extension_loaded('memcached')) {
				$this->_memcache->addServer(
					$server['hostname'], $server['port'], $server['weight']
				);
			} else {
				$this->_memcache->addServer(
					$server['hostname'], $server['port'], true, $server['weight']
				);
			}
		}
	}
	
	function is_supported() {
		return !extension_loaded('memcached') && !extension_loaded('memcache') ? false : true;
	}
}