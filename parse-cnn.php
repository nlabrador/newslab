<?php

require "vendor/autoload.php";
use PHPHtmlParser\Dom;

//For testing only
//$html = file_get_contents('cnn.html');

$html = file_get_contents('https://cnnphilippines.com/');

$dom = new Dom;
$dom->load($html);

$latest = $dom->find('.xs-special')[0];
$contents = $latest->find('li');

if (file_exists('csvs/cnn.csv')) {
    exec('rm csvs/cnn.csv');
}

$match = file_get_contents('match.csv');
$matches = explode(",", $match);

$entries = [];
foreach ($contents as $content) {
    $title = $content->find('a')[0];
    $url = 'https://cnnphilippines.com'.$title->getAttribute('href');
    $title = $title->text;

    foreach ($matches as $m) {
        if (preg_match("/$m/i", $title)) {
            $id = $url;

            if ($entries[$id]) {
                continue;
            }
            else {
                $entries[$id] = true;

                $csv = sprintf("%s,%s\n", $title, $url);

                file_put_contents('csvs/cnn.csv', $csv, FILE_APPEND | LOCK_EX);
            }
        }
    }
}
