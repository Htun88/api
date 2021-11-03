<?php
	$API = "Trigger";
	$geofence_sd_id = 100;
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

	$schemainfoArray = getMaxString ("trigger_groups", $pdo);
	
	if (!isset($inputarray['device_id'])
		&& !isset($inputarray['asset_id'])
		&& !isset($inputarray['deviceasset_id'])
		&& !isset($inputarray['device_sn'])
		&& !isset($inputarray['span'])
		) {
		$sql = "SELECT 
				*
			FROM
				trigger_groups
			WHERE 1=1";
	}
	else {
		if (!isset($inputarray['device_id'])
			&& !isset($inputarray['asset_id'])
			&& !isset($inputarray['deviceasset_id'])
			&& !isset($inputarray['device_sn'])
			) {
			errorMissing("identification", $API, $logParent);
		}
		if (isset($inputarray['span'])) {
			$inputarray['span'] = sanitise_input($inputarray['span'], "span", 9, $API, $logParent);
		}
		else {
			$inputarray['span'] = "Current";
		}
		//	sanitise these here in case as we may need them in previous span
		if (isset($inputarray['device_id'])){
			$inputarray['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		}
		if (isset($inputarray['asset_id'])){
			$inputarray['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		}
		if (isset($inputarray['deviceasset_id'])){
			$inputarray['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		}
		if (isset($inputarray['device_sn'])){
			$schemainfoArray2 = getMaxString ("devices", $pdo);
			$inputarray['device_sn'] = sanitise_input_array($inputarray['device_sn'], "device_sn", $schemainfoArray2['device_sn'], $API, $logParent);
		}
		$sql = "SELECT DISTINCT
				trigger_groups.trigger_id
				, trigger_groups.trigger_name
				, trigger_groups.trigger_type
				, trigger_groups.trigger_level
				, trigger_groups.trigger_source
				, trigger_groups.sensor_def_sd_id
				, trigger_groups.geofencing_geofencing_id
				, trigger_groups.value_operator
				, trigger_groups.trigger_value
				, trigger_groups.duration
				, trigger_groups.device_alarm
				, trigger_groups.site_alarm
				, trigger_groups.trigger_emailids
				, trigger_groups.trigger_sms
				, trigger_groups.trigger_phonecall
				, trigger_groups.trigger_fax
				, trigger_groups.suggested_actions
				, trigger_groups.reaction
				, trigger_groups.active_status
				, trigger_groups.last_modified_by
				, trigger_groups.last_modified_datetime";

		if ($inputarray['span'] == "Current" 
			|| $inputarray['span'] == "Previous"
			){
			$sql .= "
				FROM (
					users
					, user_assets
					, assets
					, deviceassets
					, deviceassets_trigger_det
					, trigger_groups
					, devices)
				LEFT JOIN userasset_details 
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
				WHERE trigger_groups.trigger_id = deviceassets_trigger_det.trigger_groups_trigger_id
				AND deviceassets_trigger_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
				AND deviceassets.assets_asset_id = assets.asset_id
				AND users.user_id = user_assets.users_user_id 
				AND user_assets.users_user_id = $user_id
				AND ((user_assets.asset_summary = 'some' 
				AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'all'))";

			if ($inputarray['span'] == "Current") {
				$sql .= "
					AND deviceassets.date_to IS NULL
					AND deviceassets.active_status = 0";
			}

			/* Kept for posterity in case things break. THis is old version
			if ($inputarray['span'] == "Previous"){			
				$sql .= " 
				AND deviceassets.deviceasset_id = (
					SELECT 
						deviceassets.deviceasset_id 
					FROM 
						deviceassets
					WHERE deviceassets.assets_asset_id = 1
					ORDER BY date_from DESC LIMIT 1)";
			}
			*/
			if ($inputarray['span'] == "Previous"){			
				$sql .= " 
				AND deviceassets.deviceasset_id IN (";

				$sql2 = " SELECT deviceasset_id FROM
						(SELECT 
							deviceassets.devices_device_id, deviceassets.deviceasset_id
						FROM
							deviceassets";
				if (isset($inputarray['device_sn'])){
					$sql2 .= " , devices
						WHERE devices.device_id = deviceassets.devices_device_id
						AND devices.device_sn IN (" . implode( ', ', $inputarray['device_sn'] ) . ")";
				}
				else {
					$sql2 .= " WHERE 1 = 1 ";
				}
				if (isset($inputarray['asset_id'])) {
					$sql2 .= " AND deviceassets.assets_asset_id IN (" . implode( ', ', $inputarray['asset_id'] ) . ")";
				}
				if (isset($inputarray['deviceasset_id'])){
					$sql2 .= " AND deviceassets.deviceasset_id IN (" . implode( ', ', $inputarray['deviceasset_id'] ) . ")";
				}
				if (isset($inputarray['device_id'])){
					$sql2 .= " AND deviceassets.devices_device_id IN (" . implode( ', ', $inputarray['device_id'] ) . ")";
				}

				$sql2 .= "ORDER BY date_to DESC
					LIMIT 2147483647
					) AS foo
					GROUP BY foo.devices_device_id";	
					//echo $sql2;	
					//echo "\n\n\n\n\n";
				$stm = $pdo->query($sql2);
				$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
				if (!isset($dbrows[0][0])){
					$sql .= " NULL)";
				}
				else {
					$sql .= implode( ', ', array_column($dbrows, 0)) . ")";
				}
			}
		}
		else if ($inputarray['span'] == "Available"){
			$sql .= "
				FROM 
					trigger_groups
					, sensor_def
					, sensors_det
					, deviceassets
					, devices
					, device_provisioning_components
					, assets 
				WHERE trigger_groups.sensor_def_sd_id = sensor_def.sd_id
				AND deviceassets.assets_asset_id = assets.asset_id
				AND sensor_def.sd_id = sensors_det.sensor_def_sd_id
				AND device_provisioning_components.device_component_id = sensors_det.sensors_sensor_id
				AND devices.device_id = deviceassets.devices_device_id
				AND device_provisioning_components.device_provisioning_device_provisioning_id = devices.device_provisioning_device_provisioning_id
				AND device_provisioning_components.device_component_type = 'Sensor'
				AND sensors_det.active_status = 0
				AND sensor_def.active_status = 0
				AND trigger_groups.active_status = 0";
		}
		else {
			//	Edge case error reporting. Shouldn't ever reach this unless sanitisation breaks
			errorInvalid("span", $API, $logParent);
		}

		// if span == available MUST have asset ID || device ID || deviceassetID && must be current eg date to is null && tied to user

		if ($inputarray['span'] != "Previous") {
			if (isset($inputarray['device_id'])){
				$sql  .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ', $inputarray['device_id'] ) . ")";
			}

			if (isset($inputarray['asset_id'])){
				$sql  .= " AND `assets`.`asset_id` IN (" . implode( ', ', $inputarray['asset_id'] ) . ")";
			}

			if (isset($inputarray['deviceasset_id'])){
				$sql  .= " AND `deviceassets`.`deviceasset_id` IN (" . implode( ', ', $inputarray['deviceasset_id'] ) . ")
					AND `deviceassets`.`date_to` IS NULL";
					//	TODO check if this is correct
			}

			if (isset($inputarray['device_sn'])){
				$sql  .= " AND `deviceassets`.`devices_device_id` = devices.device_id 
					AND `devices`.`device_sn` IN (" . implode( ', ', $inputarray['device_sn'] ) . ")";
			}
		}
	}

	//	DONE AFTER THIS POINT**************************

	if (isset($inputarray['trigger_id'])){
		$inputarray['trigger_id'] = sanitise_input_array($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		$sql  .= " AND `trigger_groups`.`trigger_id` IN (" . implode( ', ', $inputarray['trigger_id'] ) . ")";
	}

	if (isset($inputarray['trigger_type'])){
		$inputarray['trigger_type'] = sanitise_input($inputarray['trigger_type'], "trigger_type", $schemainfoArray['trigger_type'], $API, $logParent);
		$sql  .=  " AND `trigger_groups`.`trigger_type` = '" . $inputarray["trigger_type"] . "'";
	}

	if (isset($inputarray['trigger_level'])){
		$inputarray['trigger_level'] = sanitise_input_array($inputarray['trigger_level'], "trigger_level", null, $API, $logParent);
		$sql  .= " AND `trigger_groups`.`trigger_level` IN (" . implode( ', ', $inputarray['trigger_level'] ) . ")";
	}

	if (isset($inputarray['sd_id'])){
		$inputarray['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql  .= " AND `trigger_groups`.`sensor_def_sd_id` IN (" . implode( ', ', $inputarray['sd_id'] ) . ")";
	}

	if (isset($inputarray['geofence_id'])){
		$inputarray['geofence_id'] = sanitise_input_array($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		$sql  .= " AND `trigger_groups`.`geofencing_geofencing_id` IN (" . implode( ', ', $inputarray['geofence_id'] ) . ")";
	}

	if (isset($inputarray["trigger_source"])){
		$inputarray['trigger_source'] = sanitise_input($inputarray['trigger_source'], "trigger_source", $schemainfoArray['trigger_source'], $API, $logParent);
		$sql  .=  " AND `trigger_groups`.`trigger_source` = '" . $inputarray["trigger_source"] . "'";
	}

	if (isset($inputarray['duration'])) {
		$inputarray['duration'] = sanitise_input_array($inputarray['duration'], "duration", null, $API, $logParent);
		$sql .= " AND `trigger_groups`.`duration`  IN (" . implode( ', ', $inputarray['duration'] ) . ")";
	}

	if (isset($inputarray['device_alarm'])) {
		$inputarray['device_alarm'] = sanitise_input($inputarray['device_alarm'], "device_alarm", $schemainfoArray['device_alarm'], $API, $logParent);
		$sql .= " AND `trigger_groups`.`device_alarm` = '". $inputarray['device_alarm'] ."'";
	}

	if (isset($inputarray['site_alarm'])) {
		$inputarray['site_alarm'] = sanitise_input($inputarray['site_alarm'], "site_alarm", $schemainfoArray['site_alarm'], $API, $logParent);
		$sql .= " AND `trigger_groups`.`site_alarm` = '". $inputarray['site_alarm'] ."'";
	}

	if (isset($inputarray['reaction'])) {
		$inputarray['reaction'] = sanitise_input($inputarray['reaction'], "reaction", $schemainfoArray['reaction'], $API, $logParent);
		$sql .= " AND `trigger_groups`.`reaction` = '". $inputarray['reaction'] ."'";
	}

	if (isset($inputarray['active_status'])) {
		$inputarray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `trigger_groups`.`active_status` = '". $inputarray['active_status'] ."'";
	}
	
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
	if(isset($dbrows[0][0])){
		$json_triggers = array();
		$outputid = 0;
		foreach($dbrows as $row){
			$json_trigger = array(
			"trigger_id" => $row[0]
			, "trigger_name" => $row[1]
			, "trigger_type" => $row[2]
			, "trigger_level" => $row[3]
			, "trigger_source" => $row[4]
			, "sd_id" => $row[5]
			, "geofence_id" => $row[6]
			, "value_operator" => $row[7]
			, "trigger_value" => $row[8]
			, "duration" => $row[9]
			, "device_alarm" => $row[10]
			, "site_alarm" => $row[11]
			, "trigger_email" => $row[12]
			, "trigger_sms" => $row[13]
			, "trigger_phone" => $row[14]
			, "trigger_fax" => $row[15]
			, "suggested_actions" => $row[16]
			, "reaction" => $row[17]
			, "active_status" => $row[18]
			, "last_modified_by" => $row[19]
			, "last_modified_datetime" => $row[20]);
			$json_triggers = array_merge($json_triggers, array("response_$outputid" => $json_trigger));
			$outputid++;
		}
		$json = array("responses" => $json_triggers);
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

else if ($sanitisedInput['action'] == "insert"){

	$schemainfoArray = getMaxString ("trigger_groups", $pdo);

	if (isset($inputarray['trigger_name'])){
		$insertArray['trigger_name'] = sanitise_input($inputarray['trigger_name'], "trigger_name", $schemainfoArray['trigger_name'], $API, $logParent);
	}
	else{
		errorMissing("trigger_name", $API, $logParent);
	}

	if (isset($inputarray['trigger_type'])){
		//	Input: 0 | 1
		//	0 = "Critical"
		//	1 = "Warning"
		$insertArray['trigger_type'] = sanitise_input($inputarray['trigger_type'], "trigger_type", $schemainfoArray['trigger_type'], $API, $logParent);	
	}
	else{
		errorMissing("trigger_type", $API, $logParent);
	}

	if (isset($inputarray['trigger_level'])){
		$insertArray['trigger_level'] = sanitise_input($inputarray['trigger_level'], "trigger_level", null, $API, $logParent);
	}
	else{
		$insertArray['trigger_level'] = 0;
	}

	//	Need one or the other. Can't have both
	if (!isset($inputarray['sd_id'])
		&& !isset($inputarray['geofence_id'])
		){
		errorMissing("sd_id_or_geofence_id", $API, $logParent);
	}
	if (isset($inputarray['sd_id'])
		&& isset($inputarray['geofence_id'])
		){
		errorInvalid("incompatable_sd_id_and_geofence_id", $API, $logParent);
	}

	if (isset($inputarray['sd_id'])){
		//	We're now inserting a "sensor"
		$insertArray['trigger_source'] = "Sensor";
		$sanitisedInput['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		
		//	If you're pulling in an sd_id, then you've also got access to its min and max values. 
		//	We're combining the "check if valid sd_id" and "check if trigger_value is within sd_id min-max range"		
		$sql = "SELECT
				sd_id
				, sd_data_min
				, sd_data_max
			FROM
				sensor_def
			WHERE active_status = 0
			AND sd_id = " . $sanitisedInput['sd_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("sd_id", $API, $logParent);
		}
		//	Get trigger value
		if (!isset($inputarray['trigger_value'])){
			errorMissing("trigger_value", $API, $logParent);
		}
		$sanitisedInput['trigger_value'] = sanitise_input($inputarray['trigger_value'], "trigger_value", null, $API, $logParent);

		//	If the trigger value is outside the data min max value range then throw error
		if ($dbrows[0][1] > $sanitisedInput['trigger_value']
			|| $dbrows[0][2] < $sanitisedInput['trigger_value']
			) {
			errorInvalid("trigger_value_range", $API, $logParent);
		}
		$insertArray['sd_id'] = $sanitisedInput['sd_id'];
		$insertArray['trigger_value'] = $sanitisedInput['trigger_value'];
	}
	//	Catch case error for when trigger_source is Geofence and including trigger value, but NOT sd_id
	if (isset($inputarray['trigger_value'])) {
		if (isset($inputarray['geofence_id'])) {
			errorInvalid("incompatable_geofence_and_trigger_value", $API, $logParent);
		}
		else if (!isset($inputarray['sd_id'])) {
			errorMissing("sd_id", $API, $logParent);
		}
	}

	if (isset($inputarray['geofence_id'])){
		$insertArray['trigger_source'] = "Geofence";
		
		//hard code $geofence_sd_id (100) for Geofence triggers
		$insertArray['sd_id'] = $geofence_sd_id;

		$sanitisedInput['geofence_id'] = sanitise_input($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		//	Check if this is a valid geofence_id value
		//	Also need to check if there are any alarms for this geofence
		//	If an alarm has been triggered, can't add new triggers for this geofence 
		
		//NO!
		//ask tim
				
		/*$sql = "SELECT 
				geofencing.geofencing_id
				, alarm_events.alarm_events_id
			FROM 
				geofencing
			LEFT JOIN trigger_groups ON trigger_groups.geofencing_geofencing_id = geofencing.geofencing_id
			LEFT JOIN alarm_events ON alarm_events.trigger_groups_trigger_id = trigger_groups.trigger_id
			WHERE geofencing.geofencing_id = '" . $sanitisedInput['geofence_id'] . "'
			ORDER BY alarm_events.alarm_events_id DESC LIMIT 1";
		
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		//	Case if geofence ID is not a valid geofence
		if (!isset(($dbrows[0][0]))) {
			errorInvalid("geofence_id", $API, $logParent);
		}
		//	Case where alarms have been triggered for this geofence
		if (isset($dbrows[0][1])){
			logEvent($API . logText::invalidValue . str_replace('"', '\"', "{\"error\":\"GEOFENCE_LOCKED\"}"), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"GEOFENCE_LOCKED\"}");
		}
		*/
		
		$insertArray['geofence_id'] = $sanitisedInput['geofence_id'];
	}

	if (isset($inputarray['value_operator'])){
		$sanitisedInput['value_operator'] = sanitise_input($inputarray['value_operator'], "value_operator", $schemainfoArray['value_operator'], $API, $logParent);
		
		//	Need to check if we're associating a sensor value operator to a geofence type trigger, or vice versa, and throw error if so
		if ($sanitisedInput['value_operator'] == "Exit"
			|| $sanitisedInput['value_operator'] == "Entry"
			){
			if ($insertArray['trigger_source'] != "Geofence"){
				errorInvalid("incompatable_sd_id_and_value_operator", $API, $logParent);
			}
		}
		else {
			if ($insertArray['trigger_source'] != "Sensor"){
				errorInvalid("incompatable_geofence_and_value_operator", $API, $logParent);
			}
		}
		//	Passed the compatability checks, throw it into the array
		$insertArray['value_operator'] = $sanitisedInput['value_operator'];
	}
	else{
		errorMissing("value_operator", $API, $logParent);
	}
	
	if (isset($inputarray['duration'])){
		$sanitisedInput['duration'] = sanitise_input($inputarray['duration'], "duration", null, $API, $logParent);
		//if ($insertArray['trigger_source'] == "Geofence"
			//&& 	$sanitisedInput['duration'] != 0
			//) {
			//errorInvalid("duration", $API, $logParent);
		//}
		$insertArray['duration'] = $sanitisedInput['duration'];
	}
	else if ($insertArray['trigger_source'] == "Geofence") {
		$insertArray['duration'] = 0;
	}
	else {
		errorMissing("duration", $API, $logParent);
	}

	if (isset($inputarray['device_alarm'])){
		//	Input: 0 | 1
		//	0 = "Off"
		//	1 = "On"
		$insertArray['device_alarm'] = sanitise_input($inputarray['device_alarm'], "device_alarm", $schemainfoArray['site_alarm'], $API, $logParent);			
	}
	else {
		errorMissing("device_alarm", $API, $logParent);
	}

	if (isset($inputarray['site_alarm'])){
		//	Input: 0 | 1
		//	0 = "Off"
		//	1 = "On"
		$insertArray['site_alarm'] = sanitise_input($inputarray['site_alarm'], "site_alarm", $schemainfoArray['site_alarm'], $API, $logParent);			
	}
	else {
		errorMissing("site_alarm", $API, $logParent);
	}

	if (isset($inputarray['trigger_email'])){		
		$insertArray['trigger_email'] = sanitise_input($inputarray['trigger_email'], "trigger_email", $schemainfoArray['trigger_emailids'], $API, $logParent);	
	}

	if (isset($inputarray['trigger_sms'])){
		$insertArray['trigger_sms'] = sanitise_input($inputarray['trigger_sms'], "trigger_sms", $schemainfoArray['trigger_sms'], $API, $logParent);
	}

	if (isset($inputarray['trigger_phone'])){
		$insertArray['trigger_phone'] = sanitise_input($inputarray['trigger_phone'], "trigger_phone", $schemainfoArray['trigger_phonecall'], $API, $logParent);
	}

	if (isset($inputarray['trigger_fax'])){
		$insertArray['trigger_fax'] = sanitise_input($inputarray['trigger_fax'], "trigger_fax", $schemainfoArray['trigger_fax'], $API, $logParent);
	}
	
	if (isset($inputarray['suggested_actions'])){
		$insertArray['suggested_actions'] = sanitise_input($inputarray['suggested_actions'], "suggested_actions", $schemainfoArray['suggested_actions'], $API, $logParent);
	}

	if (isset($inputarray['reaction'])){
		//	Input: 0 | 1
		//	0 = "Acknowledge"
		//	1 = "Action"
		$insertArray['reaction'] = sanitise_input($inputarray['reaction'], "reaction", $schemainfoArray['reaction'], $API, $logParent);
	}

	if (isset($inputarray['active_status'])){
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);			
	}
	else {
		$insertArray['active_status'] = 0; 
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{

		$sql = "INSERT INTO trigger_groups (
				`trigger_name`
				, `trigger_type`
				, `trigger_level`
				, `trigger_source`
				, `sensor_def_sd_id`
				, `geofencing_geofencing_id`
				, `trigger_value`
				, `value_operator`
				, `duration`
				, `device_alarm`
				, `site_alarm`
				, `trigger_emailids`
				, `trigger_sms`
				, `trigger_phonecall`
				, `trigger_fax`
				, `suggested_actions`
				, `reaction`
				, `active_status`
				, `last_modified_by`
				, `last_modified_datetime`)
			VALUES (
				:trigger_name
				, :trigger_type
				, :trigger_level
				, :trigger_source";
				if (isset($insertArray['sd_id'])){
					$sql .= ", :sd_id";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['geofence_id'])){
					$sql .= ", :geofence_id";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['trigger_value'])){
					$sql .= ", :trigger_value";
				}
				else {
					$sql .= ", NULL";
				}
				$sql .= "
				, :value_operator
				, :duration
				, :device_alarm
				, :site_alarm";
				if (isset($insertArray['trigger_email'])){
					$sql .= ", :trigger_email";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['trigger_sms'])){
					$sql .= ", :trigger_sms";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['trigger_phone'])){
					$sql .= ", :trigger_phone";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['trigger_fax'])){
					$sql .= ", :trigger_fax";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['suggested_actions'])){
					$sql .= ", :suggested_actions";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['reaction'])){
					$sql .= ", :reaction";
				}
				else {
					$sql .= ", NULL";
				}
				$sql .= "
				, :active_status
				, $user_id
				, '$timestamp')";

		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['trigger_id'] = $pdo->lastInsertId();
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

// *******************************************************************************
// *******************************************************************************
// ******************************UPDATE*******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "update"){
	
	$updateArray = [];
	$updateString = "";

	//	Before we dedicate any processing power to this, we can throw an error immediately if we're updating incompatible parameters and save time. 
	//	We also declare the trigger_source value here and check against it in the trigger_id section below to validate type.
	if (isset($inputarray['trigger_value'])
		|| isset($inputarray['sd_id'])	
		){
		if (isset($inputarray['geofence_id'])){
			errorInvalid("incompatable_sensor_and_geofence", $API, $logParent);
		}
		$sanitisedInput['trigger_source'] = "Sensor";
	}
	else if (isset($inputarray['geofence_id'])){
		if (isset($inputarray['sd_id'])
			|| isset($inputarray['trigger_value'])		
			){
			errorInvalid("incompatable_sensor_and_geofence", $API, $logParent);
		}
		$sanitisedInput['trigger_source'] = "Geofence";
	}

	$schemainfoArray = getMaxString ("trigger_groups", $pdo);

	if(isset($inputarray['trigger_id'])){				
		$sanitisedInput['trigger_id'] = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		//	If there are any alarms against this trigger then you are not allowed to update anything othe than the trigger name
		//	Depending on what is being updated, we will need perform several checks on subsequent values
		//	Note we call alarm_events_id as !first value, as this can be null. We need to call a value that cannot be null as first, so the isset[0][0]. 

		$sql = "SELECT
				alarm_events.alarm_events_id
				, trigger_groups.trigger_source
				, trigger_groups.sensor_def_sd_id
				, trigger_groups.geofencing_geofencing_id
				, trigger_groups.value_operator
				, trigger_groups.duration
				, trigger_groups.trigger_value
				, sensor_def.sd_data_min
				, sensor_def.sd_data_max
			FROM
				trigger_groups
			LEFT JOIN alarm_events ON alarm_events.trigger_groups_trigger_id = trigger_groups.trigger_id
			LEFT JOIN sensor_def ON sensor_def.sd_id = trigger_groups.sensor_def_sd_id
			WHERE trigger_groups.trigger_id = '" . $sanitisedInput['trigger_id'] . "'
			ORDER BY alarm_events.alarm_events_id DESC LIMIT 1";
		
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		//	Case if trigger group ID is not a valid trigger group
		if (!isset(($dbrows[0][1]))) {
			errorInvalid("trigger_id", $API, $logParent);
		}
		$compare = $dbrows[0];
		$updateArray['trigger_id'] = $sanitisedInput['trigger_id'];

		//	Check if the trigger type on server is the same as what we're updating
		if (isset($sanitisedInput['trigger_source'])
			&& $compare[1] != $sanitisedInput['trigger_source']
			){
			//	If its not, we need to make sure we're inputting the mandatory data and set the current server data to NULL as appropriate
			if ($sanitisedInput['trigger_source'] == "Sensor") {
				$updateArray['trigger_source'] = "Sensor";
				$updateString .= " `trigger_source` = :trigger_source,";
				$updateString .= " `geofencing_geofencing_id` = NULL,";
				if (!isset($inputarray['sd_id'])){
					errorMissing("sd_id", $API, $logParent);
				}
				if (!isset($inputarray['value_operator'])){
					errorMissing("value_operator", $API, $logParent);
				}
				if (!isset($inputarray['trigger_value'])){
					errorMissing("trigger_value", $API, $logParent);
				}
			}
			else {
				$updateArray['trigger_source'] = "Geofence";
				$updateString .= " `trigger_source` = :trigger_source,";
				$updateString .= " `sensor_def_sd_id` = NULL,";
				$updateString .= " `trigger_value` = NULL,";
				if (!isset($inputarray['value_operator'])){
					errorMissing("value_operator", $API, $logParent);
				}
			}
		}
		else {
			//	Case we don't have a trigger source, OR we do AND its the same as on server
			//	This is to hold it for later use if needed in duration and value_operator 
			$sanitisedInput['trigger_source'] = $compare[1];
		}
	}
	else {
		errorMissing("trigger_id", $API, $logParent);
	}

	if (isset($inputarray['sd_id'])) {
		$sanitisedInput['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql = "SELECT 
			sd_id
			, sd_data_min
			, sd_data_max
			FROM
			sensor_def
			WHERE active_status = 0
			AND sd_id = " . $sanitisedInput['sd_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])) {
			errorInvalid("sd_id", $API, $logParent);
		}
		//	If we're updating a sensor value then there is a good chance it has different min/max values compared to what we currently have
		//	Its possible that the trigger_value now lies outside the new sensors min/max and will never trigger. 
		//	Need to check if we're updating trigger_value, and if not, then we compare against new sd min/max against the current trigger value

		if (!isset($inputarray['trigger_value'])){
			if (!isset($compare[6]) ){
				//	Case not supplying it and nothing in server
				errorMissing("trigger_value", $API, $logParent);
			}
			//	If the trigger value is outside the data min max value range then throw error
			if ($dbrows[0][1] > $compare[6]
				|| $dbrows[0][2] < $compare[6]
				) {
				errorInvalid("trigger_value_range", $API, $logParent);
			}
		}
		else {
			//	If we have set trigger value AND sd_id, we overwrite the $compare array sd_min and max values with the new sd_id min/max
			$dbrows[0][7] = $dbrows[0][1];
			$dbrows[0][8] = $dbrows[0][2];
		}

		$updateArray['sd_id'] = $sanitisedInput['sd_id'];
		$updateString .= " `sensor_def_sd_id` = :sd_id,";
	}

	if (isset($inputarray['geofence_id'])) {
		$sanitisedInput['geofence_id'] = sanitise_input($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		//	Check if this is a valid, active geofence ID
		
		/** Option one */

		$sql = "SELECT 
			geofencing_id
			FROM
			geofencing
			WHERE geofencing_id = " . $sanitisedInput['geofence_id'];
			// AND active_status = 0;

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])) {
			errorInvalid("geofence_id", $API, $logParent);
		}


		/** Option two */
		/*
		$sql = "SELECT 
				geofencing.geofencing_id
				, alarm_events.alarm_events_id
			FROM 
				geofencing
			LEFT JOIN trigger_groups ON trigger_groups.geofencing_geofencing_id = geofencing.geofencing_id
			LEFT JOIN alarm_events ON alarm_events.trigger_groups_trigger_id = trigger_groups.trigger_id
			WHERE geofencing.geofencing_id = '" . $sanitisedInput['geofence_id'] . "'
			ORDER BY alarm_events.alarm_events_id DESC LIMIT 1";
		
		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		//	Case if geofence ID is not a valid geofence
		if (!isset(($dbrows[0][0]))) {
			errorInvalid("geofence_id", $API, $logParent);
		}
		//	Case where alarms have been triggered for this geofence
        if (isset($dbrows[0][1])){
			logEvent($API . logText::invalidValue . str_replace('"', '\"', "{\"error\":\"GEOFENCE_LOCKED\"}"), logLevel::invalid, logType::error, $token, $logParent);
            die("{\"error\":\"GEOFENCE_LOCKED\"}");
        }
		/** End option two */
		
		$updateArray['geofence_id'] = $sanitisedInput['geofence_id'];
		$updateString .= " `geofencing_geofencing_id` = :geofence_id,";
	}

	if (isset($inputarray['trigger_value'])) {
		$sanitisedInput['trigger_value'] = sanitise_input($inputarray['trigger_value'], "trigger_value", $schemainfoArray['trigger_value'], $API, $logParent);
		//	Check to see if trigger value is within min max range
		//	If we've included the sd_id parameter as well, this test has already been performed against the new sd_id min/max values.
		//	If not, then we need to check against the database sd_id values in the $compare array
		if ($dbrows[0][7] > $sanitisedInput['trigger_value']
			|| $dbrows[0][8] < $sanitisedInput['trigger_value']
			) {
			errorInvalid("trigger_value_range", $API, $logParent);
		}
		else {
			$updateArray['trigger_value'] = $sanitisedInput['trigger_value'];
			$updateString .= " `trigger_value` = :trigger_value,";
		}
	}

	if (isset($inputarray['value_operator'])) {
		$sanitisedInput['value_operator'] = sanitise_input($inputarray['value_operator'], "value_operator", null, $API, $logParent);

		//	Case geofence type operator
		if ($sanitisedInput['value_operator'] == "Exit"
			|| $sanitisedInput['value_operator'] == "Entry"
			){
			if ($sanitisedInput['trigger_source'] == "Sensor"){
				errorInvalid("value_operator", $API, $logParent);
			}
		}
		//	Else it is a sensor type operator
		else {
			if ($sanitisedInput['trigger_source'] == "Geofence"){
				errorInvalid("value_operator", $API, $logParent);
			}
		}
		$updateArray['value_operator'] = $sanitisedInput['value_operator'];
		$updateString .= " `value_operator` = :value_operator,";
	}


	if (isset($inputarray['duration'])) {
		$sanitisedInput['duration'] = sanitise_input($inputarray['duration'], "duration", null, $API, $logParent);
		//	If we're not updating the trigger_source with another value, if we're attempting to update the duration for a geofence it MUST be 0
		if ($sanitisedInput['trigger_source'] == "Geofence"
			&& $sanitisedInput['duration'] != 0
			) {
			errorInvalid("duration", $API, $logParent);
		}
		$updateArray['duration'] = $sanitisedInput['duration'];
		$updateString .= " `duration` = :duration,";
	}
	else {
		if ($sanitisedInput['trigger_source'] == "Geofence") {
			$sanitisedInput['duration'] = 0;
		}
	}

	if (isset($inputarray['trigger_name'])) {
		$updateArray['trigger_name'] = sanitise_input($inputarray['trigger_name'], "trigger_name", $schemainfoArray['trigger_name'], $API, $logParent);
		$updateString .= " `trigger_name` = :trigger_name,";
	}
	
	if (isset($inputarray['trigger_level'])) {
		$updateArray['trigger_level'] = sanitise_input($inputarray['trigger_level'], "trigger_level", null, $API, $logParent);
		$updateString .= " `trigger_level` = :trigger_level,";
	}

	if (isset($inputarray['trigger_type'])) {
		$updateArray['trigger_type'] = sanitise_input($inputarray['trigger_type'], "trigger_type", $schemainfoArray['trigger_type'], $API, $logParent);
		$updateString .= " `trigger_type` = :trigger_type,";
	}

	if (isset($inputarray['device_alarm'])) {
		$updateArray['device_alarm'] = sanitise_input($inputarray['device_alarm'], "device_alarm",  $schemainfoArray['device_alarm'], $API, $logParent);
		$updateString .= " `device_alarm` = :device_alarm,";
	}

	if (isset($inputarray['site_alarm'])) {
		$updateArray['site_alarm'] = sanitise_input($inputarray['site_alarm'], "site_alarm", $schemainfoArray['site_alarm'], $API, $logParent);
		$updateString .= " `site_alarm` = :site_alarm,";
	}

	if (isset($inputarray['trigger_email'])) {
		$updateArray['trigger_email'] = sanitise_input($inputarray['trigger_email'], "trigger_email", $schemainfoArray['trigger_emailids'], $API, $logParent);
		$updateString .= " `trigger_emailids` = :trigger_email,";
	}

	if (isset($inputarray['trigger_sms'])) {
		$updateArray['trigger_sms'] = sanitise_input($inputarray['trigger_sms'], "trigger_sms", $schemainfoArray['trigger_sms'], $API, $logParent);
		$updateString .= " `trigger_sms` = :trigger_sms,";
	}

	if (isset($inputarray['trigger_phone'])) {
		$updateArray['trigger_phone'] = sanitise_input($inputarray['trigger_phone'], "trigger_phone", $schemainfoArray['trigger_phonecall'], $API, $logParent);
		$updateString .= " `trigger_phonecall` = :trigger_phone,";
	}

	if (isset($inputarray['trigger_fax'])) {
		$updateArray['trigger_fax'] = sanitise_input($inputarray['trigger_fax'], "trigger_fax", $schemainfoArray['trigger_fax'], $API, $logParent);
		$updateString .= " `trigger_fax` = :trigger_fax,";
	}

	if (isset($inputarray['suggested_actions'])) {
		$updateArray['suggested_actions'] = sanitise_input($inputarray['suggested_actions'], "suggested_actions", $schemainfoArray['suggested_actions'], $API, $logParent);
		$updateString .= " `suggested_actions` = :suggested_actions,";
	}

	if (isset($inputarray['reaction'])) {
		$updateArray['reaction'] = sanitise_input($inputarray['reaction'], "reaction", $schemainfoArray['reaction'], $API, $logParent);
		$updateString .= " `reaction` = :reaction,";
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
		//	If an alarm has been triggered for this trigger ID, the only thing we are allowing to edit is the trigger name.
		//	$compare[0] is the alarm value. Should be null if no alarm has been triggered, ie. !isset
		if (isset($compare[0])) {
			//	If an alarm has been triggered, we check the only things that are being updated are trigger_name and/or active_status
			
			foreach ($updateArray as $key => $val){
				//echo $key;
				if ($key != "trigger_id" && $key != "trigger_name" && $key != "active_status"){
					logEvent($API . logText::invalidValue . str_replace('"', '\"', "{\"error\":\"TRIGGER_LOCKED\"}"), logLevel::invalid, logType::error, $token, $logParent);
					die("{\"error\":\"TRIGGER_LOCKED\"}");
				}
			}

			//if (!isset($updateArray['trigger_name'])
				//|| count($updateArray) != 2
				//) {
				//logEvent($API . logText::invalidValue . str_replace('"', '\"', "{\"error\":\"GEOFENCE_LOCKED\"}"), logLevel::invalid, logType::error, $token, $logParent);
				//die("{\"error\":\"TRIGGER_LOCKED\"}");
			//}
		}
		$sql = "UPDATE 
			trigger_groups 
			SET ". $updateString . " `last_modified_by` = $user_id
			, `last_modified_datetime` = '$timestamp'
			WHERE `trigger_id` = :trigger_id";

		$stm= $pdo->prepare($sql);
		if($stm->execute($updateArray)){
			$updateArray ['error' ] = "NO_ERROR";
			echo json_encode($updateArray);
			$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
	//	Update config version
	try{
		$configArray['trigger_id'] = $updateArray['trigger_id'];

		$sql = "UPDATE
				trigger_groups
				, deviceassets_trigger_det
				, deviceassets
				, devices
			SET devices.triggers_version = (devices.triggers_version + 1)
			WHERE deviceassets_trigger_det.trigger_groups_trigger_id = trigger_groups.trigger_id
			AND deviceassets.deviceasset_id = deviceassets_trigger_det.deviceassets_deviceasset_id
			AND deviceassets.date_to IS null
			AND devices.device_id = deviceassets.devices_device_id
			AND trigger_groups.trigger_id = :trigger_id";

		$stm= $pdo->prepare($sql);
		if($stm->execute($configArray)){
			logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}

else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stm = null;

?>