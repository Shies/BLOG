<?PHP
abstract class Object {
	
	private $_class = "# This's a abstract core class, function custom #";
	private $_title = "# Magic Function #";
	
	private $_define = "Sorry, {define} is readonly";
	private $_ifndef = "Sorry, {ifndef} not defined";
	
	public function __get($name) {
		$getter = 'get_'.$name;
		if (property_exists($this, $getter)) {
			return $this->$getter;
		} elseif (method_exists($this, $getter)) {
			return $this->$getter();
		}
		
		$proper = get_class($this)."->$name";
		$errmsg = str_replace('{ifndef}', $proper, $this->_ifndef);
		
		throw new Exception($errmsg);
	}
	
	public function __set($name, $value) {
		$setter = 'set_'.$name;
		if (property_exists($this, $setter)) {
			return $this->$setter = $value;
		} elseif (method_exists($this, $setter)) {
			return $this->$setter($value);
		}
		
		$proper = get_class($this)."->$name";
		if ($this->get_property($name)) {
			$errmsg = str_replace('{define}', $proper, $this->_define);
		} else {
			$errmsg = str_replace('{ifndef}', $proper, $this->_ifndef);
		}
		
		throw new Exception($errmsg);
	}
	
	public function get_property($value) {
		return method_exists($this, $value) || property_exists($this, $value);
	}
	
	public function __call($name, $arguments) {
		throw new Exception("Sorry, ".get_class($this)."->$name method not defined");
	}
	
	public function __toString() {
		return get_class($this);
	}
	
	public function __invoke() {
		return PHP_VERSION >= '5.3' ? get_class($this) : null;
	}
}