<?PHP
class tree { # Discuz!X此类主要针对前台home评论展示这块 #
	
	// 存放的树形数据结构
	public $data    = array();
	// 根据父节点返回子集
	public $child   = array(-1 => array());
	// 树形的层次样式结构
	public $layer   = array(-1 => -1);
	// 存放树形结构的父节点
	public $parent  = array();
	// 记录当前指定ID的临时变量
	public $countid = 0;
	
	function __construct() {
		# TODO #
	}
	
	// 第一步必须设置节点
	function set_node($id, $parent, $value) {
		$parent = $parent ? $parent : 0;
		
		$this->data[$id] = $value;
		$this->child[$parent][] = $id;
		$this->parent[$id] = $parent;
		
		if (isset($this->layer[$parent])) {
			$this->layer[$id] = $this->layer[$parent] + 1;
		} else {
			$this->layer[$id] = 0;
		}
	}
	
	// 用root当作父节点去控制返回root下所有子节点
	function get_list(&$tree, $root = 0) {
		foreach ($this->child[$root] AS $key => $val) {
			$tree[] = $val;
			if ($this->child[$val]) {
				$this->get_list($tree, $val);
			}
		}
	}
	
	// 根据指定节点ID去获取指定节点ID数据
	function get_value($id) {
		return $this->data[$id];
	}
	
	// 根据指定ID递归得到顶级父节点, 利用递归层级来设置指定ID的样式
	function reset_layer($id) {
		if ($this->parent[$id]) {
			$this->layer[$this->countid] = $this->layer[$this->countid] + 1;
			$this->reset_layer($this->parent[$id]);
		}
	}
	
	// 根据指定ID获取自身样式
	function get_layer($id, $space = false) {
		$this->layer[$id] = 0;
		$this->countid = $id;
		$this->reset_layer($id);
		return $space ? str_repeat($space, $this->layer[$id]) : $this->layer[$id];
	}
	
	// 根据指定数据ID得到父节点
	function get_parent($id) {
		return $this->parent[$id];
	}
	
	// 根据指定ID循环得到顶级父节点, 然后获取每次循环得到的ID，用此ID对应的样式的值作为parent的键, 此ID作为parent的值, 最后返回parent(包含指定ID的所有上级父节点)
	function get_parents($id) {
		$parent = array();
		while ($this->parent[$id] != -1) {
			$id = $parent[$this->layer[$id]] = $this->parent[$id]; 
		}
		ksort($parent);
		reset($parent);
		
		return $parent;
	}
	
	// 用ID作为父节点去控制返回子节点所有ID
	function get_child($id) {
		return $this->child[$id];
	}
	
	// 用ID当作父节点去控制返回ID下所有子节点
	function get_childs($id = 0) {
		$child = array();
		$this->get_list($child, $id);
		
		return $child;
	}
	
}