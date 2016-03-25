<?PHP
// 让Controller逻辑层保持和Model数据层一致
class Controller {
	
	// Retrun This Class Is Instance
	private static $_instance;
	
	function __construct() {
		self::$_instance =& $this;
		
		// 实例化Controller之前, 将系统已经初始化的app赋给当前指针, 
		foreach (Base::loaded() AS $var => $class) {
			$this->$var =& Base::load_class($class);
		}
		// 懒加载类库
		$this->load =& Base::load_class('Loader', 'syscore');
		// 实例化Controller之后, 初始化公共部分
		$this->load->init();
	}
	
	static function &get_instance() {
		return self::$_instance;
	}
}