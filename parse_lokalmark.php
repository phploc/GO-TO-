<?php

date_default_timezone_set('Europe/Minsk');
$start_script=microtime(true);
//$mysqli = new mysqli('Localhost', 'reactor_localmar', 'DBreset', 'reactor_localmar');
$mysqli = new mysqli('127.0.0.1', 'root', '', 'localmart');
if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
$string='';

//$main = curl("http://localmart.by/");

//preg_match_all('% href="http://localmart\.by/(.+)/" class="title"%', $main, $matches, PREG_PATTERN_ORDER, 0);

$matches[1] = Array('elektronika','odezhda_i_obuv','tovary_dlja_detej','zhivotnye','mobilnaja_svjaz','kompjutery'/*,'stroitelstvo_i_remont','vsjo_dlja_doma','nedvizhimost'*/);



//$matches[1][]='';
print_r($matches);


foreach($matches[1] as $v){
	for($page=1,$max_page=1;$page<=$max_page&&$page<=10;$page++){
		usleep(400000);
	$mass_page= curl ("http://localmart.by/$v/page/$page/");
	preg_match_all('%<a href="(.+)" class="title" target="_blank"%',$mass_page, $links, PREG_PATTERN_ORDER, 0);
	preg_match_all('%<td class="td-added-time">\s+(.+?)\s+<\/td>%', $mass_page, $times, PREG_PATTERN_ORDER, 0);
		foreach($times[1] as $t){
	$mass_unix[]= parse_time($t);
	}
if(end($mass_unix)>time()-2000){
		$max_page++;
	}
	foreach($links[1] as $k=>$dat){
		$esc_url = $mysqli->real_escape_string($dat);
		//$esc_unix = $mysqli->real_escape_string($mass_unix[$k]);
		$dmy=date('Y-m-d H:i:s',$mass_unix[$k]);  // проверить возможность вставлять юникс
		$esc_dmy = $mysqli->real_escape_string($dmy);
		$string.="INSERT INTO urls(url,date) SELECT '{$esc_url}','{$esc_dmy}' FROM urls WHERE NOT EXISTS (SELECT 1 FROM urls WHERE url = '{$esc_url}' LIMIT 1) LIMIT 1;"; //добавление с проверкой
	}
}
}
$start_write=microtime(true);
	$mysqli->multi_query($string);
	printf("Errormessage: %s\n", $mysqli->error);
	$mysqli->close();
$time_write=(microtime(true)-$start_write)*1000;
echo "<br> запись в БД выполнялась $time_write милисекунд";
$time_script=(microtime(true)-$start_script)*1000;
echo "<br> программа выполнялась $time_script милисекунд";








function parse_time($str){
$unix=strtotime($str);
	if(!empty($unix)) return $unix;
$r=explode(' ',$str);
$mas[60]=levenshtein('минут',$r[1]);
$mas[3600]=levenshtein('час',$r[1]);
$mas[86400]=levenshtein('день',$r[1]);
$mas[1800]=levenshtein('назад',$r[1]);
foreach($mas as $k=>$v){
	if($v<=4){
		if($k==1800)
		return time()-$k;
	$coef=$k;
	}
}
$t=$r[0]*$coef;
return time()-$t;

}

function curl($url,$refer='http://localmart.by/') {
	$q=curl_init($url);
		curl_setopt($q, CURLOPT_HEADER, true);
	curl_setopt($q, CURLOPT_ENCODING, ''); 
	curl_setopt($q, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($q, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($q, CURLOPT_MAXREDIRS, 1);
	curl_setopt($q, CURLOPT_COOKIEJAR, 'F:\openserver\domains\enlightenment.loc\marketcookie.txt');
	curl_setopt($q, CURLOPT_COOKIEFILE, 'F:\openserver\domains\enlightenment.loc\marketcookie.txt');
	curl_setopt($q, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($q, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($q, CURLOPT_REFERER, $refer);
	curl_setopt($q, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36");
	$z=curl_exec($q);
	curl_close($q);
	return $z;
}