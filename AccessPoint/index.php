<?php
	$API = "AccessPoint";
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
	
	$sql = "SELECT `xbee_id`
		, `lat`
		, `long`
		, `alt`
		, `AP_mac_address`
		, `type`
		, `description`
		, `paired_mac_address`
		, `ptt_server_host`
		, `ptt_server_port`
		, `ntp_time_server`
		, `xbee_pan_id`
		, `active_status`
		, `last_modified_by`
		, `last_modified_datetime` 
		FROM xbee 
		WHERE 1 = 1";

	if (isset($inputarray['ap_id'])) {		
		$sanitisedInput['ap_id'] = sanitise_input($inputarray['ap_id'], "ap_id", null, $API, $logParent);
		$sql .= " AND `xbee_id` = '". $sanitisedInput['ap_id'] ."'";
	}

	if (isset($inputarray['active_status'])) {
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `active_status` = '". $sanitisedInput['active_status'] ."'";
	}

	if (isset($inputarray['type'])) {		
		$sanitisedInput['type'] = sanitise_input($inputarray['type'], "type", null, $API, $logParent);
		$sql .= " AND `type` = '". $sanitisedInput['type'] ."'";
	}

	if (isset($inputarray['mac_address'])) {
		$sanitisedInput['mac_address'] = sanitise_input($inputarray['mac_address'], "mac_address", null, $API, $logParent);
		$sql .= " AND `AP_mac_address` = '". $sanitisedInput['mac_address'] ."'";
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
	$apsrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($apsrows[0][0])){
		$json_aps = array ();
		$outputid = 0;
		foreach($apsrows as $apsrow){
			$json_ap = array(			
			"ap_id" => $apsrow[0]
			, "lat" => $apsrow[1]
			, "long" => $apsrow[2]
			, "alt" => $apsrow[3]
			, "mac_address" => $apsrow[4]
			, "type" => $apsrow[5]
			, "description" =>  $apsrow[6]
			, "active_status" => $apsrow[12]
			, "last_modified_by" => $apsrow[13]
			, "last_modified_datetime" => $apsrow[14]);
			$json_aps = array_merge($json_aps,array("response_$outputid" => $json_ap));
			$outputid++;
		}
		$json = array("responses" => $json_aps);
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
	
	$schemainfoArray = getMaxString ("xbee", $pdo);
	$insertArray = [];

	if (isset($inputarray['lat'])) {
		$insertArray['lat'] = sanitise_input($inputarray['lat'], "lat", null, $API, $logParent);
	}
	else {
		errorMissing("lat", $API, $logParent);
	}

	if (isset($inputarray['long'])) {
		$insertArray['long'] = sanitise_input($inputarray['long'], "long", null, $API, $logParent);
	}
	else {
		errorMissing("long", $API, $logParent);
	}

	if (isset($inputarray['alt'])) {
		$insertArray['alt'] = sanitise_input($inputarray['alt'], "alt", null, $API, $logParent);
	}
	else {
		errorMissing("alt", $API, $logParent);
	}

	if (isset($inputarray['mac_address'])) {
		$insertArray['mac_address'] = sanitise_input($inputarray['mac_address'], "mac_address", null, $API, $logParent);
	}
	else {
		errorMissing("mac_address", $API, $logParent);
	}

	if (isset($inputarray['type'])) {		
		$insertArray['type'] = sanitise_input($inputarray['type'], "type", null, $API, $logParent);
	}
	else {
		errorMissing("type", $API, $logParent);
	}

	if (isset($inputarray['description'])) {
		$insertArray['description'] = sanitise_input($inputarray['description'], "description", $schemainfoArray['description'], $API, $logParent);
	}
	else {
		$insertArray['description'] = "";
	}
	
	if (isset($inputarray['active_status'])) {
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		errorMissing("active_status", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO xbee(
			`lat`
			, `long`
			, `alt`
			, `AP_mac_address`
			, `type`
			, `description`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`) 
		VALUES (
			:lat
			, :long
			, :alt
			, :mac_address
			, :type
			, :description
			, :active_status
			, $user_id
			, '$timestamp')";
		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['ap_id'] = $pdo->lastInsertId();
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

	$schemainfoArray = getMaxString ("xbee", $pdo);
	$updateArray = [];
	$updateString = "";

	if (isset($inputarray['ap_id'])){
		$updateArray['ap_id'] = sanitise_input($inputarray['ap_id'], "ap_id", null, $API, $logParent);
		$sql = "SELECT
			xbee_id
			FROM
			xbee
			WHERE 
			xbee_id = " . $updateArray['ap_id'];
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("ap_id", $API, $logParent);
		}
	}
	else {
		errorMissing("ap_id", $API, $logParent);
	}

	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$updateString .= " `active_status` = :active_status,";
	}

	if (isset($inputarray['lat'])) {
		$updateArray['lat'] = sanitise_input($inputarray['lat'], "lat", null, $API, $logParent);
		$updateString .= " `lat` = :lat,";
	}

	if (isset($inputarray['long'])) {
		$updateArray['long'] = sanitise_input($inputarray['long'], "long", null, $API, $logParent);
		$updateString .= " `long` = :long,";
	}

	if (isset($inputarray['alt'])) {
		$updateArray['alt'] = sanitise_input($inputarray['alt'], "alt", null, $API, $logParent);
		$updateString .= " `alt` = :alt,";
	}

	if (isset($inputarray['mac_address'])) {
		$updateArray['mac_address'] = sanitise_input($inputarray['mac_address'], "mac_address", null, $API, $logParent);
		$updateString .= " `AP_mac_address` = :mac_address,";
	}

	if (isset($inputarray['type'])) {		
		$updateArray['type'] = sanitise_input($inputarray['type'], "type", null, $API, $logParent);
		$updateString .= " `type` = :type,";
	}

	if (isset($inputarray['description'])) {
		$updateArray['description'] = sanitise_input($inputarray['description'], "description", $schemainfoArray['description'], $API, $logParent);
		$updateString .= " `description` = :description,";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try {
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$sql = "UPDATE 
					xbee 
					SET". $updateString . " `last_modified_by` = $user_id
					, `last_modified_datetime` = '$timestamp'
					WHERE `xbee_id` = :ap_id";

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