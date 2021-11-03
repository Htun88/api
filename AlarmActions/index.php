<?php
$API = "AlarmActions";
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

	$schemainfoArray = getMaxString ("alarm_actions", $pdo);

	$sql = 	"SELECT 
			 action_id, 
			 action, 
			 active_status, 
			 last_modified_by, 
			 last_modified_datetime 
			 FROM 
			 alarm_actions 
			 WHERE 1=1";

	if (isset($inputarray['action_id'])){
        $sanitisedArray['action_id'] = sanitise_input($inputarray['action_id'], "action_id", null, $API, $logParent);
        $sql .= " AND `action_id` = '". $sanitisedArray['action_id'] ."'";
	}

	if (isset($inputarray['alarm_action'])){
        $sanitisedArray['alarm_action'] = sanitise_input($inputarray['alarm_action'], "alarm_action",  $schemainfoArray["action"], $API, $logParent);
        $sql .= " AND `action` = '". $sanitisedArray['alarm_action'] ."'";
	}

	if (isset($inputarray['active_status'])){
        $sanitisedArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
        $sql .= " AND `active_status` = '". $sanitisedArray['active_status'] ."'";
	}

	$sql .= " ORDER BY action_id";

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stm = $pdo->query($sql);
	$alarmsrows = $stm->fetchAll(PDO::FETCH_NUM);

	if (isset($alarmsrows[0][0])){
		$json_alarms  = array ();
		$outputid = 0;
		foreach($alarmsrows as $alarmsrow){
			$json_alarm = array(
				"action_id" => $alarmsrow[0]
				, "alarm_action" => $alarmsrow[1]
				, "active_status" => $alarmsrow[2]
				, "last_modified_by" => $alarmsrow[3]
				, "last_modified_datetime" => $alarmsrow[4]);

			$json_alarms = array_merge($json_alarms,array("response_$outputid" => $json_alarm));
			$outputid++;
		}

		$json = array("responses" => $json_alarms);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else{
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// ***********************************INSERT**************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "insert"){
	
	$sanitisedArray = [];
	$insertArray = [];	
	$schemainfoArray = getMaxString ("alarm_actions", $pdo);

	//alarm action should be unique
	if(isset($inputarray['alarm_action'])){
		$sanitisedArray['alarm_action'] = sanitise_input($inputarray['alarm_action'], "alarm_action", $schemainfoArray["action"], $API, $logParent);	
		$stm = $pdo->query("SELECT action FROM alarm_actions where action = '" . $sanitisedArray['alarm_action'] . "'");
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($dbrows[0][0])){  
            errorInvalid("alarm_action", $API, $logParent);      
        }	
	}
	else {
		errorMissing("alarm_action", $API, $logParent);
	}

	if (isset($inputarray['active_status'])) {
		$sanitisedArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null , $API, $logParent);
	}	
	else {
		errorMissing("active_status", $API, $logParent);
	}

	$insertArray['alarm_action'] = $sanitisedArray['alarm_action'];
    $insertArray['active_status'] = $sanitisedArray['active_status'];
	$insertArray['last_modified_by'] = $user_id;
    $insertArray['last_modified_datetime'] = $timestamp;

	try{
		$sql = "INSERT INTO alarm_actions(
            `action`
            , `active_status`
            , `last_modified_by`
            , `last_modified_datetime`)
		VALUES (
            :alarm_action
            , :active_status
            , :last_modified_by
            , :last_modified_datetime)";	
		$stmt= $pdo->prepare($sql);
		if($stmt->execute($insertArray)){
			$insertArray['action_id'] = $pdo->lastInsertId();
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
// ***********************************update**************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "update"){

	//$sanitisedArray = [];
	$updateArray = [];	
	$updateString = "";
	$schemainfoArray = getMaxString ("alarm_actions", $pdo);

	if(isset($inputarray['action_id'])){
		$updateArray['action_id'] = sanitise_input($inputarray['action_id'], "action_id", $schemainfoArray["action_id"], $API, $logParent);				
		$stm = $pdo->query("SELECT action FROM alarm_actions where action_id = '" . $updateArray['action_id'] . "'");
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){  
            errorInvalid("action_id", $API, $logParent);      
        }	
	}
	else {
		errorMissing("action_id", $API, $logParent);
	}

	//alarm action should be unique
	if(isset($inputarray['alarm_action'])){
		$updateArray['alarm_action'] = sanitise_input($inputarray['alarm_action'], "alarm_action", $schemainfoArray["action"], $API, $logParent);	
		$stm = $pdo->query("SELECT action FROM alarm_actions where action = '" . $updateArray['alarm_action'] . "'");
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($dbrows[0][0])){  
            errorInvalid("alarm_action", $API, $logParent);      
        }	

		$updateString = " `action` = :alarm_action,";
	}

	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null , $API, $logParent);
		$updateString .= " `active_status` = :active_status,";
	}	

	try{
		if (count($updateArray) < 2) {
            logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
            die("{\"error\":\"NO_UPDATED_PRAMS\"}");
        }	
		else {
			$sql = "UPDATE 
					alarm_actions 
					SET". $updateString . " `last_modified_by` = $user_id
					, `last_modified_datetime` = '$timestamp'
					WHERE `action_id` = :action_id";

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
else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;
?>