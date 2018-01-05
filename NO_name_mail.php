<?php
header('Content-Type: text/html; charset=utf-8', true);
$encoding = "utf-8";

$mail_to='s.shurchkov@yandex.ru';  //адрес получателя письма

$mail_subject='Срочна';			// тема письма

$mail_message='Мiкiта вы знайшлi Мiкалая?';  //сообщение в письме
$from_name='fg';							//имя отправителя
$from_mail='admin@circense.ru';				//указать адрес отправителя
$reply='bestbox95@bk.ru'; 					//адрес пересылки

    // Preferences for Subject field
    $subject_preferences = array(
        "input-charset" => $encoding,
        "output-charset" => $encoding,
        "line-length" => 76,
        "line-break-chars" => "\r\n"
    );

    // Mail header
    $header = "Content-type: text/html; charset=".$encoding." \r\n";
    $header .= "From: $from_name  <$from_mail> \r\n";
    $header .= "Reply-To: $reply\r\n";
    $header .= "MIME-Version: 1.0 \r\n";
    $header .= "Content-Transfer-Encoding: 8bit \r\n";
    $header .= "Date: ".date("r (T)")." \r\n";
$header .= 'DKIM-Signature: MIIEpAIBAAKCAQEAnXTlCQQz5GXPssrJJPau4/AaZTFizy0wk3bmNkt1QkvNyEq4v4+A6r21XuGAA53z0sIpCcRAwQ/rb9h7fqBR+o9aDOnLxTz8iz8BkQbmQaK6ALD1QxmsD78Nz5F0j2eQV9t8ZWDSlH7anMOOwquzkSkcZXtHFHYUcCRRYFY4fLIrxl5BhZ9/VaeD2lyTxEWFJtP7fx2kRTE9kxAE3slHGfJ3keSiMssPm95SmtQB0xrHushou2KkmYIl5n1ovTl2NAOsZdg1f3P2My2682r4+Ki0rXJLKI4166nslV3TUgCNksH0Drpfm2pPk2lYBkSw6n2FXHF269VtSz0IMlhhrwIDAQABAoIBAG74L2LHX3scdeDgIhq28FMcqL2grK6ufomvRlgFnkJ3AjSi1MnM7WKDCQwxiPMDow9qX1nOwoROH/PXclGv05bu6Nwo+b+sV6Aba5M2dZjUgppyHKiJs0X1tFoRfUCEEKqauXUJY9/b9158RGTWddtcSfMLivDUt1rBfciTe4QFQJ6q8X+dlI3KWVmS+uKlELpSYJoj+jtbhKyiN3SZvGIiUWFcsIGqzctev1GXcRxOgOia/sk3aUVAQI/onoCzIpjIlLYrqbrF7hrV66IztT4tgf+HHYmMkf6fe+yKdTAKoXcQQt2sv/S9bGLxxz5FFN0unQm/OGDDacsCMIi/iwECgYEA5BwDA/zk8JIn26Lb9j/O+JiT9ggdxWSxxDBGNwkNgNMWS6CejcRNA7IlRivkSdcCvqvRyq156bMuC9RkrpmgTMLYzjRwoI+c/AwCAoLuhY5J9xvb0sd/FaFx38GEL7bPlz7HMZv2sFoM2thG4W987k3P+RCKHldbpBXv9mKu2I8CgYEAsLVmTWp+XU4oVUzqw5Mj24HiUElesNU/d+v76IjReUe7XMNera56xLCnUdMKwF3SkbiLURP4uSUEgCRFCDcktJVD488Zf6l+0sY+BPhwFTnNTXhaEpHSbS5MY2b7IPcWhuQTn7gMcDeO47DsnykJJSVy++v4tzmULTRvvxPhNOECgYEApHPNeMKKCyZTtfTjv9Sg0nits2KGlBjUUCy9clAEfCuylzNlG6+6FkAIv15FZzars+sLhKMskV+sgrrNG1OGTdDrnT4RNLBU7SF7EXRzobXDfXOIN5278UxDXJ2XPw78+n03/nwCjYFeYygpcb1+xA81MMrLyLQSTWnDZk4e3+ECgYBP/S0b6hLdZZ24TyMN+LMIkBjDwxKL9VvPixDyx8wanC/h48Yd1JdaJaT30xJQ1aeVsvXNc77pACqzXreo6l1BKTgcDQk70pvICVjVoygJU6rtYIdVVJDuP0Dw1hLjbzXRRbjkWcm3mk6iHtpdiZPMLtGH298wAW5jsBLNadBNwQKBgQCanRUZl8V1qIkDJGh8fPXYJ2he758DTEVbtAyPLJO0IZl7wFL1SHxRT1UMQQwQawCs0dbUq9B0gzIQXgBySey+EO3bC4r9TIip3T80hUy36O3abV7hY3oxTtVhRkb86MQuqyC1osvU+CUK7z7gOc6Z9wD/9MlQPOLMzV4REN+NBg=='."\r\n";
  //  $header .= iconv_mime_encode("Subject", $mail_subject, $subject_preferences);

    // Send mail
 $res=mail($mail_to, $mail_subject, $mail_message, $header);

if($res==true)
	echo "всё ок";
else 
	echo "эррор";