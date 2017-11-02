<?php


$start_script=microtime(true);

$mysqli = new mysqli('127.0.0.1', 'root', '', 'localmart');
//$mysqli = new mysqli('Localhost', 'reactor_localmar', 'DBreset', 'reactor_localmar');
if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$DB_urls= $mysqli->query("SELECT id,url FROM urls WHERE category is NULL LIMIT 10");
while($res=$DB_urls->fetch_assoc()){
$url=$res['url'];
$ID=$res['id'];
usleep(400000);

unset($contacts,$in);

$in=parse_addinf($mysqli,$url);
if($in['name']!=NULL){
$esc_name = $mysqli->real_escape_string($in['name']);
$esc_prise = $mysqli->real_escape_string($in['prise']);
$esc_valute = $mysqli->real_escape_string($in['id_valute']);
$esc_img = $mysqli->real_escape_string($in['img']);
$esc_body = $mysqli->real_escape_string($in['body']);
$esc_town = $mysqli->real_escape_string($in['id_town']);
$esc_id_cat = $mysqli->real_escape_string($in['id_cat']);
$string.="UPDATE urls SET name='{$esc_name}', prise='{$esc_prise}', valute='{$esc_valute}', img_url='{$esc_img}', text_advert='{$esc_body}', town='{$esc_town}', category='{$esc_id_cat}' WHERE id={$ID} LIMIT 1;"; //добавление с проверкой
}

$contacts=parse_contact($url);
if($contacts['number']!=NULL){
$esc_numb = $mysqli->real_escape_string($contacts['number']);
$string.="UPDATE urls SET contact_number ='{$esc_numb}' WHERE id={$ID} LIMIT 1;";
}

if($contacts['skype']!=NULL || $contacts['icq']!=NULL){
$adds='';
	if($contacts['skype']!=NULL)
		$adds.='Skype:'.$contacts['skype'];
	if($contacts['icq']!=NULL)
		$adds.=' ICQ:'.$contacts['icq'];
		
$esc_adds = $mysqli->real_escape_string($adds);

$string.="UPDATE urls SET contact_adds ='{$esc_adds}' WHERE id={$ID} LIMIT 1;";
}
//var_dump($contacts);

//var_dump($in);

}
$mysqli->multi_query($string);
	printf("Errormessage: %s\n", $mysqli->error);
	$mysqli->close();

$time_script=(microtime(true)-$start_script)*1000;
echo "<br> программа выполнялась $time_script милисекунд";

function parse_contact($url){
preg_match_all('@//(.+?)/.+/(\d+)$@', $url, $matches, PREG_SET_ORDER, 0);
$headers=array("Host: {$matches[0][1]}",'Accept: */*','X-Requested-With: XMLHttpRequest','User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.75 Safari/537.36','Content-Type: application/x-www-form-urlencoded; charset=UTF-8',"Referer: {$url}",'Accept-Encoding: gzip, deflate, sdch','Accept-Language: ru,en-US;q=0.8,en;q=0.6','Cookie: _ym_uid=1508877069276947460; PHPSESSID=l3nukful7m64ia96827i1p2rv3; _ym_isad=2; show_categories=0; _ga=GA1.2.1453903692.1508877068; _gid=GA1.2.502684815.1509370632; _ym_visorc_21642088=w');
$quest="http://{$matches[0][1]}/item/showContactInfo?id={$matches[0][2]}";
$e=curl_contact($quest,$headers);
preg_match_all('@<p.+?class="value">.+?<@', $e, $cont, PREG_PATTERN_ORDER, 0);

foreach($cont[0] as $v){
preg_match_all('@>(.+?)<@', $v, $matc, PREG_PATTERN_ORDER, 0);
$trash=array('+','(',')',' ','-');
$i=0;
if(stristr($matc[1][0], 'skype') == TRUE)
	$contact['skype']=$matc[1][1];
elseif(stristr($matc[1][0], 'icq') == TRUE)
	$contact['icq']=$matc[1][1];
else
	$contact['number']=str_replace($trash,'',$matc[1][0]);
}
return $contact;
}


function curl_contact($url,$headers) {
	$q=curl_init($url);
	curl_setopt($q, CURLOPT_HEADER, false);
	curl_setopt($q, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($q, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($q, CURLOPT_MAXREDIRS, 1);
	curl_setopt($q, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($q, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($q, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($q, CURLOPT_ENCODING, ''); 
	$z=curl_exec($q);
	curl_close($q);
	return $z;
}






function parse_addinf(&$mysqli,$url){
	$e= curl($url,$url);
	
	preg_match_all('@<span class="user-name">(.+?)</span>@', $e, $name, PREG_SET_ORDER, 0);
	$info['name']=htmlspecialchars_decode($name[0][1],ENT_QUOTES);
	
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
