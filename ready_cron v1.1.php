<?php
var_dump($_POST);
$file=fopen('/var/www/cron_task.txt','r+');
$n_str=0;
$new_file='';
$isPOST = $_SERVER['REQUEST_METHOD'] === 'POST';

echo <<<XOF
	<form method="post" action="ready_cron.php">
   <p><b>Выберите какие задачи cron активировать</b></p>
XOF;
   
while( $v = fgets($file)){

	if($_POST[$n_str]) {
		$v = str_replace('#?','',$v);
	} elseif ($isPOST && substr($v,0,2)!='#?') {
		$v = '#?'.$v;
	}
	$new_file.=$v;
	
	$status = ((substr($v,0,1)=='#') ? '' : 'checked');
	$v=substr($v,2+strpos($v,'##'));
	echo   "<p><input type=\"checkbox\" name=\"$n_str\" value=\"on\" $status>$v<Br>";
	
	$n_str++;
	}
echo <<<XOF
	<p><input type="submit" value="Отправить"></p>
	</form>
XOF;
  
rewind($file);

fwrite($file,$new_file);
ftruncate($file, ftell($file));

fclose($file);

if($isPOST){
$output = shell_exec('crontab -u www-data /var/www/cron_task.txt');
echo "<pre>$output</pre>";
}
$output = shell_exec('crontab -u www-data -l');
echo "<pre>$output</pre>";
