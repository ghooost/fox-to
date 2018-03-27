<?php
class FoxApi {
  function __constructor($db){
    $this->db=$db;
  }
  function saveData($str){
    $data=json_decode($str,TRUE);
    print_r($data);
  }
  function insert($table,$data){
    $fields=array_keys($data);
    $values=array_values($data);

    $clearval=array();
    foreach($values as $v)
      $clearval[]='"'.addslashes($v).'"';

    $sql='INSERT INTO '.$table.' ('.join(',',$fields).') values ('.join(',',$clearval).')';
    $this->db->Execute($sql);
  }
  function sql($sql){
    $this->db->Execute($sql);
  }
}
?>
