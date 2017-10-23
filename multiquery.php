<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'landing');
if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$json_string=file_get_contents('qa.json');
$normal_string=json_decode($json_string,true);

$string='';
foreach($normal_string as $v){
	//$string.="INSERT INTO `FAQ` (`id`, `quest`, `answ`) VALUES (NULL, '{$v['q']}', '{$v['a']}');";   //просто добавление
	$string.="INSERT INTO FAQ(quest,answ) SELECT '{$v['q']}','{$v['a']}' WHERE NOT EXISTS (SELECT 1 FROM FAQ WHERE quest = '{$v['q']}' AND answ = '{$v['a']}' LIMIT 1) LIMIT 1;"; //добавление с проверкой
}

$result=$mysqli->multi_query($string);
while ($mysqli->next_result());
 
$query="SELECT * FROM FAQ";
if ($stmt = $mysqli->prepare($query)) {
    $stmt->execute();
    $stmt->bind_result($id, $quest, $answ);
    while ($stmt->fetch()) {
		echo 'Вопрос '.$id.' :'.$quest.'; <br> Ответ:'.$answ.'<br>';
    }
    $stmt->close();
}