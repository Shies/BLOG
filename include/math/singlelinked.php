<?PHP
/* *
 *	比较流行的两个链表例子就是栈和队列 
 */
header('Content-type: text/html; charset=utf-8');

# 链表节点 #
class node {
	
	public $id, $name, $next;
	
	function __construct($id, $name) {
		$this->id = $id;
		$this->name = $name;
		$this->next = null;
	}
}

# 单向链表 #
class singlelinked {
	
	private $header; // 链表头结点
	
	function __construct($id = null, $name = null) {
		$this->header = new node($id, $name);
	}
	
	// 获取链表长度
	function get_link_len() {
		$i = 0;
		$current = $this->header;
		while (NULL != $current->next) {
			$i ++;
			$current = $current->next;
		}
		
		return $i;
	}
	
	// 添加节点数据
	function append($node) {
		$current = $this->header;		
		while (NULL != $current->next) {
			if ($current->next->id > $node->id) {
				break;
			}
			$current = $current->next;
		}
		$node->next = $current->next;
		$current->next = $node;
	}
	
	// 删除链接节点
	function remove($id) {
		$current = $this->header;
		$flag = false;
		while (NULL != $current->next) {
			if ($current->next->id == $id) {
				$flag = true;
				break;
			}
			$current = $current->next;
		}
		
		if ($flag) {
			$current->next = $current->next->next;
		} else {
			echo '未找到id='.$id.'的节点';
			return;
		}
	}
	
	// 获取链表
	function get_link_list() {
		$current = $this->header;
		if (NULL == $current->next) {
			die('link\'s null');
		}
		
		while (NULL != $current->next) {
			echo 'id:'.$current->next->id." name:".$current->next->name."<br />";
			if ($current->next->next == NULL) {
				break;
			}
			$current = $current->next;
		}
	}
	
	// 获取节点名称
	function get_link_name_by_id($id) {
		$current = $this->header;
		if (NULL == $current->next) {
			die('link\'s null'); 
		}
		
		while (NULL != $current->next) {
			if ($current->id == $id) {
				break;
			}
			$current = $current->next;
		}
		
		return $current->name;
	}
	
	// 更新节点名称
	function set_link_name_by_id($name, $id) {
		$current = $this->header;
		if (NULL == $current->next) {
			die('link\'s null');
		}
		
		while (NULL != $current->next) {
			if ($current->id == $id) {
				break;
			}
			$current = $current->next;
		}
		
		return $current->name = $name;
	}
}

$list = new singlelinked;
$list->append(new node(2, 'a'));
$list->append(new node(1, 'b'));
$list->append(new node(3, 'c'));
$list->get_link_list();
echo '<br /> ------------------ 删除节点 --------------- <br />';
$list->remove(1);
$list->get_link_list();
echo '<br /> ------------------ 更新节点 --------------- <br />';
$list->set_link_name_by_id('dddddddddddddddddd', 2);
$list->get_link_list();
echo '<br /> ------------------ 节点名称 --------------- <br />';
echo $list->get_link_name_by_id(3);
echo '<br /> ------------------ 节点长度 --------------- <br />';
echo $list->get_link_len();