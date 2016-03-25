<?PHP
class GoodsModel extends Model {
	
	function __construct() {
		parent::__construct();
	}
	
	function get_goods_count($where = '') {
		$where = empty($where) ? '1' : $where;
		
		$sql = "SELECT COUNT(*) AS total FROM 
			". $this->db->table('goods') ." WHERE " . $where;
		
		return $this->db->getOne($sql);
	}
	
	function get_goods_list($arr = array(), $where = '') {		
		extract($arr);
		
		if (isset($offset) && isset($num)) {
			$limit = "LIMIT $offset, $num";
		} else {
			$limit = "LIMIT 10";
		}
		$where = empty($where) ? '1' : $where;
		
		$sql = "SELECT * FROM ". 
			$this->db->table('goods') ." 
			WHERE ".$where." ORDER BY goods_id DESC ".$limit;
		$res = $this->db->getAll($sql);
		if (!$res) {
			return false;
		}
		
		foreach ($res AS $key => &$row) {
			$row['goods_name'] = stripslashes($row['goods_name']);
			$row['goods_sn'] = stripslashes($row['goods_sn']);
			$row['goods_number'] = intval($row['goods_number']);
			$row['goods_desc'] = trim($row['goods_desc']);
			$row['shop_price'] = floatval($row['shop_price']);
			$row['is_on_sale'] = intval($row['is_on_sale']);
			$row['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
		}
		
		return $res;
	}
	
	function get_goods_info($goods_id = 0) {
		$goods_id = intval($goods_id);
		
		$sql = "SELECT * FROM ". $this->db->table('goods') ." WHERE goods_id = '$goods_id' LIMIT 1";
		$row = $this->db->getRow($sql);
	
		return $row;
	}
	
	function get_goods_sn($goods_sn = false) {
		$goods_sn = trim($goods_sn);
		
		$sql = "SELECT goods_id FROM ".$this->db->table('goods')." WHERE goods_sn = '$goods_sn' LIMIT 1";
		$row = $this->db->getOne($sql);
		
		return $row;
	}
}
?>