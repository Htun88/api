<?php
	$API = "Syslog";
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
	$schemainfoArray = getMaxString("syslog", $pdo);

	if (isset($inputarray['action'])){
        $sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, $API, $logParent);
		$logParent = logEvent($API . logText::action . ucfirst($sanitisedInput['action']), logLevel::action, logType::action, $token, $logParent)['event_id'];
	}
	else{
		errorInvalid("request", $API, $logParent);
	}

if ($sanitisedInput['action'] == "select"){

	$sql = "SELECT 
			syslog_id
			, priority 
			, message_id 
			, timestamp
			, device_device_id
			FROM syslog	
			WHERE 1 = 1";

	if (isset($inputarray['syslog_id'])){
		$sanitisedInput['syslog_id'] = sanitise_input($inputarray['syslog_id'], "message_id", null, $API, $logParent);
		$sql .= " AND `syslog`.`syslog_id` = '" . $sanitisedInput['syslog_id'] . "'";
	}

	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `syslog`.`device_device_id` IN ( '" . implode("', '", $sanitisedInput['device_id']) . "' )";
	}

	if (isset($inputarray['priority'])){
		$sanitisedInput['priority'] = sanitise_input_array($inputarray['priority'], "priority", null, $API, $logParent);
		$sql .= " AND `syslog`.`priority` IN ( '" . implode("', '", $sanitisedInput['priority']) . "' )";
	}

	if (isset($inputarray['message_id'])){
		$sanitisedInput['message_id'] = sanitise_input_array($inputarray['message_id'], "syslog_message_id", $schemainfoArray['message_id'], $API, $logParent);
		$sql .= " AND `syslog`.`message_id` IN ( '" . implode("', '", $sanitisedInput['message_id']) . "' )";
	}

	if (isset($inputarray['timestamp_to'])){
		$sanitisedInput['timestamp_to'] = sanitise_input($inputarray['timestamp_to'], "timestamp_to", null, $API, $logParent);
		$sql .= " AND `syslog`.`timestamp` <= '". $sanitisedInput['timestamp_to'] ."'";	
	}

	if (isset($inputarray['timestamp_from'])){
		$sanitisedInput['timestamp_from'] = sanitise_input($inputarray['timestamp_from'], "timestamp_from", null, $API, $logParent);
		$sql .= " AND `syslog`.`timestamp` >= '". $sanitisedInput['timestamp_from'] ."'";	
	}

	if (isset($inputarray['timestamp_to'])
		&& isset($inputarray['timestamp_from'])
		){
		$fromDate = strtotime($sanitisedInput['timestamp_from'] . " +0000");
		$toDate = strtotime($sanitisedInput['timestamp_to'] . " +0000");

		if ($fromDate >= $toDate) {
			errorInvalid("timestamp_to", $API, $logParent);
		}
	}
	
	$sql.= " ORDER BY syslog_id DESC";	

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
	
    //echo $sql;
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_parent = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"syslog_id" => $dbrow[0]
			, "device_device_id" => $dbrow[4]
			, "priority" => $dbrow[1] 
			, "message_id" => $dbrow[2]
			, "timestamp" => $dbrow[3]);

			$json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
			$outputid++;
		}
		$json = array("responses" => $json_parent);
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
// ******************************INSERT*******************************************
// *******************************************************************************
// *******************************************************************************


elseif($sanitisedInput['action'] == "insert"){
	
	//$schemainfoArray = getMaxString("syslog", $pdo);
	$insertArray = [];

	if(isset($inputarray['priority'])){
       $insertArray['priority'] = sanitise_input($inputarray['priority'], "priority", null, $API, $logParent);
	}
	else{
		errorMissing("priority", $API, $logParent);
	}

	if(isset($inputarray['message_id'])){
        $insertArray['message_id'] = sanitise_input($inputarray['message_id'], "syslog_message_id", $schemainfoArray["message_id"], $API, $logParent);

		$sql = "SELECT message_id FROM syslog_messages WHERE message_id =  '" . $insertArray['message_id'] . "'";
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("message_id", $API, $logParent);
		}
	}
	else{
		errorMissing("message_id", $API, $logParent);
	}

    if (isset($inputarray['device_id'])){		
		$insertArray['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
		$stm = $pdo->query("SELECT device_id
							FROM devices
							WHERE device_id = '" . $insertArray['device_id'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("device_id", $API, $logParent);
		}
    }
	else {
		errorMissing("device_id", $API, $logParent);
	}

	if (isset($inputarray['timestamp'])){
		$insertArray['timestamp'] = sanitise_input($inputarray['timestamp'], "timestamp", null, $API, $logParent);
	}
	else{
		 //set the current timezone
		 $insertArray['timestamp'] = gmdate("Y-m-d H:i:s");
	}

	$stm = $pdo->query("SELECT *  
						FROM syslog
						WHERE message_id = '" . $insertArray['message_id'] . "'
						AND priority = '" . $insertArray['priority'] . "'
						AND timestamp >= '" . $insertArray['timestamp'] . "'");
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){	
		errorGeneric("SYSLOG_ALREADY_EXIST", $API, $logParent);		
	}

	try{
		$sql = "INSERT INTO syslog(
				 `priority`
				, `message_id`
				, `timestamp`
				, `device_device_id`)
				VALUES (
				  :priority
				, :message_id
				, :timestamp
				, :device_id)";

		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['syslog_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"" . $e . "\"}");
	}
}
else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
} 


$pdo = null;
$stm = null;

?>