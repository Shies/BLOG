<?PHP
/* ========================================================================= //
 *	# PHP之SphinxAPI AND SphinxQL实例 #
 *
 *	# 安装 http://www.alixixi.com/program/a/2011102475701.shtml #
 *	# 配置 http://www.alixixi.com/program/a/2011102475701.shtml #
 *	# 手册 http://www.coreseek.cn/docs/coreseek_3.2-sphinx_0.9.9.html #
 *	
 *	# sphinx interface api demo #
 *	# author __Kay. #
// ========================================================================= */
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('SPHINXPATH', dirname(__FILE__).'/');
require SPHINXPATH . 'sphinx-win32/api/sphinxapi.php';

// 默认采用sphinxapi形式测试
$s = new SphinxClient();
// 设置主机名和端口
$s->setServer('localhost', 9312);
// 设置链接超时
$s->SetConnectTimeout(1);
// 设置数组结果
$s->SetArrayResult(true);
// 设置匹配模式（v2.2.2已被弃用, 推荐使用SphinxQL）
$s->setMatchMode(SPH_MATCH_ANY);
// 统计相关度计算模式，仅使用BM25评分计算
$s->SetRankingMode(SPH_RANK_PROXIMITY_BM25);
// 设置过滤条件
$s->SetFilter('group_id', array(2));
// 设置最大执行时间
$s->setMaxQueryTime(3);
// 返回结果 = 关键字 + 索引
$res = $s->query('test', 'test1');
// 关闭链接
$s->close();

echo '<pre>'; print_r($res); echo '</pre>';
exit;


/* *
 *	# 未采用mysql二进制网络协议的代码 简称：SphinxAPI OK #

function sphinxsetup($level = 0) {
	// 检查sphinx是否能连接，不能重试两次，能则连接, 不用mysql协议, 仅供参考
	$level >= 2 && exit('Max Level');
	
	$sph = new SphinxClient();
	$sph->setServer('localhost', 9312);
	
	if ($sph->open()) {
		return $sph;
	}

	// 此处为如果连接不上就更改配置文件，根据项目需要来写
	// global $configDefault;
	// updateConfig($configDefault);
	sphinxsetup(++$level);
}


$orderby = "id DESC, date_added DESC"; // 排序规则
$indexname = "*"; // 全部索引名字 例如：test1
$keywords = iconv ( "gbk", "utf-8", 'test' );

$s = sphinxsetup();
$s->SetConnectTimeout ( 1 );
$s->SetArrayResult ( true );
$s->setMatchMode ( SPH_MATCH_PHRASE );
$s->SetSortMode ( SPH_SORT_EXTENDED, $orderby );
$s->setMaxQueryTime ( 100000 );
$s->setLimits ( 0, 20, 1000 ); // offset / limit / maxmatches
$result = $s->query($keywords, $indexname );
$s->close();

if ($result['total'] > 0) {
	// 根据打印出的结果进行相应的读取
	echo '<pre>';
	print_r($result['matches']);
	exit('</pre>');
}
*/


/* *
 *	# 采用mysql二进制网络协议的代码 简称：SphinxQL OK #
 *
 *	关于开启mysql二进制网络协议参考：Sphinx/MySQL协议支持与SphinxQL
 *	http://www.xuejiehome.com/blread-1612.html
 *	如何获取总记录个数参考：sphinxql如何得到结果数？show meta的详细说明？
 *	http://www.xuejiehome.com/blread-1585.html

function checkSphinx($level = 0) {
	// 检查sphinx是否能连接，不能重试两次，能则连接,用mysql14协议
	$level >= 2 && exit('Not connected : ' . mysql_error());
	
	$conn = mysql_connect("127.0.0.1:9306");	
	if ($conn) {
		return $conn;
	}
	
	// 此处为如果连接不上就更改配置文件，根据项目需要来写
	// global $configDefault;
	// updateConfig ( $configDefault );
	checkSphinx(++$level);
}

function getTotalFound($conn) {
	$sql = "show meta";
	$total_result = @mysql_query ( $sql, $conn );
	$totals = array ();
	while ( ($row = mysql_fetch_assoc ( $total_result )) !== false ) {
		$totals [$row ['Variable_name']] = $row ['Value'];
	}
	
	return $totals;
}
       
$orderby = 'id DESC, date_added DESC'; // 排序规则
$conn = checkSphinx();
$keywords = iconv ( "gbk", "utf-8", 'test|one|two' );

// $mysql = "SELECT * FROM documents WHERE MATCH(`title`, `content`) AGAINST('{$keywords}') ORDER BY {$orderby} LIMIT 10"; // OPTION MAX_MATCHES=1000 MATCH中是索引名称、AGAINST需要查找的关键字
$sphsql = "SELECT * FROM test1 WHERE MATCH('{$keywords}') ORDER BY {$orderby} LIMIT 10 OPTION MAX_MATCHES=1000";

// $insertsql = "INSERT INTO testrt(`id`, `group_id`, `group_id2`, `date_added`, `title`, `content`)  VALUES('5', '1', '10', '2014-05-05 10:08:16', 'test five', 'are you ready ?')";
// var_dump(mysql_query($insertsql, $conn));
// exit;

$result = @mysql_query( $sphsql, $conn );
while ( ($row = mysql_fetch_assoc ( $result )) !== false ) {
	// 根据打印出的结果进行相应的读取
	print_r($row);
}

// 获取总记录个数
// 或 SELECT * FROM test1 LIMIT 0 接 SHOW meta;
// $testsql = "SELECT 1 AS full, COUNT(*) AS total FROM test1 GROUP BY full";
// $result = mysql_fetch_array(@mysql_query($testsql, $conn));
print_r(getTotalFound($conn)) . '------------<br />';

@mysql_close($conn);
 */