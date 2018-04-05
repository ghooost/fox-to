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
      // $f=fopen($_REQUEST['url'],"r");
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,"http://192.168.3.54:5002/kz");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADERFUNCTION, "HandleHeaderLine");
      $response = curl_exec($ch);
      curl_close($ch);
      $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
      $header = substr($response, 0, $header_size);
      $body = substr($response, $header_size);


      echo $header,'<hr>',$body;
//      $err[]=$content;
      //$err[]=$_REQUEST['url'];
      //$err[]=join('',file($_REQUEST['url']));
    break;
    case 'sql':
      $sql=$_REQUEST['data'];
      if($sql){
        $result=$api->sql($sql);

        if ($result === false) die("failed");
        print_r($result);

  // Section 3
        while (!$result->EOF) {
          
          echo $result->fields[0]," ",$result->fields[1]," ",$result->fields[2];
          // for ($i=0, $max=$result->FieldCount(); $i < $max; $i++) {
          //   print $result->fields[$i].' ';
          // }
          $result->MoveNext();
          print "<br>\n";
        }

      }

    break;
};
$conn->Close();

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
$viewerr
<h2>Test insert</h2>
<form action="server.php">
<input type="hidden" name="mode" value="insert">
<button>Submit</button>
</form>
<hr>

<h2>Test sql</h2>
<form action="server.php">
<input type="hidden" name="mode" value="sql">
<textarea name="data" cols=5 style="width:100%">$sql</textarea>
<button>Submit</button>
</form>
<hr>
<h2>Request</h2>
<form action="server.php">
<input type="hidden" name="mode" value="request">
<input type="text" style="width:100%" name="url" value="http://192.168.3.54:5002/kz?id=2">
<button>Do request</button>
<hr>
</form>
</body>
</html>
EOT;


?>
