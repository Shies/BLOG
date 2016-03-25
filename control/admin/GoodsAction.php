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
	
	function add() {
		$method = strtoupper($_SERVER['REQUEST_METHOD']);
		if ($method === 'POST') {
			$goodsname = trim(getgpc('goodsname', 'P'));
			
			$number = intval(getgpc('goodsnumber', 'P'));
			$goodssn = trim(getgpc('goodssn', 'P'));
			$shopprice = floatval(getgpc('shopprice', 'P'));
			
			$goodsdesc = htmlspecialchars(trim(getgpc('goosdspec', 'P')));
			
			if (empty($goodsname)) {
				$this->show_msg('Your goods name not can empty');
			} elseif (strlen($goodsname) > 255) {
				$this->show_msg('Your goods name length so max');
			}
			
			if (max($number, 0) == 0) {
				$this->show_msg('Your goods number for 0');
			}
			
			if (empty($goodssn)) {
				$this->show_msg('Your goods sn must sole');
			} elseif (!$_ENV['goods']->get_goods_sn($goodssn)) {
				$this->show_msg('Your goods sn can exsits');
			}
			
			if (strcmp($shopprice, 0) == 0) {
				$this->show_msg('Your goods name not can empty');
			}
			
			$goods = array(
				'goods_name' => $goods_name,
				'goods_sn'   => $goods_sn,
				'shop_price' => $shop_price,
				'goods_number' => $goods_number,
				'goods_desc' => $goods_desc
			);
			  
			
			
		} else {
			
		}
	}
	
	
	
}
?>