<?PHP
class Mysqli {
				
	public $slaveid;
	public $querynum;
	
	public $link;
	public $debug;
	public $config;
	# 根据数据表决定部署到哪台服务器 #
	public $map;
	
	public $driver;
	public $version;
	public $table;
	public $curlink;
	
	function __construct(&$config) {
		$this->slaveid = $this->querynum = 0;
		$this->link = $this->debug = $this->config = $this->map = array();
		
		$this->config =& $config;
		if (!empty($this->config['map'])) {
			$this->map = $this->config['map'];
		}
		$this->table = PREFIX;
	}
	
	// 分布式数据库连接, 
	// 根据指定的serverid链接指定的db
	function connect($serverid = 1) {
		if (empty($this->config) || empty($this->config[$serverid])) {
			$this->halt('database config not found');
		}
		
		$this->link[$serverid] = $this->get_link(
			$this->config[$serverid]['dbhost'],
			$this->config[$serverid]['dbuser'],
			$this->config[$serverid]['dbpw'],
			$this->config[$serverid]['dbcharset'],
			$this->config[$serverid]['dbname'],
			$this->config[$serverid]['pconnect']
			);
		$this->curlink = $this->link[$serverid];
	}
	
	function get_link($dbhost, $dbuser, $dbpass, $dbname, $pconnect, $charset, $halt = true) {
		$link = new mysqli;
		if (!$link->real_connect($dbhost, $dbuser, $dbpass, $dbname, null, null, MYSQLI_CLIENT_COMPRESS)) {
			$halt && $this->halt('not connect', $this->errno);
		}
		
		$this->curlink = $link;
		if ($this->version() > '4.1') {
			$link->set_charset($charset ? $charset : RK_CHARSET);
			$setserver = $this->version() > '5.0.1' ? 'sql_mode=\'\'' : '';
			$setserver && $link->query("SET $setserver");
		}
		
		return $link;
	}
	
	function table_name($tablename) {
		if (!empty($this->map[$tablename])) {
			$id = $this->map[$tablename];
			if (!$this->link[$id]) {
				$this->connect($id);
			}
			$this->curlink = $this->link[$id];
		} else {
			$this->curlink = $this->link[1];
		}
	}
	
	function select_db($dbname) {
		return $this->curlink->select_db($dbname);
	}
	
	function fetch_array($query, $assoc = MYSQLI_ASSOC) {
		if ($assoc == 'MYSQL_ASSOC') {
			$assoc = MYSQLI_ASSOC;
		}
		return $query ? $query->fetch_array($assoc) : null; 
	}
	
	function fetch_frist($sql) {
		return $this->fetch_array($this->query($sql));
	}
	
	function result_frist($sql) {
		return $this->result($this->query($sql), 0);
	}
	
	function query($sql, $silent = false, $unbuffered = false) {
		if (defined('SQL_DEBUG') && SQL_DEBUG) {
			$starttime = $GLOBALS['_G']['starttime'];
		}
		
		if ('UNBUFFERED' == $silent) {
			$silent = false;
			$unbuffered = true;
		} elseif ('SILENT' == $silent) {
			$silent = true;
			$unbuffered = false;
		}
		
		$mode = $unbuffered ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT;
		if (!($query = $this->curlink->query($sql, $mode))) {
			if (in_array($this->errno(), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
				$this->connect();
				return $this->curlink->query($sql, 'RETRY'.$silent);
			}
			if (!$silent) {
				$this->halt($this->error(), $this->errno(), $sql);
			}
		}
		
		if (defined('SQL_DEBUG') && SQL_DEBUG) {
			$this->debug[] = array($sql, number_format((microtime(true) - $start_time), 6), debug_backtrace(), $this->curlink);
		}
		
		$this->querynum++;
		
		return $query;
	}
	
	function affected_rows() {
		return $this->curlink->affected_rows;
	}
	
	function error() {
		return $this->curlink ? $this->curlink->error : mysqli_error();
	}
	
	function errno() {
		return $this->curlink ? $this->curlink->errno : mysqli_errno();
	}
	
	function result($query, $row = 0) {
		if (!$query || $query->num_rows == 0) {
			return null;
		}
		$query->data_seek($row);
		$assocs = $query->fetch_row();
		
		return reset($assocs);
	}
	
	function num_rows($query) {
		return $query ? $query->num_rows : 0;
	}
	
	function num_fields($query) {
		return $query ? $query->field_count : 0;
	}
	
	function free_result($query) {
		return $query ? $query->free() : null;
	}
	
	function insert_id() {
		return ($id = $this->curlink->insert_id >= 0) ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	
	function fetch_row($query) {
		return $query ? $query->fetch_row() : null;
	}
	
	function fetch_fields($query) {
		return $query ? $query->fetch_field() : null;
	}
	
	function version() {
		if (empty($this->version)) {
			$this->version = $this->curlink->server_info();
		}
		return $this->version;
	}
	
	function escape_string($str) {
		return $this->curlink->escape_string($str);
	}
	
	function close() {
		return $this->curlink->close();
	}

	function halt($msg, $code = 0, $sql = '') {
		return new Exception($msg, $code, $sql);
	}
}