<?php
$err=array();
$sql='';
//echo $conn;
// Microsoft Access connection string.
//$conn->Open("Provider=Microsoft.Jet.OLEDB.4.0; Data Source=C:\inetpub\wwwroot\php\mydb.mdb");
try {
  require "foxapi.php";
  $conn = new COM("ADODB.Connection") or die("Cannot start ADO");

  $conn->Open("Provider = VFPOLEDB.1; Data Source = \"C:\\Users\\Dev\\Desktop\\FoxProProj\\db_test\\a0a.dbf\"");
  $api=new FoxApi($conn);

  $mode=empty($_REQUEST['mode'])?'':$_REQUEST['mode'];
  switch($mode) {
    case 'insert':
      //
      $api->insert("b131",array(
         "nsert"=>"2018Данные для ДПС.23.01.00003",
         "id_13"=>1,
         "p2_a1_1"=>"это тест"
       ));
    break;
    case "request":
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $_REQUEST['url']);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      $err[]=$output;

    break;
    case 'sql':
      $sql=$_REQUEST['data'];
      if($sql)
        $api->sql($sql);

    //
    // $api->insert("b131",array(
    //   "nsert"=>"2018Данные для ДПС.23.01.00003",
    //   "id_13"=>"1234",
    //   "p2_a1_1"=>"это тест"
    // ));

    break;
};
  //$api->saveData('{"data":"see here"}');
  // $rs = $conn->Execute("SELECT * FROM b10");
  // echo "<p>Below is a list of values in the MYDB.MDB database, MYABLE table, MYFIELD field.</p>";
  //
  //
  // $f=fopen("test.txt","w");
  // // Display all the values in the records set
  // while (!$rs->EOF) {
  //     $fv = $rs->Fields("zayv");
  //     fputs($f,$fv->value."\n");
  //     echo "Value: ".$fv->value."\n";
  //     $rs->MoveNext();
  // }
  // fclose($f);
  // $rs->Close();


} catch (Exception $e){
  $err[]=$e->getMessage();
}
// SQL statement to build recordset.

$viewerr='';
if(count($err)){
  $viewerr='<div><p>'.join('</p><p>',$err).'</p></div>';
}

header('Content-type:text/html,codepage=windows-1251');
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<title></title>
</head>
<body>
$viewerr
<h2>Test sql</h2>
<form action="server.php">
<input type="hidden" name="mode" value="sql"><br>
<textarea name="data" cols=5 style="width:100%">$sql</textarea>
<button>Submit</button>
</form>
<hr>
<h2>Request</h2>
<form action="server.php">
<input type="hidden" name="mode" value="request"><br>
<input type="text" style="width:100%" name="url" value="http://192.168.3.54:5002/kz?id=2">
<button>Do request</button>
<hr>
</form>
</body>
</html>
EOT;


?>
