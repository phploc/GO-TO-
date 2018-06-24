<!-- форма загрузки файла -->
<form enctype="multipart/form-data" action="/send_on_SERVER.php" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="900000000" />
    Файл: <input name="userfile" type="file" />
    <input type="submit" value="Send File" />
</form>

<?php

var_dump($_FILES);

$file_path = 'F:\OpenServer\loadfile\\';	//директория для сохранения файла

if(move_uploaded_file($_FILES['userfile']['tmp_name'], $file_path.$_FILES['userfile']['name'])){
	echo 'Файл успешно загружен'; 
}
else{
	echo 'Произошла ошибка';
}
	



echo 1;
