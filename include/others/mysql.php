<?PHP
class Mysql {
	
	private static $_instance;
	private $conn;
	
	private function __construct() {
		$this->conn = @mysql_connect('127.0.0.1', 'root', 'root');
		if (!$this->conn) {
			die('Could not connect:'.mysql_error());
		}
		$db_selected = mysql_select_db('openfire', $this->conn);
		if (!$db_selected) {
			die ("Can\'t use test_db:".mysql_error());
		}
		@mysql_query('SET NAMES UTF8');
	}
	
	public static function &get_instance() {
		if (!self::$_instance instanceof self) 
			self::$_instance = new self;
		
		return self::$_instance;
	}
	
	public function goodscount($where = '') {
		$where = empty($where) ? '1' : $where;
		
		$sql = "SELECT COUNT(*) AS total FROM ld_goods WHERE ".$where;
		$res = mysql_query($sql, $this->conn);
		$row = mysql_fetch_array($res);
		
		return $row['total'];
	}
	
	public function goodslist($arr = array(), $where = '') {
		if (isset($arr['offset']) && isset($arr['num'])) {
			$limit = "LIMIT {$arr['offset']}, {$arr['num']}";
		} else {
			$limit = "LIMIT 15";
		}
		$where = empty($where) ? '1' : $where;
		
		$lists = array();
		$sql = "SELECT * FROM ld_goods WHERE ".$where." ORDER BY goods_id DESC ".$limit;
		$res = mysql_query($sql, $this->conn);
		if (!is_resource($res)) {
			return $lists;
		}
		
		$tmp_index = 0;
		while ($row = mysql_fetch_assoc($res)) {
			$lists[$tmp_index]['goods_id'] = $row['goods_id'];
			$lists[$tmp_index]['goods_name'] = stripslashes($row['goods_name']);
			$lists[$tmp_index]['goods_sn'] = stripslashes($row['goods_sn']);
			$lists[$tmp_index]['goods_number'] = intval($row['goods_number']);
			$lists[$tmp_index]['goods_desc'] = trim($row['goods_desc']);
			$lists[$tmp_index]['shop_price'] = floatval($row['shop_price']);
			$lists[$tmp_index]['is_on_sale'] = intval($row['is_on_sale']);
			$lists[$tmp_index]['add_time'] = date('Y-m-d H:i:s', $row['add_time']);
			
			$tmp_index ++;
		}
		
		return $lists;
	}
	
	public function goodsinfo($goods_id) {
		$goods_id = intval($goods_id);
		$res = @mysql_query("SELECT * FROM ld_goods WHERE goods_id = '$goods_id' LIMIT 1", $this->conn);
		
		return mysql_fetch_array($res);
	}
	
	public function insertgoods($goods = array()) {
		$keys = array_keys($goods);
		$values = array_values($goods);
		
		@mysql_query("INSERT INTO ld_goods(".implode(',', $keys).") 
			VALUES('".implode("','", $values)."')", $this->conn);
			
		return mysql_insert_id();
	}
	
	public function updategoods($goods_id, $goods = array()) {
		$keys = array_keys($goods);
		$values = array_values($goods);
		
		$condi = '';
		foreach ($keys AS $k => $v) {
			$condi .= ($v . '=' . "'".$values[$k]."',");
		}
		$condi = rtrim($condi, ',');
		
		@mysql_query("UPDATE ld_goods SET ".$condi." WHERE goods_id = '$goods_id'", $this->conn);
		
		return mysql_affected_rows();
	}
	
	public function deletegoods($goods_id) {
		@mysql_query("DELETE FROM ld_goods WHERE goods_id = '$goods_id'", $this->conn);
		
		return mysql_affected_rows();
	}
	
	public function __destruct() {
		mysql_close($this->conn);
	}
	
	private function __clone() {}
}
?>