<?php
$i = 0;
$cookie_file = file_get_contents('read_conf.txt');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$time_start = microtime(true);
while($i++ < 2){
$res = read_conf($cookie_file);
}
echo (microtime(true) - $time_start)*1000;
var_dump($res);

//$res = read_conf_eval('read_conf.txt');
//var_dump($res);



function read_conf($string_file){
	$arr_file = explode("\n",$string_file);
	$res = array();
	foreach($arr_file as $row){
		
		$right = explode('=',$row, 2);
		if(isset($right[1])){
		$left = explode('.',$right[0]);
	
		$s = &$res;
		foreach($left as $val){
			
			if(!isset($s[$val]) || !is_array($s[$val])){
				$s[$val] = [];
			}
			
		$s = &$s[$val];
		}
		if(empty($s))
			$s=trim($right[1]);
		else
			$s['other'][] = trim($right[1]);
	}
	}
	return $res;
}

function read_conf_eval($string_file){
	$arr_file = explode("\n",$string_file);
	$res = array();
	foreach($arr_file as $row){
		$right = explode('=',$row);
		$left = explode('.',$right[0]);
		$str='';
	foreach($left as $v){
		$str.= '['.$v.']';
	
	}
	eval('$res'.$str.'=trim($right[1]);');
	}
return $res;
}

?>