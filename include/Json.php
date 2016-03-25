<?php
class Json
{
	
	function Json() {
		;
	}

    /* *
	 *	将数据转换成json格式
	 *	@param	$value	转换的对象
	 *	
	 *	@return	(String) return_value
	 */
	function encode($value, $str = '') {
		//if (function_exists('json_encode')) 
		//	return json_encode($value);
		switch (gettype($value)) {
			case 'array' : 
				$keys = array_keys($value);
				if (!empty($value) && $keys !== range(0, sizeof($value) - 1)) {
					foreach ($value as $k => $item) {
						$str .= $str ? ',' . $this->encode($k) : $this->encode($k);
						$str .= ':' . $this->encode($item);
					}
					$return_value = '{' . $str . '}';
				} else {
					for ($i = 0; $i < count($value); $i ++) {
						$str .= $str ? ',' . $this->encode($value[$i]) : $this->encode($value[$i]);
					}
					$return_value = '[' . $str . ']';
				}
			break;
			
			case 'object' : 
				foreach (get_object_vars($value) as $k => $item) {
					$str .= $str ? ',' . $this->encode($k) : $this->encode($k);
					$str .= ':' . $this->encode($item);
				}
				$return_value = '{' . $str . '}';
			break;
			
			case 'boolean' : 
				$return_value = $value == true ? 'true' : 'false';
			break;
			
			case 'integer' :
			case 'double'  : 
				$return_value = is_numeric($value) ? $value : 'null';
			break;
			
			case 'string' : 	
				$value = strtr($value, array(
					'\\' => '\\\\', "\n" => '\\n', "\t" => '\\t', "\r" => '\\r', 
					"\b" => '\\b',  "\f" => '\\f', '/'  => '\\/',  
					"\x08" => '\b', "\x08" => '\f',
					'"' => '\\u0022', '\'' => '\\u0027', '&'  => '\\u0026', 
					'<' => '\\u003C', '>'  => '\\u003E',
				));
				mb_internal_encoding("UTF-8");
				$convmap = array(0x80, 0xFFFF, 0, 0xFFFF);
				$string = '';
				$i = mb_strlen($value) - 1;
				do {
					$mb_char = mb_substr($value, $i, 1);
					if (mb_ereg("&#(\d+);", mb_encode_numericentity($mb_char, $convmap, "UTF-8"), $match)) 
						$string = sprintf("\u%04x", $match[1]) . $string;
					else 
						$string = $mb_char . $string;
					$i --;
				} while($i >= 0);
				$return_value = '"' . $string . '"';
			break;
			
			default : 
				$return_value = $value !== null ? $value : 'undefined';
			break;
		}
		
		return $return_value;
	}

	/* *
	 *	将数据转换成json格式
	 *	@param	$value	转换的对象
	 *  @param	$assoc	true = (array) '' = (object)
	 *	
	 *	@return	(String) return_value
	 */
	function decode($value, $assoc = true) {
		//if (function_exists('json_decode')) 
		//	return json_decode($value, $assoc);
		$is_array = false;
		switch (strtolower($value)) {
			case 'true' : 
				return true;
			case 'false' : 
				return false;
			case 'null' : 
				return null;
			default : 
				if (is_numeric($value)) {
					 return $value !== null ? $value : 'undefined';
				} elseif (preg_match('/^("|\').*(\1)$/s', $value, $matches) 
						&& $matches[1] == $matches[2]) { // string
					$left   = substr($value, 0, 1);  // 开始'"'
					$char   = substr($value, 1, -1); // 排除结束'"'
					$string = '';
					$len    = strlen($char);
					for ($i = 0; $i < $len; ++ $i) {
						$s_char_2 = substr($char, $i, 2);
						switch (true) {
							case $s_char_2 == '\b':
                                $string .= "\x08"; ++$i;
                                break;
                            case $s_char_2 == '\t':
                                $string .= "\x09"; ++$i;
                                break;
                            case $s_char_2 == '\n':
                                $string .= "\x0a"; ++$i;
                                break;
                            case $s_char_2 == '\f':
                                $string .= "\x0c"; ++$i;
                                break;
                            case $s_char_2 == '\r':
                                $string .= "\x0d"; ++$i;
                                break;
                            case $s_char_2 == '\\"':
                            case $s_char_2 == '\\\'':
                            case $s_char_2 == '\\\\':
                            case $s_char_2 == '\\/':
                                if (($left == '"' && $s_char_2 != '\\\'') ||
                                   ($left == "'" && $s_char_2 != '\\"')) 
                                    $string .= $char{++$i};
                                break;
							case preg_match("/\\\u[0-9A-F]{4}/i", substr($char, $i, 6)) : 
								$code1 = base_convert(substr($char, $i + 2, 2), 16, 10);
								$code2 = base_convert(substr($char, $i + 4, 2), 16, 10);
								$string .= iconv('UCS-2', 'UTF-8', chr($code1) . chr($code2));
								$i += 5;
							break;
							default : 
								$string .= substr($char, $i, 1);
							break;
						}
					}
					return $string;
				} elseif (preg_match('/^\[.*\]$/s', $value) || 
						preg_match('/^\{.*\}$/s', $value)) { // array or object
					if ($value{0} == '[' || $assoc) {
						if ($value{0} == '[') $is_array = true;
						$array  = array(); 
						$whats  = array('array');
					} else {
						$object = new stdClass; 
						$whats  = array('object');
					}
					array_push($whats, array(
						'what'  => 'slice', // 记录切割状态
						'where' => 0, 		// 偏移量默认0
						'delim' => false	// 为了记录左边数据源出现的'"'、'['、'{'和结束符'"'、']'、'}'匹配
					));
					$char = substr($value, 1, -1);
					
					if ($char == '') {
						if (reset($whats) == 'array') return $array;
						else return $object;
					}
					$length = strlen($char);
					for ($i = 0; $i <= $length; ++ $i) {
						$last = end($whats);
						// array_pop移除最后一个后所到达的偏移量
						if ($i == $length || ($char{$i} == ',' && $last['what'] == 'slice')) {
							$slice = substr($char, $last['where'], $i - $last['where']);
							array_push($whats, array(
								'what'  => 'slice', 
								'where' => $i + 1, // +1为了加上","占用的1
								'delim' => false
							));
							if (reset($whats) == 'array') {
								if ($is_array) { // $value{0} == '['
									array_push($array, $this->decode($slice, $assoc));
								} else { // assoc == true
									$parts = array();
									if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
										$key = $this->decode($parts[1], $assoc); // 匹配的Key
										$val = $this->decode($parts[2], $assoc); // 匹配的Value
										$array[$key] = $val;
									}
								}
							} elseif (reset($whats) == 'object') {
								$parts = array();
								if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
									$key = $this->decode($parts[1], $assoc); // 匹配的Key
									$val = $this->decode($parts[2], $assoc); // 匹配的Value
									$object->$key = $val;
								}
							}
						} elseif (($char{$i} == '"' || $char{$i} == "'") && ($last['what'] != 'string')) {
							array_push($whats, array(
								'what'  => 'string',
								'where' => $i, 
								'delim' => $char{$i} // 记录开始'"'到whats数组
							));
                        } elseif ($char{$i} == $last['delim'] && $last['what'] == 'string' &&
                                 ((strlen(substr($char, 0, $i)) - strlen(rtrim(substr($char, 0, $i), '\\'))) % 2 != 1)) {
                            array_pop($whats); // 如果结束'"'与开始'"'配合成功并且是string状态, 过滤此条记录
                        } elseif ($char{$i} == '[' && in_array($last['what'], array('slice', 'array', 'object'))) {
                            array_push($whats, array(
								'what'  => 'array', 
								'where' => $i, // 记录当前的for的i到where当偏移量
								'delim' => false)
							);
                        } elseif ($char{$i} == ']' && $last['what'] == 'array') {
                            array_pop($whats); // 如果结束到']'后并且是array状态, 过滤此条记录
                        } elseif ($char{$i} == '{' && in_array($last['what'], array('slice', 'array', 'object'))) {
                            array_push($whats, array(
								'what'  => 'object', 
								'where' => $i, 
								'delim' => false
							));
                        } elseif ($char{$i} == '}' && $last['what'] == 'object') {
                            array_pop($whats);
                        }
					}
					if (reset($whats) == 'array') return $array;
					elseif (reset($whats) == 'object') return $object;
				}
			break;
		}
	}
}
?>