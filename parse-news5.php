<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

//For testing only
//$html = file_get_contents('tv5.html');

$html = file_get_contents('https://news.tv5.com.ph/');

$dom = new Dom;
$dom->load($html);

$latest = $dom->find('.sidebar')[0];
$contents = $latest->find('a');

if (file_exists('csvs/news5.csv')) {
    exec('rm csvs/news5.csv');
}

$match = file_get_contents('match.csv');
$matches = explode(",", $match);

$entries = [];
foreach ($contents as $content) {
    $time = $content->find('.category')[0];
    $datetime = $time->text;
    $datetime = preg_replace('/^.*On: /', '', $time);
    $datetime = preg_replace('/, /', ' ', $datetime);
    $datetime = preg_replace('/ <.*$/', '', $datetime);

    if ($datetime) {
        $title = $content->find('.title')[0];
        $link = $content->getAttribute('href');
        $title = $title->text;
        $title = preg_replace('/^\s+/','',$title);
        $title = preg_replace('/\s+$/','',$title);

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

                    file_put_contents('csvs/news5.csv', $csv, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
