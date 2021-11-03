<?php 
 $API = "UnitOfMeasure";
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
    $schemainfoArray = getMaxString("uom", $pdo);
    $sanitisedInput = [];

    $sql = "SELECT 
            id, 
            unit,
            chartlabel
            FROM uom
            WHERE 1=1";

    if (isset($inputarray['id'])) {		
        $sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
        $sql .= " AND `id` = '". $sanitisedInput['id'] ."'";
    }  

    if (isset($inputarray['unit'])) {		
        $sanitisedInput['unit'] = sanitise_input($inputarray['unit'], "unit", $schemainfoArray["unit"], $API, $logParent);
        $sql .= " AND `unit` = '". $sanitisedInput['unit'] ."'";
    }  

    if (isset($inputarray['chartlabel'])) {		
        $sanitisedInput['chartlabel'] = sanitise_input($inputarray['chartlabel'], "chartlabel", $schemainfoArray["chartlabel"], $API, $logParent);
        $sql .= " AND `chartlabel` = '". $sanitisedInput['chartlabel'] ."'";
    }  

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    $stm = $pdo->query($sql);
    $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
    if (isset($dbrows[0][0])){
        $json_parent = array ();
        $outputid = 0;
        foreach($dbrows as $dbrow){
            $json_child = array(
            "id"=>$dbrow[0],
            "unit"=>$dbrow[1],
            "chartlabel"=>$dbrow[2]
            );

            $json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
            $outputid++;
        }
        
        $json = array("responses" => $json_parent);
        echo json_encode($json);
        logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
    }
    else{
            logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
            die ("{\"error\":\"NO_DATA\"}");
       }
}


// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************


    else if($sanitisedInput['action'] == "insert"){

        $schemainfoArray = getMaxString("uom", $pdo);
        $insertArray = [];

        if (isset($inputarray['unit'])){
           $insertArray['unit'] = sanitise_input($inputarray['unit'], "unit", $schemainfoArray["unit"], $API, $logParent);
        }
        else{          
            errorMissing("unit", $API, $logParent);
        }

        if (isset($inputarray['chartlabel'])){
            $insertArray['chartlabel'] = sanitise_input($inputarray['chartlabel'], "chartlabel", $schemainfoArray["chartlabel"], $API, $logParent);
        }
        else{          
            errorMissing("chartlabel", $API, $logParent);
        }
    
      try{
            $sql = "INSERT INTO uom(
                    `unit`
                    , `chartlabel`)
                    VALUES  
                    (:unit, 
                    :chartlabel)";

            $stmt= $pdo->prepare($sql);
            if($stmt->execute($insertArray)){
                $insertArray['id'] = $pdo->lastInsertId();
                $insertArray['error' ] = "NO_ERROR";
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
    // *****************************UPDATE********************************************
    // *******************************************************************************
    // *******************************************************************************


    else if($sanitisedInput['action'] == "update"){

        $schemainfoArray = getMaxString ("uom", $pdo);       
        $updateArray = [];
        $updateString = "";

        if (isset($inputarray['id'])){
            $updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
            $sql = "SELECT id 
                    FROM uom
                    WHERE id = '" . $updateArray['id'] . "'";
    
            $stm = $pdo->query($sql);
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){  
                errorInvalid("id", $API, $logParent);      
            }
        } 
        else {
            errorMissing("id", $API, $logParent); 
        }
    
        if (isset($inputarray['unit'])){
            $updateArray['unit'] = sanitise_input($inputarray['unit'], "unit", $schemainfoArray["unit"], $API, $logParent);
            $updateString .= " `unit` = :unit,";
        } 
        
        if (isset($inputarray['chartlabel'])){
            $updateArray['chartlabel'] = sanitise_input($inputarray['chartlabel'], "chartlabel", $schemainfoArray["chartlabel"], $API, $logParent);
            $updateString .= " `chartlabel` = :chartlabel,";
        } 

        try{
            if (count($updateArray) < 2) {
                logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
                die("{\"error\":\"NO_UPDATED_PRAM\"}");
            }
            else {
                $sql = "UPDATE uom
                        SET ". rtrim($updateString,',') . "
                        WHERE `id` = :id";

                $stm= $pdo->prepare($sql);	
                if($stm->execute($updateArray)){
                    $updateArray ['error'] = "NO_ERROR";
                    echo json_encode($updateArray);
                    logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
                }
            }
        
        }catch(\PDOException $e){
            logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
            die("{\"error\":\"$e\"}");
        }	
    }
    else{
        logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
        errorInvalid("request", $API, $logParent);
    }


?>