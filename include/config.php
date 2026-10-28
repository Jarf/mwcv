<?php
define('DIR_ROOT', dirname(__DIR__) . '/');
define('DIR_VENDOR', DIR_ROOT . 'vendor/');
define('DIR_CLASSES', DIR_ROOT . 'classes/');
define('DIR_TPL', DIR_ROOT . 'tpl/');
define('DIR_CSS', DIR_ROOT . 'css/');

define('SITE_CSS', '/css/');
define('SITE_FONTS', '/fonts/');
define('SITE_IMG', '/img/');

$env = parse_ini_file(DIR_ROOT . '.env', false, INI_SCANNER_RAW);
if ($env === false) {
	error_log('Missing or unreadable .env file');
	http_response_code(500);
	exit('Server configuration error');
}
define('DB_USER', $env['DB_USER'] ?? '');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? '');
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_TYPE', $env['DB_TYPE'] ?? 'mysql');
unset($env);

$dev = 0;

if(isset($_SERVER['SERVER_NAME'])){
	$servername = $_SERVER['SERVER_NAME'];
	$dev = $servername === 'mwcv.local' ? 1 : 0;
}else{
	switch (gethostname()) {
		case 'localhost':
			$servername = 'martinwadeson.com';
			break;
		
		default:
			$servername = 'mwcv.local';
			$dev = 1;
			break;
	}
}

define('ISDEV', $dev);
// Compiled Twig templates; disabled in dev so template edits show immediately
define('DIR_CACHE', ISDEV ? false : DIR_ROOT . 'cache/twig/');

$https = false;
if(isset($_SERVER['HTTPS'])){
	$https = true;
}elseif(isset($_SERVER['SERVER_PROTOCOL']) && stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0){
	$https = true;
}
define('SITE_ROOT', ($https ? 'https' : 'http') . '://' . $servername . '/');
?>
