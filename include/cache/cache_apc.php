<?PHP
// PHP使用apc为include大文件数组提速, 或者提出小量数据提速等等
class cache_apc {

	function __construct() {
		if (!$this->is_supported()) exit(-1);
	}

	function save($key, $var, $ttl = 300) {
		return apc_store($key, array($var, time(), $ttl), $ttl);
	}
	
	function get($key) {
		$var = apc_fetch($key);
		return is_array($var) ? $var[0] : false;
	}
	
	function delete($key) {
		return apc_delete($key);
	}
	
	function clean() {
		return apc_clear_cache('user');
	}
	
	function cache_info($type = null) {
		return apc_cache_info($type);
	}
	
	function get_output($key) {
		$stored = apc_fetch($key);
		if (count($stored) !== 3) {
			return false;
		}
		
		list($var, $time, $ttl) = $stored;
		
		return array(
			'expire' => $time + $ttl,
			'mtime'	 => $time,
			'data'	 => $var
		);
	}
	
	function is_supported() {
		return !extension_loaded('apc') || ini_get('apc.enabled') != "1" ? false : true;
	}
}