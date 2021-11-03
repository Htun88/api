<?php
$API = "AssetAlarm";
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
		FROM (
			asset_alarm
			, asset_alarm_det
			, users
			, user_assets
			, assets
			, deviceassets
			, alarm_events
			, alarm_actions
			, trigger_groups
			, sensor_data
			, sensor_def )
   
		LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		LEFT JOIN deviceassets_chart ON deviceassets_chart.deviceassets_deviceasset_id = deviceassets.deviceasset_id
		AND deviceassets_chart.sd_id = sensor_def.sd_id
		LEFT JOIN uom_conversions ON uom_conversions.uom_id_to = deviceassets_chart.uom_show_id
		AND uom_conversions.uom_id_from = sensor_def.sd_uom_id
		LEFT JOIN uom ON IF (
			deviceassets_chart.uom_show_id IS NULL
			, uom.id = sensor_def.sd_uom_id
			, uom.id = deviceassets_chart.uom_show_id)
		LEFT JOIN sensor_data_det ON sensor_data_det.sensor_def_sd_id = trigger_groups.sensor_def_sd_id
		AND sensor_data_det.sensor_data_data_id = alarm_events.sensor_data_data_id
		WHERE asset_alarm.assets_asset_id = assets.asset_id
		AND asset_alarm_det.alarm_asset_id = asset_alarm.asset_alarm_id
		AND asset_alarm_det.alarm_event_id = alarm_events.alarm_events_id
		AND sensor_def.sd_id = trigger_groups.sensor_def_sd_id
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
			
	if (isset($inputarray['asset_id'])){
		$sanitisedArray['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `assets`.`asset_id` IN (" . implode( ', ',$sanitisedArray['asset_id'] ) . ")";
	}
	
	if (isset($inputarray['sd_id'])){
		$sanitisedArray['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql .= " AND `trigger_groups`.`sensor_def_sd_id` IN (" . implode( ', ',$sanitisedArray['sd_id'] ) . ")";
	}
	
	if (isset($inputarray['device_id'])){
		$sanitisedArray['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ',$sanitisedArray['device_id'] ) . ")";
	}

	if (isset($inputarray['trigger_id'])){
		$sanitisedArray['trigger_id'] = sanitise_input_array($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		$sql .= " AND `alarm_events`.`trigger_groups_trigger_id` IN (" . implode( ', ',$sanitisedArray['trigger_id'] ) . ")";
	}

	if (isset($inputarray['action_id'])){
		$sanitisedArray['action_id'] = sanitise_input_array($inputarray['action_id'], "action_id", null, $API, $logParent);
		$sql .= " AND `alarm_actions`.`action_id` IN (" . implode( ', ',$sanitisedArray['action_id'] ) . ")";
	}
			
	$sql .= " ORDER BY alarm_events.datetime DESC";
	
	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
//echo  $sql;
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	$stm = $pdo->query($sql);
	$assetalarmsrows = $stm->fetchAll(PDO::FETCH_NUM);

	if (isset($assetalarmsrows[0][0])){
		$json_alarms  = array ();
		$outputid = 0;
		foreach($assetalarmsrows as $assetalarmsrow){
			$equation = $assetalarmsrow[17];
			if ($equation != ""){
				$value = $assetalarmsrow[5];
				if ($value != "") { 
					eval("\$value = \"$equation\";");
					eval('$sensor_valueresult = (' . $value. ');');
				}
				else {
					$sensor_valueresult = 0;
				}
				
				if ($assetalarmsrow[11] == "" || $assetalarmsrow[11] == NULL){	
					$triggervalueresult = 0;
				}
				else{
					$value = $assetalarmsrow[11];
					
					eval("\$triggervalueresult = $equation;"); //TODO
					
					
					//eval("\$triggervalue = \"$equation\";"); //TODO
					
					//if ($triggervalue != "") {
						//eval( '$triggervalueresult = (' . $triggervalue. ');' );
					//}
					//else {
						//$triggervalueresult = 0;
					//}
				}
			}
			else {
				$sensor_valueresult = $assetalarmsrow[5];
				$triggervalueresult = $assetalarmsrow[11];
			}

			$json_alarm = array(
				"alarm_events_id" => $assetalarmsrow[0]
				, "data_id" => $assetalarmsrow[1]
				, "asset_id" => $assetalarmsrow[2]
				, "asset_name" => $assetalarmsrow[3]
				, "sd_name" => $assetalarmsrow[4]
				, "sensor_value" => strval($sensor_valueresult)
				, "datetime" => $assetalarmsrow[6]
				, "sensor_def_sd_id" => $assetalarmsrow[7]
				, "trigger_name" => $assetalarmsrow[8]
				, "trigger_type" => $assetalarmsrow[9]
				, "value_operator" => $assetalarmsrow[10]
				, "trigger_value" => $triggervalueresult
				, "duration" => $assetalarmsrow[12]                                                                 
				, "suggested_actions" => $assetalarmsrow[13]
				, "reaction" => $assetalarmsrow[14]
				, "chart_label" => $assetalarmsrow[15]
				, "uomid" => $assetalarmsrow[16]
				, "trigger_id" => $assetalarmsrow[18]
				, "action" => $assetalarmsrow[19]
			);
			
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
else {	
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>