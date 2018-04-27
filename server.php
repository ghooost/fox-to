<?php

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    return;
  }
  if (error_reporting() & $severity) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
  }
}

$out=array();
$err=array();
$sql='';
try {

  require "foxapi.php";
  $api=new FoxApi(true);

  $mode=empty($_REQUEST['mode'])?'':$_REQUEST['mode'];
  switch($mode) {
    case "request_list":
      $api->processList($api->queryData("/api/getList"));
      //$api->saveData($testJSON);
    break;
    case "send_list":
      $out[]="<b>Здесь будет загрузка отправленных сообщений из FoxPro в АДВАНС.</b>";
      $out[]="Работаем над этим.";
    break;
    //
    // case "request_item":
    //   $api->processItem(6);
    // break;
    // case "geturl":
    //   $data=$api->queryData('/api/getItem?id=6');
    //   $out[]=print_r(json_decode($data,TRUE),TRUE);
    // break;
    // case "test":
    //   $sql=join("",file("test.sql"));
    //   $api->sql($sql);
    // break;

  };
} catch (Exception $e){
  $out[]='<div class="err"><b>Ошибка:</b> '.$e->getMessage()."</div>";
}
// SQL statement to build recordset.

$viewerr='';
if(count($err)){
  $viewerr.='<div class="err">'.join('</div><div class="err">',$err).'</div>';
}

if(count($out)){
  $viewerr.='<div class="note">'.join('</div><div class="note">',$out).'</div>';
}


header('Content-type: text/html, codepage=utf-8');
$styles=join('',file('afb/styles.css'));
$styles.=join('',file('afb/logo.css'));
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta codepage="utf-8">
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<title>АДВАНС/FoxPro синхронизатор</title>
<style>
$styles
</style>
</head>
<body class="$mode">
<div class="wrapper">
  <div class="header">
    <a href="https://www.advance-docs.ru" class="logo" target="_blank"></a>
    <div class="info">
      <h1>Без названия</h1>
      <span class="info_note">решение для синхронизации АДВАС и FoxPro</span>
    </div>
  </div>
  <div class="buttons">
    <div class="cont download_tab">
      <a class="download button" href="/?mode=request_list">АДВАНС &mdash; FoxPro</a>
    </div>
    <div class="cont upload_tab">
      <a class="upload button" href="/?mode=send_list">FoxPro &mdash; АДВАНС</a>
    </div>
  </div>
  <div class="field">
    $viewerr
  </div>
  <div class="footer">
    <div class="phones">
      <span class="phone_item">8 800 770 780 5</span>
      <span class="phone_item">+7 495 795 65 45</span>
    </div>
    <div class="copy">&copy; АДВАНС. Разработка программного обеспечения для органов по сертификации и испытательных лабораторий.</div>
  </div>
</div>
<!--
<h2>Request list</h2>
<form action="server.php">
<input type="hidden" name="mode" value="request_list">
<button>Do request</button>
</form>
<hr>
<h2>Request item</h2>
<form action="server.php">
<input type="hidden" name="mode" value="request_item">
<button>Do request</button>
</form>
<hr>
<h2>Request URL</h2>
<form action="server.php">
<input type="hidden" name="mode" value="geturl">
<button>Do request</button>
</form>
<hr>
<h2>Test insertion</h2>
<form action="server.php">
<input type="hidden" name="mode" value="test">
<button>Do test</button>
</form>
<hr>
$viewerr
-->
</body>
</html>
EOT;


?>
