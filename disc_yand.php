<?php
//|||||||||||||||||||||

$outdir='F:\OpenServer\domains\enlightenment.loc\fold';		//передаваемая папка
$deldir='F:\OpenServer';									//удаляемый путь 
$yand_wal='/bacuper';										//папка в облаке в которую будет производиться запись
$separ='\\';												//изначальный разделитель директорий

						
$cloud_auth='php.loc:fuckyou1';			//логин и пароль от облака							
$time_out=10;							//CURLOPT_CONNECTTIMEOUT (Количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания.)

$obj = new yandex_disk($deldir,$yand_wal,$separ,"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",$time_out);
$obj->other_params($cloud_auth);

$results = array('file'=>array(),'dir'=>array() );
$array=$obj->getDirContents($outdir);						//получения массита адресов папок и файлов

$string_result='';
foreach($array as $value){
foreach($value as $k=>$v){
		$string_result.="[$k]=($v)".PHP_EOL;
}
}
file_put_contents('log_yandex.txt',$string_result,FILE_APPEND);




class cURL_lib {

	private $ch;
	public $deldir,$yand_wal,$separ;
	
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
	
	const CLOUD = 'https://webdav.yandex.ru';		//адрес облака
	
	public function __construct($deldir='F:\OpenServer',$yand_wal='/backuper',$separ='\\',$ua="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36",$time_out=30){
		$this->deldir=$deldir;
		$this->yand_wal=$yand_wal;
		$this->separ=$separ;
		parent::__construct($ua,$time_out);
	}
	
	public function getDirContents($dir, &$results = array('file'=>array(),'dir'=>array()) ){			//записывает в массив results все файлы и папки в папке dir
		$files = scandir($dir);
		$results['dir'][] =$dir;
		$this->folders($dir.$this->separ);
		
		foreach($files as $key => $value){
			$path = realpath($dir.DIRECTORY_SEPARATOR.$value);
			if(!is_dir($path)) {
				$results['file'][] = $path;
				$this->file_wri($path);
			} else if($value != "." && $value != "..") {
				$this->getDirContents($path, $results);
        }
		}
		return $results;
	}
	
	
	public function folders($path){
		$dir=mb_strrchr($path,$this->separ,true);
		$st=explode($this->separ,$dir);			//раздеитель директорий
		$n=count($st);
		$adres=$this->yand_wal;
		for($i=2;$i<=$n;++$i){
			$answ1=$this->chek_fold($adres);					//проверка наличия папки
			file_put_contents('log_yandex.txt','chek_fold?='.$answ1['http_code'].PHP_EOL,FILE_APPEND);
			if($answ1['http_code']!=207){
				$answ2=$this->create_fold($adres);			//создание папки
				file_put_contents('log_yandex.txt','create_fold^='.$answ2['http_code'].PHP_EOL,FILE_APPEND);
			}
			$adres.='/'.$st[$i];
	}
	}
	
	public function file_wri($file_path_str){
		$len = mb_strlen($this->deldir);				//колличество символов для удаления начала пути		
		$yand_walk=$this->yand_wal.mb_substr($file_path_str, $len);//запись файлов в соответствии с их адресом
		$yand_walk= str_replace('\\','/', $yand_walk);
		$result=$this->write_file($file_path_str,$yand_walk);
		file_put_contents('log_yandex.txt','write_file&='.$result['http_code'].PHP_EOL,FILE_APPEND);
	}
	
	public function chek_fold($url){
		$info=$this->univers($this::CLOUD.$url,'PROPFIND',array('Accept: */*','Depth: 1'));
		return $info;
	}
	
	public function create_fold($url){
		$info=$this->univers($this::CLOUD.$url,'MKCOL');
		return $info;
	}
	
	public function write_file($file_path_str,$yand_walk){
		
		
		$size=filesize($file_path_str);   //Content-Length
		$md5=hash_file('md5', $file_path_str);
		$sha=hash_file('sha256', $file_path_str);
		
		$this->file_params($file_path_str);
		$info=$this->univers($this::CLOUD.$yand_walk,'PUT',array(
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
