<?php
	$API = "Sensor";
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
		sensor_id
		, sensor_name
		, active_status
		FROM sensors 
		WHERE 1 = 1";

	if (isset($inputarray['sensor_id'])){
		$sanitisedInput['sensor_id'] = sanitise_input_array($inputarray['sensor_id'], "sensor_id", null, $API, $logParent);
		$sql .= " AND `sensor_id` IN (" . implode( ', ',$sanitisedInput['sensor_id'] ) . ")";
	}

	if (isset($inputarray['active_status'])) {		
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `active_status` = '". $sanitisedInput['active_status'] ."'";
	}

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	
	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0][0])){
		$json = array ();
		$outputid = 0;
		foreach($rows as $row){
			$jsonChild = array(			
			"id" => $row[0]
			, "name" => $row[1]
			, "active_status" => $row[2]);
			$json = array_merge($json, array("response_$outputid" => $jsonChild));
			$outputid++;
		}
		$json = array("responses" => $json);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************

else if ($sanitisedInput['action'] == "insert") {
	
	$schemainfoArray = getMaxString ("sensors", $pdo);
	$insertArray = [];

	if (isset($inputarray['sensor_name'])) {
		$insertArray['sensor_name'] = sanitise_input($inputarray['sensor_name'], "sensor_name", $schemainfoArray['sensor_name'], $API, $logParent);
	}
	else {
		errorMissing("sensor_name", $API, $logParent);
	}

	if (isset($inputarray['active_status'])) {
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		$insertArray['active_status'] = 0;
		//errorMissing("active_status", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO sensors(
			`sensor_name`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`) 
		VALUES (
			:sensor_name
			, :active_status
			, $user_id
			, '$timestamp')";
		$stm = $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['sensor_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}	

// *******************************************************************************
// *******************************************************************************
// ******************************UPDATE*******************************************
// *******************************************************************************
// ******************************************************************************* 


else if ($sanitisedInput['action'] == "update"){

	$schemainfoArray = getMaxString ("sensors", $pdo);
	$updateArray = [];
	$updateString = "";

	if (isset($inputarray['sensor_id'])) {
		$updateArray['sensor_id'] = sanitise_input($inputarray['sensor_id'], "sensor_id", null, $API, $logParent);
	}
	else {
		errorMissing("sensor_id", $API, $logParent);
	}

	if (isset($inputarray['sensor_name'])) {
		$updateArray['sensor_name'] = sanitise_input($inputarray['sensor_name'], "sensor_name", $schemainfoArray['sensor_name'], $API, $logParent);
		$updateString .= " `sensor_name` = :sensor_name,";
	}

	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$updateString .= " `active_status` = :active_status,";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try {
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$sql = "UPDATE 
					sensors 
					SET". $updateString . " `last_modified_by` = $user_id
					, `last_modified_datetime` = '$timestamp'
					WHERE `sensor_id` = :sensor_id";

			$stm= $pdo->prepare($sql);	
			if($stm->execute($updateArray)){
				$updateArray ['error'] = "NO_ERROR";
				echo json_encode($updateArray);
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
			}
		}
	}
	catch(PDOException $e){
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