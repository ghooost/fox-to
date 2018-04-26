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
//echo $conn;
// Microsoft Access connection string.
//$conn->Open("Provider=Microsoft.Jet.OLEDB.4.0; Data Source=C:\inetpub\wwwroot\php\mydb.mdb");
try {

  require "foxapi.php";
  $api=new FoxApi(true);

  $mode=empty($_REQUEST['mode'])?'':$_REQUEST['mode'];
  switch($mode) {
    case "request_list":
      $api->processList($api->queryData("/api/getList"));
      //$api->saveData($testJSON);
    break;
    case "request_item":
      $api->processItem(6);
    break;
    case "geturl":
      $data=$api->queryData('/api/getItem?id=6');
      $out[]=print_r(json_decode($data,TRUE),TRUE);
    break;
    case "test":
      $sql=join("",file("test.sql"));
      $api->sql($sql);

    break;
  };
} catch (Exception $e){
  $err[]=$e->getMessage();
}
// SQL statement to build recordset.

$viewerr='';
if(count($err)){
  $viewerr.='<div><p>'.join('</p><p>',$err).'</p></div>';
}

if(count($out)){
  $viewerr.='<div>'.join('</div><div>',$out).'</div>';
}


header('Content-type:text/html,codepage=windows-1251');
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta codepage="windows-1251">
<title></title>
</head>
<body>
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
</body>
</html>
EOT;


?>
