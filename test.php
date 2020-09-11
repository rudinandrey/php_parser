<?php

$result = file_get_contents("http://lyubimiigorod.ru/api/news/get_last_link?key=test_news&city=180");
$data = json_decode($result, true);

print_r($data);