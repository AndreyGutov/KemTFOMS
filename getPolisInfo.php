<html>
<head>
<meta charset="UTF-8">

<style type="text/css">    
	* { margin: 0; padding: 0; }
    p { padding: 10px; }
    #left { position: absolute; left: 0; top: 5%; width: 15%; }
    #right { position: absolute; right: 10%; top: 5%; width: 50%; } 
</style>

</head>
<body>
<div id="left">
<form method="POST">
<p><b>ФИО:</b><br>
<input type="text" name="fio" style="width: 17em"/>
<p><b>Дата рождения:</b><br>
<input type="date" name="birth" style="width: 17em"/>
<p><b>Номер полиса:</b><br>
<input type="number" name="npolis" style="width: 17em"/>
<p><input type="submit" value="Проверить"></p>
</div>

<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$db = mysqli_connect("адрес сервера mysql", "логин", "пароль", "имя БД");

if ($db->connect_error) {
    echo "Error".mysqli_connect_error();
    exit;
}

//$fio = "";
//$birth = "";
//$npolis = "";

if (!empty($_POST)) {

if(isset($_POST["fio"])){

    $fio = $_POST["fio"];
}
if(isset($_POST["birth"])){

    $birth = $_POST["birth"];
}
if(isset($_POST["npolis"])){

    $npolis = $_POST["npolis"];
}

$result_fio = explode(" ", $fio);

$xml = new XMLWriter(); 
$xml->openMemory(); 
$xml->startDocument($version = "1.0", $encoding = "UTF-8"); 
$xml->startElement("service"); 
	$xml->writeAttribute("name", 'chkPol');
	$xml->writeAttribute("uid", 'ИМЯ ТФОМС');
	$xml->writeAttribute("pwd", 'ПАРОЛЬ ТФОМС');
	$xml->writeAttribute("xmlns", 'http://site.kemoms.ru/xsd/Service.xsd');
    $xml->startElement("req");
		$xml->writeAttribute("date",date('Y-m-d'));
	$xml->startElement("pers");
		$xml->writeAttribute("shifr", 'TF001');
		$xml->writeAttribute("fn", $result_fio[1]);
		$xml->writeAttribute("ln", $result_fio[0]);
		$xml->writeAttribute("mn", $result_fio[2]);
		$xml->writeAttribute("birth",$birth);
	$xml->endElement();
	$xml->startElement("polis");
		$xml->writeAttribute("num",$npolis);
	$xml->endElement();
    $xml->endElement();
$xml->endElement();
$xmlReady = $xml->outputMemory();

$filename = "send.xml";
    $file_handle = fopen($filename, "w");
    fwrite($file_handle, $xmlReady); 
    fclose($file_handle);
	
	
$upload_url = "http://IP адрес из внутренней сети/service/default.aspx";


$ch = curl_init($upload_url);
$curlfile = curl_file_create(__DIR__.DIRECTORY_SEPARATOR.'send.xml');
$data = array("file"=>$curlfile);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$answData = curl_exec($ch);

$answData = simplexml_load_string($answData);

$lpu=$answData->answ[0]->med['lpu']; 
$ate=$answData->answ[0]->med['ate']; 


$query=$db->query("SELECT NAMELPU FROM checkAttachPol WHERE KDLPU='$lpu' AND KDATEMU='$ate'");
while($row = mysqli_fetch_assoc($query)) {
   $namelpu = $row['NAMELPU']; 
}
echo '<div id="right">';
echo "Поликлиника прикрепления: <b>".$namelpu. "</b><br>";
echo "Номер полиса: <b>".$answData->answ[0]->polis['num']."</b><br>"; 
echo "Фамилия: <b>".$answData->answ[0]->pers['ln']."</b><br>"; 
echo "Имя: <b>".$answData->answ[0]->pers['fn']."</b><br>"; 
echo "Отчество: <b>".$answData->answ[0]->pers['mn']."</b><br>"; 
echo "Снилс: <b>".$answData->answ[0]->pers['ss']."</b><br>"; 
//echo $answData->answ[0]->smo['id']."<br>"; 
echo '</div>';
}
?>

</form>
</body>
</html>






