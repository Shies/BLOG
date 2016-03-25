<?PHP
	/* *
	 *	类似TenCent-QQ空间留言板分页算法去计算
	 *	@param $total	 总统计
	 *	@param $page	 当前页
	 *	@param $pagesize 每页显示多少数据
	 *	@param $uri		 你的url
	 *
	 *	@return  (String) page_and_start
	 */

function pager($total, $page, $pagesize, $uri) {
	$total    = $total ? intval($total) : 0;
	if ($total <= 0) return false;
	
	$pagesize = max(1, intval($pagesize));
	$uri      .= strpos($uri, '?') !== false ? '&' : '?';
	
	$page_and_start = '';
	
	$page_count = $total > 0 ? @ceil($total / $pagesize) : 0;
	$page 		= max(1, min($page_count, intval($page)));
	
	$offset     = $page > $page_count ? 
			($page_count - 1) * $pagesize : 
		($page - 1) * $pagesize;
	
	if ($page - 1 > 0) {
		$page_and_start .= " <a href=" . $uri . "page=" . ($page - 1) . ">上一页</a> ";
	} else {
		$page_and_start .= " <a href='javascript:;'>上一页</a> ";
	}
	
	if ($page >= 9) {
		$page_and_start .= " <a href=" . $uri . "page=1>1</a> " . "...";
	}
	
	if ($page > 0 && $page < 9) {
		$from_start = 1;
		$to_end 	= ($page_count < 9) ? $page_count : 9;
	}
	elseif ($page >= 9 && $page_count - 8 >= $page) {
		$from_start = ($offset - 2) < 1 ? 1 : ($offset - 2);
		$to_end 	= ($offset + 4) > $page_count ? $page_count : ($offset + 4);
	}
	else {
		$from_start = $page_count - 8;
		$to_end 	= $page_count;
	}
	
	for ($i = $from_start; $i <= $to_end; $i ++) {
		if ($i == $page) {
			$page_and_start .= " <a href=" . $uri . "page=$i><strong>$i</strong></a> ";
		} else {
			$page_and_start .= " <a href=" . $uri . "page=$i>$i</a> ";
		}
	}
	
	if ($page_count - 8 >= $page) {
		$page_and_start .= "..." . " <a href=" . $uri . "page=" . $page_count . ">" . $page_count . "</a> ";
	}
	
	if ($page + 1 <= $page_count) {
		$page_and_start .= " <a href=" . $uri . "page=" . ($page + 1) . ">下一页</a> ";
	} else {
		$page_and_start .= " <a href='javascript:;'>下一页</a> ";
	}
	
	return array(
		'offset'     => $offset,
		'pager'      => $page_and_start,
	);
}