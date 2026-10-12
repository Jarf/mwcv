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
$pagevars['inlinecss'] = file_get_contents(DIR_CSS . 'main.css');

switch ($page) {
	default:
		$template = 'home.twig';
		break;
}

$loader = new \Twig\Loader\FilesystemLoader(array(DIR_TPL, DIR_TPL . 'include/', DIR_CSS));
$twig = new \Twig\Environment($loader, array(
	'cache' => DIR_CACHE,
	'debug' => (bool) ISDEV
));
if(ISDEV){
	$twig->addExtension(new \Twig\Extension\DebugExtension());
}

$interval = date_diff(date_create("2012-09-01"), date_create(date('Y-m-d')));
$pagevars['years_experience'] = $interval->format("%y");

$output[] = $twig->render($template, $pagevars);
$output = implode('', $output);
print $output;
?>