<?PHP
/*
	keywords：中文分词、PHP中文分词、trie数据结构、Doubule Array Trie Datastruct
	原理：
	Trie数据结构的名词介绍我就不介绍了，大家google,百度可以搜索一大堆的文章来.
	Tire索引树法
	结构：首字散列表、Trie索引树结点
	优点：分词中，不需预知待查询词的长度，沿树链逐字匹配。
	缺点：构造和维护比较复杂，单词树枝多，浪费了一定的空间。
*/

/* * 
 * @file		tire2.h
 * @description	构造通用的字典算法，并写了一个简易的分词 
 * @author 		__Kay.
 * @date		$ 2014/05/13 17:11:29 $
 * @version		1.0.20120513
 * @remark		Trie字典树
 */
class trie2 {
	
	private $trie;
	
	function __construct() {
		$this->trie = array('children' => array(), 'isword' => false);
	}
	
	# 把词加入词典 #
	function &set_word($word = '') {	
		$trienode =& $this->trie;
		$i = 0;
		while ($i < strlen($word)) {
			$char = $word[$i];
			if (!isset($trienode['children'][$char])) {
				$trienode['children'][$char] = array('isword' => false);
			}
			if ($i == strlen($word) - 1) {
				$trienode['children'][$char] = array('isword' => true);
			}
			$trienode =& $trienode['children'][$char];
			$i ++;
		}
	}
	
	# 判断是否为词典词 #
	function &is_word($word = '') {
		$trienode =& $this->trie;
		$i = 0;
		while ($i < strlen($word)) {
			$char = $word[$i];
			if (!isset($trienode['children'][$char])) {
				return false;
			}
			
			// 判断词结束
			if ($i == strlen($word) - 1 && $trienode['children'][$char]['isword'] == true) {
				return true;
			} elseif ($i == strlen($word) - 1 && $trienode['children'][$char]['isword'] == false) {
				return false;
			}
			$trienode =& $trienode['children'][$char];
			$i ++;
		}
	}
	
	function search($text = null) {
		$textlen = strlen($text);		
		$trienode = $tree = $this->trie;
		$find = array();
		$wordrootposition = 0; // 词根位置
		$prenode = false; // 回溯参数, 当词典ab, 在字符串aab中, 需要把$i向前回溯一次  
		$word = '';
			
		for ($i = 0; $i < $textlen; $i ++) {
			if (isset($trienode['children'][$text[$i]])) {
				$word .= $text[$i];
				$trienode = $trienode['children'][$text[$i]];
				if (!$prenode) {
					$wordrootposition = $i;
				}
				$prenode = true;
				if ($trienode['isword']) {
					$find[] = array('position' => $wordrootposition, 'word' => $word);
				}
			} else {
				// 恢复必要参数初始值, 并把当前的指针回溯一位
				$trienode = $tree;
				$word = '';
				if ($prenode) {
					$i -= 1;
					$prenode = false;
				}
			}
		}
		
		return $find;
	}
}
$trie = new trie2();
$trie->set_word('fuck');
$trie->set_word('you');
$trie->set_word('come');
$trie->set_word('on');
// var_dump($trie->is_word('fuck'));
// var_dump($trie->is_word('a'));
// var_dump($trie->is_word('db'));
// var_dump($trie->is_word('comeon'));
// var_dump($trie->is_word('you'));
print_r($trie->search('hello,tencent,i tell you sonme about bbe,
fuck you comeon baby,come on,baby,baby,come on,tellgyou fuckdkkdkflsjflsjf'));
?>