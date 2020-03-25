<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

//For testing only
//$html = file_get_contents('doh.html');

//exec("curl -k https://www.doh.gov.ph > /home/ubuntu/doh.html");
//$html = file_get_contents('/home/ubuntu/doh.html');

$dom = new Dom;
$dom->load($html);

$imgs = $dom->find('img');
$img = $imgs[21];

$src = null;

if ($img) {
    $src = $img->getAttribute('src');
}

if (preg_match('/data:image/', $src)) {
    $csv = "image,".$src;
}
else {
    $as_of = $dom->find('h3');
    $strong = $as_of[1]->find('strong');
    $spans = $strong[0]->find('span');

    $datetime = $spans[0]->text;

    $bigs = $dom->find('big');
    $confirmed = $bigs[1]->firstChild()->text;
    $negative = $bigs[3]->firstChild()->text;
    $pending = $bigs[5]->firstChild()->text;

    if (file_exists('csvs/doh.csv')) {
        exec('rm csvs/doh.csv');
    }

    $csv = sprintf('%s,%s,%s,%s',$datetime, $confirmed, $negative, $pending);

}

file_put_contents('csvs/doh.csv', $csv);
