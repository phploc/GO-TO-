<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'landing');
if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

$normal_string=json_decode(file_get_contents('qa.json'),true);

$string='';
foreach($normal_string as $v){
	$esc_ques = $mysqli->real_escape_string($v['q']);
	$esc_answ = $mysqli->real_escape_string($v['a']);
	//$string.="INSERT INTO `FAQ` (`id`, `quest`, `answ`) VALUES (NULL, '{$v['q']}', '{$v['a']}');";   //просто добавление
	$string.="INSERT INTO FAQ(quest,answ) SELECT '{$esc_ques}','{$esc_answ}' WHERE NOT EXISTS (SELECT 1 FROM FAQ WHERE quest = '{$esc_ques}' AND answ = '{$esc_answ}' LIMIT 1) LIMIT 1;"; //добавление с проверкой
}

$mysqli->multi_query($string);
while ($mysqli->next_result());

$resul=$mysqli->query("SELECT * FROM FAQ");
while($row=$resul->fetch_row()){
	echo 'Вопрос '.$row[0].' :'.$row[1].'; <br> Ответ:'.$row[2].'<br>';
}
$mysqli->close();
/*
if ($stmt = $mysqli->prepare("SELECT * FROM FAQ")) {
    $stmt->execute();
    $stmt->bind_result($id, $quest, $answ);
    while ($stmt->fetch()) {
		echo 'Вопрос '.$id.' :'.$quest.'; <br> Ответ:'.$answ.'<br>';
    }
    $stmt->close();
}*/
