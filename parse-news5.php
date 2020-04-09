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

if (file_exists('/home/ubuntu/newslab/csvs/news5.csv')) {
    exec('rm /home/ubuntu/newslab/csvs/news5.csv');
}

$match = file_get_contents('/home/ubuntu/newslab/match.csv');
$matches = explode(",", $match);

$entries = [];
foreach ($contents as $content) {
    $time = $content->find('.category')[0];
    $datetime = $time->text;
    $datetime = preg_replace('/^.*On: /', '', $time);
    $datetime = preg_replace('/, /', ' ', $datetime);
    $datetime = preg_replace('/ <.*$/', '', $datetime);

    $entries = [];
    if ($datetime) {
        $title = $content->find('.title')[0];
        $link = $content->getAttribute('href');
        $title = $title->text;
        $title = preg_replace('/^\s+/','',$title);
        $title = preg_replace('/\s+$/','',$title);
        $title = preg_replace('/,/',' ',$title);

	if (preg_match('/http/', $link)) {
            $url = $link; 
	}
	else {
            $url = 'https://news.tv5.com.ph'.$link; 
	}

        foreach ($matches as $m) {
            if (preg_match("/$m/i", $title)) {
                $id = $url;
                $html = file_get_contents($url);

                $dom = new Dom;
                $dom->load($html);

                $post = $dom->find('.post-cont');
                $ps = $post[0]->find('p');

                $summary = '';
                foreach ($ps as $p) {
                    $text = $p->text;
                    $text = preg_replace('/,/', '', $text);

                    $summary = $summary . '<br><br>' . $text;
                }

                if (isset($entries[$id])) {
                    continue;
                }
                else {
                    $entries[$id] = true;

                    $csv = sprintf("%s,%s,%s,%s,%s,%s\n", $datetime, $title, $url,'',$summary,'');

                    file_put_contents('/home/ubuntu/newslab/csvs/news5.csv', $csv, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}
