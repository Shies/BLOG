<?PHP
class GoodsAction extends Controller {
	
	function __construct() {
		parent::__construct();
		$this->app->init(true);
		$this->load->model('goods');
	}
	
	function lists() {
		$page = intval(getgpc('page', 'G'));
		if ($page == 0) {
			$page = 1;
		}
		$pagesize = 10;
		
		$goodsname = trim(getgpc('gname', 'G'));

		$where = '1';
		if ($goodsname !== '') {
			$where .= " AND goodsname = '$goodsname'";
		}
		
		$query_string = "index.php?app=goods&act=lists&gname=".$goodsname."&adm=1";
		$this->load->file('pager', 'helper');
		$count = $_ENV['goods']->get_goods_count($where);	
		$pager = pager($count, $page, $pagesize, $query_string);
		if (isset($pager['offset'])) {
			$offset = intval($pager['offset']);
		} else {
			$offset = 0;
		}
		$lists = $_ENV['goods']->get_goods_list( 
			array('offset' => $offset, 'num' => $pagesize), $where);
			
		$adv_val = array(
			'goodsname' => $goodsname
		);
		
		$this->view->assign('adv_val', $adv_val);
		$this->view->assign('lists', $lists);
		$this->view->assign('pager', $pager);
		
		$this->view->display('goods.htm');
	}
	
}
?>