<?php
class FoxApi {
  function __construct($debugMode=false, $dbConnect="",$apiURL=""){
    $def=array(
      "dbConnect"=>"Provider = VFPOLEDB.1; Data Source = \"C:\\Users\\Dev\\Desktop\\FoxProProj\\db_test\\a0a.dbf\"",
      "apiURL"=>"http://192.168.3.54:5002/kz"
    );

    $this->connStr=$dbConnect!=""?$dbConnect:$def['dbConnect'];
    $this->apiURL=$apiURL!=""?$apiURL:$def['apiURL'];
    $this->db=null;

    $this->debugMode=$debugMode;
  }

  function connectDB(){
    if(!$this->db){
      $this->db = new COM("ADODB.Connection") or die("Cannot start ADO");
      $this->db->Open($this->connStr);
    };
  }

  function closeDB(){
    $this->db->Close();
    $this->db=null;
  }

  function saveData($str){
    $data=json_decode($str,TRUE);
    foreach($data as $v){
      $this->insertItem($v);
    };
  }

  function insertItem($data){
//    foreach($data['OtherInfo'] as $k=>$v) echo $k,'=>',$v,'<br>';
    // $itemData=array();
    // $this->processRootSection($data,$itemData);
    $data['base_id']=$this->makeDocId($data);
    $data['nsert']=date('Y').$this->makeDocId($data);
    $this->fillB10($data);
    $this->fillB14($data);
//    print_r($itemData);
  }

  function fillB10($dataIn){
    $dataToInsert=emptyFields(
      array("nsert","p2_1","p2_2","p2_3","p2_4","p2_5","p2_7","p2_8_3","zayv",
      "prod","p2_a_2","p2_a_3","p2_c","priznak","p2_g","p2_h","p2_i","p2_k_1",
      "name_st","p2_k2_1","p2_k2_2","p2_k2_3","p2_k_3","p2_k_4","p2_k_5","p2_l",
      "fio","pole_zay","pole_zayk","fio_zay","fio_zayk","fio_zayr","fio_zayrk",
      "pole_izg","pole_izgk","pole_prod","pole_prodk","pole_tnved","pr_pril",
      "pole_npa","pole_npak","pole_osn","pole_osnk","pole_dop","pole_dopk",
      "pole_exp","pole_expk","stat_otpr","dt_st_otpr","stat_pol"),
      array(),
      array("p2_3","p2_4","p2_5","p2_k2_2","p2_k_3","p2_k_4","dt_st_otpr")
    );

    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    $dataToInsert['p2_2']=$this->makeString($dataIn['base_id']);
    $dataToInsert['p2_4']=$this->makeDate($dataIn['OtherInfo']['start_date']);
    $dataToInsert['p2_5']=$this->makeDate($dataIn['OtherInfo']['end_date']);
    //TODO: получить blank id
    $dataToInsert['p2_7']=$this->makeString("blank id");//required

    //TODO: количество листов в приложении - где?
    $dataToInsert['p2_8_3']=0;//required

    $dataToInsert['zayv']=$this->makeString(" ");//required

    $dataToInsert['prod']=$this->makeString(" ");//required

    //TODO: понять что такое p2_a_2
    $dataToInsert['p2_a_2']=0;//required
    $dataToInsert['p2_a_3']=
      $this->chooseFromList(
        $dataIn['Production']['ProductType'],
        array(
          'Ser'=>'серия',
          'Part'=>'партия',
          'Single'=>'единичное изделие'
        )
      );

    //TODO: понять что такое p2_c
    $dataToInsert['p2_c']=0;//required


    //TODO: спросить где в данных  Признак включения продукции в единый перечень
    $dataToInsert['priznak']=
    $this->chooseFromList(
      //$dataIn['priznak'],
      '',
      array(
        '1'=>'Продукция исключена из единого перечня ( по ТР ТС/ЕАЭС )',
        '2'=>'Продукция включена в единый перечень (по Решению КТС № 620)'
      )
    );

    //TODO: понять что такое p2_g
    $dataToInsert['p2_g']=0;//required

    //TODO: понять что такое p2_h
    $dataToInsert['p2_h']=$this->makeString(" ");//required

    $dataToInsert['p2_i']=$this->makeString($dataIn['Production']['shema_id']);

    $this->insert('b10',$dataToInsert);
  }

  function fillB14($dataIn){
    $dataToInsert=emptyFields(
      array("nsert","p2_a4_1","p2_a4_2","p2_a4_3","p2_a4_4","p2_a4_5","p2_a4_6")
    );

    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);

    //TODO: узнать где хранится код страны
    $dataToInsert['p2_a4_1']=$this->makeString("KZ");

    $dataToInsert['p2_a4_2']=$this->makeString($dataIn['Izgotovitel']['title']);
    $dataToInsert['p2_a4_3']=$this->makeString($dataIn['Izgotovitel']['shortTittle']);
    $dataToInsert['p2_a4_4']=$this->makeString($dataIn['Izgotovitel']['codeOpf']);
    $dataToInsert['p2_a4_5']=$this->makeString($dataIn['Izgotovitel']['org_prav_forma']);

    $dataToInsert['p2_a4_6']=$this->makeString($dataIn['Izgotovitel']['ogrn']);

    $this->insert('b14',$dataToInsert);
  }

  function fillB16($dataIn){
    $dataToInsert=emptyFields(
      array("nsert","p2_b_1","p2_b_2","p2_b_3","p2_b_4","p2_b_5","p2_b_6","p2_b_7","p2_b_8"),
      array(),
      array("p2_b_2")
    );

    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);

    //TODO: узнать где хранится код страны
    $dataToInsert['p2_b_1']=$this->makeString("KZ");

    $dataToInsert['p2_b_2']=$this->makeString($dataIn['Izgotovitel']['title']);
    $dataToInsert['p2_b_3']=$this->makeString($dataIn['Izgotovitel']['shortTittle']);
    $dataToInsert['p2_b_4']=$this->makeString($dataIn['Izgotovitel']['codeOpf']);
    $dataToInsert['p2_b_5']=$this->makeString($dataIn['Izgotovitel']['org_prav_forma']);

    $dataToInsert['p2_b_6']=$this->makeString($dataIn['Izgotovitel']['ogrn']);

    $this->insert('b16',$dataToInsert);
  }

  function chooseFromList($data,$list){
    if(empty($list[$data])){
      return "'Wrong value ".$data."'";
    };
    return $this->makeString($list[$data]);
  }

  function makeDate($date=""){
    $pieces=preg_split('/-/',$date);
    if(count($pieces)!=3){
      return 'DATE()';
    } else {
      return 'DATE('.$pieces[0].','.$pieces[1].','.$pieces[2].')';
    };
  }

  function makeString($data){
    return '"'.addslashes($data).'"';
  }

  function makeDocId($dataIn){
    //Possible values:
    $types=array(
      "сертификат партии продукции"=>1,
      "сертификат серийного произодства"=>2,
      "декларация партии продукции"=>3,
      "декларация о серийном производстве"=>4
    );
    //TODO: сделать реальный выбор типа документа
    $type=1;
    $ret='';
    switch($type){
      case 1:
        $ret="21.01";
      break;
      case 2:
        $ret="22.01";
      break;
      case 3:
        $ret="23.01";
      break;
      case 4:
        $ret="24.01";
      break;
    };
    return "Данные для ДПС.".$ret.".".$dataIn['OtherInfo']['time_number'];
  }


  function emptyFields($fields,$num_fields=array(),$date_fields=array()){
    $dataToInsert=array();
    foreach($fields as $v)  $dataToInsert[$v]=$this->makeString(" ");
    foreach($num_fields as $v)  $dataToInsert[$v]=0;
    foreach($date_fields as $v)  $dataToInsert[$v]=$this->makeDate();
    return $dataToInsert;
  }
  function insert($table,$data){
    $fields=array_keys($data);
    $values=array_values($data);

    $sql='INSERT INTO '.$table.' ('.join(',',$fields).') values ('.join(',',$values).')';
    $f=fopen("sql.txt","w");
    fputs($f,$sql);
    fputs($f,"\r\nDATA:\r\n");
    foreach($data as $k=>$v)
      fputs($f,"$k=$v\r\n");
    fclose($f);


    $this->connectDB();
    if($this->db){
      $this->db->Execute(iconv("UTF-8","Windows-1251",$sql));
    };
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
