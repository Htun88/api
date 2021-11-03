<?php 
$API = "DeviceProvisioning";
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

    $schemainfoArray = getMaxString ("device_provisioning", $pdo);

    $sql = "SELECT 
            id, name, active_status, last_modified_by, 
            last_modified_datetime
            FROM device_provisioning WHERE 1=1";          

    if (isset($inputarray['id'])) {		
        $sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
        $sql .= " AND `id` = '". $sanitisedInput['id'] ."'";
    }  

    if (isset($inputarray['name'])) {		
        $sanitisedInput['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray["name"], $API, $logParent);
        $sql .= " AND `name` = '". $sanitisedInput['name'] ."'";
    }  
    
    if (isset($inputarray['active_status'])) {		
        $sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
        $sql .= " AND `active_status` = '". $sanitisedInput['active_status'] ."'";
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
            "name"=>$dbrow[1],
            "active_status"=>$dbrow[2],
            "last_modified_by"=>$dbrow[3],
            "last_modified_datetime"=>$dbrow[4]
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
        
        $schemainfoArray = getMaxString ("device_provisioning", $pdo);
        $insertArray = [];

        if (isset($inputarray['name'])) {
            $insertArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray["name"], $API, $logParent);
        }
        else {
            errorMissing("name", $API, $logParent);
        }

        if (isset($inputarray['active_status'])) {
            $insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
        }
        else {
            errorMissing("active_status", $API, $logParent);
        }

        $insertArray['last_modified_by'] = $user_id;
        $insertArray['last_modified_datetime'] = $timestamp;

        $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
        
        try{ 
             
            $stm = $pdo->query("SELECT * FROM device_provisioning where name = '" . $insertArray["name"] . "'");
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){    
                $sql = "INSERT INTO device_provisioning
                        (name, 
                        active_status, 
                        last_modified_by, 
                        last_modified_datetime) 
                        VALUES 
                        (:name, 
                        :active_status, 
                        :last_modified_by, 
                        :last_modified_datetime)";
                
                $stmt= $pdo->prepare($sql);
                if($stmt->execute($insertArray)){
                    $insertArray['id'] = $pdo->lastInsertId();
                    $insertArray['error' ] = "NO_ERROR";
                    echo json_encode($insertArray);
                    logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
                }     
            }
            else{
                $outputArray['name'] = $insertArray['name'];
                $outputArray['active_status'] = $insertArray['active_status'];
                $outputArray['error' ] = "Data_Already_Exit";
                echo json_encode($outputArray);
                logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);
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

        $schemainfoArray = getMaxString("device_provisioning", $pdo);
        $updateArray = [];
        $updateString = "";

        if(isset($inputarray['id'])){
            $updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
            $sql = "SELECT id FROM device_provisioning WHERE 
                    id = '" . $updateArray['id'] . "'";
            $stm = $pdo->query($sql);
            $rows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($rows[0][0])) {
             errorInvalid("id", $API, $logParent);
            }
        }else{
             errorMissing("id", $API, $logParent);
        }
        
        if (isset($inputarray['name'])) {
            $updateArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray["name"], $API, $logParent);
            $updateString .= " `name` = :name,";
        }
    
        if (isset($inputarray['active_status'])){
            $updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
            $updateString .= " `active_status` = :active_status,";
        }
          
        try{
 
            if (count($updateArray) < 2) {
                logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
                die("{\"error\":\"NO_UPDATED_PRAM\"}");
            }
            else {
                $sql = "UPDATE 
                        device_provisioning 
                        SET". $updateString . " `last_modified_by` = $user_id
                        , `last_modified_datetime` = '$timestamp'
                        WHERE `id` = :id";

                $stm= $pdo->prepare($sql);	
                if($stm->execute($updateArray)){

                    $outputArray['id'] = $updateArray['id'];
                    
                    if(isset($updateArray['name'])){
                      $outputArray['name'] = $updateArray['name'];
                    }

                    if(isset($updateArray['active_status'])){
                      $outputArray['active_status'] = $updateArray['active_status'];
                    }

                    $outputArray['error'] = "NO_ERROR";
                    echo json_encode($outputArray);
                    logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);
                }
            }
        }
        catch(PDOException $e){
            logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
            die("{\"error\":\"$e\"}");
        }	
    }
    else{               
        logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	    errorInvalid("request", $API, $logParent);
    }                   

$pdo = null;
$stm = null;

?>