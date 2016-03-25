<?PHP
	/* *
	 *	中文英文字符串截取
	 *	@param $string  你要截取的字符串
	 *	@param $start   你从哪开始截取
	 *	@param $length  你截取的长度
	 *	@param $charset 你截取的字符串编码
	 *	@param $dot		是否以...省略
	 *
	 *	@return (String) slice
	 */

function str_cut($string, $start, $length, $charset='utf-8', $dot = '...') {
	// mb_substr and mb_strlen
	if (function_exists('mb_substr')) {
		if (mb_strlen($string, $charset) > $length) {
			return mb_substr($string, $start, $length, $charset) . $dot;
		}
		return mb_substr($string, $start, $length, $charset);
	// iconv_substr and iconv_strlen
	} elseif (function_exists('iconv_substr')) {
		if (iconv_strlen($string, $charset) > $length) {
			return iconv_substr($string, $start, $length, $charset) . $dot;
		}
		return iconv_substr($string, $start, $length, $charset);
	}
	switch (strtolower($charset)) {
		case 'utf-8' : 
			preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $string, $matches);
			if (func_num_args() >= 3) {
				if (sizeof($matches[0]) > $length) {
					return join("", array_slice($matches[0], $start, $length)) . $dot;
				}
				return join("", array_slice($matches[0], $start, $length));
			} else {
				return join("", array_slice($matches[0], $start));
			}
		break;
		default : 
			$tmpstr = '';
			$start  = $start * 2;
			$length = $length * 2;
			$strlen = strlen($string);
			for ($i = 0; $i <= $strlen; $i ++) {
				if ($i >= $start && $i < ($start + $length)) {
					if (ord(substr($string, $i, 1)) > 129) 
						$tmpstr .= substr($string, $i, 2);
					else 
						$tmpstr .= substr($string, $i, 1);
				}
				if (ord(substr($string, $i, 1)) > 129) $i ++;
			}
			if (strlen($tmpstr) < $strlen) {
				$tmpstr .= $dot;
			}
			return $tmpstr;
		break;
	}
}