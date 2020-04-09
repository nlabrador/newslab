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

if (file_exists('/home/ubuntu/newslab/csvs/sunstar.csv')) {
    exec('rm /home/ubuntu/newslab/csvs/sunstar.csv');
}

$match = file_get_contents('/home/ubuntu/newslab/match.csv');
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
        $title = preg_replace('/,/', ' ', $title);

	    $entries = [];
        foreach ($matches as $m) {
            if (preg_match("/$m/i", $title)) {
                $url = $link; 
                $id = $url;

                exec("curl $url > sunstar.html");
                $html = file_get_contents('sunstar.html');

                $dom = new Dom;
                $dom->load($html);

                $item_div = $dom->find('.img'); 
                $img = isset($item_div[0]) ? $item_div[0]->find('img') : [];
                $img = isset($img[0]) ? $img[0]->getAttribute('src') : '';

                if (!$img) {
                    $item_div = $dom->find('.imgArticle'); 
                    $img = isset($item_div[0]) ? $item_div[0]->find('img') : [];
                    $img = isset($img[0]) ? $img[0]->getAttribute('src') : '';
                }

                $body = $dom->find(".articleBody");
                $ps = $body[1]->find('p');
                $summary = $ps[0]->innerHtml();
                $summary = preg_replace('/,/', '', $summary);

                if (isset($entries[$id])) {
                    continue;
                }
                else {
                    $entries[$id] = true;

                    $csv = sprintf("%s,%s,%s,%s,%s,%s\n", $datetime, $title, $url, '', $summary, $img);

                    file_put_contents('/home/ubuntu/newslab/csvs/sunstar.csv', $csv, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
