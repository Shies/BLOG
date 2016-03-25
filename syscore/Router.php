<?PHP
class Router {

	private static $directory;
	private static $class = 'passport';
	private static $method = 'login';
	private static $params = array();

    public static function parseUrl()
    {
        if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME']))
        {
            $uri = $_SERVER['REQUEST_URI'];
            $httpPre = (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
            if (strpos($uri, $httpPre) === 0)
            {
                $uri = substr($uri, strlen($httpPre));
            }
            if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
            {
                $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            }
            elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
            {
                $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
            }
            $parts = preg_split('#\?#i', $uri, 2);
            $uri = $parts[0];
            if ($uri && $uri != '/')
            {
                $uri = parse_url($uri, PHP_URL_PATH);
                $uri = str_replace(array('//', '../'), '/', trim($uri, '/'));
                // $uri = removeInvisibleCharacters($uri);
                //兼容.php后缀
                $uri = preg_replace("|.php$|", "", $uri);
                if ($uri)
                {
                    if (!preg_match("|^[" . str_replace(array('\\-', '\-'), '-', preg_quote('a-z 0-9~%.:_\-/', '-')) . "]+$|i", $uri))
                    {
                        header("HTTP/1.0 400 Bad Request");
						exit;
                    }
                    $bad = array('$', '(', ')', '%28', '%29');
                    $good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;');
                    $uri = str_replace($bad, $good, $uri);
                    $segments = explode('/', preg_replace("|/*(.+?)/*$|", "\\1", $uri));
                    if (is_dir(ROOT_PATH . '/control/' . $segments[0]))
                    {
                        self::$directory = $segments[0] . '/';
                        unset($segments[0]);
                        if (isset($segments[1]))
                        {
                            self::$class = preg_replace_callback(
                                    '#((.)\_(.))#', create_function(
                                            '$matches', 'return $matches[2] . strtoupper($matches[3]);'
                                    ), $segments[1]
                            );
                            unset($segments[1]);
                            if (isset($segments[2]))
                            {
                                self::$method = preg_replace_callback(
                                    '#((.)\_(.))#', create_function(
                                    '$matches', 'return $matches[2] . strtoupper($matches[3]);'
                                ), $segments[2]
                                );
                                unset($segments[2]);
                            }
                        }
                    }
                    else
                    {
                        self::$class = preg_replace_callback(
                                '#((.)\_(.))#', create_function(
                                        '$matches', 'return $matches[2] . strtoupper($matches[3]);'
                                ), $segments[0]
                        );
                        unset($segments[0]);
                        if (isset($segments[1]))
                        {
                            self::$method = $segments[1];
                            unset($segments[1]);
                        }
                    }
                    self::$params = array_values($segments);
                }
            }
        }
    }

    public static function fetchDirectory()
    {
        return self::$directory;
    }

    public static function fetchClass()
    {
        return self::$class;
    }

    public static function fetchMethod()
    {
        return self::$method;
    }

    public static function fetchParams()
    {
        return self::$params;
    }
	
}