<?php
/* *
 *	WebSocket应用实例
 *	http://blog.csdn.net/trace332/article/details/6325986
 *
 *	PHP发送新浪微博程序(CURL推送)
 *	http://www.lao8.org/article_1356/php_faweibo
 */

# 暂时用最简单的JQuery+PHP来实现轮询推送技术 #
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$filename  = dirname(__FILE__).'/data.txt';

// store new message in the file
$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';
if ($msg != '')
{
	file_put_contents($filename, $msg);
	die();
}

// infinite loop until the data file is not modified
$lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
$currentmodif = filemtime($filename);
while ($currentmodif <= $lastmodif) // check if the data file has been modified
{
	usleep(3000000); // sleep 10ms to unload the CPU
	// clearstatcache();
	$currentmodif = filemtime($filename);
	break;
}

// return a json array
$response = array();
$response['msg']       = file_get_contents($filename);
$response['timestamp'] = $currentmodif;
echo json_encode($response);
flush();
?>