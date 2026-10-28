<?php
// Generates public/img/stars.svg: a seamless 512px tile of pixel stars.
// The CSS repeats it at three sizes for the parallax starfield.
// Run: php tools/make-stars.php (the output is committed)
mt_srand(1984);
$size = 512;
$path = '';
for ($i = 0; $i < 70; $i++) {
	$x = mt_rand(0, $size - 2);
	$y = mt_rand(0, $size - 2);
	$s = mt_rand(1, 10) === 1 ? 2 : 1; // the odd bigger star
	$path .= "M{$x} {$y}h{$s}v{$s}h-{$s}z";
}
$svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"$size\" height=\"$size\"><path d=\"$path\" fill=\"#fff\"/></svg>\n";
file_put_contents(dirname(__DIR__) . '/public/img/stars.svg', $svg);
echo 'Wrote public/img/stars.svg (' . strlen($svg) . " bytes)\n";
