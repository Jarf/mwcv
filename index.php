<?php
include(dirname(__FILE__) . '/include/config.php');
include(dirname(__FILE__) . '/include/autoload.php');

$pagevars = array();
$page = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$isHome = in_array($page, array('', 'index.php'), true);
if(!$isHome){
	http_response_code(404);
}

$db = new db();
// Only count real visits to the homepage, not bots or unknown URLs
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isBot = $userAgent === '' || preg_match('/bot|crawl|spider|slurp|curl|wget|python|httpclient|headless|preview|scan/i', $userAgent);
if($isHome && !$isBot){
	$db->query('UPDATE visitors SET count = count + 1');
	$db->execute();
}
$db->query('SELECT count FROM visitors LIMIT 1');
$db->execute();
$pagevars['visitorcount'] = str_pad($db->fetch()->count, 8, '0', STR_PAD_LEFT);
$pagevars['cssversion'] = filemtime(DIR_CSS . 'main.css');

$interval = date_diff(date_create("2012-09-01"), date_create(date('Y-m-d')));
$pagevars['years_experience'] = $interval->format("%y");

$loader = new \Twig\Loader\FilesystemLoader(array(DIR_TPL, DIR_TPL . 'include/'));
$twig = new \Twig\Environment($loader, array(
	'cache' => DIR_CACHE,
	// Recompile when a template changes, so deploys don't need a cache clear
	'auto_reload' => true,
	'debug' => (bool) ISDEV
));
if(ISDEV){
	$twig->addExtension(new \Twig\Extension\DebugExtension());
}

print $twig->render('home.twig', $pagevars);
?>
