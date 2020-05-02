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

if (file_exists('/home/ubuntu/newslab/csvs/cnn.csv')) {
    exec('rm /home/ubuntu/newslab/csvs/cnn.csv');
}

$match = file_get_contents('/home/ubuntu/newslab/match.csv');
$matches = explode(",", $match);

$entries = [];
foreach ($contents as $content) {
    $title = $content->find('a')[0];
    $url = 'https://cnnphilippines.com'.$title->getAttribute('href');
    $title = $title->text;
    $title = preg_replace('/,/', ' ', $title);

    $entries = [];
    foreach ($matches as $m) {
        if (preg_match("/$m/i", $title)) {
            $id = $url;

            $html = file_get_contents($url);

            $dom = new Dom;
            $dom->load($html);

            $imgContainer = $dom->find('.img-container');
            $img = isset($imgContainer[0]) ? $imgContainer[0]->find('img') : '';
            $img = isset($img[0]) ? 'https://cnnphilippines.com'.$img[0]->getAttribute('src') : '';

            $vidContainer = $dom->find('.video-container');
            $vid = isset($vidContainer[0]) ? $vidContainer[0]->find('iframe') : '';
            $vid = isset($vid[0]) ? $vid[0]->getAttribute('src') : '';

            $row = $dom->find('div[id*="content-body-"]');
            $ps = $row[0]->find('p');

            $summary = '';
            foreach ($ps as $p) {
                if ($p->text != " ") {
                    $text = $p->text;
                    $text = preg_replace('/,/', ' ', $text);
                    $summary = $summary . $text;
                }
            }

            if (isset($entries[$id])) {
                continue;
            }
            else {
                $entries[$id] = true;

                $csv = sprintf("%s,%s,%s,%s,%s\n", $title, $url, $vid, $summary, $img);

                file_put_contents('/home/ubuntu/newslab/csvs/cnn.csv', $csv, FILE_APPEND | LOCK_EX);
            }
        }
    }
}
