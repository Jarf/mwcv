<?php
define('DIR_ROOT', dirname(__DIR__) . '/');
define('DIR_INCLUDE', DIR_ROOT . 'include/');
define('DIR_VENDOR', DIR_ROOT . 'vendor/');
define('DIR_CLASSES', DIR_ROOT . 'classes/');
define('DIR_TPL', DIR_ROOT . 'tpl/');
define('DIR_CACHE', false);
define('DIR_CSS', DIR_ROOT . 'css/');
define('DIR_JS', DIR_ROOT . 'js/');
define('DIR_IMG', DIR_ROOT . 'img/');

define('SITE_CSS', '/css/');
define('SITE_JS', '/js/');
define('SITE_VENDOR', '/vendor/');
define('SITE_AJAX', '/ajax/');
define('SITE_ICO', '/ico/');
define('SITE_FONTS', '/fonts/');
define('SITE_IMG', '/img/');

define('DB_USER', 'cv');
define('DB_PASS', 'WNPKCkSGOnIw5DJ4');
define('DB_NAME', 'cv');
define('DB_HOST', 'localhost');
define('DB_TYPE', 'mysql');

define('TURNSTILE_KEY', '0x4AAAAAACNZ0IxxkHtnfVr_');

$dev = 0;

if(isset($_SERVER['SERVER_NAME'])){
	$servername = $_SERVER['SERVER_NAME'];
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

$https = false;
if(isset($_SERVER['HTTPS'])){
	$https = true;
}elseif(isset($_SERVER['SERVER_PROTOCOL']) && stripos($_SERVER['SERVER_PROTOCOL'], 'https') === 0){
	$https = true;
}
define('SITE_ROOT', ($https ? 'https' : 'http') . '://' . $servername . '/');
?>