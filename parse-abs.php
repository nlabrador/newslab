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

if (file_exists('/home/ubuntu/newslab/csvs/abs.csv')) {
    exec('rm /home/ubuntu/newslab/csvs/abs.csv');
}

$match = file_get_contents('/home/ubuntu/newslab/match.csv');
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

    $entries = [];
    if ($datetime) {
        $link = $content->find('a')[0];
        $title = $link->text;
        $title = preg_replace('/,/', ' ', $title);

        foreach ($matches as $m) {
            if (preg_match("/$m/i", $title)) {
                $url = 'https://news.abs-cbn.com' . $link->getAttribute('href');
                $id = $url;
                $html = file_get_contents($url);
                $dom = new Dom;
                $dom->load($html);

                $articleContent = $dom->find('.article-content');
                $video = '';
                $img = '';
                if (isset($articleContent[0])) {
                    $figures = $articleContent[0]->find('figure');

                    if (isset($figures[0])) {
                        $img = $figures[0]->find('img');
                        $img = isset($img[1]) ? $img[1]->getAttribute('src') : '';
                    }

                    $ps = $articleContent[0]->find('p');

                    $summary = '';
                    foreach ($ps as $p) {
                        $text = $p->text;
                        $text = preg_replace('/,/', ' ', $text);
                        $summary = $summary . "<br><br>" . $text;
                    }
                }
                else {
                    $iframe = $dom->find('iframe');
                    $video = isset($iframe[0]) ? $iframe[0]->getAttribute('src') : '';
                    $article = $dom->find('article');
                    $metas = $article[0]->find('meta');
                    $summary = $metas[9]->getAttribute('content');
                    $summary = preg_replace('/,/', ' ', $summary);
                    $img = '';
                }

                if (isset($entries[$id])) {
                    continue;
                }
                else {
                    $entries[$id] = true;

                    $csv = sprintf("%s,%s,%s,%s,%s,%s\n", $datetime, $title, $url,$video,$summary,$img);

                    file_put_contents('/home/ubuntu/newslab/csvs/abs.csv', $csv, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
