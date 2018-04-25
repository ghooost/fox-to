<?php
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
    case "test":
      $testJSON=join("",file("response2.json"));
      $api->saveData($testJSON);
      $api->closeDB();
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
