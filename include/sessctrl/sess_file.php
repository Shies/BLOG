<?PHP
// 当我们设置 ini_set('session.save_handler', 'files') AND session_save_path($path); 那么session的保存方式就是file模式, 一般情况下我们习惯性用mysql的存储方式.
class sess_file {
	
	private $savepath;

	function __construct($savepath = '') {
		// ini_set('session.save_handler', 'files');
		session_save_path($savepath);
		// 加强默认处理
		call_user_func(array(&$this, 'rewrite'));
	}
	
	function rewrite() {
		session_set_save_handler(
			array(&$this, 'open'), array(&$this, 'close'), 
			array(&$this, 'read'), array(&$this, 'write'), 
			array(&$this, 'destroy'), array(&$this, 'gc'));
		register_shutdown_function('session_write_close');
		session_start();
	}
	
	function open($savepath, $sessname) {
		$this->savepath = $savepath;
		if (!is_dir($this->savepath)) 
			mkdir($this->savepath, 0777, true);
		
		return true;
	}
	
	function close() {
		return true;
	}
	
	function read($id) {
		$file = "$this->savepath/sess_$id";
		if (function_exists('file_get_contents')) {
			return (String) @file_get_contents($file);
		}
		
		if (false !== ($fp = fopen($file, 'rb'))) {
			$return = fread($fp, filesize($file));
			fclose($fp);
			
			return $return;
		}
	}
	
	function write($id, $data) {
		$file = "$this->savepath/sess_$id";
		if (function_exists('file_put_contents')) {
			return file_put_contents($file, $data);
		}
		
		if (false !== ($fp = fopen($file, 'wb'))) {
			$return = fwrite($fp, $data);
			fclose($fp);
			
			return $return;
		}
	}
	
	function destroy($id) {
		$file = "$this->savepath/sess_$id";
		if (file_exists($file)) @unlink($file);
		
		return true;
	}
	
	function gc($maxlifetime) {
		foreach (glob("$this->savepath/sess_*") AS $file) {
			if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
				@unlink($file);
			}
		}
		return true;
	}
}
// new sess_file(dirname(__FILE__).'/session');