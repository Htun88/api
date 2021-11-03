<?php
$API = "ParamDefinition";
header('Content-Type: application/json');
include '../Includes/db.php';
include '../Includes/checktoken.php';
include '../Includes/sanitise.php';
include '../Includes/functions.php';

$entitybody = file_get_contents('php://input');
$inputarray = json_decode($entitybody, true);

if ($_SERVER['REQUEST_METHOD'] == "GET") {
  $inputarray = null;
  $inputarray['action'] = "select";
}

$logParent = logEvent($API . logText::accessed, logLevel::accessed, logType::accessed, $token, null)['event_id'];
checkKeys($inputarray, $API, $logParent);

if (isset($inputarray['action'])){
      $sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, $API, $logParent);
  $logParent = logEvent($API . logText::action . ucfirst($sanitisedInput['action']), logLevel::action, logType::action, $token, $logParent)['event_id'];
}
else{
  errorInvalid("request", $API, $logParent);
}

if ($sanitisedInput['action'] == "select"){

    $sql = "SELECT id, 
            param_name, 
            module_def_id 
            FROM param_def 
            WHERE 1=1";

    if (isset($inputarray['id'])) {		
      $sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
      $sql .= " AND `id` = '". $sanitisedInput['id'] ."'";
    }

    if (isset($inputarray['param_name'])) {
      $sanitisedInput['param_name'] = sanitise_input($inputarray['param_name'], "param_name", null, $API, $logParent);
      $sql .= " AND `param_name` = '". $sanitisedInput['param_name'] ."'";
    }

    if (isset($inputarray['module_id'])) {
      $sanitisedInput['module_id'] = sanitise_input($inputarray['module_id'], "module_id", null, $API, $logParent);
      $sql .= " AND `module_def_id` = '". $sanitisedInput['module_id'] ."'";
    }

    $sql .= " ORDER BY module_def_id";

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    //echo $sql;
    $stm = $pdo->query($sql);
    $rows = $stm->fetchAll(PDO::FETCH_NUM);
    if (isset($rows[0][0])){
      $json_items = array();
      $outputid = 0;
        foreach($rows as $row) {
          $json_item = array(
            "id" => $row[0],
            "param_name" => $row[1],
            "module_id" => $row[2]
          );
          $json_items = array_merge( $json_items, array("response_$outputid" => $json_item));
          $outputid++;
        }

      $json = array("responses" => $json_items);
      echo json_encode($json);
      logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);

    } else {
        logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
        die ("{\"error\":\"NO_DATA\"}");
      }
}


    
  // *******************************************************************************
  // *******************************************************************************
  // ******************************INSERT*******************************************
  // *******************************************************************************
  // ******************************************************************************* 


  elseif ($sanitisedInput['action'] == "insert"){
  
    $schemainfoArray = getMaxString ("param_def", $pdo);
    $insertArray = [];

    if (isset($inputarray['param_name'])) {
      $insertArray['param_name'] = sanitise_input_array($inputarray['param_name'], "param_name", $schemainfoArray['param_name'], $API, $logParent);
    }
    else {
      errorMissing("param_name", $API, $logParent);
    }

   
    if (isset($inputarray['module_id'])){
      $insertArray['module_id'] = sanitise_input($inputarray['module_id'], "module_id", null, $API, $logParent);
      $sql = "SELECT id 
              FROM module_def
              WHERE 
              id = " . $insertArray['module_id'];
      $stm = $pdo->query($sql);
      $rows = $stm->fetchAll(PDO::FETCH_NUM);
      if (!isset($rows[0][0])) {
        errorInvalid("module_id", $API, $logParent);
      }
    }else{
      errorMissing("module_id", $API, $logParent);
    }


    $values = array();
    foreach ($insertArray['param_name'] as $param_name){
      $paramidArray[] = $param_name;
      $values[] .= "( '" . $param_name . "', " .  $insertArray['module_id']  . " )";      
    }

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    try{
      
          $sql = "INSERT INTO param_def(
            `param_name`
          , `module_def_id`)
          VALUES " . implode(', ', $values) . "
          ON DUPLICATE KEY UPDATE
            param_name = VALUES(param_name)
           ,module_def_id = VALUES (module_def_id)";

          $stm= $pdo->prepare($sql);
          if($stm->execute()){

            $sql = "SELECT id
                    FROM
                    param_def
                    WHERE param_name IN ('". implode("', '", $insertArray['param_name']) ."')
                    AND module_def_id = '". $insertArray['module_id'] . "'
                    ORDER BY id ASC";

            $stm = $pdo->query($sql);
            $rows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($rows[0][0])) {
              errorGeneric("Issue", $API, $logParent);
            }

            //$insertArray['id'] = $pdo->lastInsertId();
            $insertArray['id'] = array_column($rows, 0);
            $insertArray ['error' ] = "NO_ERROR";
            echo json_encode($insertArray);
            logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
          }

    }catch(\PDOException $e){
      logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		  die("{\"error\":\"$e\"}");
   }
  }
  
  
  // *******************************************************************************
  // *******************************************************************************
  // ******************************UPDATE*******************************************
  // *******************************************************************************
  // ******************************************************************************* 

  
  elseif($sanitisedInput['action'] == "update"){

      $schemainfoArray = getMaxString("param_def", $pdo);
      $updateArray = [];
      //$updateString = "Update param_def SET";

      
      if (isset($inputarray['id'])) {
        if (isset($inputarray['module_id'])
          ){
             errorGeneric("Incompatable_Identification_params", $API, $logParent);
            }
      }
      else{
        if (!isset($inputarray['module_id'])) {
          errorMissing("module_id", $API, $logParent);
        }
      }

      if(isset($inputarray['id'])) 
      {       
        $updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
        $sql = "SELECT id 
                FROM param_def 
                WHERE id = " . $updateArray['id'] . "";
        $stm = $pdo->query($sql);
        $rows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($rows[0][0])) {
            errorInvalid("id", $API, $logParent);
        }
      }else{
        errorMissing("id", $API, $logParent);
      }
      
      if (isset($inputarray['module_id'])){
        $updateArray['module_id'] = sanitise_input($inputarray['module_id'], "module_id", null, $API, $logParent);
        $sql = "SELECT param_def.id
                FROM param_def, module_def
                WHERE param_def.module_def_id = module_def.id
                AND param_def.module_def_id = '". $updateArray['module_id'] ."'";

        $stm = $pdo->query($sql);
        $rows = $stm->fetchAll(PDO::FETCH_NUM);
        if(!isset($rows[0][0])) {
           errorInvalid("module_id", $API, $logParent);
        }
       // $updateString .= " module_def_id = :module_id,";
      }else{
        errorMissing("module_id", $API, $logParent);
      }


      if(isset($inputarray['param_name'])) 
      {
        $updateArray['param_name'] = sanitise_input($inputarray['param_name'], "param_name", $schemainfoArray["param_name"], $API, $logParent);
        //$updateString .= " param_name = :param_name,";
      }else{
         errorMissing("param_name", $API, $logParent);
       }
    

    try {       

      if (count($updateArray) < 1) {
        logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
        die("{\"error\":\"NO_UPDATED_PRAM\"}");
      }
      else {
            $updateArray ['last_modified_by'] = $user_id;
            $updateArray ['last_modified_datetime'] = $timestamp;
            $updateString .= "`last_modified_by` = :last_modified_by
              , `last_modified_datetime` = :last_modified_datetime";
            
            $sql = "UPDATE device_custom_param_group_components 
                    SET " . $updateString;
            if (isset($inputarray['id'])){
              $sql .= " WHERE `id` IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
            }
            else {
              $updateArray["group_id"]  = $sanitisedInput['group_id'];
              $sql .= " WHERE `device_custom_param_group_id` = :group_id AND device_custom_param_id IN (" . implode( ', ', $sanitisedInput['param_id'] ) . ")";
            }

            $stm= $pdo->prepare($sql);
            if ($stm->execute($updateArray)){
              if (isset($inputarray['id'])){
                $updateArray['id'] = $sanitisedInput['id']; 
              }
              else {
                $updateArray['group_id'] = $sanitisedInput['group_id'];
              }

              $updateArray['error' ] = "NO_ERROR";
              echo json_encode($updateArray);
              logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
            }
        }













          $sql = substr_replace($updateString," ", -1) . " WHERE `id` = :id";
          //echo $sql;
          $stm= $pdo->prepare($sql);	
          if($stm->execute($updateArray)){
              $updateArray ['error'] = "NO_ERROR";
              echo json_encode($updateArray);
              logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
           }


       }
        catch(PDOException $e){
          logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
          die("{\"error\":\"$e\"}");
        }		
  }else{
      logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
      errorInvalid("request", $API, $logParent);
  }

  $pdo = null;
  $stm = null;

  ?>