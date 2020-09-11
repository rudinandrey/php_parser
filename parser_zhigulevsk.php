<?php

require("simple_html_dom.php");

function post($params) {
    if( $curl = curl_init() ) {
        $params["key"] = "test_news";
        $data = http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, 'http://lyubimiigorod.ru/api/news/add_one_news');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $out = curl_exec($curl);
        curl_close($curl);
        return $out;
    }
}

function lastLink($city) {
    $result = file_get_contents("http://lyubimiigorod.ru/api/news/get_last_link?key=test_news&city=$city");
    $data = json_decode($result, true);

    return $data["result"]["link"];
}

function addCount($city, $count) {
    $result = file_get_contents("http://lyubimiigorod.ru/api/news/add_metrika?key=test_news&category=0&city=$city&cnt=$count");
    $data = json_decode($result, true);
    return $data;
}


$url = "https://www.zhigulevsk.org/novostiview/";
$domain = "https://www.zhigulevsk.org";

$page = file_get_html($url);

$h2s = $page->find("h2 a");


$links = [];

foreach($h2s as $h2a) {
    $links[] = $domain.$h2a->href;
}

$allNews = [];

$last = lastLink(180);

foreach($links as $link) {

    if($link == $last) break;

    $newsOne = file_get_html($link);

    try {
        $title = $newsOne->find("td.content-middle h1")[0];
        $image = $newsOne->find("div.news-image img")[0];
        $content = $newsOne->find("div#news-content")[0];

        $one = [];
        $one["city"] = 180;
        $one["link"] = $link;
        $one["source"] = $link;
        $one["title"] = $title->innertext . PHP_EOL;
        $one["image"] = $domain . $image->src . PHP_EOL;

        $imgsContent = $content->find("img");

        foreach ($imgsContent as $img) {
            if (preg_match("/^\/.+/", $img->src)) {
                $img->src = $domain . $img->src;
            }
            $img->style = "max-width:100%;";
            // echo $img->src.PHP_EOL;
        }

        // обработка ссылок в новости, добавлять домен к ссылке, если ссылка не полная.
        // так же добавляет аттрибут target="_blank"
        

        $hrefs = $content->find("a");
        foreach($hrefs as $a) {
            if(preg_match("/^\/.*/", $a->href)) {
                $a->href=$domain.$a->href;
                $a->target="_blank";
            }
        }

        $one["body"] = $content->innertext . PHP_EOL;

        $allNews[] = $one;
        echo ".";

    } catch (Exception $e) {
        echo $e->getMessage();
    }

}
echo PHP_EOL;

$allReverseNews = array_reverse($allNews);

$cnt = 0;

foreach($allReverseNews as $n) {
    $result = post($n);
    $data = json_decode($result, true);
    $c = $data["result"]["cnt"];
    $cnt += $c;
}

$result = addCount(180, $cnt);

print_r($result);

echo PHP_EOL;

