<?PHP
class Http {

	/* *
	 *	socket跨域传输数据(socket transport)
	 *
	 *	@return	(Null)	void
	 */
	function use_socket($url, $param, $method, $cookie) {
		// extension_loaded('socket') or function_exists('fsockopen') or dl('socket.dll')
		if (empty($url)) {
			exit('You request\'s url invalid');
		}
		
		$parts = parse_url($url);
		
		// preg_replace('/^&/', '', '&a=one&b=two&c=three') also can
		$param = http_build_query($param);
		
		$is_post = 'POST' === strtoupper($method);
		$query = strval(null);
		
		if (!$is_post) {	
			$query = '?' . (isset($parts['query']) ? $parts['query'] : $param);
		}
		$path = $parts['path'] . $query;
		// return url default port 
		$port = isset($parts['port']) ? $parts['port'] : 80;
		
		$request = "$method $path HTTP/1.0\r\n";
		$request .= "Host: " . $parts['host'] . "\r\n"; // HTTP/1.1的Host不能省略
		
		/*
			以下头信息域可以省略
			$request .= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13 \r\n";
			$request .= "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,q=0.5 \r\n";
			$request .= "Accept-Language: en-us,en;q=0.5 ";
			$request .= "Accept-Encoding: gzip,deflate\r\n";
		*/
		
		if ($cookie && is_array($cookie)) {
			$_cookie = strval(null);
			foreach ($cookie AS $key => $val) {
				$_cookie .= $key . '=' . $val . ';';
			}
			$request .= "Cookie: ".base64_encode($_cookie)."\r\n";
		}
		
		if ($is_post) {
			$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: " . strlen($param) . "\r\n\r\n";
			$request .= $param . "\r\n";
		}
		$request .= "Connection: Close\r\n\r\n";

		$fp = @fsockopen($parts['host'], $port, $errmsg, $errno, 30);
		if (!$fp) {
			return false;
		}
		
		if (!@fwrite($fp, $request)) {
			die('You\'s socket request write fail');
		}
		
		$reponse = '';
		while (!feof($fp)) {
			$reponse .= fgets($fp, 1024); // fread($fp, 1024); 不关心服务器的返回
		}
		
		if (!$reponse) {
			return false;
		}
		
		$sep = '/\r\n\r\n|\n\n|\r\r/'; // 间隔符
		list($header, $body) = preg_split($sep, $reponse, 2);
		
		$reponse = array(
			'header' => $header, 'body' => $body
		);

		@fclose($fp);
		
		return $reponse;
	}
	
	/* *
	 *	curl跨域传输数据(curl transport)
	 *
	 *	@return	(Null)	void
	 */
	function use_curl($url, $param, $method, $my_header) {
		// extension_loaded('curl') or function_exists('curl_init') or dl('curl.dll')
		if (is_null($url)) {
			exit('You request\'s url invalid');
		}
		
		// 开启curl会话
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true); // 处理完结果, 关闭链接, 释放资源
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // 不直接返回结果
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		
		$parts = parse_url($url);
		
		// preg_replace('/^&/', '', '&a=one&b=two&c=three') also can
		$param = http_build_query($param);
		
		$is_post = 'POST' === strtoupper($method);
		$header = array();
		
		$header[] = "Host: " . $parts['host'];
		if ($my_header && is_array($my_header)) {
            foreach ($my_header AS $key => $val) {
                $header[] = $key . ': ' . $val;
            }
			$my_header = null;
        }
		
		if (!$is_post) {
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			$url .= '?' . (isset($parts['query']) ? $parts['query'] : $param);
		} else {
			curl_setopt($ch, CURLOPT_POST, true);
			$header[] = "Content-Type: application/x-www-form-urlencoded";
			$header[] = "Content-Length: " . strlen($param);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		
		ob_start();
		curl_exec($ch);		
		$reponse = ob_get_contents();
		ob_end_clean();
		
		if (curl_errno($ch) != 0) {
			return false;
		}
		
		$sep = "/\r\n\r\n|\n\n|\r\r/";
		list($header, $body) = preg_split($sep, $reponse, 2);
		
		$reponse = array(
			'header' => $header, 'body' => $body
		);
		
		curl_close($ch);
		
		return $reponse;
	}
	
	/* *
	 *	stream流数据包跨域传输(stream transport)
	 *
	 *	@return	(Null)	void
	 */
	function use_stream($url, $param, $method, $my_header) {
		if (null === $url) {
			exit('You request\'s url invalid');
		}
	
		$options = array(
			'http' => array(
				'method'  => $method,
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
							"Connection: close\r\n\r\n",
				'content' => http_build_query($param),
				'timeout' => 30,
			)
		);
		$handle = stream_context_create($options);
		
		$reponse = '';
		if (!function_exists('file_get_contents')) {
			$fp = fopen($url, 'r', false, $handle);
			while (!feof($fp)) {
				$reponse .= fgets($fp, 1024);
			}
		} else {
			$reponse = file_get_contents($url, false, $handle);
		}
		
		if (!$reponse) {
			return false;
		}

		return $reponse;
	}
}