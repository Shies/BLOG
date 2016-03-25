<?PHP
class Captcha {

	/* *
	 *	生成验证码
	 *	@param	$mode  		  验证码类型(1-数字2-字母3-数字字母)
	 *	@param	$captchaN	  验证码个数(默认=4)
	 *	@param	$captchaW	  验证码宽度(默认=60)
	 *	@param	$captchaH	  验证码高度(默认=20)
	 *	@param	$obPixel	  干扰像素(默认=5)
	 *	@param	$obLine 	  干扰线条(默认=5)
	 *	@param	$obsnow		  干扰雪花(默认=5)
	 *	@param	$fontFileDir  字体文件目录(随机让每次出现的验证码字体形式不一样)
	 *	@param	$fontSize	  字体大小(默认=12)
	 *
	 *	@return (Null) void
	 */
	function create($mode = 1, $captchaN = 4, $captchaW = 60, $captchaH = 20, $obPixel = 5, $obLine = 5, $obSnow = 5, $fontFileDir = '', $fontSize = 12) {
		switch (intval($mode)) {
			case 1 : 
				$chars = '0,1,2,3,4,5,6,7,8,9';
			break;
			case 2 : 
				$chars = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m.n,o,p,q,r,s,t,u,v,w,x,y,z';
			break;
			case 3 : 
				$chars = '0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,a,b,c,d,e,f,g,h,i,j,k,l,m.n,o,p,q,r,s,t,u,v,w,x,y,z';
			break;
			default : 
				return false;
			break;
		}

		// GD 2.0.1 -
		$captcha_handle = ImageCreate($captchaW, $captchaH);
		
		$bgcolor = imagecolorallocate($captcha_handle, 120, 218, 157);
		$iborder = imagecolorallocate($captcha_handle, 123, 132, 213);
		$white   = imagecolorallocate($captcha_handle, 255, 255, 255);
		
		// $fontfiles = $this->scandir($fontFileDir);
		// $fontnum   = count($fontfiles) - 1;
		
		$captcha_code = '';
		$char_list = explode(',', $chars);
		for ($i = 0; $i < $captchaN; $i ++) { // 生成验证码
			$this_char = $char_list[mt_rand(0, count($char_list) - 1)];
			$captcha_code .= $this_char;

			$fontcolor = imagecolorallocate($captcha_handle, mt_rand(0, 200), mt_rand(0, 100), mt_rand(0, 250));
			imagestring($captcha_handle, 
				mt_rand(3, 5), // 内置字体格式
				$i * 11 + 6,  // X
				mt_rand(2, 4), // Y
				$this_char, 
				$fontcolor
			);
			/*
			imagettftext($captcha_handle, 
				$fontSize, 
				mt_rand(0, $captchaH), // 角度
				intval($captchaW / $captchaN) * $i + 5, // X轴
				mt_rand(intval($captchaH / 2), $captchaH), // Y轴
				imagecolorallocate($captcha_handle, mt_rand(0, 200), mt_rand(0, 100), mt_rand(0, 250)), 
				$fontFileDir . '/' . $fontfiles[mt_rand(0, $fontnum)], 
				iconv("GB2312", "UTF-8", $this_char)
			);
			*/
		}
		
		for ($i = 0; $i < $obPixel; $i ++)  { // 干扰像素
			imagesetpixel($captcha_handle, 
				mt_rand(0, $i * $captchaW), // X
				mt_rand(0, $i * $captchaH), // Y
				imagecolorallocate($captcha_handle, mt_rand(50, 200), mt_rand(50, 250), mt_rand(50, 250))
			);
		}
		
		for ($i = 0; $i < $obLine; $i ++) { // 干扰线条
			imageline($captcha_handle, 
				mt_rand(0, $i * $captchaW), // X1
				mt_rand(0, $i * $captchaH), // Y1
				mt_rand(0, $i * $captchaW), // X2
				mt_rand(0, $i * $captchaH), // Y2
				imagecolorallocate($captcha_handle, mt_rand(0, 250), mt_rand(0, 250), mt_rand(0, 250))
			);
		}
		
		for($i = 0; $i < $obSnow; $i ++) { //雪花
			imageString($captcha_handle, 
				1, 
				mt_rand(0, $i * $captchaW), // X
				mt_rand(0, $i * $captchaH), // Y
				'*', 
				imageColorAllocate($captcha_handle, mt_rand(150, 255), mt_rand(150, 255), mt_rand(150, 255))
			);
		}
		
		imagerectangle($captcha_handle, 0, 0, $captchaW - 1, $captchaH - 1, $iborder); //边框
		imageantialias($captcha_handle, true); // 抗锯齿
		
		// here is $_SESSION or $_COOKIE record can all for 'captcha_code'
		if (isset($captcha_code)) {
			$_SESSION['verify'] = md5($captcha_code);
		}
		
		header("Pragma: no-cache");
		header("Cache-Control: no-cache");
		if (function_exists('imagejpeg')) {
			header("Content-type: image/jpeg");
			imagejpeg($captcha_handle);
		} elseif (function_exists('imagepng')) {
			header("Content-type: image/png");
			imagepng($captcha_handle);
		} elseif (function_exists('imagegif')) {
			header("Content-type: image/gif");
			imagegif($captcha_handle);
		} else {
			die('No allowed this is image type');
		}
		
		imagedestroy($captcha_handle);
	}
	
	/* *
	 *	递归目录文件
	 *	@param	$path	指定的目录路径
	 *	@param	$mode	0-升序1-降序
	 *
	 *	@return	(Array)	$files
	 */
	function scandir($path, $mode = 0) {
		if (function_exists('scandir')) {
			return scandir($path, $mode);
		}
	
		$handle = opendir($path);
		while (FALSE !== ($filename = readdir($handle))) {
			if ($filename != '.' && $filename != '..' && 
			($filename == 'arial.ttf' || $filename == 'ariali.ttf')
			) {
				$_path = $path . '/' . $filename;
				if (is_dir($_path)) {
					// or $this->scandir($_path)
					$files[$filename] = $this->scandir($_path); 
				} else {
					$files[] = $filename;
				}
			} else {
				continue;
			}
		}
		closedir($handle);
		
		switch (intval($mode)) {
			case 0 : 
				sort($files);
			break;
			case 1 : 
				rsort($files);
			break;
			default : 
				sort($files);
			break;
		}
		
		return $files;
	}
	
}