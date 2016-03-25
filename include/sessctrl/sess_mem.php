<?PHP
class sess_mem {

	private static $lifetime = 0;
	private static $mem;
	
	function __construct() {
		self::$mem =& Base::load_class("cache_memcache", "include/cache");
		ini_set('session.save_handle', 'user');
		session_set_save_handler(
			array('sess_mem', 'open'), array('sess_mem', 'close'), array('sess_mem', 'read'), array('sess_mem', 'write'), array('sess_mem', 'destroy'), array('sess_mem', 'gc'));
		session_start();
	}
	
	static function open() {
		self::$lifetime = ini_get('session.gc_maxlifetime');
		return true;
	}
	
	static function close() {
		return true;
	}
	
	static function read($sessid) {	
		return self::$mem->get($sessid);
	}
	
	static function write($sessid, $data) {
		return self::$mem->save($sessid, $data, self::$lifetime);
	}
	
	static function destroy($sessid) {
		return self::$mem->delete($sessid);
	}

	static function gc($maxlifetime) {
		return true;
	}
	
	function __destruct() {
		session_write_close();
	}
}