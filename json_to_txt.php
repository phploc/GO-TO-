<?php

$json_string=file_get_contents('qa.json');
$normal_string=json_decode($json_string,true);
$i=1;
foreach($normal_string as $v){
		$string=$string.'Вопрос '.$i.' :'.$v['q'].';'.PHP_EOL.'Ответ:'.$v['a'].PHP_EOL;
	$i++;
}
mb_convert_variables('Windows-1251','utf-8',$string);
	file_put_contents('ans.txt',$string);
