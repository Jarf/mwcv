<?php
// Retro hit counter, served as /counter.svg (see .htaccess). The page is static
// HTML, so this is the only PHP a visitor's browser runs. Only browsers that load
// images count, and known bots are skipped.
header('Content-Type: image/svg+xml');
header('Cache-Control: no-store');
// An Expires header stops mod_expires applying the 1-year SVG cache to this file
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$count = null;
try {
	require dirname(__DIR__) . '/include/config.php';
	require dirname(__DIR__) . '/classes/db.php';
	$db = new db();
	$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
	$isBot = $userAgent === '' || preg_match('/bot|crawl|spider|slurp|curl|wget|python|httpclient|headless|lighthouse|preview|scan/i', $userAgent);
	if (!$isBot && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
		$db->query('UPDATE visitors SET count = count + 1');
		$db->execute();
	}
	$db->query('SELECT count FROM visitors LIMIT 1');
	$db->execute();
	$count = (int) $db->fetch()->count;
} catch (Throwable $e) {
	error_log('Visitor counter failed: ' . $e->getMessage());
}

// 3x5 pixel digits, one string per row
const GLYPHS = [
	'0' => ['111', '101', '101', '101', '111'],
	'1' => ['010', '110', '010', '010', '111'],
	'2' => ['111', '001', '111', '100', '111'],
	'3' => ['111', '001', '111', '001', '111'],
	'4' => ['101', '101', '111', '001', '001'],
	'5' => ['111', '100', '111', '001', '111'],
	'6' => ['111', '100', '111', '101', '111'],
	'7' => ['111', '001', '010', '010', '010'],
	'8' => ['111', '101', '111', '101', '111'],
	'9' => ['111', '101', '111', '001', '111'],
];
const DIGITS = 8;
const CELL_W = 18;
const CELL_H = 26;
const PIXEL = 4;

// Blank cells if the count is unavailable, rather than a misleading number
$digits = $count === null ? str_repeat(' ', DIGITS) : str_pad((string) min($count, 10 ** DIGITS - 1), DIGITS, '0', STR_PAD_LEFT);

$cells = '';
$pixels = '';
foreach (str_split($digits) as $i => $digit) {
	$x = $i * CELL_W;
	$cells .= sprintf('M%d.5 .5h%dv%dh-%dz', $x, CELL_W - 1, CELL_H - 1, CELL_W - 1);
	foreach (GLYPHS[$digit] ?? [] as $row => $bits) {
		foreach (str_split($bits) as $col => $bit) {
			if ($bit === '1') {
				$pixels .= sprintf('M%d %dh%dv%dh-%dz', $x + 3 + $col * PIXEL, 3 + $row * PIXEL, PIXEL, PIXEL, PIXEL);
			}
		}
	}
}

$width = DIGITS * CELL_W;
printf(
	'<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d"><rect width="%1$d" height="%2$d"/><path d="%3$s" fill="none" stroke="#fff"/><path d="%4$s" fill="#0f0"/></svg>',
	$width,
	CELL_H,
	$cells,
	$pixels
);
