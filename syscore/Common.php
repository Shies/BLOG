<?PHP
# use strict #

function array2xml($arr, $htmlon = false, $level = 1) {
	$xml =& Base::load_class('Xml');
	return $xml->encode($arr, $htmlon, $level);
}

function xml2array($xml, $isnormal = false) {
	$xml =& Base::load_class('Xml');
	return $xml->decode($xml);
}

function json2str($code) {
	if (function_exists('json_encode')) {
		return json_encode($code);
	}
	
	$json =& Base::load_class('Json');
	return $json->encode($code);
}

function str2json($code, $assoc = false) {
	if (function_exists('json_decode')) {
		return json_decode($code, $assoc);
	}
	
	$json =& Base::load_class('Json');
	return $json->decode($code, $assoc);
}

function obj2arr($obj) {
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
	
	$arr = array();
	foreach ($_arr as $key => $val) {
		$arr[$key] = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
	}
	
	return $arr;
}

function get_files($path) {
	$handle = opendir($path);
	while (false !== ($file = readdir($handle))) {
		if ($file != '.' && $file != '..') {
			$_path = $path.'/'.$file;
			if (is_dir($_path)) {
				$files[$file] = get_files($_path);
			} else {
				$files[] = $file;
			}
		} else {
			continue;
		}
	}
	closedir($handle);
	
	return $files;
}

function rm_dir($path, $rmdir = false) {
	$handle = opendir($path);
	while (false !== ($file = readdir($handle))) {
		if ($file != '.' && $file != '..') {
			$_path = $path.'/'.$file;
			if (is_dir($_path)) {
				rm_dir($_path);
			} else {
				@unlink($_path);
			}
		}
	}
	closedir($handle);
	if (false !== $rmdir) @rmdir($path);

	return true;
}

function mk_dir($path) {
	return is_dir($path) || (mk_dir(dirname($path)) && mkdir($path, 0777) && chmod($path, 0777));
}

function dstrpos($string, $arr, $returnval = false) {
	if (empty($string)) {
		return false;
	}
	
	$return = '';
	foreach ((array) $arr AS $val) {
		if (strpos($string, $val) !== false) {
			$return = $returnval ? $val : true;
			break;
		}
	}
	
	return $return;
}

function diconv($value, $charset = 'utf-8') {
	if (is_array($value)) {
		foreach ($value AS $key => $val) {
			$value[$key] = diconv($val);
		}
	} else {
		$value = @iconv(RK_CHARSET, $charset, $value);	
	}
	
	return $value;
}

function daddslashes($string, $force = 0, $strip = FALSE) {
	if (!MAGIC_QUOTES_GPC || $force) {
		if (is_array($string)) {
			foreach ($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

function dstripslashes($items) {
	return is_array($items) ? array_map('dstripslashes', $items) : $items;
}

function applyarr(&$arr, $func = '', $applykey = false) {
	static $recursive_counter = 0;
	if (++$recursive_counter > 1000) {
		return;
	}
	
	foreach ($arr AS $key => $val) {
		if (is_array($val)) {
			applyarr($val, $func, $applykey);
		} else {
			$arr[$key] = $func($val);
		}
	}
	
	if (false !== $applykey && is_string($key)) {
		$_key = $func($key);
		if ($_key != $key) {
			$arr[$_key] = $arr[$key];
			unset($arr[$key]);
		}
	}
	$recursive_counter --;
}

function str_exists($str, $find) {
	return !(strpos($str, $find) === false);
}

function is_robot($useragent = '') {
	static $spiders = array('bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla');
	static $browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');
	
	$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
	if (false === strpos('http://', $useragent) && dstrpos($useragent, $browsers)) return false;
	if (dstrpos($useragent, $spiders)) return true;
	
	return false;
}

function formhash($attch = '') {
	global $_G;
	return substr(md5(substr($_G['timestamp'], 0, -7).$_G['authkey'].$attch), 8, 8);
}

function convert($size) {
	static $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
	return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function random() {
	$source = range(1, 9);
	
	$str = '';
	for ($i = 0, $count = sizeof($source); $i < $count; $i ++) {
		$str .= $source[mt_rand(0, $count)];
	}
	
	return $str;
}

function return_bytes($val) {
	$val = trim($val);
	
	$lastchar = strtolower($val{strlen($val) - 1});
	switch ($lastchar) {
		case 'g' : $val *= 1024;
		case 'm' : $val *= 1024;
		case 'k' : $val *= 1024;
	}
	
	return $val;
}

function get_full_url() {
	$head = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$phpself = isset($_SERVER['PHP_SELF']) ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
	$pathinfo = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
	$requesturi = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $phpself.(isset($_SERVER['QUERY_STRING']) ? '?'.safe_replace($_SERVER['QUERY_STRING']) : $pathinfo);
	
	return $head.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$requesturi;
}

function redirect($uri = '', $method = 'location', $http_response_code = 302) {
	if (!preg_match('#^https?://#i', $uri)) {
		$app = split('/', $uri);
		$uri = 'index.php?app='.$app[0].'&act='.$app[1];
	}

	switch ($method) {
		case 'refresh'	: header("Refresh:0;url=".$uri);
			break;
		default			: header("Location: ".$uri, TRUE, $http_response_code);
			break;
	}
	exit;
}

// debug function
function show_msg($msg = "", $go_url = "") {
	header("Content-type:text/html; charset=utf-8");
	echo '<script type="text/javascript">';
	echo 'alert("' . $msg . '");';

	if ($go_url == "") {
		echo 'history.back();';
	} else	{
		echo "location.href = '$go_url'";
	}
	echo '</script>';
	exit;
}

/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string) {
	$string = str_replace('%20', '', $string);
	$string = str_replace('%27', '', $string);
	$string = str_replace('%2527', '', $string);
	$string = str_replace('*', '', $string);
	$string = str_replace('"', '&quot;', $string);
	$string = str_replace("'", '', $string);
	$string = str_replace('"', '', $string);
	$string = str_replace(';', '', $string);
	$string = str_replace('<', '&lt;', $string);
	$string = str_replace('>', '&gt;', $string);
	$string = str_replace("{", '', $string);
	$string = str_replace('}', '', $string);
	$string = str_replace('\\', '', $string);
	
	return $string;
}

function getgpc($key, $var = 'R') {
	switch (strval($var)) {
		case 'C': $var = &$_COOKIE;  break;
		case 'R': $var = &$_REQUEST; break;
		case 'G': $var = &$_GET;     break;
		case 'P': $var = &$_POST;    break;
	}
	return isset($var[$key]) ? $var[$key] : NULL;
}

function &get_instance() {
	return Controller::get_instance();
}