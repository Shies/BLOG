<?PHP

// 定义全局路径
if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', dirname(__FILE__).'/');
}

// 单一入口
if (include(ROOT_PATH.'syscore/Base.php')) {
	// 运行Application
	class_exists('Base') AND Base::run();
}

?>