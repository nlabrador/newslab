<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

//For testing only
//$html = file_get_contents('abs.html');

$html = file_get_contents('https://news.abs-cbn.com');

$dom = new Dom;
$dom->load($html);

$latest = $dom->find('#latestnews')[0];
$contents = $latest->find('.post-title');

if (file_exists('csvs/abs.csv')) {
    exec('rm csvs/abs.csv');
}

$match = file_get_contents('match.csv');
$matches = explode(",", $match);

$entries = [];
foreach ($contents as $content) {
    $time = $content->find('time');
    $datetime = null;

    try {
        $datetime = $time->getAttribute('datetime');
    }
    catch (Exception $e) {
        $datetime = null;
    }

    if ($datetime) {
        $link = $content->find('a')[0];
        $title = $link->text;
        $title = preg_replace('/,/', ' ', $title);

        foreach ($matches as $m) {
            if (preg_match("/$m/i", $title)) {
                $url = 'https://news.abs-cbn.com' . $link->getAttribute('href');
                $id = $url;

                if ($entries[$id]) {
                    continue;
                }
                else {
                    $entries[$id] = true;

                    $csv = sprintf("%s,%s,%s\n", $datetime, $title, $url);

                    file_put_contents('csvs/abs.csv', $csv, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
