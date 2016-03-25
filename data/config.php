<?php
// database host
define('DB_HOST', "localhost:3306");

// database name
define('DB_NAME', "payment");

// database username
define('DB_USER', "root");

// database password
define('DB_PASS', "123456");

// table prefix
define('PREFIX', "");

// system charset
define('RK_CHARSET', 'utf-8');

// ------------------- db ---------------------- //

$_config['db'][1]['dbhost'] = DB_HOST;
$_config['db'][1]['dbuser'] = DB_NAME;
$_config['db'][1]['dbpw'] = DB_PASS;
$_config['db'][1]['dbcharset'] = RK_CHARSET;
$_config['db'][1]['pconnect'] = 0;
$_config['db'][1]['dbname'] = DB_NAME;
$_config['db'][1]['tablepre'] = PREFIX;
$_config['db']['slave'] = '';
$_config['db']['common']['slave_except_table'] = '';

// ------------------ output ----------------------- //

$_config['output']['charset'] = RK_CHARSET;
$_config['output']['forceheader'] = 1;
$_config['output']['gzip'] = '0';
$_config['output']['tplrefresh'] = 1;
$_config['output']['language'] = 'zh_cn';
$_config['output']['staticurl'] = 'static/';
$_config['output']['ajaxvalidate'] = '0';
$_config['output']['iecompatible'] = '0';

// ------------------ cookie ------------------ // 
$_config['cookie']['cookiepre'] = '8XdI_';
$_config['cookie']['cookiedomain'] = '';
$_config['cookie']['cookiepath'] = '/';

// ----------------- security ------------------ //

$_config['security']['authkey'] = 'fa38f64iY64BaPFc';
$_config['security']['urlxssdefend'] = 1;
$_config['security']['attackevasive'] = '0';
$_config['security']['querysafe']['status'] = 1;
$_config['security']['querysafe']['dfunction']['0'] = 'load_file';
$_config['security']['querysafe']['dfunction']['1'] = 'hex';
$_config['security']['querysafe']['dfunction']['2'] = 'substring';
$_config['security']['querysafe']['dfunction']['3'] = 'if';
$_config['security']['querysafe']['dfunction']['4'] = 'ord';
$_config['security']['querysafe']['dfunction']['5'] = 'char';
$_config['security']['querysafe']['daction']['0'] = '@';
$_config['security']['querysafe']['daction']['1'] = 'intooutfile';
$_config['security']['querysafe']['daction']['2'] = 'intodumpfile';
$_config['security']['querysafe']['daction']['3'] = 'unionselect';
$_config['security']['querysafe']['daction']['4'] = '(select';
$_config['security']['querysafe']['daction']['5'] = 'unionall';
$_config['security']['querysafe']['daction']['6'] = 'uniondistinct';
$_config['security']['querysafe']['dnote']['0'] = '/*';
$_config['security']['querysafe']['dnote']['1'] = '*/';
$_config['security']['querysafe']['dnote']['2'] = '#';
$_config['security']['querysafe']['dnote']['3'] = '--';
$_config['security']['querysafe']['dnote']['4'] = '"';
$_config['security']['querysafe']['dlikehex'] = 1;
$_config['security']['querysafe']['afullnote'] = '0';

// ----------------- debug ------------------ //

$_config['debug'] = 0;


?>