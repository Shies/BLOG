<?PHP

// Sphinx 全文检索引擎(Sphinx API Interface app) ! important
class search_interface {

	function __construct() {
		Base::load_config('sphinxapi', 'include/sphinx/sphinx-win32/api', false);
		$this->sph = new SphinxClient();
		$settings = Base::load_config('sphinx', 'data', true);
		
		$mode = SPH_MATCH_EXTENDED2; // 匹配模式
		$host = $settings['sphinxhost'];
		$port = $settings['sphinxport'];
		$ranker = SPH_RANK_PROXIMITY_BM25; // 统计相关度计算模式，仅使用BM25评分计算
	
		$this->sph->SetServer($host, $port);
		$this->sph->SetConnectTimeout(1);
		$this->sph->SetArrayResult(true);
		$this->sph->SetMatchMode($mode);
		$this->sph->SetRankingMode($ranker);
	}
	
	function search($keywords, $where = array(), $offset = 0, $limit = 20, 
		/* $datetime = array(), */ $orderby = '@id desc') {
		if (!defined('CHARSET')) {
			define('CHARSET', 'utf-8');
		}
		
		$keywords = @iconv('gbk', CHARSET, $keywords);
		
		if ($where && is_array($where)) {
			foreach ($where AS $key => $val) {
				if (!$val) continue;
				$this->sph->SetFilter($key, $val);
			}
			$where = array();
		}
		
		/*
		if ($datetime && is_array($datetime)) {
			reset($datetime);
			$key = key($datetime);
			$val = current($datetime);
			$val = array_map('strtotime', $val);
			
			$this->sph->SetFilterRange($key, array_shift($val), array_pop($val));
		}
		*/
		
		if ($orderby) {
			$this->sph->SetSortMode(SPH_SORT_EXTENDED, $orderby);
		}
		
		if ($limit) {
			$this->sph->SetLimits($offset, $limit, $limit > 1000 ? $limit : 1000);
		}
		
		$this->sph->setMaxQueryTime(10000);
		
		// main, delta // 主量和增量索引
		$res = $this->sph->query($keywords, 'test1, testrt'); 
		
		return $res;
	}
}

// OK
// $sph = Base::load_class('search_interface', 'include/sphinx');
// print_r($sph->search('test', array('group_id' => array(2)), 0, 20, 'id DESC'));
// exit;