<?php
$err=array();
$sql='';
//echo $conn;
// Microsoft Access connection string.
//$conn->Open("Provider=Microsoft.Jet.OLEDB.4.0; Data Source=C:\inetpub\wwwroot\php\mydb.mdb");
try {

  function HandleHeaderLine( $curl, $header_line ) {
    echo "<br>YEAH: ".$header_line; // or do whatever
    return strlen($header_line);
  }

  require "foxapi.php";
  $api=new FoxApi(true);

  $mode=empty($_REQUEST['mode'])?'':$_REQUEST['mode'];
  switch($mode) {
    case "request":
      $testJSON=join("",file("responce.js"));
      $api->saveData($testJSON);
    break;
    case "test":
      $testJSON=join("",file("responce.js"));
      $api->saveData($testJSON);
    break;
  };
} catch (Exception $e){
  $err[]=$e->getMessage();
}
// SQL statement to build recordset.

$viewerr='';
if(count($err)){
  $viewerr='<div><p>'.join('</p><p>',$err).'</p></div>';
}



header('Content-type:text/html,codepage=utf-8');
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta codepage="utf-8">
<title></title>
</head>
<body>
<h2>Request</h2>
<form action="server.php">
<input type="hidden" name="mode" value="request">
{$api->apiURL}&nbsp;<input type="text" style="width:200px" name="url" value="?id=2">
<button>Do request</button>
<hr>
<h2>Test insertion</h2>
<form action="server.php">
<input type="hidden" name="mode" value="test">
<button>Do test</button>
<hr>
$viewerr
</form>
</body>
</html>
EOT;


?>
