<?PHP
class Syserror {

	const SAVE_PATH = 'data/errlog/';
	
	function __construct() {
	}

	static function error($msg, $show = true, $save = true, $halt = true) {
		if (!empty($msg)) {
			if (isset(Base::app()->lang[$msg])) {
				$msg = Base::app()->lang[$msg];
			}
		} else { // __CLASS__
			die(__FUNCTION__);
		}
		
		list($show, $log) = self::debug_backtrace();		
		if ($save) {
			$msgsave = "Error：".$msg." ----- ".$log;
			file_put_contents(ROOT_PATH.self::SAVE_PATH.'error.log', $msgsave);
		}
		
		if ($show) {
			echo 'Error：'.$msg.' <br /> '.$show;
		}
		
		if ($halt) {
			exit;
		} else {
			return $msg;
		}
	}

	static function exception($exception) {
		if ($exception instanceof Exception) {
			$type = 'system';
		} else {
			$type = '';
		}
		
		if ('' === $type) {
			return;
		} else {
			$message = $exception->getMessage();
		}
		
		$trace = $exception->getTrace();
		krsort($trace);
		
		$trace[] = array('file' => $exception->getFile(), 'line' => $exception->getLine(), 'msg' => $message);
		foreach ($trace AS $error) {
			if ($error) print_r($error).PHP_EOL;
		}
		
		return true;
	}

	static function debug_backtrace() {
		$return = array('', '');
		
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);		
		foreach ($debug_backtrace AS $error) {
			self::backtrace($return, $error);
		}
		
		return $return;
	}
	
	static function backtrace(&$return, $error) {	
		$file = str_replace(ROOT_PATH, '', $error['file']);
		
		$func = isset($error['class']) ? $error['class'] : '';
		$func .= isset($error['type']) ? $error['type'] : '';
		$func .= isset($error['function']) ? $error['function'] : '';
		if ($func === '') {
			return;
		}
		$error['line'] = sprintf('%04d', $error['line']);
		
		$return[0] .= $file.'('.$func.') => '.$error['line'].PHP_EOL;
		$return[1] .= !empty($return[1]) ? ' -> ' : '';
		$return[1] .= $file.'：'.$error['line'];
	}
}