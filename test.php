<?php

$result = file_get_contents("http://lyubimiigorod.ru/api/news/get_last_link?key=test_news&city=180");
$data = json_decode($result, true);

$link = $data["result"]["link"];

echo $link;