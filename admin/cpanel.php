<?PHP
ini_set("memory_limit", -1);
ini_set("display_error", 1);
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Asia/Shanghai');
require(dirname(__FILE__) . '/../include/mysql.php'); # DB配置、增删查改 #

abstract class abstractcpanel {

	# 删除商品信息抽象函数 #
	abstract function delete();
	# 更新商品信息抽象函数 #
	abstract function update();
	# 插入商品信息抽象函数 #
	abstract function insert();
	# 获取商品列表抽象函数 #
	abstract function get_goods_list();
}

class cpanel extends abstractcpanel {

	private $db;
	
	function __construct() {
		$this->db =& Mysql::get_instance();
	}

	function get_goods_list() {
		$data =& $_GET;
		if (!$data) {
			$data = array();
		}
		$data = $this->addslashes_deep($data);
		unset($_GET);
		
		$goodsname = isset($data['goodsname']) ? trim($data['goodsname']) : '';
		$page = isset($data['page']) ? intval($data['page']) : 1;
		
		$where = '1';
		
		if ($goodsname != '') {
			$where .= " AND goods_name = '".$goodsname."' ";
		}
	
		$query_string = "goods.php?goodsname=" . $goodsname;
		
		$count = $this->db->goodscount($where);
		$pager = $this->pager($count, $page, 15, $query_string);
		
		$offset = isset($pager['offset']) ? intval($pager['offset']) : 0;
		$goodsl = $this->db->goodslist(array('offset' => $offset, 'num' => '15'), $where);

		$adv_val = array(
			'goodsname' => $goodsname,
		);

		return array($goodsl, $pager, $adv_val);
	}
	
	function insert() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data =& $_POST;
			if (!$data) {
				return;
			}
			$data = $this->addslashes_deep($data);
			unset($_POST);
			
			$goods_name = isset($data['goods_name']) ? trim($data['goods_name']) : '';
			$goods_sn = isset($data['goods_sn']) ? trim($data['goods_sn']) : '';
			$shop_price = isset($data['shop_price']) ? floatval($data['shop_price']) : 0;
			
			$goods_number = isset($data['goods_number']) ? intval($data['goods_number']) : 0;
			$goods_desc = isset($data['goods_desc']) ? htmlspecialchars(trim($data['goods_desc'])) : '';
		
			if (empty($goods_name)) {
				die('商品名称不能为空');
			}
			
			if (empty($goods_sn)) {
				die('商品货号不能为空');
			}
			
			if (max($shop_price, 0) == 0) {
				die('商品价格不能为0');
			}
			
			if (max($goods_number, 0) == 0) {
				die('商品库存不能为0');
			}
			
			$goods = array(
				'goods_name' => $goods_name,
				'goods_sn'   => $goods_sn,
				'shop_price' => $shop_price,
				'goods_number' => $goods_number,
				'goods_desc' => $goods_desc,
				'is_on_sale' => 1,
				'add_time'	 => time()
			);
			$insert_id = $this->db->insertgoods($goods);
			if ($insert_id > 0) {
				die('添加成功');
			}
		}
	}
	
	function update() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$data =& $_POST;
			if (!$data) {
				return;
			}
			$data = $this->addslashes_deep($data);
			unset($_POST);
			
			$goods_id = isset($data['goods_id']) ? intval($data['goods_id']) : 0;
			$goods_name = isset($data['goods_name']) ? trim($data['goods_name']) : '';
			$goods_sn = isset($data['goods_sn']) ? trim($data['goods_sn']) : '';
			
			$shop_price = isset($data['shop_price']) ? floatval($data['shop_price']) : 0;
			
			$goods_number = isset($data['goods_number']) ? intval($data['goods_number']) : 0;
			$goods_desc = isset($data['goods_desc']) ? htmlspecialchars(trim($data['goods_desc'])) : '';
		
			if ($goods_id <= 0) {
				die('编辑的产品不存在');
			}
		
			if (empty($goods_name)) {
				die('商品名称不能为空');
			}
			
			if (empty($goods_sn)) {
				die('商品货号不能为空');
			}
			
			if (max($shop_price, 0) == 0) {
				die('商品价格不能为0');
			}
			
			if (max($goods_number, 0) == 0) {
				die('商品库存不能为0');
			}
			
			$goods = array(
				'goods_name' => $goods_name,
				'goods_sn'   => $goods_sn,
				'shop_price' => $shop_price,
				'goods_number' => $goods_number,
				'goods_desc' => $goods_desc
			);
			$affected_rows = $this->db->updategoods($goods_id, $goods);
			if ($affected_rows > 0) {
				die('更新成功');
			}
		} else {
			$data =& $_GET;
			if (!$data) {
				return;
			}
			$data = $this->addslashes_deep($data);
			unset($_GET);
	
			$goods_id = isset($data['id']) ? intval($data['id']) : '';
			if ($goods_id <= 0) {
				die('商品编号非法');
			}
		
			$goods = $this->db->goodsinfo($goods_id);
		
			return $goods;
		}
	}
	
	function delete() {
		$data =& $_GET;
		if (!$data) {
			return;
		}
		$data = $this->addslashes_deep($data);
		unset($_GET);
	
		$goods_id = isset($data['id']) ? intval($data['id']) : '';
		
		if ($goods_id <= 0) {
			die('商品编号非法');
		}
			
		if ($this->db->deletegoods($goods_id)) {
			die('删除成功');
		}
		die('删除失败');
	}
	
	function pager($total, $page, $pagesize, $uri) {
		$total    = $total ? intval($total) : 0;
		if ($total <= 0) return false;
		$pagesize = max(1, intval($pagesize));
		$uri      .= strpos($uri, '?') !== false ? '&' : '?';
		
		$page_and_start = '';
		$page_count = $total > 0 ? @ceil($total / $pagesize) : 0;
		$page 		= max(1, min($page_count, intval($page)));
		$offset     = $page > $page_count ? 
				($page_count - 1) * $pagesize : 
			($page - 1) * $pagesize;
		if ($page - 1 > 0) {
			$page_and_start .= " <a href=" . $uri . "page=" . ($page - 1) . ">上一页</a> ";
		} else {
			$page_and_start .= " <a href='javascript:;'>上一页</a> ";
		}
		if ($page >= 9) {
			$page_and_start .= " <a href=" . $uri . "page=1>1</a> " . "...";
		}
		if ($page > 0 && $page < 9) {
			$from_start = 1;
			$to_end 	= ($page_count < 9) ? $page_count : 9;
		}
		elseif ($page >= 9 && $page_count - 8 >= $page) {
			$from_start = ($offset - 2) < 1 ? 1 : ($offset - 2);
			$to_end 	= ($offset + 4) > $page_count ? $page_count : ($offset + 4);
		}
		else {
			$from_start = $page_count - 8;
			$to_end 	= $page_count;
		}
		for ($i = $from_start; $i <= $to_end; $i ++) {
			if ($i == $page) {
				$page_and_start .= " <a href=" . $uri . "page=$i><strong>$i</strong></a> ";
			} else {
				$page_and_start .= " <a href=" . $uri . "page=$i>$i</a> ";
			}
		}
		if ($page_count - 8 >= $page) {
			$page_and_start .= "..." . " <a href=" . $uri . "page=" . $page_count . ">" . $page_count . "</a> ";
		}
		if ($page + 1 <= $page_count) {
			$page_and_start .= " <a href=" . $uri . "page=" . ($page + 1) . ">下一页</a> ";
		} else {
			$page_and_start .= " <a href='javascript:;'>下一页</a> ";
		}
		
		return array(
			'offset'     => $offset,
			'pager'      => $page_and_start,
		);
	}
	
	function addslashes_deep($value) {
		$value = is_array($value) ?
					array_map(array($this, 'addslashes_deep'), $value) :
					addslashes($value);

		return $value;
	}
}

$cp = new cpanel;
$script_name = basename($_SERVER['SCRIPT_NAME']);
if (empty($_REQUEST['act'])) {
	if (strcasecmp($script_name, 'goods.php') == 0) {
		$_REQUEST['act'] = 'lists';
	} else {
		die('Your run\'s filename invaild');
	}
}

if ($_REQUEST['act'] == 'lists') {
	$goods = $cp->get_goods_list();
}

elseif ($_REQUEST['act'] == 'add') {
	$cp->insert();
}

elseif ($_REQUEST['act'] == 'edit') {
	$goods = $cp->update();
}

elseif ($_REQUEST['act'] == 'dele') {
	$cp->delete();
}
?>