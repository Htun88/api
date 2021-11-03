<?php
$API = "AlarmEvents";
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
	$sql = 	"SELECT
			  alarm_events.alarm_events_id
			, sensor_data.data_id
			, assets.asset_id
			, assets.asset_name
			, sensor_def.sd_name
			, sensor_data_det.sensor_value
			, alarm_events.datetime
			, trigger_groups.sensor_def_sd_id
			, trigger_groups.trigger_name
			, trigger_groups.trigger_type
			, trigger_groups.value_operator
			, trigger_groups.trigger_value
			, trigger_groups.duration
			, trigger_groups.suggested_actions
			, trigger_groups.reaction
			, uom.chartlabel
			, uom.id
			, uom_conversions.equation
			, alarm_events.trigger_groups_trigger_id
			, alarm_actions.action
			, `deviceassets`.`devices_device_id`
			, `deviceassets`.`deviceasset_id`

			FROM (users
				, user_assets
				, assets
				, deviceassets
				, alarm_events
				, alarm_actions
				, trigger_groups
				, sensor_data
				, sensor_def    
			)
  
			LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			LEFT JOIN deviceassets_chart ON deviceassets_chart.deviceassets_deviceasset_id = deviceassets.deviceasset_id
				AND deviceassets_chart.sd_id = sensor_def.sd_id
            LEFT JOIN uom_conversions ON uom_conversions.uom_id_to = deviceassets_chart.uom_show_id 
				AND uom_conversions.uom_id_from = sensor_def.sd_uom_id
			LEFT JOIN uom ON IF(deviceassets_chart.uom_show_id IS NULL ,uom.id = sensor_def.sd_uom_id, uom.id = deviceassets_chart.uom_show_id)
			LEFT JOIN sensor_data_det ON sensor_data_det.sensor_def_sd_id = trigger_groups.sensor_def_sd_id
			AND sensor_data_det.sensor_data_data_id = alarm_events.sensor_data_data_id
			WHERE sensor_def.sd_id = trigger_groups.sensor_def_sd_id
			AND alarm_actions.action_id = alarm_events.alarm_actions_action_id
			AND sensor_data.data_id = alarm_events.sensor_data_data_id
			AND trigger_groups.trigger_id = alarm_events.trigger_groups_trigger_id
			AND alarm_events.assets_asset_id = assets.asset_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND deviceassets.date_from <= sensor_data.data_datetime
			AND (deviceassets.date_to >= sensor_data.data_datetime
				OR deviceassets.date_to IS NULL)
			AND users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))";
	
	if (isset($inputarray['trigger_id'])){
        $sanitisedArray['trigger_id'] = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
        $sql .= " AND `alarm_events.trigger_groups_trigger_id` = '". $sanitisedArray['trigger_id'] ."'";
	}

	if (isset($inputarray['timestamp_to'])){
			$sanitisedArray['timestamp_to'] = sanitise_input($inputarray['timestamp_to'], "timestamp_to", null, $API, $logParent);
			$sql .= " AND `alarm_events`.`datetime` <= '". $sanitisedArray['timestamp_to'] ."'";	
	}

	if (isset($inputarray['timestamp_from'])){
		$sanitisedArray['timestamp_from'] = sanitise_input($inputarray['timestamp_from'], "timestamp_from", null, $API, $logParent);
		$sql .= " AND `alarm_events`.`datetime` >= '". $sanitisedArray['timestamp_from'] ."'";   	
	}

	if (isset($sanitisedArray['timestamp_to'])
		&& isset($sanitisedArray['timestamp_from'])
		){
		$fromDate = strtotime($inputarray['timestamp_from'] . " +0000");
		$toDate = strtotime($inputarray['timestamp_to'] . " +0000");
		
		if ($fromDate > $toDate) { 
			errorInvalid("timestamp_to", $API, $logParent);
		}
	}

	if (isset($inputarray['asset_id'])){
		$sanitisedArray['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `alarm_events`.`assets_asset_id` in (" . implode( ', ',$sanitisedArray['asset_id'] ) . ")";
	}
	
	if (isset($inputarray['device_id'])){
		$sanitisedArray['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` in (" . implode( ', ',$sanitisedArray['device_id'] ) . ")";
	}
		
	if (isset($inputarray['deviceasset_id'])){
		$sanitisedArray['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`deviceasset_id` in (" . implode( ', ',$sanitisedArray['deviceasset_id'] ) . ")";
	}

	if (isset($inputarray['alarm_events_id'])){
		$sanitisedArray['alarm_events_id'] = sanitise_input_array($inputarray['alarm_events_id'], "alarm_events_id", null, $API, $logParent);
		$sql .= " AND `alarm_events`.`alarm_events_id` in (" . implode( ', ',$sanitisedArray['alarm_events_id'] ) . ")";
	}

	if (isset($inputarray['action_id'])){
        $sanitisedArray['action_id'] = sanitise_input($inputarray['action_id'], "action_id", null, $API, $logParent);
        $sql .= " AND `alarm_events`.`alarm_actions_action_id` = '". $sanitisedArray['action_id'] ."'";
	}
	
	//	TODO stretch add? Search by a geofence_id, return all alarm events involving deviceassets that triggered on this geofence_id

	$sql .= " ORDER BY alarm_events.datetime DESC";

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	//echo $sql;
	$stm = $pdo->query($sql);
	$alarmeventsrows = $stm->fetchAll(PDO::FETCH_NUM);

	if (isset($alarmeventsrows[0][0])){
		$json_alarms  = array ();
		$outputid = 0;
		foreach($alarmeventsrows as $alarmeventsrow){
			$equation = $alarmeventsrow[17];
			if ($equation != ""){
				$value = $alarmeventsrow[5];
				if ($value != "") { 
					eval("\$value = \"$equation\";");
					eval('$sensor_valueresult = (' . $value. ');');
				}
				else {
					$sensor_valueresult = 0;
				}

										
				$value = $alarmeventsrow[11];
				if ($value != "") { 
					eval("\$triggervalue = \"$equation\";"); //TODO
					eval( '$triggervalueresult = (' . $triggervalue. ');' );
				}
				else {
					$triggervalueresult = 0;
				}
			}
			else {
				$sensor_valueresult = $alarmeventsrow[5];
				$triggervalueresult = $alarmeventsrow[11];
			}

			$json_alarm = array(
				"alarm_events_id" => $alarmeventsrow[0]
				, "data_id" => $alarmeventsrow[1]
				, "asset_id" => $alarmeventsrow[2]
				, "device_id" => $alarmeventsrow[20]
				, "deviceasset_id" => $alarmeventsrow[21]
				, "asset_name" => $alarmeventsrow[3]
				, "sd_name" => $alarmeventsrow[4]
				, "sensor_value" => strval($sensor_valueresult)
				, "datetime" => $alarmeventsrow[6]
				, "sensor_def_sd_id" => $alarmeventsrow[7]
				, "trigger_name" => $alarmeventsrow[8] 
				, "trigger_type" => $alarmeventsrow[9]
				, "value_operator" => $alarmeventsrow[10]
				, "trigger_value" => strval($triggervalueresult)
				, "duration" => $alarmeventsrow[12] 
				, "suggested_actions" => $alarmeventsrow[13]
				, "reaction" => $alarmeventsrow[14] 
				, "chartlevel" => $alarmeventsrow[15]
				, "uom_id" => $alarmeventsrow[16] 
				, "equation" => $alarmeventsrow[17]
				, "trigger_id" => $alarmeventsrow[18] 
				, "action" => $alarmeventsrow[19] );

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
	
	if(!isset($inputarray['device_id'])
	&& !isset($inputarray['asset_id'])
	){
		errorMissing("asset_id_or_device_id", $API, $logParent);
	}
	//if(isset($inputarray['device_id'])
	//&& isset($inputarray['asset_id'])
	//){
		//errorMissing("asset_id_or_device_id", $API, $logParent);
	//}							
//echo $inputarray['device_id'];
	if(isset($inputarray['asset_id'])){
		$sanitisedArray['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$stm = $pdo->query("SELECT
			assets.asset_id
			FROM (
			users
			, user_assets
			, assets)
			LEFT JOIN userasset_details 
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'all'))
			AND assets.asset_id = '" . $sanitisedArray['asset_id'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){		
		}
		else {
			errorInvalid("asset_id", $API, $logParent);
		}
	}
	else {
		$sanitisedArray['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
	}

	if (isset($inputarray['datetime'])) {
			$sanitisedArray['datetime'] = sanitise_input($inputarray['datetime'], "alarm_datetime", null, $API, $logParent);
	}
	else{
			$sanitisedArray['datetime'] = gmdate("Y-m-d H:i:s");
		}

	//////  CHECK THE ASSET HAS CURRENTLY LINKED OR WAS PREVIOUSLY LINKED WITH THE DEVICE THE PREIOD THAT USERS PROVIDED ACCORDING TO API INPUTS  //////


/*
	$sql = "SELECT 
	deviceasset_id, 
	assets_asset_id 
	FROM 
	deviceassets 
	WHERE
	
	"; 
	
	if (isset($sanitisedArray['deviceasset_id'])) {
	}
	
	if (isset($sanitisedArray['asset_id'])) {
		$sql .= " assets_asset_id = '" . $sanitisedArray['asset_id'] . "' 
					AND ((deviceassets.date_from <= '" . $sanitisedArray['datetime'] . "'					 
					AND deviceassets.date_to >= '" . $sanitisedArray['datetime'] ."')
					OR deviceassets.date_to IS NULL)";
	}

	if(isset($sanitisedArray['device_id'])) {
		$sql .= " devices_device_id = '" . $sanitisedArray['device_id'] . "' 
		AND ((deviceassets.date_from <= '" . $sanitisedArray['datetime'] . "'
		AND deviceassets.date_to >= '" . $sanitisedArray['datetime'] . "')	 
		OR deviceassets.date_to IS NULL)";
	}

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (!isset($dbrows[0][0])){			
		errorInvalid("datetime", $API, $logParent);
	}else{
		$deviceasset_id  = $dbrows[0][0];
		$sanitisedArray['asset_id']  = $dbrows[0][1];			
	}


*/

	$sql = "SELECT
		deviceassets.deviceasset_id
		, deviceassets.devices_device_id
		, deviceassets.assets_asset_id
		FROM (
		users
		, user_assets
		, assets
		, deviceassets
		, devices
		, devicelicense
		)
		LEFT JOIN userasset_details
		ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		WHERE 
		deviceassets.assets_asset_id = assets.asset_id
		AND devices.device_id = deviceassets.devices_device_id
		AND devicelicense.devicelicense_id = devices.devicelicense_devicelicense_id
		AND deviceassets.assets_asset_id = assets.asset_id
		AND devicelicense.expdatetime >= NOW()
		AND deviceassets.date_from <= '" . $sanitisedArray['datetime'] . "'
		AND (deviceassets.date_to >=  '" . $sanitisedArray['datetime'] . "'
			OR deviceassets.date_to IS NULL)
		AND users.user_id = user_assets.users_user_id
		AND user_assets.users_user_id = $user_id
		AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
		OR (user_assets.asset_summary = 'all'))";

	if(isset($inputarray['asset_id'])){
		$sanitisedArray['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`assets_asset_id` = " . $sanitisedArray['asset_id'];
    }

	if(isset($inputarray['device_id'])){
		$sanitisedArray['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` = " . $sanitisedArray['device_id'];
    }
	
	if(isset($inputarray['deviceasset_id'])){
		$sanitisedArray['deviceasset_id'] = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`deviceasset_id` = " . $sanitisedArray['deviceasset_id'];
    }

	if (!isset($inputarray['device_id'])
		&& !isset($inputarray['asset_id'])
	&& !isset($inputarray['deviceasset_id'])
	){
		errorMissing("asset_id_or_device_id_or_deviceasset_id", $API, $logParent);
	}

	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	//echo $sql;
	//print_r($rows);
	if (!isset($rows[0][0])) {
		errorInvalid("asset_id_or_device_id_or_deviceasset_id", $API, $logParent);
	}
	else {
		$sanitisedArray['deviceasset_id'] = $rows[0][0];
		$sanitisedArray['device_id'] = $rows[0][1];
		$sanitisedArray['asset_id'] = $rows[0][2];
	}




	if (isset($inputarray['user_agent'])){
		$user_agent = sanitise_input($inputarray['user_agent'], "user_agent", 50, $API, $logParent);
	}
	else{
		errorMissing("user_agent", $API, $logParent);
	}	

	$stm = $pdo->query("SELECT data_id 
						FROM sensor_data
						WHERE data_datetime = '" . $sanitisedArray['datetime'] . "'
						AND deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "' 
						AND sensor_data.type  = 1"
						);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){			
		$sanitisedArray['data_id'] = $dbrows[0][0];
	}
	else {			

		$sensorArray = array();
		$sensorArray['alarm_datetime'] = $sanitisedArray['datetime'];
		$sensorArray['user_agent'] = $user_agent;	
		$sensorArray['deviceasset_id'] = $sanitisedArray['deviceasset_id'];		
		
		try{
			$sql = "INSERT INTO sensor_data(
					data_datetime
					, deviceassets_deviceasset_id
					, user_agent
					, type)  
					VALUES (
					:alarm_datetime
					, :deviceasset_id
					, :user_agent
					, 1)";
			$stm= $pdo->prepare($sql);
			if($stm->execute($sensorArray)){					
				$sanitisedArray['data_id'] = $pdo->lastInsertId();
			}
		}
		catch (PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"$e\"}");
		}
	}

	if (isset($inputarray['action_id'])){
		$sanitisedArray['action_id'] = sanitise_input($inputarray['action_id'], "action_id", null, $API, $logParent);
		$stm = $pdo->query("SELECT action_id 
		FROM alarm_actions
		WHERE action_id = '" . $sanitisedArray['action_id'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){						
		}
		else{
			errorInvalid("action_id", $API, $logParent);
		}
	}
	else{
			errorMissing("action_id", $API, $logParent);
		}

	/// Check the trigger_id is bounding to the deviceassset or not? ///
	if(isset($inputarray['trigger_id'])) {
		$sanitisedArray['trigger_id'] = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		$stm = $pdo->query("SELECT * FROM trigger_groups, deviceassets_trigger_det 
							WHERE trigger_groups.trigger_id = deviceassets_trigger_det.trigger_groups_trigger_id 
							AND trigger_groups.trigger_id = '" . $sanitisedArray['trigger_id'] . "'
							AND deviceassets_trigger_det.deviceassets_deviceasset_id ='" . $sanitisedArray['deviceasset_id'] . "'");

		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){			
		}
		else {
			errorInvalid("trigger_id", $API, $logParent);
		}
	}
	else {
		errorMissing("trigger_id", $API, $logParent);
	}

	$stm = $pdo->query("SELECT *  
						FROM alarm_events
						WHERE datetime = '" . $sanitisedArray['datetime'] . "'
						AND trigger_groups_trigger_id = '" . $sanitisedArray['trigger_id'] . "'
						AND assets_asset_id = '" . $sanitisedArray['asset_id'] . "' 
						AND alarm_actions_action_id = '" . $sanitisedArray['action_id'] . "'");
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){			
		die("{\"error\":\"ALARM_EVENTS_ALREADY_EXIST\"}");
	}

	try{
		
		$insertArray = array();
		$insertArray['asset_id'] = $sanitisedArray['asset_id'];
		$insertArray['action_id'] = $sanitisedArray['action_id'];
		$insertArray['trigger_id'] = $sanitisedArray['trigger_id'];
		$insertArray['datetime'] = $sanitisedArray['datetime'];

		if(isset($sanitisedArray['data_id'])){
			$insertArray['data_id'] = $sanitisedArray['data_id'];				
		}

			//print_r($insertArray);

		$sql = "INSERT INTO alarm_events(
			`datetime`
			, `sensor_data_data_id`
			, `trigger_groups_trigger_id`
			, `assets_asset_id`
			, `alarm_actions_action_id`
			, `last_modified_by`
			, `last_modified_datetime`) 
		VALUES (
				:datetime
			, :data_id
			, :trigger_id
			, :asset_id
			, :action_id"; 

		$sql .= "
			, $user_id
			, '$timestamp')";
		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['event_id'] = $pdo->lastInsertId();			
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);	
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($sanitisedArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}

	//	ADDING EMAIL THINGIE

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
		AND alarm_events_id = " . $insertArray['event_id'];

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
		
		/*
		if (mail($to, $subject, $message, $headers)) {
			logEvent($API . logText::emailSuccess . $to, logLevel::request, logType::request, $token, null);
		}
		else {
			logEvent($API . logText::emailError, logLevel::requestError, logType::requestError, $token, null);
		}
		*/
	}

	//	FINISHED EMAIL
	
	$stm = $pdo->query("SELECT asset_alarm.asset_alarm_id
						FROM asset_alarm, alarm_events, asset_alarm_det
						WHERE alarm_events.alarm_events_id = asset_alarm_det.alarm_event_id
						AND asset_alarm.asset_alarm_id = asset_alarm_det.alarm_asset_id
						AND alarm_events.trigger_groups_trigger_id = '" . $sanitisedArray['trigger_id'] . "'
						AND alarm_events.assets_asset_id  = '" . $sanitisedArray['asset_id'] . "'");
	$assetalarmrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($assetalarmrows[0][0])){			
		// update 
		
		$stm = $pdo->query("SELECT asset_alarm.asset_alarm_id
							FROM asset_alarm, alarm_events, asset_alarm_det
							WHERE alarm_events.alarm_events_id = asset_alarm_det.alarm_event_id
							AND asset_alarm.asset_alarm_id = asset_alarm_det.alarm_asset_id
							AND alarm_events.trigger_groups_trigger_id = '" . $sanitisedArray['trigger_id'] . "' 
							AND alarm_events.assets_asset_id  = '" . $sanitisedArray['asset_id'] . "'
							AND (alarm_events.alarm_actions_action_id = '" . $sanitisedArray['action_id'] . "'
							OR alarm_events.alarm_actions_action_id = '1')
							AND alarm_events.datetime > '" . $sanitisedArray['datetime'] . "'");

		$assetalarmdetrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($assetalarmdetrows[0][0])){	

			$asset_alarm_id = $assetalarmrows[0][0];
			if($sanitisedArray['action_id'] == 1){

			$sql = "DELETE FROM asset_alarm_det
					WHERE asset_alarm_det.alarm_event_id IN 
					(SELECT alarm_events.alarm_events_id 
					from alarm_events, asset_alarm_det
					WHERE alarm_events.datetime < '" . $sanitisedArray['datetime'] . "'
					AND alarm_events.alarm_events_id = asset_alarm_det.alarm_event_id
					AND asset_alarm_det.alarm_asset_id = $asset_alarm_id
					) AND asset_alarm_det.alarm_asset_id = $asset_alarm_id";
			}
			else{

				$sql = "DELETE FROM asset_alarm_det
				WHERE asset_alarm_det.alarm_event_id IN 
				(SELECT alarm_events.alarm_events_id 
				from alarm_events, asset_alarm_det
				WHERE alarm_events.datetime < '" . $sanitisedArray['datetime'] . "'
				AND alarm_events.alarm_events_id = asset_alarm_det.alarm_event_id
				AND alarm_events.alarm_actions_action_id = '" . $sanitisedArray['action_id'] . "'
				AND asset_alarm_det.alarm_asset_id = $asset_alarm_id
				) AND asset_alarm_det.alarm_asset_id = $asset_alarm_id";

			}

			$stm= $pdo->prepare($sql);
			if($stm->execute()){					
			}			

			$AssetAlarmDetArray = array();
			$AssetAlarmDetArray['alarm_asset_id'] = $asset_alarm_id;
			$AssetAlarmDetArray['event_id'] = $insertArray['event_id'] ;	
			$sql = "INSERT INTO asset_alarm_det(
				alarm_event_id, alarm_asset_id) VALUES (:event_id, :alarm_asset_id)";
			$stm= $pdo->prepare($sql);
			if($stm->execute($AssetAlarmDetArray)){					
				$AssetAlarmDetArray['asset_alarm_det_id'] = $pdo->lastInsertId();
			}			
		
		}
	}

	else {			
		//insert
		$AssetAlarmArray = array();
		//$AssetAlarmArray['event_id'] = $insertArray['event_id'] ;	
		$AssetAlarmArray['trigger_id'] = $sanitisedArray['trigger_id'] ;	
		$AssetAlarmArray['asset_id'] = $sanitisedArray['asset_id'];		
		$AssetAlarmArray['last_modified_by'] = $user_id;		
		$AssetAlarmArray['last_modified_datetime'] = $timestamp;		
		
		try{
					
			$sql = "INSERT INTO asset_alarm(
					trigger_groups_trigger_id
					, assets_asset_id
					, last_modified_by
					, last_modified_datetime) 
					VALUES (
					:trigger_id
					, :asset_id
					, :last_modified_by
					, :last_modified_datetime)";

			$stm= $pdo->prepare($sql);
			if($stm->execute($AssetAlarmArray)){					
				$AssetAlarmArray['alarm_asset_id'] = $pdo->lastInsertId();
			}

			$AssetAlarmDetArray = array();
			$AssetAlarmDetArray['event_id'] = $insertArray['event_id'] ;	
			$AssetAlarmDetArray['alarm_asset_id'] = $AssetAlarmArray['alarm_asset_id'] ;
			
			$sql = "INSERT INTO asset_alarm_det(
				alarm_event_id, alarm_asset_id) VALUES (:event_id, :alarm_asset_id)";
			$stm= $pdo->prepare($sql);
			if($stm->execute($AssetAlarmDetArray)){					
				$AssetAlarmDetArray['asset_alarm_det_id'] = $pdo->lastInsertId();
			}
		}
		catch (PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"$e\"}");
		}
	}


	


	try{
		//	TODO Conor here ish
		// 	Once inserted, do a query with Tims special query. 
		//	Find the cases where an alarm has been triggered per alarm ID and asset ID AND have NOT also got an accompanying clear (option 2, 3, 4, 6) 

		//	If one is found
			// If 2 OR 4 or 6 == cleared
			//	else if 3 OR 4 OR 6 == acknowledged
		//	update assets_position table 
		//		update the alarm_reset_status if cleared
		//	update the alarm_ack_status if acknowledged
		//	

		$insertArray2['deviceasset_id'] = $sanitisedArray['deviceasset_id'];
		$insertArray2['asset_id'] = $insertArray['asset_id'];
		$insertArray2['data_id'] = $insertArray['data_id'];
		$insertArray2['data_datetime'] = "'" . $sanitisedArray['datetime'] . "'";

		$sql = "SELECT 
				asset_alarm.asset_alarm_id
				, alarm_events.alarm_actions_action_id
				, alarm_events.assets_asset_id
			FROM 
				asset_alarm
				, asset_alarm_det
				, alarm_events
			WHERE asset_alarm.assets_asset_id = '" . $insertArray2['asset_id'] . "'
			AND asset_alarm_det.alarm_asset_id = asset_alarm.asset_alarm_id
			AND alarm_events.alarm_events_id = asset_alarm_det.alarm_event_id";
			//echo $sql;
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("asset_id", $API, $logParent);
		}
	
		foreach ($rows as $row) {
			if (!isset($alarms[$row[0]])) {
				$alarms[$row[0]] = array();
			}
			$alarms[$row[0]][] = $row[1];
		}
		
		$insertArray2['alarm_ack_status'] = "'YES'";
		$insertArray2['alarm_reset_status'] = "'YES'";
		foreach ($alarms as $alarm) {
			//print_r($alarm);
			$alarmcleared = 0;
			$alarmacked = 0;
			foreach ($alarm as $state) {
				if($state == 2){
					$alarmcleared = 1;
				}
				if($state == 3){
					$alarmacked = 1;
				}
				if($state == 4){
					$alarmcleared = 1;
					$alarmacked = 1;
				}
				if($state == 6){
					$alarmcleared = 1;
					$alarmacked = 1;
				}
			}
			
			if ($alarmcleared == 0){
				$insertArray2['alarm_reset_status'] = "'NO'";
			}
			if ($alarmacked == 0){
				$insertArray2['alarm_ack_status'] = "'NO'";
			}
		}
		
		
		
		/*
		
		$result = array();
		$triggered = array();
		$countAck = 0;
		$countClr = 0;
		$countTrg = 0;
		foreach ($hello as $keys) {
			$thing = max($keys);
			//echo "$thing\n";
			switch ($thing) {
				CASE  6: 
					$countClr ++;
					$countAck ++;
					BREAK;
				CASE  4: 
					$countClr ++;
					$countAck ++;
					BREAK;
				CASE  3: 
					$countAck ++;
					BREAK;
				CASE  2: 
					$countClr ++;
					BREAK;
				CASE  1: 
					$result2['Triggered'] = 1;
					BREAK;
			}
		}
		if (isset($result2['Triggered'])){
			$insertArray2['alarm_ack_status'] = "'NO'";
			$insertArray2['alarm_reset_status'] = "'NO'";
		}
		else {
			$totalCount = count($hello);
			if ($countClr >= $totalCount) {
				$insertArray2['alarm_reset_status'] = "'YES'";
			}
			else {
				$insertArray2['alarm_reset_status'] = "'NO'";
			}
			if ($countAck >= $totalCount) {
				$insertArray2['alarm_ack_status'] = "'YES'";
			}
			else {
				$insertArray2['alarm_ack_status'] = "'NO'";
				
			}
		}
		
		echo $totalCount;
		echo "<br>";
		echo $countClr; //3
		echo "<br>";
		echo $countAck;
		*/
		
		//echo $insertArray2['alarm_reset_status'];
		//echo $insertArray2['alarm_ack_status'];
		
		
		
		$sql = "INSERT INTO assets_position(
				`deviceassets_deviceasset_id`
				, `assets_asset_id`
				, `sensor_data_data_id`
				, `data_datetime`
				, `alarm_reset_status`
				, `alarm_ack_status`) 
			VALUES (" . implode(', ', $insertArray2) .  ")
			ON DUPLICATE KEY UPDATE
				sensor_data_data_id = VALUES(sensor_data_data_id)
				, data_datetime = VALUES(data_datetime)
				, alarm_reset_status = " . $insertArray2['alarm_reset_status'] . "
				, alarm_ack_status = " . $insertArray2['alarm_ack_status'] . "";	

		$logArray['action'] = "update/insert";
		$logArray['insertTable'] = "assets_position";
		$logParent2 = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($insertArray2)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];
//echo $sql;
		$stm= $pdo->prepare($sql);
		
		if ($stm->execute()){
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray2)), logLevel::response, logType::response, $token, $logParent2);
		}
	}
	catch (PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent2);
	}
}


else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stm = null;

?>