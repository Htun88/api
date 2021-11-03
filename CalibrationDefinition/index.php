<?php
$API = "CalibrationDefinition";
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

	$schemainfoArray = getMaxString ("calibration_def", $pdo);
	$sanitisedArray = [];

	$sql = "SELECT `id`,`name`
			FROM calibration_def 
			WHERE active_status = 0";

	$stm = $pdo->query($sql);
	$dbinformation_schema = $stm->fetchAll(PDO::FETCH_NUM);
	foreach($dbinformation_schema as $dbrows){
		if (isset($dbrows[0][0])) {
			$schemainfoArray[$dbrows[0]] = $dbrows[1];
		}
	}

	if (isset($inputarray['id'])){
		$sanitisedArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent); 
		$stm = $pdo->query("SELECT * FROM calibration_def where `id` = '" . $inputarray['id'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			$sql .= " AND `id` = '". $sanitisedArray['id'] ."'";
		}
		else {
			errorInvalid("id", $API, $logParent);
		}
	}

	if (isset($inputarray['name'])){
		$sanitisedArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
		$stm = $pdo->query("SELECT * FROM calibration_def where `name` = '" . $sanitisedArray['name'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			$sql .= " AND `name` = '". $sanitisedArray['name'] ."'";
		}
		else {
			errorInvalid("name", $API, $logParent);
		}
	}

	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0][0])) {
		$json_items = array();
		$outputid = 0;
		foreach($rows as $row){
			$json_item = array(
			"id" => $row[0]
			, "name" => $row[1]);
			$json_items = array_merge($json_items, array("response_$outputid" => $json_item));
			$outputid++;
		}

		$json = array("responses" => $json_items);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"NO_DATA\"}");
	}
}
else {			
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>