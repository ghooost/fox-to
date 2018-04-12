<?php
class FoxApi {
  function __construct($debugMode=false, $dbConnect="",$apiURL=""){
    $def=array(
      "dbConnect"=>"Provider = VFPOLEDB.1; Data Source = \"C:\\Users\\Dev\\Desktop\\FoxProProj\\db_test\\a0a.dbf\"",
      "apiURL"=>"http://192.168.3.54:5002/kz"
    );

    $this->connStr=$dbConnect!=""?$dbConnect:$def['dbConnect'];
    $this->apiURL=$apiURL!=""?$apiURL:$def['apiURL'];

    $this->debugMode=$debugMode;
  }

  function connectDB($connStr){
    $this->db = new COM("ADODB.Connection") or die("Cannot start ADO");
    $this->db->Open($this->connStr);
  }

  function closeDB(){
    $this->db->Close();
  }

  function saveData($str){
    $data=json_decode($str,TRUE);
    foreach($data as $v){
      $this->insertItem($v);
    };
  }

  function insertItem($data){
    foreach($data['OtherInfo'] as $k=>$v) echo $k,'=>',$v,'<br>';
    $itemData=$this->processRootSection($data,array());
    //print_r($data);
  }

  function processRootSection($dataIn,$dataOut){
    $dataOut['type_doc_id']
  }

  function insert($table,$data){
    $fields=array_keys($data);
    $values=array_values($data);

    $clearval=array();
    foreach($values as $v)
      $clearval[]='"'.addslashes($v).'"';

    $sql='INSERT INTO '.$table.' ('.join(',',$fields).') values ('.join(',',$clearval).')';
//    echo $sql;
    $this->db->Execute($sql);
  }

  function sql($sql){
    return $this->db->Execute($sql);
  }
  function queryData($url=""){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$this->urlBase.$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, "HandleHeaderLine");
    $response = curl_exec($ch);
    curl_close($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    $this->applyItemJSON($body);
  }


}
?>
