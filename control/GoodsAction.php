<?PHP
class GoodsAction extends Controller {
	
	function __construct() {
		parent::__construct();		
		$this->app->init(false);
		// $this->load->store('Session', 'include/sessctrl');		
		$this->load->model('goods');
	}
	
	function lists() {
		echo "hello world";
		/*
		$page = intval(getgpc('page', 'G'));
		if (!$page) {
			$page = 1;
		}
		$pagesize = 10;
		
		$count = $_ENV['goods']->get_goods_count();
		
		$this->load->file('pager', 'helper');
		$pager = pager(
			$count, $page, $pagesize, 
			'index.php?app=goods&act=lists'
		);
		if (isset($pager['offset'])) {
			$offset = intval($pager['offset']);
		} else {
			$offset = 0;
		}
		
		$goods = $_ENV['goods']->get_goods_list(
			array('offset' => $offset, 'num' => $pagesize)
		);
		
		$this->view->assign('goods', $goods);
		$this->view->assign('pager', $pager);
		
		*/
		$this->view->display('goods.htm');
	}
	
	function detail() {
		$goods_id = intval(getgpc('id', 'G'));
		if ($goods_id <= 0) {
			show_msg('You request\'s id invalid');
		}
		
		$cache_file = $this->app->ini_cache();
		if ($cache_file->get($goods_id)) {
			$goods = $cache_file->get($goods_id);
		} else {
			$goods = $_ENV['goods']->get_goods_info($goods_id);
			$cache_file->save($goods_id, $goods);
		}
		
		if (!$goods) {
			show_msg('Not exists');
		}
		
		$this->view->assign('goods', $goods);
	
		$this->view->display('goods.htm');
	}
}
?>