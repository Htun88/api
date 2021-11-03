<?php
	$API = "MessageCode";
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
		message_code_id
		, message
		, device_type
		, emergency 
	FROM 
		message_codes";

	if (isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " WHERE `message_code_id` IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
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
		$json_levels  = array ();
		$outputid = 0;
		foreach($rows as $row){
			$json_level = array(
			"message_code_id" => $row[0]
			, "message" => $row[1]
			, "device_type" => $row[2]
			//, "emergency" => $row[3]
			);
			$json_levels = array_merge($json_levels,array("response_$outputid" => $json_level));
			$outputid++;
		}
		$json = array("responses" => $json_levels);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"NO_DATA\"}");
	}
}

else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stm = null;

?>