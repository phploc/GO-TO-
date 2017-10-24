<?php
$start_script=microtime(true);
$mysqli = new mysqli('127.0.0.1', 'root', '', 'localmart');
if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
$string='';

$main = curl("http://localmart.by/");

preg_match_all('% href="http://localmart\.by/(.+)/" class="title"%', $main, $matches, PREG_PATTERN_ORDER, 0);

//print_r($matches);

foreach($matches[1] as $k=>$v){
	$adress[$k]="http://localmart.by/$v/page/1/";
	$mass_page= curl ($adress[$k]);
	preg_match_all('%<a href="(.+)" class="title" target="_blank"%',$mass_page, $match, PREG_PATTERN_ORDER, 0);	
	//echo json_encode($match[1]);
	foreach($match[1] as $v){
		$esc_url = $mysqli->real_escape_string($v);
		$string.="INSERT INTO urls(url) SELECT '{$esc_url}' WHERE NOT EXISTS (SELECT 1 FROM urls WHERE url = '{$esc_url}' LIMIT 1) LIMIT 1;"; //добавление с проверкой
	}

	
}
$start_write=microtime(true);
	$mysqli->multi_query($string);
	while ($mysqli->next_result());
$time_write=(microtime(true)-$start_write)*1000;
echo "<br> запись в БД выполнялась $time_write милисекунд";
$time_script=(microtime(true)-$start_script)*1000;
echo "<br> программа выполнялась $time_script милисекунд";

//file_put_contents('res.txt',$mass_page);



function curl($url) {
	$q=curl_init($url);
	curl_setopt($q, CURLOPT_HEADER, false);
	curl_setopt($q, CURLOPT_ENCODING, ''); 
	curl_setopt($q, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($q, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($q, CURLOPT_MAXREDIRS, 5);
	curl_setopt($q, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($q, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($q, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36");
	$z=curl_exec($q);
	curl_close($q);
	return $z;
}