<?php
include(dirname(__FILE__) . '/include/config.php');
include(dirname(__FILE__) . '/include/autoload.php');

$output = $pagevars = array();
$urlpath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$urlpath = explode('/', $urlpath);
$page = current($urlpath);
$db = new db();
$db->query('UPDATE visitors SET count = count + 1');
$db->execute();
$db->query('SELECT count FROM visitors LIMIT 1');
$db->execute();
$pagevars['visitorcount'] = str_pad($db->fetch()->count, 8, '0', STR_PAD_LEFT);
$pagevars['css'] = file_get_contents(DIR_CSS . 'home.min.css');

switch ($page) {
	default:
		$template = 'home.twig';
		break;
}

$loader = new \Twig\Loader\FilesystemLoader(array(DIR_TPL, DIR_TPL . 'home/', DIR_TPL . 'include/'));
$twig = new \Twig\Environment($loader, array(
	'cache' => DIR_CACHE,
	'debug' => (bool) ISDEV
));
if(ISDEV){
	$twig->addExtension(new \Twig\Extension\DebugExtension());
}

$output[] = $twig->render($template, $pagevars);
$output = implode('', $output);
print $output;
?>