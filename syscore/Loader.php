<?PHP
class Loader {
		
	private $_loaded = array();
	
	function __construct() {
		;
	}
	
	function init() {
		$this->_loaded =& Base::loaded();
		
		return $this;
	}

	function file($file, $part = 'data') {
		if (is_array($file)) {
			foreach ($file AS $val) {
				$this->file($val, $part);
			}
			return;
		}
		
		if ('' == $file) {
			return;
		}
	
		Base::load_config($file, $part, false);
	}
	
	function get_config($file, $part = 'data', $key = null) {
		$return = array();
		if (is_array($file)) {
			foreach ($file AS $val) {
				$this->get_config($val, $part, $key);
			}
			return;
		}
		
		$C =& get_instance();
		if ('' == $file || 
			isset($return[$file]) || 
			isset($C->config[$file])) {
			return;
		}
		
		$return[$file] =& Base::load_config($file, $part, true, $key);
		
		return $return;
	}
	
	function config($file, $part = 'data', $key = null) {
		$args = func_get_args();
		$total = func_num_args();
		
		if ($total != sizeof($args)) {
			$args = array_merge($args, array_fill(sizeof($args), $total - sizeof($args), ''));
		}
	
		$C =& get_instance();
		$C->config =& $this->get_config($args[0], $args[1], $args[2]);
		unset($args, $total);
	}
	
	function store($class, $part = null, $args = null, $alias = null) {
		if (is_array($class)) {
			foreach ($class AS $val) {
				$this->store($val, $part, $args, $alias);
			}
			return;
		}
		
		$class = strtolower($class);
		if ($class == '' || 
			isset($this->_loaded[$class])) {
			return;
		}
		
		if (false !== strripos($class, 'class')) {
			$class = str_replace(array('class', 'Class'), '', $class);
		}
		
		$C =& get_instance();
		if ($alias != '') {
			$class = $alias;
		}
		
		if (isset($C->$class)) {
			return;
		}
		
		$C->$class =& Base::load_class(
				ucfirst($class), $part, $args, $alias
			);
	}
	
	function model($model, $base = null) {
		if (is_array($model)) {
			foreach ($model AS $val) {
				$this->model($val, $base);
			}
			return;
		}
		
		if ($model == '' || 
			isset($_ENV[$model])) {
			return;
		}
		
		if ('Model' === strtoupper(substr($model, -5))) {
			$model = preg_replace('/Model$/i', '', $model);
		}
		
		if ($base !== null && !is_object($base)) {
			return;
		}
	
		Base::load_model($model, $base);
	}
}