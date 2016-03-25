<?PHP
/* *
 * 链表元素结点类
 */
class Node {
	
	public $pre  = NULL; // 前驱
	public $next = NULL; // 后继
	public $key  = NULL; // 元素键值
	public $val  = NULL; // 结点值
	
	function __construct($key, $val) {
		$this->key = $key;
		$this->val = $val;
	}
}
 
/**
 * 双向链表类
 */
class doublelinked {
	
	private $head; // 头指针
	private $tail; // 尾指针
	private $current; // 当前指针
	private $length;  // 链表长度
	
	function __construct() {
		$this->head = self::_getNode(null, null);
		$this->current = $this->head;
		$this->tail = $this->head;
		$this->length = 0;
	}
 
	/* *
	 * @ desc: 读取链表全部结点
	 */
	public function readAll() {
		$tmp = $this->head;
		while ($tmp->next !== null) {
			$tmp = $tmp->next;
			echo $tmp->key, $tmp->val . '<br />';
		}
	}
	
	/* *
	 *	交换两个位置的key和val
	 */
	public function move($pos1, $pos2) {
		$pos1Node = $this->find_by_pos($pos1);
		$pos2Node = $this->find_by_pos($pos2);
		
		if ($pos1Node === NULL 
			OR $pos2Node === NULL) {
			return false;
		}
		
		$tmpKey = $pos1Node->key;
		$tmpData = $pos1Node->val;
		$pos1Node->key = $pos2Node->key;
		$pos1Node->val = $pos2Node->val;
		$pos2Node->key = $tmpKey;
		$pos2Node->val = $tmpData;
		
		return true;
	}
 
	/* *
	 * @ desc: 在指定关键词删除结点
	 *
	 * @param : $key
	 * 指定位置的链表元素key
	 */
	public function remove_by_id($key) {
		$pos = $this->find_by_id($key);
		if (NULL === $pos) {
			return true;
		}
		
		!isset($tmp) && $tmp = $pos;
		$flag = true;
		// 当重复元素中有某个是最后一个指针的时候, 
		// 删除不掉, 此BUG暂未搞定
		while ($tmp->next !== null
			AND $tmp->next->key === $key) {
			$tmp = $tmp->next;
			if (!$flag) {
				$this->release('');
			}
			$flag = false;
		}
		
		if ($tmp->next) {
			$pos->pre->next = $tmp->next;
			$tmp->next->pre = $pos->pre;
		} else {
			$pos->pre->next = null;
		}
		
		$this->release(array($pos, $tmp));
	}
	
	/* *
	 * @ desc: 在指定位置删除结点
	 *
	 * @param : $key
	 *        	指定位置的链表元素key
	 */
	public function remove_by_pos($pos) {
		$tmp = $this->find_by_pos($pos);
		if ($tmp === null) {
			return true;
		}
		
		if ($tmp === $this->getTail()) {
			$tmp->pre->next = null;
			$this->release($tmp);
			return true;
		}
		
		$tmp->pre->next = $tmp->next;
		$tmp->next->pre = $tmp->pre;
		$this->release($tmp);
	}
 
	/**
	 * @ desc: 在指定键值之前插入结点
	 *
	 * @param : $key
	 *        	//指定位置的链表元素key
	 * @param : $data
	 *        	//要插入的链表元素数据
	 * @param : $flag
	 *        	//是否顺序查找位置进行插入
	 */
	function append_by_id($key, $val, $flag = 1) {
		$newNode = self::_getNode($key, $val);
		$tmp = $this->find_by_id($key, $flag);
		$this->length ++;
		
		if ($tmp !== null) {
			$newNode->pre = $tmp->pre;
			$newNode->next = $tmp;
 
			$tmp->pre = $newNode;
			$newNode->pre->next = $newNode;
		} else {
			$newNode->pre = $this->tail;
			$this->tail->next = $newNode;
			$this->tail = $newNode;
		}
	}
	/**
	 * @ desc: 在指定位置之前插入结点
	 *
	 * @param : $pos
	 *        	指定插入链表的位置
	 * @param : $key
	 *        	指定位置的链表元素key
	 * @param : $data
	 *        	要插入的链表元素数据
	 */
	function append_by_pos($pos, $key, $val) {
		$newNode = self::_getNode($key, $val);
		$tmp = $this->find_by_pos($pos);
		$this->length ++;
		
		if ($tmp !== null) {
			$newNode->pre = $tmp->pre;
			$newNode->next = $tmp;
 
			$tmp->pre = $newNode;
			$newNode->pre->next = $newNode;
		} else {
			$newNode->pre = $this->tail;
			$this->tail->next = $newNode;
			$this->tail = $newNode;
		}
	}
	/**
	 * @ desc: 根据key值查询指定位置数据
	 *
	 * @param : $key
	 *        	//指定位置的链表元素key
	 * @param : $flag
	 *        	//是否顺序查找
	 */
	function find_by_id($key, $flag = 1) {
		if ($flag) {
			$tmp = $this->head;
			while ($tmp->next !== null) {
				$tmp = $tmp->next;
				if ($tmp->key === $key) return $tmp;
			}
		} else {
			$tmp = $this->getTail();
			while ($tmp->pre !== null) {
				if ($tmp->key === $key) return $tmp;
				$tmp = $tmp->pre;
			}
		}
		
		return null;
	}
	/**
	 * @ desc: 根据位置查询指定位置数据
	 *
	 * @param : $pos
	 *        	//指定位置的链表元素key
	 */
	public function find_by_pos($pos) {
		if ($pos <= 0 || $pos > $this->length)
			return null;
		
		if ($pos < ($this->length / 2 + 1)) {
			$tmp = $this->head;
			$count = 0;
			while ( $tmp->next !== null ) {
				$tmp = $tmp->next;
				$count ++;
				if ($count === $pos) {
					return $tmp;
				}
			}
		} else {
			$tmp = $this->tail;
			$pos = $this->length - $pos + 1;
			$count = 1;
			while ( $tmp->pre !== null ) {
				if ($count === $pos) {
					return $tmp;
				}
				$tmp = $tmp->pre;
				$count ++;
			}
		}
		
		return null;
	}
	/**
	 * @ desc: 返回链表头节点
	 */
	public function getHead() {
		return $this->head->next;
	}
	/**
	 * @ desc: 返回链表尾节点
	 */
	public function getTail() {
		return $this->tail;
	}
	/**
	 * @ desc: 查询链表节点个数
	 */
	public function getLength() {
		return $this->length;
	}
	
	private static function _getNode($key, $val) {
		$newNode = new Node($key, $val);
		if (is_object($newNode)) {
			return $newNode;
		}
		exit("new node fail!");
	}
	
	private function release($node = null) {
		if ($node && is_array($node)) {
			foreach ($node AS $val) {
				$this->release($val);
			}
		} else {
			unset($node); 
			$this->length --;
		}
	}
}
 
$myList = new doublelinked ();
$myList->append_by_id ( 2, "test1" );
$myList->append_by_id ( 4, "test2" );
$myList->append_by_id ( "2b", "test2-b" );
$myList->append_by_id ( 2, "test2-c" );
$myList->append_by_id ( 1, "test3" );
$myList->append_by_pos( 5, "t", "testt" );
$myList->readAll();
exit;
echo "+++";
$myList->readAll();
echo "..." . $myList->getLength();
var_dump ( $myList->find_by_pos(5)->val);