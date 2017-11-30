<?php
//|||||||||||||||||||||

$_OUTDIR='F:/OpenServer/domains/javascript'; //передаваемая папка
$_DELDIR='F:/OpenServer';							//удаляемый путь
define(CLOUD_ADRESS,'https://webdav.yandex.ru'); //адрес облака
define(CLOUD_AUTH,'php.loc:fuckyou1'); //логин и пароль от облака
$yand_wal='/backup';							//папка в облаке

//|||||||||||||||||||||

$results = array('file'=>array(),'dir'=>array(0=>$_OUTDIR) );
$array=getDirContents($_OUTDIR,$results);
var_dump($array);
foreach($array['dir'] as $key){
	$st=explode('\\',$key);
	$n=count($st);
	$adres='/backup';
	for($i=2;$i<=$n;++$i){
		$answ1=check_fold_yand($adres);
		if($answ1['http_code']!=207){
			$answ2=create_fold_yand($adres);
		}
		$adres.='/'.$st[$i];
	}
}

$len = mb_strlen($_DELDIR);

foreach($array['file'] as $key){
	$file_path_str = $key;
	$yand_walk=$yand_wal.mb_substr($key, $len);
	$yand_walk= str_replace('\\','/', $yand_walk);
	write_to_yand($file_path_str,$yand_walk);
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


function check_fold_yand($adres){							//проверяет наличие указанной папки в облаке
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, CLOUD_ADRESS.$adres );
    curl_setopt($ch, CURLOPT_USERPWD, CLOUD_AUTH);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'PROPFIND');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Host: webdav.yandex.ru','Accept: */*','Depth: 1'));
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
												'Accept: */*',
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

