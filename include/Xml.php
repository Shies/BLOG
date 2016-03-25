<?PHP
class Xml
{ 

  function Xml() {
	;
  }

  function encode($arr, $htmlon = false, $level = 1) {
	$s = $level == 1 ? "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<root>\r\n" : '';
	$space = str_repeat("\t", $level);
		
	foreach ((array) $arr AS $key => $val) {
		if (is_array($val)) {
			$s .= "<item id=\"$key\">\r\n".$this->encode($val, $htmlon, $level + 1).$space."</item>";
		} else {
			$s .= "<item id=\"$key\">".($htmlon ? '<![CDATA[' : '').$val.($htmlon ? ']]>' : '')."</item>\r\n";
		}
	}
	$s = preg_replace("/([\x01-\x08\x0b-\x0c\x0e-\x1f])+/", ' ', $s);
		
	return $level == 1 ? ($s."</root>") : $s;
  }

  function decode($xml) {
	if (is_file($xml) && file_exists($xml)) {
		$xml = implode('', file($xml)); // simple_load_file
	}
	
	if (PHP_VERSION < '5.0.0') {
		# use xml.php in uc_server/lib/xml.php #
		Syserror::error('Your PHP version so low');
	}
	
	$xmlarr = (array) simplexml_load_string($xml);
	foreach ($xmlarr AS $key => $val) {
		$xmlarr[$key] = $this->struct2array((array) $val);
	}
	
	return $xmlarr;
  }
  
  function struct2array($items) {
	return is_array($items) ? array_map(array(&$this, 'struct2array'), $items) : $items;
  }
  
}

?>