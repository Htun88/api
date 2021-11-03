<?php
	$API = "SensorDataID";
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
			sensor_data.data_id
			, assets.asset_id
			, assets.asset_name
			, sensor_data.data_datetime 
		FROM (
			sensor_data
			, assets
			, deviceassets
			, users
			, user_assets)
		LEFT JOIN userasset_details 
		ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		WHERE deviceassets.deviceasset_id = sensor_data.deviceassets_deviceasset_id 
		AND deviceassets.assets_asset_id = assets.asset_id
		AND users.user_id = user_assets.users_user_id 
		AND user_assets.users_user_id = 1
		AND ((user_assets.asset_summary = 'some' 
		AND assets.asset_id = userasset_details.assets_asset_id)
		OR (user_assets.asset_summary = 'all'))
		AND deviceassets.date_from <= sensor_data.data_datetime
		AND (deviceassets.date_to >= sensor_data.data_datetime
		OR deviceassets.date_to IS NULL)"
		;

	if (isset($inputarray['data_id'])){
		$sanitisedInput['data_id'] = sanitise_input_array($inputarray['data_id'], "data_id", null, $API, $logParent);
		$sql .= " AND `sensor_data`.`data_id` IN (" . implode( ', ',$sanitisedInput['data_id'] ) . ")";
	}

	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `assets`.`asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['timestamp_from'])){
		$sanitisedInput['timestamp_from'] = sanitise_input($inputarray['timestamp_from'], "timestamp_from", null, $API, $logParent);
		$sql .= " AND `sensor_data`.`data_datetime` >= '". $sanitisedInput['timestamp_from'] ."'";
	}

	if (isset($inputarray['timestamp_to'])){
		$sanitisedInput['timestamp_to'] = sanitise_input($inputarray['timestamp_to'], "timestamp_to", null, $API, $logParent);
		$sql .= " AND `sensor_data`.`data_datetime` <= '". $sanitisedInput['timestamp_to'] ."'";
	}

	if (isset($sanitisedInput['timestamp_to'])
		&& isset($sanitisedInput['timestamp_from'])
		){
		$fromDate = strtotime($sanitisedInput['timestamp_from'] . " +0000");
		$toDate = strtotime($sanitisedInput['timestamp_to'] . " +0000");
		if ($fromDate >= $toDate) {
			errorInvalid("timestamp_to", $API, $logParent);
		}
	}

	$sql .= " ORDER BY `sensor_data`.`data_datetime` DESC";	

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stm = $pdo->query($sql);
	$sensordatasrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($sensordatasrows[0][0])){							
		$json_parent = array();
		$outputid = 0;
		foreach($sensordatasrows as $sensordatasrow){
			$json_child = array(
			"data_id" => $sensordatasrow[0]
			, "asset_id" => $sensordatasrow[1]
			, "asset_name" => $sensordatasrow[2]
			, "data_datetime" => $sensordatasrow[3]);
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
else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>