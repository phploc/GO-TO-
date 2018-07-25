<?php

sess_start();			//старт сессии
if(empty($_SESSION)){	//если сессия пуста, то записывается информация о пользователе
	set_cookie();
}



$file_path = __DIR__.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;	//директория для сохранения файла
chek_fold($file_path, 0744);					// проверка существования директории/создание с требуемыми правами


if(empty($_FILES)){								//если массив _FILES пуст, то выводится форма для загрузки файлов
	echo shapes::load_form(cryt());
} elseif(chek_hash(cook_hash(), cryt($_POST["random"]))){		//если массив _FILES не пуст, то проверяется соотверствие ключа формы данному пользователю
	ready_files($_FILES['file'], $file_path, 3);				// функция подговоки информации о файле к перемещению, 
	echo shapes::go_main();										//передаётся информация о загруженых файлах, путь загрузки и колличество загружаемых файлов
} else{
	echo 'Произошла ошибка';
}




function ready_files($files, $file_path, $max_files = 3){

	if(is_array($files['error'])){
		$count = 0;
		foreach($files['error'] as $key => $value){
			if($value == UPLOAD_ERR_OK && $count < $max_files){
				$name = str_replace('.', ';', basename($files['name'][$key]));
				$tmp_name = $files['tmp_name'][$key];
				save_file($name, $tmp_name, $file_path);			//функция перемещения файла из временной директории
			}
			++$count;
		}
		
	} elseif($files['error'] == UPLOAD_ERR_OK){
		$name = str_replace('.', ';', basename($files['name']));
		$tmp_name = $files['tmp_name'];
		save_file($name, $tmp_name, $file_path);

	}
}

function save_file($name, $tmp_name, $file_path){
	if(move_uploaded_file($tmp_name, $file_path.$name)){
		chmod($file_path.$name, 0744);
		echo '<br>Файл успешно загружен'; 
	}
}


function real_ip(){
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }elseif(isset($_SERVER['HTTP_X_REAL_IP'])){
        return $_SERVER['HTTP_X_REAL_IP'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function set_cookie(){
	$_SESSION['ip'] = real_ip();
	$_SESSION['ua'] = $_SERVER["HTTP_USER_AGENT"];
	$_SESSION['hash'] = hash_hmac('sha256', $_SERVER["HTTP_USER_AGENT"],  real_ip());
}

function cook_hash(){
	return 	$_SESSION['hash'];
}

function chek_hash($cook, $form){
	return 	hash_equals($cook, $form);
}

function sess_start(){
	session_start([
		'name' => 'sendler',
		'gc_maxlifetime' => 604800,
		'cookie_lifetime' => 604800,
		'cookie_httponly' => true,
		'use_cookies' => true,
		'use_only_cookies' => true,
		'sid_length' => 64,
		'sid_bits_per_character' => 5,
		'gc_probability' => 1,
		'gc_divisor' => 10,
		//'save_path' => configs::get('sess_path'),
	]);
}

function cryt($meth = false){
	if($meth === false){
		$key = rand_string();
		$str = openssl_encrypt(cook_hash(), 'RC4-HMAC-MD5', $key);
		$str = $key.':'.$str;
	} else {
		list($key, $string) = explode(':', $meth);
		$str = openssl_decrypt($string, 'RC4-HMAC-MD5', $key);
	}
	return $str;
}

function rand_string($length = 10) {
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str_leng = strlen($str);
    $rand_str = '';
    for ($i = 0; $i < $length; $i++) {
        $rand_str .= $str[rand(0, $str_leng - 1)];
    }
    return $rand_str;
}

function chek_fold($file_path, $mode = 0777){
	if(!file_exists($file_path)){
		return mkdir($file_path, $mode);
	}
}

class shapes{
	
	public static function load_form($cryt){
		
		return '
		<form enctype="multipart/form-data" action="/send_on_SERVER.php" method="POST">
			<input type="hidden" name="MAX_FILE_SIZE" value="900000000" />
			<input type="hidden" name="random" value="'.$cryt.'" />
			<input type="file" min="1" max="10" name="file[]" multiple="true" />
			<input type="submit" value="Send File" />
		</form>
		';
	}
	
	public static function go_main(){
		return "<br><a href='".$_SERVER['REQUEST_URI']."' ><input type='submit' name='main' value='На главную' ></a><br>";
	}
	
}

/*
function save_files($files, $file_path, $max_files = 3){
	$count = 0;
	foreach($files as $key => $value){
		if($value['error'] == UPLOAD_ERR_OK && $count < $max_files){
			++$count;
			$name = basename($value['name']);
			$name = str_replace('.', ';', $name);
			$tmp_name = $value['tmp_name'];
			if(move_uploaded_file($tmp_name, $file_path.$name)){
				chmod($file_path.$name, 0744);
				echo 'Файл успешно загружен'; 
			}
		} 		
	}
}


	public static function load_form($cryt, $num = 1){
		$str = '';
		for($i = 1; $i <= $num; $i++){
			$str .= 'Файл'.$i.': <input name="userfile'.$i.'" type="file" /><br>';
		}
		
		return '
		<form enctype="multipart/form-data" action="/send_on_SERVER.php" method="POST">
			<input type="hidden" name="MAX_FILE_SIZE" value="900000000" />
			<input type="hidden" name="random" value="'.$cryt.'" />
			'.$str.'
			<input type="file" min="1" max="2" name="file[]" multiple="true" />
			<input type="submit" value="Send File" />
		</form>
		';
	}

*/
