<?php
//|||||||||||||||||||||

$_OUTDIR='F:/OpenServer/domains/enlightenment.loc/fold'; //передаваемая папка
$_DELDIR='F:/OpenServer';							//удаляемый путь 
$cloud_auth='php.loc:fuckyou1';					//логин и пароль от облака
$yand_wal='/backuper';							//папка в облаке в которую будет производиться запись
$time_out=10;				//CURLOPT_CONNECTTIMEOUT (Количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания.)

$obj = new yandex_disk("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",$time_out);
$obj->other_params($cloud_auth);

//|||||||||||||||||||||

$results = array('file'=>array(),'dir'=>array(0=>$_OUTDIR) );
$array=getDirContents($_OUTDIR,$results);						//получения массита адресов папок и файлов
var_dump($array);

foreach($array['dir'] as $key){				//проверка нашичия папок в облаке, при отсутствии создаёт
	$st=explode('/',$key);			//раздеитель директорий
	$n=count($st);
	$adres=$yand_wal;
	for($i=2;$i<=$n;++$i){
		
		$answ1=$obj->chek_fold($adres);					//проверка наличия папки
		var_dump('chek_fold='.$answ1['http_code']);
		if($answ1['http_code']!=207){
			$answ2=$obj->create_fold($adres);			//создание папки
			var_dump('create_fold='.$answ2['http_code']);
		}
		$adres.='/'.$st[$i];
	}
}

$len = mb_strlen($_DELDIR);					//колличество символов для удаления начала пути

foreach($array['file'] as $key){			//запись файлов в соответствии с их адресом
	$file_path_str = $key;
	$yand_walk=$yand_wal.mb_substr($key, $len);
	$yand_walk= str_replace('\\','/', $yand_walk);
	$result=$obj->write_file($file_path_str,$yand_walk);
	var_dump('write_file='.$result['http_code']);
}



function getDirContents($dir, &$results = array()){			//записывает в массив results все файлы и папки в папке dir
    $files = scandir($dir);

    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
            $results['file'][] = $path;
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results['dir'][] = $path;
        }
    }
    return $results;
}


class cURL_lib {

	private $ch;
	
	public function __construct($ua="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",$time_out=30){
		$this->ch = curl_init();						//инициализация curl (основные параметры)
		curl_setopt($this->ch, CURLOPT_HEADER, false); 
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $time_out);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $ua);		
	}
	
	public function other_params($auth=''){				//логин и пароль для входа
		    curl_setopt($this->ch, CURLOPT_USERPWD, $auth);

	}
	
	public function file_params($file_path_str){		//включение опций для для записи файла
		curl_setopt($this->ch, CURLOPT_POST,true);
		$fh_res = fopen($file_path_str, 'r');
		curl_setopt($this->ch, CURLOPT_INFILE, $fh_res);
		curl_setopt($this->ch, CURLOPT_INFILESIZE, filesize($file_path_str));
	}
	
	 
	public function univers($url,$meth,$head=array('Accept: */*'),$param=0){ //универсальный запрос устанавливается:ссылка, метод запроса,заголовки, тело значения тип
		if($param!=0){
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $param); 	
		}
		curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $meth); 
		curl_setopt($this->ch, CURLOPT_URL, $url); 
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $head);
		
		$data = curl_exec($this->ch);
		$info=curl_getinfo($this->ch);
		
		return $info;
		
	}
	
	
	public function GET($url,$head,$param){			//GET-запрос ссылка, заголовки, поля
		curl_setopt($this->ch, CURLOPT_URL, $url.'?'.http_build_query($param)); 
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $head);
		$data = curl_exec($this->ch);
		return $data;
	}
	
	public function POST($url,$head,$param){		//POST-запрос ссылка, заголовки, поля
		curl_setopt($this->ch, CURLOPT_URL, $url); 
		curl_setopt($this->ch, CURLOPT_POST, true); 
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $param); 
		curl_setopt($this->ch, CURLOPT_URL, $url); 
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $head);
		$data = curl_exec($this->ch); 
		return $data;
	}
	
}
class yandex_disk extends cURL_lib{
	
	private $cloud_adress='https://webdav.yandex.ru';	//адрес облака
	
	public function chek_fold($url){
		$info=$this->univers($this->cloud_adress.$url,'PROPFIND',array('Accept: */*','Depth: 1'));
		return $info;
	}
	
	public function create_fold($url){
		$info=$this->univers($this->cloud_adress.$url,'MKCOL');
		return $info;
	}
	
	public function write_file($file_path_str,$yand_walk){
		
		
		$size=filesize($file_path_str);   //Content-Length
		$md5=hash_file('md5', $file_path_str);
		$sha=hash_file('sha256', $file_path_str);
		
		$this->file_params($file_path_str);
		$info=$this->univers($this->cloud_adress.$yand_walk,'PUT',array(
												'Host: webdav.yandex.ru',
												'Accept: * /*',
												"Etag: $md5",
												"Sha256: $sha",
												'Expect: 100-continue',
												'Content-Type: application/binary',
												"Content-Length: $size"));
		return $info;
	}

}


/*

function check_fold_yand($adres,&$obj){							//проверяет наличие указанной папки в облаке
	$info=$obj->chek_fold($adres);
	return($info);
	
}


function create_fold_yand($adres,&$obj){							//создаёт папку в облаке
	$info=$obj->create_fold($adres);
	return $info;
}


function write_to_yand($file_path_str,$yand_walk,&$obj){				//записывает указанный файл (file_path_str) в указанную папку (yand_walk)
	$info=$obj->write_file($file_path_str,$yand_walk);
	/*$size=filesize($file_path_str);   //Content-Length
	$md5=hash_file('md5', $file_path_str);
	$sha=hash_file('sha256', $file_path_str);
    
	
	
	$info=$obj->file_params(CLOUD_ADRESS.$yand_walk,'PUT',array(
												'Host: webdav.yandex.ru',
												'Accept: * /*',
												"Etag: $md5",
												"Sha256: $sha",
												'Expect: 100-continue',
												'Content-Type: application/binary',
												"Content-Length: $size"),$file_path_str);
	
	$info=$obj->univers(CLOUD_ADRESS.$yand_walk,'PUT','',array(
												'Host: webdav.yandex.ru',
												'Accept: * /*',
												"Etag: $md5",
												"Sha256: $sha",
												'Expect: 100-continue',
												'Content-Type: application/binary',
												"Content-Length: $size"));
    */
/*
												
    var_dump($info);
	
	return $info;
}

/*




function check_fold_yand($adres){							//проверяет наличие указанной папки в облаке
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CLOUD_ADRESS.$adres );
    curl_setopt($ch, CURLOPT_USERPWD, CLOUD_AUTH);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'PROPFIND');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Host: webdav.yandex.ru','Accept: * /*','Depth: 1'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
    $curl_response_res = curl_exec ($ch);
	$info=curl_getinfo($ch);
	curl_close($ch);
	return $info;
}


function create_fold_yand($adres){							//создаёт папку в облаке
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CLOUD_ADRESS.$adres );
    curl_setopt($ch, CURLOPT_USERPWD, CLOUD_AUTH);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'MKCOL');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
    $curl_response_res = curl_exec ($ch);
	$info=curl_getinfo($ch);
	curl_close($ch);
	return $info;
}


function write_to_yand($file_path_str,$yand_walk){				//записывает указанный файл (file_path_str) в указанную папку (yand_walk)
	$size=filesize($file_path_str);   //Content-Length
	$md5=hash_file('md5', $file_path_str);
	$sha=hash_file('sha256', $file_path_str);
    
	
	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CLOUD_ADRESS.$yand_walk);
    curl_setopt($ch, CURLOPT_USERPWD, CLOUD_AUTH);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'PUT');
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $fh_res = fopen($file_path_str, 'r');
    curl_setopt($ch, CURLOPT_INFILE, $fh_res);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path_str));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
												'Host: webdav.yandex.ru',
												'Accept: * /*',
												"Etag: $md5",
												"Sha256: $sha",
												'Expect: 100-continue',
												'Content-Type: application/binary',
												"Content-Length: $size"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
    $curl_response_res = curl_exec ($ch);
	$info=curl_getinfo($ch);
	curl_close($ch);
	
	return $info;
}




*/