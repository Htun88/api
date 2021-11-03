<?php
	$API = "Xbee";
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

			$sql = "SELECT 
			trigger_name
			, trigger_type
			, trigger_source
			, sd_id
			, sd_name
			, geofencing_geofencing_id
			, value_operator
			, trigger_value
			, device_alarm
			, site_alarm
			, suggested_actions
			, reaction
			, duration
			, trigger_emailids
			, devices_device_id
			, asset_id
			, asset_task
			, asset_type
			, asset_name
			, sensor_value
			, alarm_events.datetime
			, deviceassets.deviceasset_id
			, device_name
			, device_sn
		FROM (
			alarm_events
			, trigger_groups
			, sensor_def
			, sensor_data
			, deviceassets
			, devices
			, assets )
		LEFT JOIN sensor_data_det ON sensor_data_det.sensor_data_data_id = alarm_events.sensor_data_data_id
			AND sensor_data_det.sensor_def_sd_id = sensor_def.sd_id
		WHERE trigger_groups.trigger_id = alarm_events.trigger_groups_trigger_id
		AND sensor_def.sd_id	= trigger_groups.sensor_def_sd_id
		AND sensor_data.data_id = alarm_events.sensor_data_data_id
		AND deviceassets.deviceasset_id = sensor_data.deviceassets_deviceasset_id
		AND assets.asset_id= deviceassets.assets_asset_id
		AND devices.device_id = deviceassets.devices_device_id	
		AND trigger_groups.trigger_emailids IS NOT NULL
		AND alarm_events_id = 145" ;

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		$search = array();
		$replace = array();

		if (!isset($dbrows[0][0])) {
		logEvent($API . logText::emailError, logLevel::requestError, logType::requestError, $token, null);
		}
		else {
		foreach($dbrows as $dbrow){
			$replace['email_triggerName'] = $dbrow[0];
			$replace['email_triggerType'] = $dbrow[1];
			$replace['email_triggerSource'] = strtolower($dbrow[2]);
			$replace['email_sensorID'] = strtolower($dbrow[3]);
			$replace['email_sensorName'] = $dbrow[4];
			$replace['email_geofenceID'] = $dbrow[5];
			$replace['email_valueOperator'] = $dbrow[6];
			$replace['email_triggerValue'] = $dbrow[7];
			$replace['email_deviceAlarm'] = strtolower($dbrow[8]);
			$replace['email_siteAlarm'] = strtolower($dbrow[9]);
			$replace['email_suggestedAction'] = $dbrow[10];
			$replace['email_reaction'] = $dbrow[11];
			$replace['email_duration'] = $dbrow[12];
			$to = $dbrow[13];
			$replace['email_deviceID'] = $dbrow[14];
			$replace['email_assetID'] = $dbrow[15];
			$replace['email_assetTask'] = $dbrow[16];
			$replace['email_assetType'] = $dbrow[17];
			$replace['email_assetName'] = $dbrow[18];
			$replace['email_sensorValue'] = $dbrow[19];
			$replace['email_timestamp'] = $dbrow[20];
			$replace['email_deviceAsset'] = $dbrow[21];
			$replace['email_deviceName'] = $dbrow[22];
			$replace['email_deviceSerialNo'] = $dbrow[23];

			if (!isset($dbrow[5])){
				$replace['email_geofenceID'] = "N/A";
			}
			if (!isset($dbrow[5])){
				$replace['email_suggestedAction'] = "N/A";
			}
			
		}
		$search['email_triggerName'] = "email_triggerName";
		$search['email_triggerType'] = "email_triggerType";
		$search['email_triggerSource'] = "email_triggerSource";
		$search['email_sensorID'] = "email_sensorID";
		$search['email_sensorName'] = "email_sensorName";
		$search['email_geofenceID'] = "email_geofenceID";
		$search['email_valueOperator'] = "email_valueOperator";
		$search['email_triggerValue'] = "email_triggerValue";
		$search['email_deviceAlarm'] = "email_deviceAlarm";
		$search['email_siteAlarm'] = "email_siteAlarm";
		$search['email_suggestedAction'] = "email_suggestedAction";
		$search['email_reaction'] = "email_reaction";
		$search['email_duration'] = "email_duration";
		$search['email_deviceID'] = "email_deviceID";
		$search['email_assetID'] = "email_assetID";
		$search['email_assetTask'] = "email_assetTask";
		$search['email_assetType'] = "email_assetType";
		$search['email_assetName'] = "email_assetName";
		$search['email_sensorValue'] = 'email_sensorValue';
		$search['email_deviceAsset'] = 'email_deviceAsset';
		$search['email_timestamp'] = 'email_timestamp';
		$search['email_deviceName'] = 'email_deviceName';
		$search['email_deviceSerialNo'] = 'email_deviceSerialNo';

		$subject = 'USM alarm';
		$headers = 'From: Testing@usm.net.au' . "\r\n" .
			'Reply-To: Testing@usm.net.au' . "\r\n" .
			'X-Mailer: PHP/' . phpversion() . "\r\n" .
			'Content-Type: text/html' . "\r\n";	

		if (isset($dbrow[5])) {
			$html = file_get_contents("https://core.dev.usm.net.au/v1/Includes/GeofenceEmail.html");
		}
		else {
			$html = file_get_contents("https://core.dev.usm.net.au/v1/Includes/SensorEmail.html");
		}
		$message = str_replace($search, $replace, $html);

		if (mail($to, $subject, $message, $headers)) {
			logEvent($API . logText::emailSuccess . $to, logLevel::request, logType::request, $token, null);
		}
		else {
			logEvent($API . logText::emailError, logLevel::requestError, logType::requestError, $token, null);
		}
	}

$insertArray['asset_id']  = 1;

$sql = "SELECT 
		asset_alarm.asset_alarm_id
		, alarm_events.alarm_actions_action_id
		, alarm_events.assets_asset_id
	FROM 
		asset_alarm
		, asset_alarm_det
		, alarm_events
	WHERE asset_alarm.assets_asset_id = '" . $insertArray['asset_id'] . "'
	AND asset_alarm_det.alarm_asset_id = asset_alarm.asset_alarm_id
	AND alarm_events.alarm_events_id = asset_alarm_det.alarm_event_id";
	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (!isset($rows[0][0])){
		errorInvalid("asset_id", $API, $logParent);
	}

	foreach ($rows as $row) {
		if (!isset($hello[$row[0]])) {
			$hello[$row[0]] = array();
		}
		$hello[$row[0]][] = $row[1];
	}
	//print_r($hello);
	$result = array();
	$triggered = array();
	$countAck = 0;
	$countClr = 0;
	$countTrg = 0;
	foreach ($hello as $keys => $value) {
		//$result[] = strval($keys);
		$num = strval($keys);
		if (in_array(4, $value)) {
			//$result[$num] = "Cleared and Acknowledged";
			$countClr ++;
			$countAck ++;
		}
		else if (in_array(3, $value)) {
			//$result[$num] = "Acknowledged";
			$countAck ++;
		}
		else if (in_array(2, $value)) {
			//$result[$num] = "Cleared";
			$countClr ++;
		}
		else if (in_array(1, $value)) {
			//$result[$num] = "Triggered";
			$result2['Triggered'] = 1;
			$countTrg ++;
		}

	}

	if (isset($result2['Triggered'])){
		$insertArray['alarm_ack_status'] = "NO";
		$insertArray['alarm_reset_status'] = "NO";
	}
	else {
		$totalCount = count($hello);
		if ($countClr != $totalCount) {
			$insertArray['alarm_reset_status'] = "NO";
		}
		else {
			$insertArray['alarm_reset_status'] = "YES";
		}
		if ($countAck != $totalCount) {
			$insertArray['alarm_ack_status'] = "NO";
		}
		else {
			$insertArray['alarm_ack_status'] = "YES";
		}
	}
	
	try {
		$sql = "UPDATE 
			assets_position 
			SET  alarm_reset_status = :alarm_reset_status
			, alarm_ack_status = :alarm_ack_status
			 WHERE assets_asset_id = :asset_id";

		$logArray['action'] = "update";
		$logArray['insertTable'] = "assets_position";
		$logParent2 = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($insertArray)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$stm= $pdo->prepare($sql);
		if ($stm->execute($insertArray)){
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent2);
		}
	}
	catch (PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent2);
	}

	print_r($insertArray);
	
	
	//	TODO search inarray for 1's and 2'ss


die("reached");


if ($sanitisedInput['action'] == "select"){
	
	$sql = 	"SELECT 
		`xbee_id`
		,`lat`
		,`long`
		,`alt`
		,`AP_mac_address`
		,`type`
		,`description`
		,`paired_mac_address`
		,`ptt_server_host`
		,`ptt_server_port`
		,`ntp_time_server`
		,`xbee_pan_id`
		,`active_status`
		,`last_modified_by`
		,`last_modified_datetime`
	FROM 
		xbee
	WHERE 
		1=1";

	if (isset($inputarray['xbee_id'])){
		$sanitisedInput['xbee_id'] = sanitise_input_array($inputarray['xbee_id'], "xbee_id", null, $API, $logParent);
		$sql .= " AND `xbee_id` IN (" . implode( ', ',$sanitisedInput['xbee_id'] ) . ")";
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

	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_parent = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"xbee_id"=>$dbrow[0],
			"lat"=>$dbrow[1],
			"long"=>$dbrow[2],
			"alt"=>$dbrow[3],
			"mac_address"=>$dbrow[4],
			"type"=>$dbrow[5],
			"description"=>$dbrow[6],
			"paired_mac_address"=>$dbrow[7],
			"ptt_server_host"=>$dbrow[8],
			"ptt_server_port"=>$dbrow[9],
			"ntp_time_server"=>$dbrow[10],
			"xbee_pan_id"=>$dbrow[11],
			"active_status"=>$dbrow[12],
			"last_modified_by"=>$dbrow[13],
			"last_modified_datetime"=>$dbrow[14] );
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