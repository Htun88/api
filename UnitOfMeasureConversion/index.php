<?php 
 $API = "UnitOfMeasureConversion";
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

    $sql = "SELECT 
            id, 
            uom_id_from, 
            equation,
            uom_id_to
            FROM uom_conversions 
            WHERE 1=1";

    if (isset($inputarray['id'])) {		
        $sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
        $sql .= " AND `id` = '". $sanitisedInput['id'] ."'";
    }  

    if (isset($inputarray['uom_id_from'])) {		
        $sanitisedInput['uom_id_from'] = sanitise_input($inputarray['uom_id_from'], "uom_id_from", null, $API, $logParent);
        $sql .= " AND `uom_id_from` = '". $sanitisedInput['uom_id_from'] ."'";
    }  

    if (isset($inputarray['uom_id_to'])) {		
        $sanitisedInput['uom_id_to'] = sanitise_input($inputarray['uom_id_to'], "uom_id_to", null, $API, $logParent);
        $sql .= " AND `uom_id_to` = '". $sanitisedInput['uom_id_to'] ."'";
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
            "uom_id_from"=>$dbrow[1],
            "equation"=>$dbrow[2],
            "uom_id_to"=>$dbrow[3]
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

        $schemainfoArray = getMaxString("uom_conversions", $pdo);
        $insertArray = [];

        if (isset($inputarray['uom_id_from'])){
            $insertArray['uom_id_from'] = sanitise_input($inputarray['uom_id_from'], "uom_id_from", null, $API, $logParent);
            $sql = "SELECT id
                    FROM uom 
                    WHERE id = '" . $insertArray['uom_id_from'] . "'";

            $stm = $pdo->query($sql);
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){ 
                errorInvalid("uom_id_from", $API, $logParent);
            }
        }
        else{
            errorMissing("uom_id_from", $API, $logParent);
        }

        if (isset($inputarray['uom_id_to'])){
            $insertArray['uom_id_to'] = sanitise_input($inputarray['uom_id_to'], "uom_id_to", null, $API, $logParent);
            $sql = "SELECT id
                    FROM uom 
                    WHERE id = '" . $insertArray['uom_id_to'] . "'";
            $stm = $pdo->query($sql);
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){ 
                errorInvalid("uom_id_to", $API, $logParent);
            }
        }
        else{
            errorMissing("uom_id_to", $API, $logParent);
        }
    
        if (isset($inputarray['equation'])){
            $insertArray['equation'] = sanitise_input($inputarray['equation'], "equation", $schemainfoArray["equation"], $API, $logParent);
        }
        else{
            errorMissing("equation", $API, $logParent);
        }

        $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
        
        try{ 

            $sql = "INSERT INTO uom_conversions(
                      `uom_id_from`
                    , `equation`
                    , `uom_id_to`)
                    VALUES  
                    (:uom_id_from, 
                     :equation, 
                     :uom_id_to)
                    ON DUPLICATE KEY UPDATE
                    uom_id_from = VALUES(uom_id_from)
                    ,uom_id_to = VALUES (uom_id_to)";
            
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



    else if ($sanitisedInput['action'] == "update"){

        $schemainfoArray = getMaxString ("uom_conversions", $pdo);       
        $updateArray = [];
        $updateString = "";

        if(isset($inputarray['id'])){
            $updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
            $sql = "SELECT id 
                    FROM uom_conversions 
                    WHERE id = '" . $updateArray['id'] . "'";
    
            $stm = $pdo->query($sql);
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){  
                errorInvalid("id", $API, $logParent);      
            }
        }
        else{
            errorMissing("id", $API, $logParent);  
        }

    
        if (isset($inputarray['uom_id_from'])){
            $updateArray['uom_id_from'] = sanitise_input($inputarray['uom_id_from'], "uom_id_from", null, $API, $logParent);
            $sql = "SELECT id
                    FROM uom 
                    WHERE id = '" . $updateArray['uom_id_from'] . "'";

            $stm = $pdo->query($sql);
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){ 
                errorInvalid("uom_id_from", $API, $logParent);
            }

            $updateString .= " `uom_id_from` = :uom_id_from,";
        }


        if (isset($inputarray['uom_id_to'])){
            $updateArray['uom_id_to'] = sanitise_input($inputarray['uom_id_to'], "uom_id_to", null, $API, $logParent);
            $sql = "SELECT id
                    FROM uom 
                    WHERE id = '" . $updateArray['uom_id_to'] . "'";
            $stm = $pdo->query($sql);
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){ 
                errorInvalid("uom_id_to", $API, $logParent);
            }

            $updateString .= " `uom_id_to` = :uom_id_to,";
        }
       
        if (isset($inputarray['equation'])){
            $updateArray['equation'] = sanitise_input($inputarray['equation'], "equation", $schemainfoArray["equation"], $API, $logParent);
            $updateString .= " `equation` = :equation,";
        }

        //rtrim($updateString,',')."
        
        try{

            if (count($updateArray) < 2) {
                logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
                die("{\"error\":\"NO_UPDATED_PRAM\"}");
            }
            else {
                $sql = "UPDATE uom_conversions 
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