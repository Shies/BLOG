<?PHP
class PassportAction extends Controller {

	private $reg_type = array('user', 'email', 'mobile');

	public function __construct() {
		parent::__construct();
		$this->app->init(false);
		// $this->load->store('Session', 'include/sessctrl');		
		$this->load->model('passport');
	}
	
	public function index() {
		$this->check_user_real();
	}
	
	public function check_user_real() {
		if (empty($_SESSION['user_id'])) {
			$this->load->store('Cookie', 'include/sessctrl');			
			if ($this->cookie->check()) {
				$_SESSION['user_id'] = $this->cookie->get_userid();
				$_SESSION['username'] = $this->cookie->get_username();
				$_ENV['passport']->save_visit($_SESSION['user_id']);
				
				redirect('passport/welcome');
			}
			redirect('passport/login');
		}
	}
	
	public function welcome() {
		$this->check_user_real();
	}
	
	public function login() {
		if (IS_POST) {
			$username = trim(getgpc('username', 'G'));
			$password = trim(getgpc('password', 'G'));
			if (!empty($password)) {
				$password = md5(md5($password));
			}
			
			$captcha = intval(getgpc('captcha', 'G'));
			if (empty($captcha) || !isset($_SESSION['verify'])) {
				show_msg('验证码不能为空');
			} else {
				if (md5($captcha) != $_SESSION['verify']) {
					show_msg('验证码输入有误');
				}
			}

			$userinfo = $_ENV['passport']->get_user_info($username, $password);
			if (!$userinfo) {
				show_msg('登录失败');
			}
			
			if ($userinfo['is_special'] & 2 == 2) {
				show_msg('此用户已禁止使用');
			}
			
			if (true == getgpc('remember', 'G')) {
				$this->load->store('Cookie', 'include/sessctrl');
				$this->cookie->setvalue(
					$userinfo['user_id'], $userinfo['username']
				);
			}
			$_SESSION['user_id'] = $userinfo['user_id'];
			$_SESSION['username'] = $userinfo['username'];
			$_ENV['passport']->save_visit($userinfo['user_id']);
			
			redirect('passport/welcome');
		} else {
		
			$this->view->assign('random', mt_rand());
			
			$this->view->display('login.html');
		}
	}
	
	public function register() {
		if (IS_POST) {
			// username register
			if (in_array(getgpc('reg_type', 'G'), $this->reg_type)) {
				$username = getgpc('username', 'G');
				if (strlen($username) < 2) {
					show_msg('用户名最低不能少于2个字符');
				} else {
					if (strlen($username) > 14) {
						show_msg('用户名最长不能超过7个汉字');
					}
				}
			}
			
			$user_auth = $_ENV['passport']->get_user_by_name($username);
			if (!empty($user_auth)) {
				show_msg('此用户名已经被注册过');
			}
			
			$nick_name = trim(getgpc('nickname', 'G'));
			$sex 	   = intval(getgpc('sex', 'G'));
			$password  = trim(getgpc('password', 'G'));
			$comfirm_password = trim(getgpc('comfirm_password', 'G'));
			$captcha   = intval(getgpc('captcha', 'G'));
			
			if (strlen($password) < 6) {
				show_msg('密码最少不能低于6位');
			} elseif (strpos($password, ' ') > 0) {
				show_msg('密码不能含有空格字符');
			} else {
				if ($password != $comfirm_password) {
					show_msg('确认密码同上输入不一致');
				}
			}
			
			if (md5($captcha) != $_SESSION['verify']) {
				show_msg('验证码输入有误');
			}

			$userinfo = array(
				'nick_name'	  => $nick_name,
				'sex'		  => $sex,
				'password'	  => md5(md5($password)),
				'reg_time'	  => TIMESTAMP,
				'last_time'	  => date('Y-m-d H:i:s', TIMESTAMP),
				'last_login'  => TIMESTAMP,
				'last_ip'	  => $this->app->vars['ip'],
				'username'	  => $username,
				'email'		  => null,
				'is_email_validated' => 0
			);
			
			$insert_id = $this->db->autoExecute('users', $userinfo, 'INSERT');
			if ($insert_id <= 0) {
				show_msg('注册失败');
			}
			redirect('passport/login');
		} else {
			
			$this->view->assign('random', mt_rand());
			
			$this->view->display('register.html');
		}
	}
	
	public function logout() {
		$this->load->store('Cookie', 'include/sessctrl');
		$this->cookie->clear();
		unset($_SESSION['user_id'], $_SESSION['username']);
		
		redirect('passport/login');
	}
	
	public function captcha() {
		$this->load->store(array('Captcha'), 'include');
		$this->captcha->create(1);
	}
	
	public function is_registered() {
		$username = getgpc('username', 'G');
		if (!empty($username)) {
			$username = trim($username);
		}
		
		$user_auth = $_ENV['passport']->get_user_by_name($username);
		if (empty($user_auth)) {
			die(json2str('此名称可以使用'));
		}
		
		die(json2str('此名称已经被用'));
	}
	
	public function get_password() {
		
	}
}