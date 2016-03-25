<?PHP
// 让Model数据层保持和Controller逻辑层一致
class Model {
	
	
	function __construct() {
		; # initing .... #
	}
	
	function __get($name) {
		$C =& get_instance();
		return $C->$name;
	}
}