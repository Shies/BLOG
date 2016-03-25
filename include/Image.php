<?PHP
class Image {
	
	/* *
	 *	普通文件上传 
	 *	@param	$name	上传的名称
	 *	@param	$size	限制上传的大小
	 *	@param	$dir	上传文件制定的目录
	 *
	 *	@return (String) filename
	 */
	function do_upload($filename, $filesize, $filedir, $mega = 1024) {
		// filename can't empty
		if ($filename === '') {
			die();
		}
		$fname = trim($filename);
		
		// if size has 'M' or no 'M'
		if ($filesize > ($mega * $mega)) {
			$fsize = ceil($filesize / $mega) * $mega;
		} elseif (stripos($filesize, 'M') !== false) {
			$m = intval(substr($filesize, 0, stripos($filesize, 'M')));
			$fsize = $m * $mega * $mega;
		} elseif ($filesize != 0 && $filesize < 20) {
			$fsize = intval($filesize) * $mega * $mega;
		} else {
			return false;
		}
		
		if (intval($_FILES[$fname]['error']) > 0) {
			// return file error 
			die('return code: ' . $_FILES[$fname]['error'] . '<br />');
		}
		// get param value
		$path  = pathinfo($_FILES[$fname]['name']);
		$suffix = strtolower($path['extension']);
		$md5name = substr(md5($_FILES[$fname]['name']), 16, -6);

		if ($_FILES[$fname]['size'] > $fsize) {
			// upload file size so max
			die('upload file size so max ' . '<br />');
		}
		
		if (!in_array($suffix, array('jpg', 'gif', 'png', 'bmp', 'jpeg'))) {
			// upload file invalid param
			die('upload file invalid param ' . '<br />');
		}
		
		if (!file_exists($filedir) && !is_dir($filedir)) {
			mkdir($filedir, 0777, true);
		}
			
		$tmp_file = $filedir . '/' . $md5name . '.' . $suffix;
		// move file to file dir 
		move_uploaded_file($_FILES[$fname]['tmp_name'], $tmp_file);
		
		return array(
			'filedir'  => $filedir,
			'md5name'  => $md5name,
			'suffix'   => $suffix,
			'tmp_file' => $tmp_file
		);
	}
	
	/* *
	 *	创建缩略图 (支持jpg, jpeg, gif, png)
	 *	@param	$srcImgPath		源图片路径所在地
	 *	@param	$targetImgPath	新图片路径所在地(自定义文件名)
	 *	@param	$targetW		新图片的宽度
	 *	@param	$targetH		新图片的高度
	 *	@param	$echoType		输出类型(0-直接输出1-保存目录2-保存二进制)
	 *	@param	$mode			(0-直接压缩1-裁剪复制2-等比率压缩填充背景)
	 *
	 *	@return (Null)	void
	 */
	function thumbnail($srcImgPath, $targetImgPath, $targetW, $targetH, $echoType = 1, $mode = 2) {
		// get image size and type
		$imgSize = getimagesize($srcImgPath);
		switch ($imgSize[2]) {
			case 1 : $srcImg = imagecreatefromgif($srcImgPath);  break;
			case 2 : $srcImg = imagecreatefromjpeg($srcImgPath); break;
			case 3 : $srcImg = imagecreatefrompng($srcImgPath);  break;
			default : die('Not allowed this image type !'); break;
		}
		// get self width and height
		$srcW = imagesx($srcImg); $srcH = imagesy($srcImg);
		
		// count thumbnail's targetX and targetY
		if ($srcW > $targetW || $srcH > $targetH) {
			$srcX 	 = 0; $srcY	   = 0;
			$targetX = 0; $targetY = 0;
			switch (intval($mode)) {
				case 0 : //	仅宽高即可
					$finalW = $targetW != 0 ? $targetW : false;
					$finalH = $targetH != 0 ? $targetH : false;
				break;
				case 1 : // 坐标 + 宽高
					$finalW = $imgSize[0];
					$finalH = $imgSize[1];
					$srcX   = floor(($srcW - $targetW) / 2);
					$srcY   = floor(($srcH - $targetH) / 2);
				break;
				case 2 : // 坐标 + 宽高
					if ($srcW > $srcH) {
						$finalW  = $targetW;
						$finalH  = round($srcH * $finalW / $srcW);
						$targetY = floor(($targetH - $finalH) / 2);
					} else {
						$finalH  = $targetH;
						$finalW  = round($srcW * $finalH / $srcH);
						$targetX = floor(($targetW - $finalW) / 2);
					}
				break;
				default : 
					die('You echo is param has error');
				break;
			}
			
			// put color to thumbnail
			if (function_exists('ImageCreateTrueColor')) {
				// GD 2.0.1 +
				$targetImg = ImageCreateTrueColor($targetW, $targetH);
			} else {
				// GD 2.0.1 -
				$targetImg = ImageCreate($targetW, $targetH);
			}
			
			// special limit
			$targetX = ($targetX < 0) ? 0 : $targetX;
			$targetY = ($targetY < 0) ? 0 : $targetY;
			$targetX = ($targetX > $targetW / 2) ? floor($targetW / 2) : $targetX;
			$targetY = ($targetY > $targetH / 2) ? floor($targetH / 2) : $targetY;
			
			// background color
			$white = ImageColorAllocate($targetImg, 255, 255, 255);
			ImageFilledRectangle($targetImg, 0, 0, $targetW, $targetH, $white);
			
			// 新图片地址, 源图片地址, 源图片在新图片中的x坐标, 源图片在新图片中的y坐标, 源图要载入的区域x坐标,
			// 源图要载入的区域y坐标, 载入的原图的宽度, 载入的原图的高度, 原图要载入的宽度, 原图要载入的高度
			if (function_exists('ImageCopyResampled')) {
				// GD 2.0.1 + , 速度慢
				ImageCopyResampled($targetImg, $srcImg, $targetX, $targetY, $srcX, $srcY, 
					$finalW, $finalH, $imgSize[0], $imgSize[1]);
			} else {
				// GD 1.0 + , 质量差
				ImageCopyResized($targetImg, $srcImg, $targetX, $targetY, $srcX, $srcY, 
					$finalW, $finalH, $imgSize[0], $imgSize[1]);
			}
			
			// output thumbnail to browser
			switch (intval($echoType)) {
				case 0 : // direct echo
					switch ($imgSize[2]) {
						case 1 : 
							header("Content-type: image/gif");
							imagegif($targetImg);  
						break;
						case 2 : 
							header("Content-type: image/jpeg");
							imagejpeg($targetImg); 
						break;
						case 3 : 
							header("Content-type: image/png");
							imagepng($targetImg);  
						break;
						default : 
							die('Not allowed this image type !'); 
						break;
					}
				break;
				case 1 : // direct put dir
					switch ($imgSize[2]) {
						case 1 : 
							imagegif($targetImg, $targetImgPath);  
						break;
						case 2 :
							imagejpeg($targetImg, $targetImgPath); 
						break;
						case 3 : 
							imagepng($targetImg, $targetImgPath);  
						break;
						default : 
							die('Not allowed this image type !'); 
						break;
					}
				break;
				case 2 : // need match up file_get_contents
					ob_start();
					switch ($imgSize[2]) {
						case 1 : 
							imagegif($targetImg);  
						break;
						case 2 : 
							imagejpeg($targetImg); 
						break;
						case 3 : 
							imagepng($targetImg);  
						break;
						default : 
							die('Not allowed this image type !'); 
						break;
					}
					$binary_mode = ob_get_contents();
					ob_end_clean();
					
					return $binary_mode;
				break;
				default : 
					die('You echo is param has error !');
				break;
			}
			
			// free result
			ImageDestroy($srcImg);
			ImageDestroy($targetImg);
		} else { 
			
			// 不超出指定宽高则直接复制
			copy($srcImgPath, $targetImgPath);
			ImageDestroy($srcImg);
		}
	}
	
	/* *
	 *	创建水印 (支持jpg, jpeg, gif, png)
	 *	@param	$groundImage	背景图片(即需要加水印的图片，暂只支持GIF,JPG,PNG格式)
	 *	@param	$waterPos		0-随机位置1-顶端偏左2-顶端居中3-顶端偏右4-中部偏左5-中部居中6-中部偏右7-底部偏左8-底部居中9-底部偏右
	 *	@param	$waterImage		图片水印(即作为水印的图片，暂只支持GIF,JPG,PNG格式)
	 *	@param	$waterText		文字水印(即把文字作为为水印，支持ASCII码，不支持中文)
	 *	@param	$fontSize		文字大小(值为1、2、3、4或5，默认为5)
	 *	@param	$textColor		文字颜色(值为十六进制颜色值，默认为#CCCCCC(白灰色))
	 *	@param	$fontFile		字体文件(ttf字体文件，即用来设置文字水印的字体。使用windows的用户在系统盘的目录中搜索*.ttf可以得到系统中安装的字体文件，将所要的文件拷到网站合适的目录中,默认是当前目录下arial.ttf)
	 *	@param	$xOffset		x轴偏移量(+偏右, -偏左, 默认为0)
	 *	@param	$yOffset		y轴偏移量(+偏下, -偏上, 默认为0)
	 *	@param	$waterAlpha		水印透明度(0-可见100-不可见)
	 *
	 *	@return (Integer) number
	 */
	function watermark($groundImage, $waterImage = '', $waterPos = 0, $waterText = '测试', $fontSize = 12, $textColor = '#FFFFFF', $fontFile = 'AdobeHeitiStd-Regular.otf', $xOffset = 0, $yOffset = 0, $waterAlpha = 50) {
		if (!empty($groundImage) && file_exists($groundImage)) {
			$groundinfo = getimagesize($groundImage);
			$groundW	= $groundinfo[0];
			$groundH	= $groundinfo[1];
			switch ($groundinfo[2]) {
				case 1 : $groundimg = imagecreatefromgif($groundImage);  break;
				case 2 : $groundimg = imagecreatefromjpeg($groundImage); break;
				case 3 : $groundimg = imagecreatefrompng($groundImage);  break;
				default : die('Not allowed this image type !');
			}
		} else {
			die('No exists this water image !');
		}
		
		$isWaterImage = false;
		if (!empty($waterImage) && file_exists($waterImage)) {
			$isWaterImage = true;
			$waterinfo = getimagesize($waterImage);
			$waterW    = $waterinfo[0];
			$waterH	   = $waterinfo[1];
			switch ($waterinfo[2]) {
				case 1 : $waterimg = imagecreatefromgif($waterImage);  break;
				case 2 : $waterimg = imagecreatefromjpeg($waterImage); break;
				case 3 : $waterimg = imagecreatefrompng($waterImage);  break;
				default : die('Not allowed this image type !');
			}
		}
		
		if ($isWaterImage === true) {
			$water_w = $waterW;
			$water_h = $waterH;
			$water_label = 'image layout';
		} else {
			if (!file_exists($fontFile))
				die('No exists this font file');
			// 返回一个含有 8 个单元的数组表示了文本外框的四个角
			$temp = imagettfbbox($fontSize, 0, $fontFile, $waterText);
			$water_w = $temp[2] - $temp[6];
			$water_h = $temp[3] - $temp[7];
			unset($temp);
		}
		if ($groundW < $water_w || $groundH < $water_h)
			die('Need ground image is size not can than water image is size still little');
		
		switch (intval($waterPos)) {
			case 0 : 
				$posX = rand(0, ($groundW - $water_w));
				$posY = rand(0, ($groundH - $water_h));
			break;
			case 1 : 
				$posX = 0;
				$posY = 0;
			break;
			case 2 : 
				$posX = floor(($groundW - $water_w) / 2);
				$posY = 0;
			break;
			case 3 : 
				$posX = $groundW - $water_w;
				$posY = 0;
			break;
			case 4 : 
				$posX = 0;
				$posY = floor(($groundH - $water_h) / 2);
			break;
			case 5 : 
				$posX = floor(($groundW - $water_w) / 2);
				$posY = floor(($groundH - $water_h) / 2);
			break;
			case 6 : 
				$posX = $groundW - $water_w;
				$posY = floor(($groundH - $water_h) / 2);
			break;
			case 7 : 
				$posX = 0;
				$posY = $groundH - $water_h;
			break;
			case 8 : 
				$posX = floor(($groundW - $water_w) / 2);
				$posY = $groundH - $water_h;
			break;
			case 9 : 
				$posX = $groundW - $water_w;
				$posY = $groundH - $water_h;
			break;
			default : 
				return false;
			break;
		}
		
		imagealphablending($groundimg, true); // 混色
		if ($isWaterImage === true) {
			// 如果是图片水印直接copy水印图片到指定image上
			imagecopy($groundimg, $waterimg, $posX + $xOffset, $posY + $yOffset, 0, 0, $water_w, $water_h);
		} else {
			if (!empty($textColor) && strlen($textColor) == 7) {
				$R = hexdec(substr($textColor, 1, 2));
				$G = hexdec(substr($textColor, 3, 2));
				$B = hexdec(substr($textColor, 5));
			} else {
				die('Water text color error');
			}
			// 启动alpha模式
			imagesavealpha($groundimg, true);
			// 将文字直接copy到指定的image上并且加上alpha
			imagettftext($groundimg, $fontSize, 0, $posX + $xOffset, $posY + $water_h + $yOffset, 
			imagecolorallocatealpha($groundimg, $R, $G, $B, $waterAlpha), $fontFile, $waterText);
		}
		
		@unlink($groundImage);
		switch ($groundinfo[2]) {
			case 1 : imagegif($groundimg, $groundImage);  break;
			case 2 : imagejpeg($groundimg, $groundImage); break;
			case 3 : imagepng($groundimg, $groundImage);  break;
			default : die('Water ground image type error !');
		}
		
		if (isset($waterinfo)) unset($waterinfo);
		if (isset($waterimg)) imagedestroy($waterimg);
		unset($groundinfo); imagedestroy($groundimg);
	}

}