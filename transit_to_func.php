<?php


$a = 50;
$b=null;
if($b) {
	$arr = [$a,$b];
}
else {
	$arr = [$a];
}
op(...$arr);

function op($a, $b = 10 ){
	var_dump($a, $b);	
	
}

?>