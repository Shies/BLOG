<?PHP
class PassportModel extends Model {
	
	function __construct() {
		parent::__construct();
	}
	
	function get_user_by_id($user_id) {
		static $users = array();
		
		if (empty($users[$user_id])) {
			$sql = "SELECT u.* FROM " . $this->db->table('users') . " AS u WHERE 
				u.user_id = " . $user_id . " LIMIT 1";
			$users[$user_id] = $this->db->getRow($sql);	
		}
		
		return $users[$user_id];
	}
	
	function get_user_by_name($username) {
		$username = trim($username);
		
		$sql = "SELECT user_id FROM " . $this->db->table('users')	. " WHERE 
				username = '".$username."' LIMIT 1";
		
		return $this->db->getOne($sql);
	}
	
	function get_user_info($username, $password) {
		$username = trim($username);
		
		$sql = "SELECT user_id, username, is_special FROM " . $this->db->table('users')	. " WHERE 
				username = '".$username."' AND password = '".$password."' LIMIT 1";
		
		return $this->db->getRow($sql);
	}
	
	function get_user_statis_info($user_id) {
		static $statis = array();
		
		if (empty($statis[$user_id])) {
			$sql = "SELECT * FROM " . $this->db->table('user_statis') . " WHERE 
				user_id = " . $user_id . " LIMIT 1";
			$statis[$user_id] = $this->db->getRow($sql);
		}
		
		return $statis[$user_id];
	}
		
	function save_visit($user_id) {
		return $this->db->query("UPDATE ".$this->db->table('users')." SET visit_count = visit_count + 1 
			WHERE user_id = " . $user_id. " LIMIT 1");
	}
}
?>