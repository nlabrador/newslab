<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

//For testing only
exec('curl https://www.sunstar.com.ph/ > sunstar.html');
//$html = file_get_contents('https://www.sunstar.com.ph');
$html = file_get_contents('sunstar.html');

$dom = new Dom;
$dom->load($html);

$latest = $dom->find('.topStoriesArticles')[0];
$contents = $latest->find('.col-right');

if (file_exists('csvs/sunstar.csv')) {
    exec('rm csvs/sunstar.csv');
}

$match = file_get_contents('match.csv');
$matches = explode(",", $match);

$entries = [];
foreach ($contents as $content) {
    $time = $content->find('.title-C12')[0];
    $datetime = $time->text;
    $datetime = preg_replace("/^\s+/",'',$datetime);
    $datetime = preg_replace("/,/",'',$datetime);

    if ($datetime) {
        $title1 = $content->find('.title-B16')[0];
        $title2 = $content->find('.title-C16')[0];
        $link = $title2->getAttribute('href');
        $title = $title1->text . " - " . $title2->text;

        foreach ($matches as $m) {
            if (preg_match("/$m/i", $title)) {
                $url = $link; 
                $id = $url;

                if ($entries[$id]) {
                    continue;
                }
                else {
                    $entries[$id] = true;

                    $csv = sprintf("%s,%s,%s\n", $datetime, $title, $url);

                    file_put_contents('csvs/sunstar.csv', $csv, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
