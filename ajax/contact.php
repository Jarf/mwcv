<?php
$return = false;
if(isset($_POST) && isset($_POST['token']) && isset($_POST['name']) && isset($_POST['contact']) && isset($_POST['message'])){
	include(dirname(__DIR__) . '/include/config.php');
	include(dirname(__DIR__) . '/include/autoload.php');

	$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
	$dotenv->load();

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => $_ENV['TURNSTILE_SECRET'], 'response' => $_POST['token'])));
	$response = curl_exec($ch);
	$response = @json_decode($response);
	if($response !== null){
		$return = $response->success;
		$db = new db();
		$db->query('INSERT INTO contact (name, email, message) VALUES (:name, :contact, :message)');
		$db->bind('name', $_POST['name'], PDO::PARAM_STR);
		$db->bind('contact', $_POST['contact'], PDO::PARAM_STR);
		$db->bind('message', $_POST['message'], PDO::PARAM_STR);
		$db->execute();
	}
}
header('Content-type: application/json; charset=utf-8');
print $return ? 1 : 0;
?>