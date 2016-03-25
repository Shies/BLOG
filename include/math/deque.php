<?PHP
class Deque {

	private $_deque = array();
	
	function addFirst($item) {
		return array_unshift($this->_deque, $item);
	}
	
	function addList($item) {
		return array_push($this->_deque, $item);
	}
	
	function removeFirst() {
		return array_shift($this->_deque);
	}
	
	function removeList() {
		return array_pop($this->_deque);
	}
}

$d = new Deque;

