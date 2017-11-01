<?php

//$mysqli = new mysqli('Localhost', 'reactor_localmar', 'DBreset', 'reactor_localmar');
$mysqli = new mysqli('127.0.0.1', 'root', '', 'localmart');
if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$DB_urls= $mysqli->query("SELECT id,url FROM urls WHERE category is NULL LIMIT 5");
while($res=$DB_urls->fetch_assoc()){
$url=$res['url'];
$ID=$res['id'];
usleep(400000);

$in=parse_addinf($mysqli,$url);
$esc_name = $mysqli->real_escape_string($in['name']);
$esc_prise = $mysqli->real_escape_string($in['prise']);
$esc_valute = $mysqli->real_escape_string($in['id_valute']);
$esc_img = $mysqli->real_escape_string($in['img']);
$esc_body = $mysqli->real_escape_string($in['body']);
$esc_town = $mysqli->real_escape_string($in['id_town']);
$esc_id_cat = $mysqli->real_escape_string($in['id_cat']);

$string.="UPDATE urls SET name='{$esc_name}', prise='{$esc_prise}', valute='{$esc_valute}', img_url='{$esc_img}', text_advert='{$esc_body}', town='{$esc_town}', category='{$esc_id_cat}' WHERE id={$ID} LIMIT 1;"; //добавление с проверкой
//var_dump($in);
//var_dump($res);
}
$mysqli->multi_query($string);
	printf("Errormessage: %s\n", $mysqli->error);
	$mysqli->close();
/*
$url="http://minsk.localmart.by/item/pylesos_bosch_so_skidkoy/98906035";/*'http://minsk.localmart.by/item/showContactInfo?id=98906035'*/









function parse_addinf(&$mysqli,$url){
	$e= curl($url,$url);
	
	preg_match_all('@<span class="user-name">(.+?)</span>@', $e, $name, PREG_SET_ORDER, 0);
	$info['name']=$name[0][1];
	
	preg_match_all('@<span class="price main">(.+?)</span>@sm', $e, $matches, PREG_SET_ORDER, 0);
	if($matches[0][1]=='Договорная'){
		$info['prise']=0;
		$valute='negotiable';
	}
	else{
	preg_match_all('@[\dA-z]+@',$matches[0][1],$r);
	foreach($r[0] as $v)
	$str.=$v;
	preg_match_all('@(\d+)(\D+)@', $str, $prise_val, PREG_SET_ORDER, 0);
	$info['prise']=$prise_val[0][1];
	$valute=$prise_val[0][2];
	}
	
	$esc_val = $mysqli->real_escape_string($valute);
	$mysqli->multi_query("INSERT INTO currency(valute) SELECT '{$esc_val}' FROM currency WHERE NOT EXISTS (SELECT 1 FROM currency WHERE valute = '{$esc_val}' LIMIT 1) LIMIT 1; SELECT * FROM currency WHERE valute='{$esc_val}';");
		if ($mysqli->error) printf("Errormessage: %s\n", $mysqli->error);
		$mysqli->next_result();
		$chek_val = $mysqli->store_result();
		$res_val=$chek_val->fetch_array(MYSQLI_NUM);
		$info['id_valute']=$res_val[0];
	
	preg_match_all('@<img id="item-big-photo" src="(.+?)"@m', $e, $img, PREG_SET_ORDER, 0); //ссылка на фото
	$info['img']=$img[0][1];
	
	preg_match_all('@<div class="addition-word.*?">(.+?)</div>@s', $e, $text, PREG_SET_ORDER, 0); //тело текста
	$info['body']=trim(strip_tags($text[0][1]));
	
	preg_match_all('@<div class="item">Город: <span class="value">(.+?)</span></div>@', $e, $city, PREG_SET_ORDER, 0);
	$town=$city[0][1];
	
	$esc_city = $mysqli->real_escape_string($town);
	$mysqli->multi_query("INSERT INTO cities(town) SELECT '{$esc_city}' FROM cities WHERE NOT EXISTS (SELECT 1 FROM cities WHERE town = '{$esc_city}' LIMIT 1) LIMIT 1; SELECT * FROM cities WHERE town='{$esc_city}';");
		if ($mysqli->error) printf("Errormessage: %s\n", $mysqli->error);
		$mysqli->next_result();
		$chek_town = $mysqli->store_result();
		$res_tow=$chek_town->fetch_array(MYSQLI_NUM);
		$info['id_town']=$res_tow[0];
	
	$flag=false;
	preg_match_all('@<div class="breadcrumbs">(.+?)</div>@s', $e, $town_cat, PREG_SET_ORDER, 0);  // город и категория
	preg_match_all('/title="(.+?)"/', $town_cat[0][1], $town_cat, PREG_PATTERN_ORDER, 0);  
	$id_cat=0;

	foreach($town_cat[1] as $key=>$value){
	if($value==$town){
		$flag=true;
	}
	elseif($flag==true){
		$esc_cat = $mysqli->real_escape_string($value);
		$esc_moth = $mysqli->real_escape_string($id_cat);					//добавление с проверкой
		$mysqli->multi_query("INSERT INTO categories(category,mother) SELECT '{$esc_cat}','{$esc_moth}' FROM categories WHERE NOT EXISTS (SELECT 1 FROM categories WHERE category = '{$esc_cat}' LIMIT 1) LIMIT 1; SELECT * FROM categories WHERE category='{$esc_cat}';");
		if ($mysqli->error) printf("Errormessage: %s\n", $mysqli->error);
		$mysqli->next_result();
		$chek_cat = $mysqli->store_result();
		$result=$chek_cat->fetch_array(MYSQLI_NUM);
		$id_cat=$result[0];
		}
	}
	
	$info['id_cat']=$id_cat;
	
	return $info;
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
