<?PHP
// 我们可以把一些不经常变动的数组，或者从DB获取的数据存进文件，减少数据库负载
class cache_file {
	
	protected $_setting = array(
				'lockex' => 1,
				'sufix'  => '.cache.php', // 缓存文件后缀
				'dtype'  => 'json_encode' // 可能还存在serialize/base64_encode/json_encode/string/null等等
			);
	
	protected $cachepath = './data/caches/';
	
	function __construct($setting = '') {
		if (!$this->is_supported()) {
			exit(-1);
		}
		$this->get_setting($setting);
	}
	
	function save($key, $var, $ttl = 300, $cachepath = '', $setting = '') {
		$this->get_setting($setting);
		
		$cachename = $this->get_cachename($key, $cachepath);
		$contents = array(
				'time'		=> time(),
				'ttl'		=> $ttl,			
				'data'		=> $var
			);
		if ($this->_setting['dtype'] == 'array') {
			$contents['data'] = var_export($var, true);
		} elseif ($this->_setting['dtype'] == 'serialize') {
			$contents['data'] = serialize($var);
		} elseif ($this->_setting['dtype'] == 'base64_encode') {
			$contents['data'] = base64_encode($var);
		} elseif ($this->_setting['dtype'] == 'json_encode') {
			$contents['data'] = json_encode($var);
		} else {
			$contents['data'] = implode(',', $var);
		}
		
		if ($this->_setting['lockex']) {
			$res = file_put_contents($cachename, serialize($contents), LOCK_EX);
		} else {
			$res = file_put_contents($cachename, serialize($contents));
		}
		
		if ($res) {
			@chmod($cachename, 0777);
			return true;
		}
		return $res;
	}
	
	function get($key, $cachepath = '', $setting = '') {
		$this->get_setting($setting);
		
		$cachename = $this->get_cachename($key, $cachepath);
		if (!file_exists($cachename)) {
			return false;
		}
		$contents = @unserialize(file_get_contents($cachename));
		if ($this->_setting['dtype'] == 'serialize') {
			$data = @unserialize($contents['data']);
		} elseif ($this->_setting['dtype'] == 'base64_encode') {
			$data = base64_decode($contents['data']);
		} elseif ($this->_setting['dtype'] == 'json_encode') {
			$data = json_decode($contents['data'], true);
		} else {
			$data = $contents['data'];
		}
		
		if (time() > $contents['time'] + $contents['ttl']) {
			@unlink($cachename);
			return false;
		}
		return $data;
	}
	
	function delete($key, $cachepath = '', $setting = '') {
		$this->get_setting($setting);
		
		$cachename = $this->get_cachename($key, $cachepath);
		if (file_exists($cachename)) {
			return @unlink($cachename);
		}
		return false;
	}
	
	function clean($cachepath, $isself = false, $level = 0) {
		$cachepath = rtrim($cachepath, '/');
		
		$handle = opendir($cachepath);
		while (false !== ($filename = readdir($handle))) {
			if ($filename != '.' && $filename != '..') {
				$_path = $cachepath.'/'.$filename;
				if (is_dir($_path)) {
					if ($filename{0} != '.') {
						$this->clean($_path, $isself, $level + 1);
					}
				} else {
					@unlink($_path);
				}
			}
		}
		closedir($handle);
		
		if ($isself && $level > 0) return @rmdir($path);
		return true;
	}
	
	function cache_info($key, $cachepath = '', $setting = '') {
		$this->get_setting($setting);
		
		$res = array();
		$cachename = $this->get_cachename($key, $cachepath);
		if (!file_exists($cachename)) {
			return false;
		} else {
			$res['filename'] = $key.$this->_setting['sufix'];
			$res['filepath'] = $this->cachepath;
			$res['fileatime'] = fileatime($cachename); // 上次写入时间
			$res['filectime'] = filectime($cachename); // 上次修改时间
			$res['filemtime'] = filemtime($cachename); // 写入时间
			$res['filesize'] = filesize($cachename);
			
			return $res;
		}
	}
	
	function get_output($key, $cachepath = '', $setting = '') {
		$this->get_setting($setting);
		
		$cachename = $this->get_cachename($key, $cachepath);
		if (!file_exists($cachename)) {
			return false;
		}
		$contents = @unserialize(file_get_contents($cachename));
		if (is_array($contents)) {
			if ($this->_setting['sufix'] == 'serialize') {
				$data = unserialize($contents['data']);
			} elseif ($this->_setting['sufix'] == 'base64_encode') {
				$data = base64_decode($contents['data']);
			} elseif ($this->_setting['sufix'] == 'json_encode') {
				$data = json_decode($contents['data']);
			} else {
				$data = $contents['data'];
			}
			$mtime = filemtime($cachename);
			if (!isset($contents['ttl'])) {
				return false;
			}
			return array(
				'expire' => $mtime + $contents['ttl'],
				'mtime'  => $mtime,
				'data'   => $data
			);
		}
		return false;
	}
	
	function get_cachename($key, $cachepath = '') {
		if ($cachepath) {
			$this->cachepath = array_merge($this->cachepath, $cachepath);
		}
		
		if (!is_dir($this->cachepath)) {
			@mkdir($this->cachepath, 0777, true);
		}
		
		$filename  = $key . $this->_setting['sufix'];
		
		return $this->cachepath.$filename;
	}
	
	function get_setting($setting = '') {
		if ($setting) {
			$this->_setting = array_merge($this->_setting, $setting);
		}
	}
	
	function is_supported() {
		if (!is_dir($this->cachepath)) {
			@mkdir($this->cachepath, 0777, true);
		}
		return is_writable($this->cachepath);
	}
}