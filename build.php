<?php
// Builds the static site into public/ from data/cv.php and tpl/.
// Run on every deploy and daily by cron (so the years of experience stay current):
//   php build.php
if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

require __DIR__ . '/vendor/autoload.php';

const PUBLIC_DIR = __DIR__ . '/public/';

$cv = require __DIR__ . '/data/cv.php';
$today = new DateTimeImmutable('today');

$years = (new DateTimeImmutable($cv['career_start']))->diff($today)->y;

// Replace {years} in every string in the data
array_walk_recursive($cv, function (&$value) use ($years) {
	if (is_string($value)) {
		$value = str_replace('{years}', (string) $years, $value);
	}
});

foreach ($cv['experience'] as &$job) {
	$start = new DateTimeImmutable($job['start'] . '-01');
	$end = $job['end'] ? new DateTimeImmutable($job['end'] . '-01') : $today;
	$span = $start->diff($end);
	$job['start_label'] = $start->format('M Y');
	$job['end_label'] = $job['end'] ? $end->format('M Y') : 'present';
	$job['score'] = sprintf('%02dY %02dM', $span->y, $span->m);
}
unset($job);

// Last content change, for the sitemap and structured data. Falls back to today
// outside a git checkout.
$lastCommit = trim((string) shell_exec('git -C ' . escapeshellarg(__DIR__) . ' log -1 --format=%cI 2>/dev/null'));
$modified = $lastCommit !== '' ? new DateTimeImmutable($lastCommit) : $today;

// Cache-busting version for the stylesheet, from its contents
$cssVersion = substr(md5_file(PUBLIC_DIR . 'css/main.css'), 0, 8);

$person = [
	'@type' => 'Person',
	'@id' => $cv['url'] . '#person',
	'name' => $cv['name'],
	'alternateName' => $cv['alternate_name'],
	'jobTitle' => $cv['job_title'],
	'description' => $cv['description'],
	'url' => $cv['url'],
	'email' => 'mailto:' . $cv['email'],
	'image' => $cv['url'] . 'img/ogimage.png',
	'address' => [
		'@type' => 'PostalAddress',
		'addressLocality' => $cv['locality'],
		'addressCountry' => $cv['country'],
	],
	'worksFor' => [
		'@type' => 'Organization',
		'name' => $cv['experience'][0]['company'],
	],
	'alumniOf' => array_map(fn($q) => [
		'@type' => 'EducationalOrganization',
		'name' => $q['place'],
	], $cv['qualifications']),
	'hasCredential' => array_map(fn($q) => [
		'@type' => 'EducationalOccupationalCredential',
		'name' => $q['title'],
		'credentialCategory' => 'degree',
		'recognizedBy' => ['@type' => 'EducationalOrganization', 'name' => $q['place']],
	], $cv['qualifications']),
	'knowsAbout' => array_values(array_unique(array_merge(...array_map(
		fn($s) => $s['items'] ?? [],
		array_slice($cv['skills'], 0, 7)
	)))),
	'sameAs' => array_column($cv['links'], 'url'),
];

$structuredData = [
	'@context' => 'https://schema.org',
	'@graph' => [
		[
			'@type' => 'WebSite',
			'@id' => $cv['url'] . '#website',
			'name' => $cv['name'],
			'url' => $cv['url'],
		],
		[
			'@type' => 'ProfilePage',
			'@id' => $cv['url'] . '#page',
			'url' => $cv['url'],
			'name' => $cv['name'] . ' – ' . $cv['job_title'],
			'isPartOf' => ['@id' => $cv['url'] . '#website'],
			'dateModified' => $modified->format(DATE_ATOM),
			'mainEntity' => $person,
		],
	],
];

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/tpl'), [
	'strict_variables' => true,
	'autoescape' => 'name',
]);

$vars = [
	'cv' => $cv,
	'years' => $years,
	'year' => $today->format('Y'),
	'css_version' => $cssVersion,
	'modified' => $modified,
	'structured_data' => json_encode(
		$structuredData,
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR
	),
];

$outputs = [
	'index.html' => 'index.html.twig',
	'404.html' => '404.html.twig',
	'llms.txt' => 'llms.txt.twig',
	'sitemap.xml' => 'sitemap.xml.twig',
];

// Characters the subset font covers (tools/font-unicodes.txt)
$fontRanges = array_map(function ($range) {
	[$from, $to] = array_pad(explode('-', substr(trim($range), 2)), 2, null);
	return [hexdec($from), hexdec($to ?? $from)];
}, explode(',', trim(file_get_contents(__DIR__ . '/tools/font-unicodes.txt'))));

function missingGlyphs(string $html, array $ranges): array
{
	$text = html_entity_decode(strip_tags(preg_replace('#<(script|head)\b.*?</\1>#s', '', $html)), ENT_QUOTES | ENT_HTML5);
	$missing = [];
	foreach (mb_str_split(preg_replace('/\s+/u', ' ', $text)) as $char) {
		$code = mb_ord($char);
		$covered = false;
		foreach ($ranges as [$from, $to]) {
			if ($code >= $from && $code <= $to) {
				$covered = true;
				break;
			}
		}
		if (!$covered) {
			$missing[$char] = sprintf('U+%04X', $code);
		}
	}
	return $missing;
}

foreach ($outputs as $file => $template) {
	$html = $twig->render($template, $vars);
	// Write to a temp file then rename, so the live site never serves a half-written file
	$tmp = PUBLIC_DIR . '.' . $file . '.tmp';
	file_put_contents($tmp, $html);
	chmod($tmp, 0644);
	rename($tmp, PUBLIC_DIR . $file);
	echo "Built public/$file (" . strlen($html) . " bytes)\n";
	if (str_ends_with($file, '.html') && $missing = missingGlyphs($html, $fontRanges)) {
		fwrite(STDERR, "Warning: public/$file uses characters the font subset lacks: "
			. implode(' ', array_map(fn($c, $u) => "$c ($u)", array_keys($missing), $missing))
			. "\nAdd them to tools/font-unicodes.txt and run tools/subset-font.sh\n");
	}
}
