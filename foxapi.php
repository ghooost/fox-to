<?php
class FoxApi {
  function __construct($debugMode=false, $dbConnect="",$apiURL=""){
    $def=array(
      "dbConnect"=>"Provider = VFPOLEDB.1; Data Source = \"C:\\Users\\Dev\\Desktop\\FoxProProj\\db_test\\a0a.dbf\"",
      "apiURL"=>"http://192.168.3.54:5002"
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
    foreach($data['items'] as $v){
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
    $this->fillB16($data);
    $this->fillB17($data);
    $this->fillB18($data);
    $this->fillB19($data);
    $this->fillB21($data);
    $this->fillB211($data);
    $this->fillB11($data);

    $this->fillB12($data);
    $this->fillB14($data);
//    print_r($itemData);
  }

  function fillB10($dataIn){
    $dataToInsert=$this->emptyFields(
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

    //TODO: получить blank id Subtopic1?
    $dataToInsert['p2_7']=$this->makeString(" ");//required

    //TODO: количество листов в приложении - где?
    $dataToInsert['p2_8_3']=0;//required

    $dataToInsert['zayv']=$this->makeString($dataIn['Zajavitel']['result_string']);//required

    $dataToInsert['prod']=$this->makeString(" ");//required

    //TODO: понять что такое p2_a_2
    $dataToInsert['p2_a_2']=0;//required
    $dataToInsert['p2_a_3']=
      $this->chooseFromList(
        $dataIn['Production']['ProductType'],
        array(
          'Serial'=>'серия',
          'Part'=>'партия',
          'Single'=>'единичное изделие'
        )
      );

    //TODO: понять что такое p2_c
    $dataToInsert['p2_c']=0;//required


    //TODO: спросить где в данных  Признак включения продукции в единый перечень
    $dataToInsert['priznak']=
    $this->chooseFromList(
      $dataIn['OtherInfo']['productOnPerechen'],
      array(
        '0'=>' ',
        '1'=>'Продукция исключена из единого перечня ( по ТР ТС/ЕАЭС )',
        '2'=>'Продукция включена в единый перечень (по Решению КТС № 620)'
      )
    );

    //TODO: понять что такое p2_g
    $dataToInsert['p2_g']=0;//required

    //TODO: понять что такое p2_h
    $dataToInsert['p2_h']=$this->makeString(" ");//required

    $dataToInsert['p2_i']=$this->makeString($dataIn['Production']['shema_id']);

    $dataToInsert['pole_zay']=$this->makeString($dataIn['Zajavitel']['result_string']);//required
    $dataToInsert['pole_zayk']=$this->makeString($dataIn['Zajavitel']['result_string_kz']);//required

    $dataToInsert['pole_izg']=$this->makeString($dataIn['Izgotovitel']['result_string']);//required
    $dataToInsert['pole_izgk']=$this->makeString($dataIn['Izgotovitel']['result_string_kz']);//required

    $pole_prod=array();
    $pole_tnved=array();
    if(!empty($dataIn['Production']) && !empty($dataIn['Production']['production_item_ids']))
      foreach($dataIn['Production']['production_item_ids'] as $v){
        if($v['result_string'])
          $pole_prod[]=$v['result_string'];
          if($v['tnved_ids'])
            $pole_tnved[]=$v['tnved_ids'];
      };
    $dataToInsert['pole_prod']=$this->makeString(join(", ",$pole_prod));//required
    //TODO: узнать у Стаса результирующее поле продукции на казахском
    $dataToInsert['pole_prodk']=$this->makeString(" ");
    $dataToInsert['pole_tnved']=$this->makeString(join(", ",$pole_tnved));//required

    //TODO: применение бланков приложения
    $dataToInsert['pr_pril']=$this->makeString(" ");



    $this->insert('b10',$dataToInsert);
  }

//Сведения о документах подтверждающих соответствие
  function fillB16($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","p2_b_1","p2_b_2","p2_b_3","p2_b_4","p2_b_5","p2_b_6","p2_b_7","p2_b_8"),
      array(),
      array("p2_b_2")
    );

    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    //TODO: узнать где Сведения о документах подтверждающих соответствие
    //вроде бы никаких essential_documents_ids не видать
    // $dataToInsert['p2_b_1']=$this->makeString("KZ");
    // $dataToInsert['p2_b_2']=$this->makeString($dataIn['Izgotovitel']['title']);
    // $dataToInsert['p2_b_3']=$this->makeString($dataIn['Izgotovitel']['shortTittle']);
    // $dataToInsert['p2_b_4']=$this->makeString($dataIn['Izgotovitel']['codeOpf']);
    // $dataToInsert['p2_b_5']=$this->makeString($dataIn['Izgotovitel']['org_prav_forma']);
    // $dataToInsert['p2_b_6']=$this->makeString($dataIn['Izgotovitel']['ogrn']);
    // $dataToInsert['p2_b_7']=$this->makeString($dataIn['Izgotovitel']['ogrn']);
    // $dataToInsert['p2_b_8']=$this->makeString($dataIn['Izgotovitel']['ogrn']);
    //
    // $this->insert('b16',$dataToInsert);
  }

//Требования
  function fillB17($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","p2_d","name_tr","name_trk"),
      array(),
      array()
    );
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    if(!empty($dataIn['Trebovaniya']) && !empty($dataIn['Trebovaniya']['reglament_ids']))
      foreach($dataIn['Trebovaniya']['reglament_ids'] as $v){
        $dataToInsert['p2_d']=$this->makeString($v['number']);
        $dataToInsert['name_tr']=$this->makeString($v['name']);
        $dataToInsert['name_trk']=$this->makeString($v['name_kz']);

        $this->insert('b17',$dataToInsert);
      };
  }

//НПА, на соответствие требованиям которых проводилась проверка
  function fillB18($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","p2_e_1","p2_e_2","p2_e_3"),
      array(),
      array("p2_e_2")
    );
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    if(!empty($dataIn['Trebovaniya']) && !empty($dataIn['Trebovaniya']['npa']))
      foreach($dataIn['Trebovaniya']['npa'] as $v){
        $dataToInsert['p2_e_1']=$this->makeString($v['npa_d_title_doc']);
        $dataToInsert['p2_e_2']=$this->makeDate($v['npa_d_date_of_issue']);
        $dataToInsert['p2_e_3']=$this->makeString($v['npa_d_number_doc']);

        $this->insert('b18',$dataToInsert);
      };
  }

//Эксперты
  function fillB19($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","p2_f_1","p2_f_2","p2_f_3","fio","i_k","o_k","f_k"),
      array(),
      array()
    );
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    //TODO: узнать где инфа по экспертам
  }

//Сведенья о документах обеспечивающих соблюдение требований
  function fillB21($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","id","p2_j_2","p2_j_3","p2_j_4","p2_j_5"),
      array(),
      array("p2_j_5")
    );
    $id=1;
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    if(!empty($dataIn['Dopinfo']) && !empty($dataIn['Dopinfo']['norm_doc']))
      foreach($dataIn['Dopinfo']['norm_doc'] as $v){
        $dataToInsert['id']=$id;
        $dataToInsert['p2_j_2']=
          $this->chooseFromList(
            $dataIn['n_d_isPerechenInclude'],
            array(
              '1'=>'Да',
              '0'=>'Нет'
            )
          );
        $dataToInsert['p2_j_3']=$this->makeString($v['n_d_title_doc']);
        $dataToInsert['p2_j_4']=$this->makeString($v['n_d_number_doc']);
        $dataToInsert['p2_j_5']=$this->makeDate($v['n_d_date_of_issue']);

        $this->insert('b21',$dataToInsert);

        if(!empty($v['n_d_norm_razdel_ids']))
          foreach($v['n_d_norm_razdel_ids'] as $v1)
            $this->fillB211($v1,$id,$dataToInsert['nsert']);

        $id+=1;
      };
  }

//Разделы документов обеспечивающих соблюдение требований
  function fillB211($itemData,$id,$nsert){
    $dataToInsert=$this->emptyFields(
      array("nsert","id","p2_j1_1","p2_j1_2"),
      array(),
      array()
    );
    $dataToInsert['nsert']=$nsert;
    $dataToInsert['id']=$id;
    $dataToInsert['p2_j1_1']=$this->makeString($itemData['n_r_title_doc']);
    $dataToInsert['p2_j1_2']=$this->makeString($itemData['n_r_name_element']);
    $this->insert('b211',$dataToInsert);
  }

//Номера бланков приложений
  function fillB11($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","p2_8_1","p2_8_2"),
      array(),
      array()
    );
    $id=1;
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);
    if(!empty($dataIn['Production']) && !empty($dataIn['Production']['app_form_numbers_ids']))
      foreach($dataIn['Production']['app_form_numbers_ids'] as $v){
        $dataToInsert['p2_8_1']=$this->makeString($v['idNumber']);
        $dataToInsert['p2_8_2']=$this->makeString($v['numbersAppForm']);

        $this->insert('b11',$dataToInsert);
      };
  }

//Заявитель
  function fillB12($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","p2_9_1","p2_9_2","p2_9_3","p2_9_4","p2_9_5","p2_9_6",
        "p2_9a_11","p2_9a_12","p2_9a_13","p2_9a_2","i_k","o_k","f_k","d_k",
        "ir","or","fr","dr","ir_k","or_k","fr_k","dr_k","ruk","p2_9a_41",
        "p2_9a_42","p2_9a_43"),
      array(),
      array("p2_9a_42")
    );
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);

    $dataToInsert['p2_9_1']=$this->makeString($dataIn['Zajavitel']['root_country_id']);
    $dataToInsert['p2_9_2']=$this->makeString($dataIn['Zajavitel']['title']);
    $dataToInsert['p2_9_3']=$this->makeString($dataIn['Zajavitel']['shortTittle']);
    $dataToInsert['p2_9_4']=$this->makeString($dataIn['Zajavitel']['codeOpf']);
    $dataToInsert['p2_9_5']=$this->makeString($dataIn['Zajavitel']['org_prav_forma']);
    $dataToInsert['p2_9_6']=$this->makeString($dataIn['Zajavitel']['ogrn']);
    $dataToInsert['p2_9a_2']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['post_id']['post_im_pad']);
    $dataToInsert['p2_9a_11']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['name']);
    $dataToInsert['p2_9a_12']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['patronymicName']);
    $dataToInsert['p2_9a_13']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['surName']);

    $dataToInsert['o_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['patronymicName_kz']);
    $dataToInsert['i_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['name_kz']);
    $dataToInsert['f_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['surName_kz']);
    $dataToInsert['d_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['post_id']['post_im_pad_kz']);

    $dataToInsert['dr']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['post_id']['post_rd_pad']);
    $dataToInsert['ir']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['rd_name']);
    $dataToInsert['or']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['rd_patronymicName']);
    $dataToInsert['fr']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['rd_surName']);

    $dataToInsert['dr_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['post_id']['post_rd_pad_kz']);
    $dataToInsert['ir_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['rd_name_kz']);
    $dataToInsert['or_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['rd_patronymicName_kz']);
    $dataToInsert['fr_k']=$this->makeString($dataIn['Zajavitel']['fio_item_id']['rd_surName_kz']);

    $dataToInsert['ruk']=$this->makeString(
      $dataIn['Zajavitel']['fio_item_id']['post_id']['post_im_pad']+
      $dataIn['Zajavitel']['fio_item_id']['surName']+
      $dataIn['Zajavitel']['fio_item_id']['name']+
      $dataIn['Zajavitel']['fio_item_id']['patronymicName']
    );



    $id=0;
    if(!empty($dataIn['Zajavitel']['fillials']))
      foreach($dataIn['Zajavitel']['fillials'] as $v){
        $id++;
        $this->fillB120($v,$id,$dataToInsert['nsert']);
      };

    if(!empty($dataIn['Zajavitel']['adress']))
      foreach($dataIn['Zajavitel']['adress'] as $v){
        $this->fillB121($v,$dataToInsert['nsert']);
      };

    $id=0;
    foreach($dataIn['Zajavitel'] as $k=>$v){
      $cType=$this->getConnectionType($k);
      if($cType!=null){
        $id++;
        $this->fillB122($v,$cType['code'],$cType['name'],$id,$dataToInsert['nsert']);
      }
    }

    //TODO: наименование документа и адрес сайта для связи?

    $this->insert('b12',$dataToInsert);
  }

//Филиалы заявителя
  function fillB120($dataIn,$id_f,$nsert){
    $dataToInsert=$this->emptyFields(
      array("nsert","id_f","p2_99_1","p2_99_2","p2_99_3","p2_99_4","p2_99_5",
        "p2_99_6","p2_99_7","p2_99_8","p2_99_9"),
      array(),
      array()
    );

    $dataToInsert['nsert']=$this->makeString($nsert);

    $dataToInsert['id_f']=$this->makeString($id_f);

    if(!empty($dataIn['address']) && count($dataIn['address'])){
      $addr="";
      foreach($dataIn['address'] as $v){
        $this->fillB1201($v,$id_f,$nsert);
        if(!$addr && !empty($v['countryCode']))
          $addr=$v['countryCode'];
      };

      $dataToInsert['p2_99_1']=$this->makeString($addr);
    };

    $id=0;
    foreach($dataIn as $k=>$v){
      $cType=$this->getConnectionType($k);
      if($cType!=null){
        $id++;
        $this->fillB1202($v,$cType['code'],$cType['name'],$id,$id_f,$dataToInsert['nsert']);
      }
    }


    $dataToInsert['p2_99_2']=$this->makeString($dataIn['title']);
    $dataToInsert['p2_99_3']=$this->makeString($dataIn['shortTittle']);
    $dataToInsert['p2_99_4']=$this->makeString($dataIn['codeOpf']);
    $dataToInsert['p2_99_5']=$this->makeString($dataIn['org_prav_forma']);
    $dataToInsert['p2_99_6']=$this->makeString($dataIn['ogrn']);

    $this->insert('b120',$dataToInsert);
  }

  //адреса филиалов Заявителя
  function fillB1201($dataIn,$id_f,$nsert){
    $dataToInsert=$this->emptyFields();
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_f']=$this->makeString($id_f);
    $dataToInsert=$this->applyAddress($dataIn,'p2_99_a_',$dataToInsert);
    $this->insert('b1201',$dataToInsert);
  }

  //виды связи филиалов заявителя
  function fillB1202($value, $code,$name,$id,$id_f,$nsert){
    $arr=preg_split('/[\,\s]+/',$value);
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['id_f']=$this->makeString($id_f);

    if($arr && count($arr)){
      $dataToInsert['p2_99_b_1']=$this->makeString($code);
      $dataToInsert['p2_99_b_2']=$this->makeString($name);

      $this->insert('b1202',$dataToInsert);

      foreach($arr as $val)
        if($val!="")
          $this->fillB12021($val,$id,$id_f,$nsert);
    };
  }

  //значения видов связи филиалов заявителя
  function fillB12021($value,$id,$id_f,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['id_f']=$this->makeString($id_f);
    $dataToInsert['p2_99_b_3']=$this->makeString($value);

    $this->insert('b12021',$dataToInsert);
  }

  //адреса заявителя
  function fillB121($dataIn,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert=$this->applyAddress($dataIn,'p2_97_',$dataToInsert);
    $this->insert('b121',$dataToInsert);
  }

  //виды связи заявителя
  function fillB122($value, $code,$name,$id,$nsert){
    $arr=preg_split('/[\,\s]+/',$value);
    if($arr && count($arr)){
      $dataToInsert['nsert']=$this->makeString($nsert);
      $dataToInsert['id']=$this->makeString($id);

      $dataToInsert['p2_96_1']=$this->makeString($code);
      $dataToInsert['p2_96_2']=$this->makeString($name);

      $this->insert('b122',$dataToInsert);

      foreach($arr as $val)
        if($val!="")
          $this->fillB1221($val,$id,$nsert);
    };
  }

  //значения видов связи заявителя
  function fillB1221($value,$id,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['p2_96_3']=$this->makeString($value);

    $this->insert('b1221',$dataToInsert);
  }




  //Таблица изготовителя
    function fillB14($dataIn){
      $dataToInsert=$this->emptyFields(
        array("nsert","p2_a4_1","p2_a4_2","p2_a4_3","p2_a4_4","p2_a4_5","p2_a4_6")
      );

      $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);

      $dataToInsert['p2_a4_1']=$this->makeString($dataIn['Izgotovitel']['root_country_id']);

      $dataToInsert['p2_a4_2']=$this->makeString($dataIn['Izgotovitel']['title']);
      $dataToInsert['p2_a4_3']=$this->makeString($dataIn['Izgotovitel']['shortTitle']);
      $dataToInsert['p2_a4_4']=$this->makeString($dataIn['Izgotovitel']['codeOpf']);
      $dataToInsert['p2_a4_5']=$this->makeString($dataIn['Izgotovitel']['org_prav_forma']);

      $dataToInsert['p2_a4_6']=$this->makeString($dataIn['Izgotovitel']['ogrn']);

      $id=0;
      if(!empty($dataIn['Izgotovitel']['fillials']))
        foreach($dataIn['Izgotovitel']['fillials'] as $v){
          $id++;
          $this->fillB140($v,$id,$dataToInsert['nsert']);
        };


      if(!empty($dataIn['Izgotovitel']['adress']))
        foreach($dataIn['Izgotovitel']['adress'] as $v){
          $this->fillB141($v,$dataToInsert['nsert']);
        };

      $id=0;
      foreach($dataIn['Izgotovitel'] as $k=>$v){
        $cType=$this->getConnectionType($k);
        if($cType!=null){
          $id++;
          $this->fillB142($v,$cType['code'],$cType['name'],$id,$dataToInsert['nsert']);
        }
      }

      $this->insert('b14',$dataToInsert);
    }

  //филиал изготовителя
  function fillB140($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","id_f","p2_a4_91","p2_a4_92","p2_a4_93","p2_a4_94","p2_a4_95",
        "p2_a4_96","p2_a4_97","p2_a4_98","p2_a4_99"),
      array(),
      array()
    );

    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_f']=$this->makeString($id_f);

    if(!empty($dataIn['address']) && count($dataIn['address'])){
      $addr="";
      foreach($dataIn['address'] as $v){
        $this->fillB1401($v,$id_f,$nsert);
        if(!$addr && !empty($v['countryCode']))
          $addr=$v['countryCode'];
      };

      $dataToInsert['p2_a4_91']=$this->makeString($addr);
    };

    $id=0;
    foreach($dataIn as $k=>$v){
      $cType=$this->getConnectionType($k);
      if($cType!=null){
        $id++;
        $this->fillB1402($v,$cType['code'],$cType['name'],$id,$id_f,$dataToInsert['nsert']);
      }
    }


    $dataToInsert['p2_a4_92']=$this->makeString($dataIn['title']);
    $dataToInsert['p2_a4_93']=$this->makeString($dataIn['shortTittle']);
    $dataToInsert['p2_a4_94']=$this->makeString($dataIn['codeOpf']);
    $dataToInsert['p2_a4_95']=$this->makeString($dataIn['org_prav_forma']);
    $dataToInsert['p2_a4_96']=$this->makeString($dataIn['ogrn']);

    $this->insert('b140',$dataToInsert);

  }


  //адреса филиалов Изготовителя
  function fillB1401($dataIn,$id_f,$nsert){
    $dataToInsert=$this->emptyFields();
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_f']=$this->makeString($id_f);
    $dataToInsert=$this->applyAddress($dataIn,'p2a4_9a',$dataToInsert);
    $this->insert('b1401',$dataToInsert);
  }

  //виды связи филиалов заявителя
  function fillB1402($value, $code,$name,$id,$id_f,$nsert){
    $arr=preg_split('/[\,\s]+/',$value);
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['id_f']=$this->makeString($id_f);

    if($arr && count($arr)){
      $dataToInsert['p2a4_9b1']=$this->makeString($code);
      $dataToInsert['p2a4_9b2']=$this->makeString($name);

      $this->insert('b1402',$dataToInsert);

      foreach($arr as $val)
        if($val!="")
          $this->fillB14021($val,$id,$id_f,$nsert);
    };
  }

  //значения видов связи филиалов заявителя
  function fillB14021($value,$id,$id_f,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['id_f']=$this->makeString($id_f);
    $dataToInsert['p2a4_9b3']=$this->makeString($value);

    $this->insert('b14021',$dataToInsert);
  }

  //адреса заявителя
  function fillB141($dataIn,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert=$this->applyAddress($dataIn,'p2_a4_7',$dataToInsert);
    $this->insert('b121',$dataToInsert);
  }

  //виды связи заявителя
  //TODO: понять где виды связи
  function fillB142($value, $code,$name,$id,$nsert){
    $arr=preg_split('/[\,\s]+/',$value);
    if($arr && count($arr)){
      $dataToInsert['nsert']=$this->makeString($nsert);
      $dataToInsert['id']=$this->makeString($id);

      $dataToInsert['p2_a4_81']=$this->makeString($code);
      $dataToInsert['p2_a4_82']=$this->makeString($name);

      $this->insert('b142',$dataToInsert);

      foreach($arr as $val)
        if($val!="")
          $this->fillB1221($val,$id,$nsert);
    };
  }

  //значения видов связи заявителя
  function fillB1421($value,$id,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['p2_a4_83']=$this->makeString($value);

    $this->insert('b1421',$dataToInsert);
  }

  //продукция
  function fillB13($dataIn){
    $dataToInsert=$this->emptyFields(
      array("nsert","id_13","p2_a1_2","p2_a1_4","p2_a1_5",
        "p2_a1_6","p2_a1_8",
        "tnved    ","npa_izg","npa_izgk   ","name_pr","name_prk"),
      array(),
      array()
    );
    $dataToInsert['nsert']=$this->makeString($dataIn['nsert']);

    //TODO: вообще-то это капец, но так оно сделано - id_13 есть и в
    //идентификаторе продукта, но этот ID никак не связан
    //с ID_13 продукта
    if(!empty($dataIn['Production']['app_form_numbers_ids']) && count($dataIn['Production']['app_form_numbers_ids'])){
      foreach($dataIn['Production']['app_form_numbers_ids'] as $v){
          $this->fillB131($v['numbersAppForm'],$v['idNumber'],$dataIn['nsert']);
      }
    }

    $id=0;
    if(!empty($dataIn['Production']['production_item_ids']) && count($dataIn['Production']['production_item_ids'])){
      foreach($dataIn['Production']['production_item_ids'] as $v){
        $id+=1;
        $dataToInsert['id_13']=$this->makeString($id);

        //TODO: выяснить где у Стаса наименование продукции присвоенное изготовителем
        $dataToInsert['p2_a1_2 ']=$this->makeString($v['name_of_prod']);
        $dataToInsert['p2_a1_4 ']=$this->makeString($v['opisanie_information']);
        $dataToInsert['p2_a1_5']=$this->makeString($v['inaya_information']);
        $dataToInsert['p2_a1_6']=$this->makeString($v['number_part']);

        //TODO: выяснить где у Стаса европейский номер товара
        $dataToInsert['p2_a1_8']=$this->makeString("");
        $dataToInsert['tnved']=$this->makeString($v['tnved_ids']);

        $npa_izg=array();
        if($v['production_svedeniya_ids'] && count($v['production_svedeniya_ids']))
          foreach($v['production_svedeniya_ids'] as $v1){
            $npa_izg[]=$v1['title_doc'].' '.$v1['number_doc'];
            $this->fill134($v,$id,$dataIn['nsert']);
          }

        if(count($npa_izg)){
          $dataToInsert['npa_izg']=$this->makeString(join(", ",$npa_izg));
        }

        $id_solo=0;
        if($v['production_solo_ids'] && count($v['production_solo_ids']))
          foreach($v['production_solo_ids'] as $v1){
            $id_solo+=1;
            $this->fillB133($v1,$id_solo,$id,$dataIn['nsert']);
          }

        $dataToInsert['name_pr']=$this->makeString($v['name_of_prod']);
        //TODO: выяснить где у Стаса название продукции на казахском
        //$dataToInsert['name_prk']=$this->makeString($v['name_of_prod']);

        $this->fillB132($v['name_of_prod'],$id,$dataIn['nsert']);
        $this->fillB135($v['tnved_ids'],$id,$dataIn['nsert']);

        $this->insert('b13',$dataToInsert);
      };
    };

  }

  //идентификатор продукта
  function fillB131($value,$id,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id);
    $dataToInsert['p2_a1_1']=$this->makeString($value);
    $this->insert('b131',$dataToInsert);
  }

  //наименование продукции
  function fillB132($value,$id,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id);
    $dataToInsert['p2_a1_3']=$this->makeString($value);
    $this->insert('b132',$dataToInsert);
  }

  //количество продукции
  function fillB133($dataIn,$id,$id_13,$nsert){
    $dataToInsert=$this->emptyFields(
      array("nsert","id_13","id",
        "p2_a1_71","p2_a1_71a","p2_a1_71b","p2_a1_72",
        "p2_a1_73","p2_a1_75","p2_a1_76","tnved"),
      array(),
      array("p2_a1_75","p2_a1_76")
    );

    //TODO: Выяснить у Стаса о количестве продукции

    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id_13);
    $dataToInsert['id']=$this->makeString($id);


    $dataToInsert['p2_a1_71']=$dataIn['count'];
    $dataToInsert['p2_a1_71a']=$this->makeString($dataIn['lot_id']['title']);
    $dataToInsert['p2_a1_71b']=$this->makeString($dataIn['lot_id']['code']);
    $dataToInsert['p2_a1_72']=$this->makeString($dataIn['factory_number']);
    $dataToInsert['p2_a1_73']=$this->makeString($dataIn['group_name']);
    $dataToInsert['p2_a1_75']=$this->makeDate($dataIn['production_date']);
    $dataToInsert['p2_a1_76']=$this->makeDate($dataIn['storage_life']);
    $dataToInsert['tnved']=$this->makeString($dataIn['tnved_ids']);


    $arr=preg_split('/[\,\s]+/',$dataIn['p_d_dop_svedeniya']);
    foreach($arr as $v)
      if($v){
          $this->fill1331($v,$id,$id_13,$nsert);
      };

    $arr=preg_split('/[\,\s]+/',$dataIn['tnved_ids']);
    foreach($arr as $v)
      if($v){
          $this->fill1332($v,$id,$id_13,$nsert);
      };

    $this->insert('b133',$dataToInsert);
  }

  function fill1331($value, $id, $id_13, $nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id_13);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['p2_a1_74']=$this->makeString($value);
    $this->insert('b1331',$dataToInsert);
  }

  function fill1332($value, $id, $id_13, $nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id_13);
    $dataToInsert['id']=$this->makeString($id);
    $dataToInsert['p2_a1_77']=$this->makeString($value);
    $this->insert('b1332',$dataToInsert);
  }

  //сведенья о документах, в соответствии с которыми изготовлено изделие
  function fill134($dataIn,$id,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id);

    $dataToInsert['p2_a1_91']=$this->makeString($dataIn['title_doc']);
    $dataToInsert['p2_a1_92']=$this->makeDate($dataIn['date_of_issue']);
    $dataToInsert['p2_a1_93']=$this->makeDate($dataIn['number_doc']);

    $this->insert('b134',$dataToInsert);
  }

  function fill135($value,$id,$nsert){
    $dataToInsert['nsert']=$this->makeString($nsert);
    $dataToInsert['id_13']=$this->makeString($id);

    $arr=preg_split('/[\,\s]+/',$value);
    if(count($arr))
      foreach ($arr as $v)
        if($v){
          $dataToInsert['p2_a1_a']=$v;
          $this->insert('b135',$dataToInsert);
        }
  }


  function getConnectionType($code){
    switch($code){
      case 'phone':
        return array('code'=>'TE','name'=>'телефон');
      break;
      case 'faxnum':
        return array('code'=>'FX','name'=>'факс');
      break;
      case 'email':
        return array('code'=>'EM','name'=>'электронная почта');
      break;
      case 'website':
        return array('code'=>'AO','name'=>'адрес сайта');
      break;
    }
    return null;
  }

  function processList($str){
    global $out;
    print_r($str);
    $data=json_decode($str,TRUE);
    print_r($data);
    foreach($data['ids'] as $v){
      try {
        $body=$this->queryData("/api/getItem?id=".$v);
        $itemData=json_decode($body,TRUE);
        $this->insertItem($itemData);
      } catch(Exception $e) {
        $out[]=$e->getMessage();
      }
    };
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
    return "'".addslashes($data)."'";
  }

  function makeDocId($dataIn){
    //TODO: выяснить правильный вариант генерации docid
    if(!empty($dataIn['OtherInfo']['reg_number_full_string'])){
      return $dataIn['OtherInfo']['reg_number_full_string'];
    } else {
      //Possible values:
      $types=array(
        "сертификат партии продукции"=>1,
        "сертификат серийного произодства"=>2,
        "декларация партии продукции"=>3,
        "декларация о серийном производстве"=>4
      );
      //сделать реальный выбор типа документа
      $type=$dataIn['OtherInfo']['typedocint'];
      $ret='';
      switch($type){
        case 21:
          $ret="21.01";
        break;
        case 22:
          $ret="22.01";
        break;
        case 23:
          $ret="23.01";
        break;
        case 24:
          $ret="24.01";
        break;
      };
      return "Данные для ДПС.".$ret.".".$dataIn['OtherInfo']['time_number'];
    };
  }


  function applyAddress($dataIn,$prefix,$dataToInsert){
    $dataToInsert[$prefix.'1']=$this->makeString($dataIn['adress_string']);
    $dataToInsert[$prefix.'2']=$this->makeString($dataIn['country_id']);
    $dataToInsert[$prefix.'3']=$this->makeString(" ");
    $dataToInsert[$prefix.'4']=$this->makeString($dataIn['region_id']);
    $dataToInsert[$prefix.'5']=$this->makeString($dataIn['aria']);
    $dataToInsert[$prefix.'6']=$this->makeString($dataIn['city']);
    $dataToInsert[$prefix.'7']=$this->makeString($dataIn['locality']);
    $dataToInsert[$prefix.'8']=$this->makeString($dataIn['street']);
    $dataToInsert[$prefix.'9']=$this->makeString($dataIn['buildingNumber']);
    $dataToInsert[$prefix.'a']=$this->makeString($dataIn['officeNumber']);
    $dataToInsert[$prefix.'b']=$this->makeString($dataIn['zipCode']);
    $dataToInsert['text']=$this->makeString($dataIn['adress_string']);
    return $dataToInsert;
  }


  function emptyFields($fields,$num_fields=array(),$date_fields=array()){
    $dataToInsert=array();
    foreach($fields as $v)  $dataToInsert[$v]=$this->makeString(" ");
    foreach($num_fields as $v)  $dataToInsert[$v]=0;
    foreach($date_fields as $v)  $dataToInsert[$v]=$this->makeDate();
    return $dataToInsert;
  }
  function insert($table,$data){
    global $out;
    $sql="";
    try {
      $fields=array_keys($data);
      $values=array_values($data);

      $sql='INSERT INTO '.$table.' ('.join(',',$fields).') values ('.join(',',$values).')';
      $this->connectDB();
      if($this->db){
        $this->db->Execute(iconv("UTF-8","Windows-1251",$sql));
      };
      $out[]=$sql;
    } catch (Exception $e){
      $out[]="Err: ".$sql."\r\n".$e->getMessage();
      $f=fopen("sql_err.txt","a");
      fputs($f,$sql);
      // fputs($f,"\r\nDATA:\r\n");
      // foreach($data as $k=>$v)
      //   fputs($f,"$k=$v\r\n");
      fclose($f);
    };
  }

  function sql($sql){
    return $this->db->Execute($sql);
  }

  function queryData($url=""){
    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$this->apiURL.$url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $response = curl_exec($ch);
      curl_close($ch);
    } catch(Exception $e) {
      throw new Exception("Curl error at ".$this->apiURL.$url.": ".$e->getMessage());
    };
    return $response;
  }

  function queryDataAdv($url=""){
    $ch = curl_init();
    echo $this->apiURL.$url."<br>";
    curl_setopt($ch, CURLOPT_URL,$this->apiURL.$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, "HandleHeaderLine");
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);
    curl_close($ch);
    return $response;
  }
}

function HandleHeaderLine( $curl, $header_line ) {
  return strlen($header_line);
}

?>
