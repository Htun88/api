<?php
	$API = "SensorDetails";
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
			sensors_det.sensor_det_id
			, sensors_det.sensors_sensor_id 
			, sensor_def.sd_id
			, sensors.sensor_name
			, sensor_def.sd_name
			, sensors_det.active_status
		FROM 
			sensor_def
			, sensors_det 
			, sensors
		WHERE sensor_def.sd_id = sensors_det.sensor_def_sd_id
		AND sensors.sensor_id = sensors_det.sensors_sensor_id";

	if (isset($inputarray['sensor_det_id'])){
		$sanitisedInput['sensor_det_id'] = sanitise_input_array($inputarray['sensor_det_id'], "sensor_det_id", null, $API, $logParent);
		$sql .= " AND `sensors_det`.`sensor_det_id` IN (" . implode( ', ',$sanitisedInput['sensor_det_id'] ) . ")";
	}

	if (isset($inputarray['sensor_id'])){
		$sanitisedInput['sensor_id'] = sanitise_input_array($inputarray['sensor_id'], "sensor_id", null, $API, $logParent);
		$sql .= " AND `sensors_det`.`sensors_sensor_id` IN (" . implode( ', ',$sanitisedInput['sensor_id'] ) . ")";
	}

	if (isset($inputarray['sd_id'])){
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql .= " AND `sensors_det`.`sensor_def_sd_id` IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")";
	}

	if (isset($inputarray['active_status'])){
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `sensors_det`.`active_status` = " . $sanitisedInput['active_status'];
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
		$jsonParent = array ();
		$outputid = 0;
		foreach($rows as $row){
			$jsonChild = array(	
			"sensor_det_id" => $row[0]
			, "sensor_id" => $row [1]	
			, "sd_id" => $row[2]
			, "sensor_name" => $row[3]
			, "name" => $row[4]
			, "active_status" => $row[5]);
			$jsonParent = array_merge($jsonParent,array("response_$outputid" => $jsonChild));
			$outputid++;
		}
		$json = array("responses" => $jsonParent);
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
	
	$insertArray = [];

	if (isset($inputarray['sensor_id'])) {
		$sanitisedInput['sensor_id'] = sanitise_input($inputarray['sensor_id'], "sensor_id", null, $API, $logParent);
		$sql = "SELECT
				sensor_id
			FROM
				sensors
			WHERE sensor_id = " . $sanitisedInput['sensor_id'];
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("sensor_id", $API, $logParent);
		}
		$insertArray['sensor_id'] = $sanitisedInput['sensor_id'];	
	}
	else {
		errorMissing("sensor_id", $API, $logParent);
	}

	if (isset($inputarray['sd_id'])) {
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);		
		$sql = "SELECT
				sensor_def.sd_id
			FROM
				sensor_def
			WHERE sensor_def.sd_id IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("sd_id", $API, $logParent);
		}
		arrayExistCheck($sanitisedInput['sd_id'], array_column($rows, 0), "sd_id", $API, $logParent);
		//	sort the sd_id numerically for some work in future
		sort ($sanitisedInput['sd_id']);
		$insertArray['sd_id'] = $sanitisedInput['sd_id'];
	}
	else {
		errorMissing("sd_id", $API, $logParent);
	}

	if (isset($inputarray['active_status'])) {
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		$insertArray['active_status'] = 0;
	}

	$insertArray['last_modified_by'] = $user_id;
	$insertArray['last_modified_datetime'] = strval($timestamp);

	foreach ($insertArray['sd_id'] as $value) {
		$data[] = "( " . $insertArray['sensor_id'] . ", " . $value . ", " . $insertArray['active_status'] . ", " . $insertArray['last_modified_by'] . ", '" . $insertArray['last_modified_datetime'] . "')";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO sensors_det(
			`sensors_sensor_id`
			, `sensor_def_sd_id`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`) 
		VALUES " . implode(', ', $data) . 
		" ON DUPLICATE KEY UPDATE 
			active_status = VALUES(active_status)
			, last_modified_by = VALUES(last_modified_by)
			, last_modified_datetime = VALUES(last_modified_datetime)";
		//echo $sql;
		$stm= $pdo->prepare($sql);
		$stm->execute();
		$sql = "SELECT 
				sensor_det_id
			FROM
				sensors_det
			WHERE sensors_sensor_id = '" . $insertArray['sensor_id'] . "'
			AND sensor_def_sd_id IN (" . implode( ', ',$insertArray['sd_id'] ) . ")
			ORDER BY sensor_def_sd_id ASC";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorGeneric("Issue", $API, $logParent);
		}
		$insertArray['sensor_det_id'] = array_column($rows, 0);
		$insertArray ['error' ] = "NO_ERROR";
		echo json_encode($insertArray);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"" . $e . "\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************

else if($sanitisedInput['action'] == "update"){

	$updateString = "";

	//	Can use either sensor det id OR BOTH sensor id AND sd_id to set active_status 
	//	Cannot use both, throw error if so

	if (isset($inputarray['sensor_det_id'])) {
		if (isset($inputarray['sensor_id'])
			|| isset($inputarray['sd_id'])
			) {
			errorGeneric("Incompatable_Identification_params", $API, $logParent);
		}
	}
	else{
		if (!isset($inputarray['sensor_id'])) {
			errorMissing("sensor_id", $API, $logParent);
		}
		if (!isset($inputarray['sd_id'])) {
			errorMissing("sd_id", $API, $logParent);
		}
	}

	if (isset($inputarray['sensor_det_id'])) {
		$sanitisedInput['sensor_det_id'] = sanitise_input_array($inputarray['sensor_det_id'], "sensor_det_id", null, $API, $logParent);
		//	Todo
		//	Might need a more indepth sql? On that limits updates to only approved users?
		$sql = "SELECT
					sensor_det_id
				FROM
					sensors_det
				WHERE  sensor_det_id IN (" . implode( ', ',$sanitisedInput['sensor_det_id'] ) . ")";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("sensor_det_id", $API, $logParent);
		}
		arrayExistCheck($sanitisedInput['sensor_det_id'], array_column($rows, 0), "sensor_det_id", $API, $logParent);
	}

	if (isset($inputarray['sensor_id'])) {
		$sanitisedInput['sensor_id'] = sanitise_input($inputarray['sensor_id'], "sensor_id", null, $API, $logParent);
		$updateArray['sensor_id'] = $sanitisedInput['sensor_id'];
	}

	if (isset($inputarray['sd_id'])) {
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql = "SELECT 
				sensor_def.sd_id 
			FROM 
				sensor_def 
			WHERE sensor_def.sd_id IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("sd_id", $API, $logParent);
		}
		arrayExistCheck(array_column($rows, 0), $sanitisedInput['sd_id'], "sd_id", $API, $logParent);	
	}

	if (!isset($inputarray['sensor_det_id'])){
		$sql = "SELECT
				sensors_sensor_id
				, sensors_det.sensor_det_id
				, sensors_det.sensor_def_sd_id
			FROM
				sensors
			LEFT JOIN sensors_det ON sensors_det.sensors_sensor_id = sensors.sensor_id
				AND sensors_det.sensor_def_sd_id IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")
			WHERE sensors.sensor_id = " . $sanitisedInput['sensor_id'];
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("sensor_id", $API, $logParent);
		}
		//	TODO check if missing one error case - Conor
		if (!isset($rows[0][1])) {
			errorGeneric("no_Sensor_ID_and_sd_id_association", $API, $logParent);
		}
		arrayExistCheck($sanitisedInput['sd_id'], array_column($rows, 2), "sd_id", $API, $logParent);	
	}

	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$updateString .= " `active_status` = :active_status,";
	}
	else {
		errorMissing("active_status", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

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
			
			$sql = "UPDATE 
					sensors_det 
					SET " . $updateString;
			if (isset($inputarray['sensor_det_id'])){
				$sql .= " WHERE `sensor_det_id` IN (" . implode( ', ',$sanitisedInput['sensor_det_id'] ) . ")";
			}
			else {
				$sql .= " WHERE `sensors_sensor_id` = :sensor_id AND sensor_def_sd_id IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")";
			}

			$stm= $pdo->prepare($sql);
            if ($stm->execute($updateArray)){
				if (isset($inputarray['sensor_det_id'])){
					$updateArray['sensor_det_id'] = $sanitisedInput['sensor_det_id']; 
				}
				else {
					$updateArray['sd_id'] = $sanitisedInput['sd_id'];
				}
                $updateArray ['error' ] = "NO_ERROR";
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