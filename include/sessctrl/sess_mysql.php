<?PHP
class sess_mysql {
	
	private $ctrl, $lifetime;
	
	function __construct(&$ctrl, $lifetime = 1800) {
		$this->ctrl =& $ctrl;
		// get_cfg_var('session.gc_maxlifetime'); 
		// ini_get('session.gc_maxlifetime')
		$this->lifetime = $lifetime;
		session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), 
			array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc'));
		session_start();
	}
	
	function open($savepath, $sessname) {
		return true;
	}
	
	function close() {
		return $this->gc($this->lifetime);
	}
	
	function read($sessid) {
		$sql = sprintf("SELECT data FROM %s WHERE session_id = '%s' LIMIT 1", 
				mysql_real_escape_string($this->ctrl->db->table('sessions')), 
				mysql_real_escape_string($sessid));

		$row = $this->ctrl->db->getRow($sql);
		
		return $row ? $row['data'] : '';
	}
	
	function write($sessid, $data) {
		// sprintf("%s", $sql);
		$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
		
		$sess = $this->ctrl->db->getRow("SELECT data FROM 
			".$this->ctrl->db->table('sessions')." WHERE session_id = ".$sessid." LIMIT 1");
			
		$sessdata = array(
			'user_id'	 => $user_id,
			'ip'		 => $this->ctrl->settings['ip'],
			'useragent'  => $_SERVER['HTTP_USER_AGENT'],
			'lastvisit'	 => $this->ctrl->settings['time'],
			'data'		 => $data
		);
		if (!empty($sess)) {
			return $this->ctrl->db->autoExecute('sessions', $sessdata, 'UPDATE', "session_id='$sessid'");
		} else {
			$sessdata['session_id'] = $sessid;
			
			return $this->ctrl->db->autoExecute('sessions', $sessdata, 'INSERT');
		}
	}
	
	function destroy($sessid) {
		$sql = sprintf("DELETE FROM %s WHERE session_id = '%s'", 
					mysql_real_escape_string($this->ctrl->db->table('sessions')),
					mysql_real_escape_string($sessid));

		return $this->ctrl->db->query($sql);
	}
	
	function gc($maxlifetime) {
		$expiretime = $this->ctrl->settings['time'] - $maxlifetime;
		
		$sql = "DELETE FROM ".$this->ctrl->db->table('sessions')." 
			WHERE `lastvisit` < $expiretime";
		
		return $this->ctrl->db->query($sql);
	}
}