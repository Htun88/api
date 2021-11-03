<?php
	$API = "SensorData";
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
		, sensor_data.data_datetime
		, assets.asset_id
		, sensor_def.sd_id
		, sensor_data_det.sensor_value
		, sensor_def.sd_name
		, uom.chartlabel
		, deviceassets_chart.uom_show_id
		, uom_conversions.equation
		, sensor_def.sd_data_min
		, sensor_def.sd_data_max
		, sensor_def.sd_graph_min
		, sensor_def.sd_graph_max
		, sensor_def.chart

		, sensor_def.colorcode
		, sensor_def.chartlabel 
		, sensor_data.user_agent
		FROM (users
		, user_assets
		, assets
		, deviceassets
		, sensor_data
		, sensor_data_det
		, sensor_def
		)
		LEFT JOIN deviceassets_chart 
		ON deviceassets_chart.deviceassets_deviceasset_id = deviceassets.deviceasset_id
		AND deviceassets_chart.sd_id = sensor_def.sd_id
		LEFT JOIN uom_conversions 
		ON uom_conversions.uom_id_to = deviceassets_chart.uom_show_id
		AND sensor_def.sd_uom_id = uom_conversions.uom_id_from
		
		LEFT JOIN uom 
		ON IF (deviceassets_chart.uom_show_id IS NULL
			, uom.id = sensor_def.sd_uom_id
			, uom.id = deviceassets_chart.uom_show_id)
			
		LEFT JOIN userasset_details 
		ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		WHERE sensor_def.sd_id = sensor_data_det.sensor_def_sd_id 
		AND sensor_data_det.sensor_data_data_id = sensor_data.data_id
		AND sensor_data.deviceassets_deviceasset_id = deviceassets.deviceasset_id
		AND deviceassets.date_from <= sensor_data.data_datetime
		AND (deviceassets.date_to >= sensor_data.data_datetime
		OR deviceassets.date_to IS NULL)
		AND deviceassets.assets_asset_id = assets.asset_id
		AND users.user_id = user_assets.users_user_id 
		AND user_assets.users_user_id = $user_id
		AND ((user_assets.asset_summary = 'some' 
			AND assets.asset_id = userasset_details.assets_asset_id)
		OR (user_assets.asset_summary = 'all'))";

	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `assets`.`asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if (isset($inputarray['data_id'])){
		$sanitisedInput['data_id'] = sanitise_input_array($inputarray['data_id'], "data_id", null, $API, $logParent);
		$sql .= " AND `sensor_data`.`data_id` IN (" . implode( ', ',$sanitisedInput['data_id'] ) . ")";
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
	else {
		$sanitisedInput['timestamp_from'] = gmdate( "M d Y H:i:s", strtotime("- 1 month"));
		$sql .= " AND `sensor_data`.`data_datetime` >= '". $sanitisedInput['timestamp_from'] ."'";
	}

	//	$sql needs an initial " AND (" to begin the sd_id search regardless of search method
	if (isset($inputarray['sd_id'])
		|| isset($inputarray['filter'])
		){
		$sql .= " AND (";
	}

	if (isset($inputarray['sd_id'])){
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql .= " `sensor_def_sd_id` IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")";
	}
	
	//	To make it play nice given either the absence or existance of sd_id the initial "OR" $sql will change, but it also relies on the initial "OR" every loop. 
	//	To get around this used a second $sql_2 string, did the stuff here then joined the two strings at the end. 
	if (isset($inputarray['filter'])){
		
		$sql_2 = "";

		foreach($inputarray['filter'] as $innerObject){

			if (!isset($innerObject["sd_id"])
				|| !isset($innerObject["value"])
				){
				errorInvalid("filter", $API, $logParent);
			}
			
			$sanitisedInnerInput['sd_id'] = sanitise_input($innerObject['sd_id'], "sd_id", null, $API, $logParent);
			$sql_2 .= " OR ( `sensor_def_sd_id` = '" . $sanitisedInnerInput['sd_id'] . "' AND (";

			foreach($innerObject['value'] as $innerInnerObject){

				if (!isset($innerInnerObject["comp"])
					|| !isset($innerInnerObject["val"])
					|| !isset($innerInnerObject["op"])
					){
					errorInvalid("filter", $API, $logParent);
				}

				$sanitisedInnerInnerObject['comp'] = sanitise_input($innerInnerObject['comp'], "math_Comparison", null, $API, $logParent);
				$sanitisedInnerInnerObject['value'] = sanitise_input($innerInnerObject['val'], "sensor_value", null, $API, $logParent);
				$sanitisedInnerInnerObject['operator'] = sanitise_input($innerInnerObject['op'], "operator", null, $API, $logParent);

				$sql_2 .= " `sensor_data_det`.`sensor_value` " . $sanitisedInnerInnerObject['comp'] . " '" . $sanitisedInnerInnerObject["value"] . "' ";
				
				if (strtolower($sanitisedInnerInnerObject["operator"]) == 1) {
					$sql_2 .= " AND";
				}
				else if (strtolower($sanitisedInnerInnerObject["operator"]) == 0) {
					$sql_2 .= "  OR";
				} 
			}
			$sql_2 = substr($sql_2, 0, -3) . ") ) ";		//	Trim off the last operator. Note that the OR has extra whitespace to make 3 characters
		}	
		//	If sd_id is not also present, the initial " OR" will break things. Cut it off. 
		if (!isset($inputarray['sd_id'])) {
			$sql_2 = substr($sql_2, 3);
		}
		$sql .= $sql_2;	
	}

	//	$sql needs a final " )" to end the sd_id search
	if (isset($inputarray['sd_id'])
		|| isset($inputarray['filter'])
		){
		$sql .= " )";
	}

	//	Close the $sql sd_id search by bracket, and add on some sorting.
	$sql .= " ORDER BY `sensor_data`.`data_datetime`";	

	if (isset($inputarray['order'])){
		$sanitisedInput['order'] = sanitise_input($inputarray['order'], "order", 4, $API, $logParent);
		$sql .= " " . $sanitisedInput['order'];
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
	$sensordatasrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($sensordatasrows[0][0])){							
		$json_parent = array();
		$outputid = 0;
		foreach($sensordatasrows as $sensordatasrow){

				//	TODO can't test this till deviceAssetsChart API is done. 
				$value = $sensordatasrow[4];
				
				$data_min_converted = $sensordatasrow[9];
				$data_max_converted = $sensordatasrow[10];
				$graph_min_converted = $sensordatasrow[11];
				$graph_max_converted = $sensordatasrow[12];

				$equation = "$sensordatasrow[8]";
				$equation = sanitise_input($equation, "equation", null, $API, $logParent);
				
				$value = sanitise_input($value, "math_Value", null, $API, $logParent);

				if ($equation != ""){
					eval("\$value = \"$equation\";");
					if ($value != "") {
						eval( '$result = (' . $value. ');' );
						$result = strval($result);
					}
					else {
						$result = null;
					}

					if ($data_min_converted != "" && $data_min_converted != null) {
						$value = floatval($data_min_converted);
						eval("\$data_min_converted = $equation;");
					}
					if ($data_max_converted != "" && $data_max_converted != null) {
						$value = floatval($data_max_converted);
						eval("\$data_max_converted = $equation;");
					}
					if ($graph_min_converted != "" && $graph_min_converted != null) {
						$value = floatval($graph_min_converted);
						eval("\$graph_min_converted = $equation;");
					}
					if ($graph_max_converted != "" && $graph_max_converted != null) {
						$value = floatval($graph_max_converted);
						eval("\$graph_max_converted = $equation;");
					}
				}
				else {
					$result = $sensordatasrow[4];
				}
				
			$json_child = array(
				"data_id" => $sensordatasrow[0]
				, "data_datetime" => $sensordatasrow[1]
				, "asset_id" => $sensordatasrow[2]
				, "sd_id" => $sensordatasrow[3]
				, "user_agent" => $sensordatasrow[16]
				, "sensor_value" => $result	
				, "sd_name" => $sensordatasrow[5]
				, "uom_chartlabel" => $sensordatasrow[6]
				, "equation" => $sensordatasrow[8]
				, "data_min" => $data_min_converted
				, "data_max" => $data_max_converted
				, "graph_min" => $graph_min_converted
				, "graph_max" => $graph_max_converted
				, "chart" => $sensordatasrow[13]
				, "colorcode" => $sensordatasrow[14]
				, "sd_chartlabel" => $sensordatasrow[15]);
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
// ***********************************INSERT**************************************
// *******************************************************************************
// ******************************************************************************* 

else if ($inputarray['action'] == "insert") {
	
	$schemainfoArray = getMaxString ("sensor_data", $pdo);
	$sanitisedArray = array();
	$outputArray = array();
	$sd_idArray = array();
 
	if (isset($inputarray['timestamp'])){
		$sanitisedArray['timestamp'] = sanitise_input($inputarray['timestamp'], "timestamp",null, $API, $logParent);
	}
	else{
		$sanitisedArray['timestamp'] = gmdate("Y-m-d H:i:s");
	}

	if (isset($inputarray['user_agent'])){
		$sanitisedArray['user_agent'] = sanitise_input($inputarray['user_agent'], "user_agent", $schemainfoArray['user_agent'], $API, $logParent);
	}
	else {
		errorMissing("user_agent", $API, $logParent);
	}

	if (isset($inputarray['packet_type'])){
		$sanitisedArray['packet_type'] = sanitise_input($inputarray['packet_type'], "packet_type", null, $API, $logParent);
	}

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
		AND deviceassets.date_from <= '" . $sanitisedArray['timestamp'] . "'
		AND (deviceassets.date_to >=  '" . $sanitisedArray['timestamp'] . "'
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


	//	This should go after the isset version, but its a very long if statement, so including here for legibility
	if (!isset($inputarray['sensor_data'])){
		errorMissing("sensor_data", $API, $logParent);
	}
	if (isset($inputarray['sensor_data'])){

		foreach($inputarray['sensor_data'] as $innerObject){
			
			if (!isset($innerObject["sd_id"])
				|| !isset($innerObject["value"])
				){
				errorInvalid("sensor_data", $API, $logParent);
			}	
			//	Save the new value in an array object.
			$sanitiseArgtype = "sensor_value";
			if ($innerObject['sd_id'] == 12){
				$sanitiseArgtype = "sensor_value_12";
			}
			else if ($innerObject['sd_id'] == 13) {
				$sanitiseArgtype = "sensor_value_13";
			}
			//	If duplicates sensor sd_id exist in insert, throw error
			if (array_key_exists($innerObject['sd_id'], $sd_idArray)) {
				errorInvalid("duplicate_sd_id", $API, $logParent);
			}
			//	Gorgeous. Beautiful
			$sd_idArray[sanitise_input($innerObject['sd_id'], "sensor_data_sensor_id", null, $API, $logParent)]['value'] = sanitise_input($innerObject['value'], $sanitiseArgtype, null, $API, $logParent);
			 
			//	Check that the sensor Data number is a) valid, and b) within the expected max and min range. Error out if not.
			//TODO
			$stm = $pdo->query("SELECT 
				sd_id
				, sd_data_min
				, sd_data_max
				, sd_deactivated_data
				, sensors_sensor_id
				, sd_overload_data
				FROM 
				sensor_def 
				, sensors_det
				WHERE sensors_det.sensor_def_sd_id = sensor_def.sd_id
				AND sd_id = '" . array_key_last($sd_idArray) . "' ");
				
			//$stm = $pdo->query("SELECT 
				//sd_id
				//, sd_data_min
				//, sd_data_max
				//, sd_deactivated_data
				//FROM 
				///sensor_def 
				//WHERE sd_id = '" . array_key_last($sd_idArray) . "' ");	
				
				
			
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($dbrows[0][0])) {								//	If not set, error
				errorInvalid("sensor_data_sensor_id1", $API, $logParent);
			}
			if (isset($dbrows[0][0])){		

			//	If set but not within certain range
			
			//print_r($dbrows);
				if (($sd_idArray[array_key_last($sd_idArray)]['value'] <  $dbrows[0][1]
					|| $sd_idArray[array_key_last($sd_idArray)]['value'] >  $dbrows[0][2])
					&& $sd_idArray[array_key_last($sd_idArray)]['value'] !=  $dbrows[0][3]
					&& $sd_idArray[array_key_last($sd_idArray)]['value'] !=  $dbrows[0][5]
					){
					//echo $sd_idArray[array_key_last($sd_idArray)]['value'] ;
					errorInvalid("sensor_data_value", $API, $logParent);
				}
				
			}
			$sd_idArray[array_key_last($sd_idArray)]['sensor_id'] = $dbrows[0][4];
			
			//TODO add device_component_type = 'Sensor def' ask Tim
			$stm = $pdo->query("SELECT 
				sensor_def.sd_id
				FROM 
				devices
				, device_provisioning_components
				, sensors
				, sensors_det
				, sensor_def 
				WHERE device_id = '" . $sanitisedArray["device_id"] . "' 
				AND devices.device_provisioning_device_provisioning_id = device_provisioning_components.device_provisioning_device_provisioning_id
				AND device_component_type = 'Sensor'
				AND sensors.sensor_id = device_component_id
				AND sensors_det.sensors_sensor_id = sensors.sensor_id
				AND sensor_def.sd_id = sensors_det.sensor_def_sd_id
				AND sensor_def.sd_id = '" . array_key_last($sd_idArray) . "' ");

			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])){
				//echo array_key_last($sd_idArray);
				errorInvalid("sensor_data_sensor_id", $API, $logParent);
			}
		}

		//	Throw variables into new array so that sql doesn't break
		//	This is a critical SQL. Please talk to Tim before modification. 
		//	This checks if there is already an alarm value for this date time, and if so inserts the data to it
		//		If there is no, it creates an empty sensor_data value in preperation for the alarm.
		//	Either way, require the sensor_data_data_id for future
		$insertArray = array();
		$insertArray['timestamp'] = $sanitisedArray['timestamp'];
		$insertArray['deviceasset_id'] = $sanitisedArray['deviceasset_id'];
		$insertArray['user_agent'] = $sanitisedArray['user_agent'];
		if (isset($sanitisedArray['packet_type'])) {
			$insertArray['packet_type'] = $sanitisedArray['packet_type'];
		}
		else{
			$insertArray['packet_type'] = 1;
		}
		
		//	Check if sensor_data has a value for this datetime already
		$sql = "SELECT 
			data_id 
			FROM 
			sensor_data
			WHERE data_datetime = '" . $insertArray['timestamp'] . "'
			AND deviceassets_deviceasset_id = '" . $insertArray['deviceasset_id'] . "' ";
		
		if (isset($insertArray['packet_type'])) {
			$sql .= " AND type = " . $insertArray['packet_type'];
		}
		//echo $sql;
		
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		
		//	If found
		if (isset($rows[0][0])){
			$sanitisedArray['sensor_data_data_id'] = $rows[0][0];
		}
		//	If not found
		else if (!isset($rows[0][0])){

			//	Including unnecessary values for the logging array
			$logArray['action'] = "insert";
			$logArray['insertTable'] = "sensor_data";
			$logParent2 = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($insertArray)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];

			try{
				$sql = "INSERT INTO sensor_data (
						data_datetime
						, deviceassets_deviceasset_id
						, user_agent
						, type)  
					VALUES (
						:timestamp
						, :deviceasset_id
						, :user_agent";
						if(isset($insertArray['packet_type'])){
							$sql .= ", :packet_type)";
						}
						else {
							$sql .= ", NULL)";
						}
				$stm= $pdo->prepare($sql);
				if($stm->execute($insertArray)){
					$sanitisedArray['sensor_data_data_id'] = $pdo->lastInsertId();
					$insertArray['sensor_data_data_id'] = $sanitisedArray['sensor_data_data_id'];
					$insertArray['error'] = "NO_ERROR";
					logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent2);
				}
			}
			catch (PDOException $e){
				logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent2);
				die("{\"error\":\"$e\"}");
			}
		}
	}	

	try {
		$pdo->beginTransaction();

		//	Loop through $sd_idArray and add values to a string for execution. 
		//	Also add values to output array.
		$data = array();
		foreach($sd_idArray as $key => $value ) {
			$data[$key] = "( " . $sanitisedArray['sensor_data_data_id'] . ", " . $key  . ", " .  $sd_idArray[$key]['sensor_id']  . ", " .  $sd_idArray[$key]['value'] . ")";
			$outputArray['sd_id'][$key] = $key;
			$outputArray['sensor_id'][$key] = $sd_idArray[$key]['sensor_id'];
			$outputArray['sensor_value'][$key] = $sd_idArray[$key]['value'];
		}

		//	Check if there already exists a data ID at this timestamp for this deviceasset
		//	If not, then there is no data already in the database to check against, we can go straight to inserting new data
		$sql = "SELECT 
				data_id
			FROM 
				sensor_data
			WHERE data_datetime = '" . $sanitisedArray['timestamp'] . "'
			AND deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'";
		
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		if (isset($rows[0][0])) {
			//$sql = "SELECT
					//sensor_def_sd_id 
				//FROM 
					//sensor_data_det
				//LEFT JOIN sensor_data 
					//ON data_id = sensor_data_data_id AND sensor_data.`type` = " . $insertArray['packet_type'] ."
				//WHERE data_datetime = '" . $sanitisedArray['timestamp'] . "'
				//AND deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'
				//AND sensor_def_sd_id IN (" . implode( ', ',$outputArray['sd_id'] ) . ")";
				
				
			$sql = "SELECT
				sensor_def_sd_id 
			FROM 
			sensor_data 
			,sensor_data_det
			WHERE	
			sensor_data.data_id = sensor_data_det.sensor_data_data_id 
			AND sensor_data.`type` = " . $insertArray['packet_type'] . "
			AND sensor_data.data_datetime = '" . $sanitisedArray['timestamp'] . "'
			AND sensor_data.deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'
			AND sensor_def_sd_id IN (" . implode( ', ',$outputArray['sd_id'] ) . ")";
			
			$stm = $pdo->query($sql);
			//echo $sql;
			$rows = $stm->fetchAll(PDO::FETCH_NUM);

			//	If values already exist in the database for this sensor at this timestamp, do not duplicate it. 
			//	Removes these values from $data array so they are not submitted to the database
			if (isset($rows[0][0])) {
				$issues = array();
				foreach ($rows as $depth) {
					foreach ($depth as $key => $value) {
						if (in_array($value, $outputArray['sd_id'])) {
							unset($data[$value]);
							unset($outputArray['sd_id'][$value]);
							unset($outputArray['sensor_id'][$value]);
							unset($outputArray['sensor_value'][$value]);
						}
					}
				}
			}

			//	Case where all data being inserted is duplicate on the server
			if (count($data) == 0) {
				logEvent($API . logText::requestIncompatable . "NO_UNIQUE_SENSOR_DATA", logLevel::requestError, logType::requestError, $token, $logParent);
				die("{\"error\":\"NO_UNIQUE_SENSOR_DATA\"}");	
			}
		}
		

		//	Including unnecessary values for the logging array
		$logArray = array();
		$logArray['action'] = "insert";
		$logArray['insertTable'] = "sensor_data_det";
		//	TODO Nicen up this log maybe? Doesn't display 'sd_id' as a key value pair, just its value. Low priority -Conor
		$logParent = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($sd_idArray)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$sql = "INSERT INTO sensor_data_det (
				sensor_data_data_id
				, sensor_def_sd_id
				, sensor_sensor_id
				, sensor_value) 
				VALUES " . implode(', ', $data); 

		//echo $sql;

		$stm = $pdo->prepare($sql);
		if ($stm->execute()){
			$outputArray['asset_id'] = $sanitisedArray['asset_id'];
			$outputArray['device_id'] = $sanitisedArray['device_id'];
			$outputArray['deviceasset_id'] = $sanitisedArray['deviceasset_id'];
			$outputArray['timestamp'] = $sanitisedArray['timestamp'];
			$outputArray['user_agent'] = $sanitisedArray['user_agent'];
			$outputArray['sd_id'] = implode(", " , $outputArray['sd_id'] );
			$outputArray['sensor_id'] = implode(", " , $outputArray['sensor_id'] );
			$outputArray['sensor_value'] = implode(", " , $outputArray['sensor_value'] );
			$outputArray['data_id'] = $sanitisedArray['sensor_data_data_id'];
			$outputArray['error'] = "NO_ERROR";
			echo json_encode($outputArray);
			$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];

		}
		$pdo->commit();
	}
	catch (PDOException $e){
		$pdo->rollback();
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}

	$insertArray = array();
	$insertArray['deviceasset_id'] = $sanitisedArray['deviceasset_id'];
	$insertArray['asset_id'] = $sanitisedArray['asset_id'];
	$insertArray['sensor_data_data_id'] = $sanitisedArray['sensor_data_data_id'];
	$insertArray['timestamp'] = $sanitisedArray['timestamp'];

	if (isset($sd_idArray['12']['value'])) {
		$insertArray['lat'] = $sd_idArray['12']['value'];
	}
	if (isset($sd_idArray['13']['value'])) {
		$insertArray['long'] = $sd_idArray['13']['value'];
	}
	if (isset($sd_idArray['14']['value'])) {
		$insertArray['alt'] = $sd_idArray['14']['value'];
	}

	$sql = "SELECT 
			id
			, data_datetime
			, deviceassets_deviceasset_id
		FROM 
			assets_position 
		WHERE assets_asset_id = '" . $insertArray["asset_id"] . "' ";

	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	//	Case where there is no asset_id
	if (!isset($rows[0][0])){
		//	Including unnecessary values for the logging array
		$logArray = array();
		$logArray['action'] = "insert";
		$logArray['insertTable'] = "assets_position";
		$insertArray['alarm_reset_status'] = "YES";
		$insertArray['alarm_ack_status'] = "YES";

		$logParent2 = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($insertArray)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];
				
		try{
			$sql = "INSERT INTO assets_position (
				deviceassets_deviceasset_id
				, assets_asset_id
				, sensor_data_data_id
				, data_datetime
				, lat
				, lng
				, alt
				, alarm_reset_status
				, alarm_ack_status
				, assets_positioncol
				, imei)  
			VALUES (
				:deviceasset_id
				, :asset_id
				, :sensor_data_data_id
				, :timestamp";
				if (isset($insertArray['lat'])
					&& isset($insetArray['long'])
					) {
					$sql .= ", :lat
						, :long";
				}
				else {
					unset($insertArray['lat']);
					unset($insertArray['long']);
					$sql .= ", NULL
						, NULL";
				}
				if (isset($insertArray['alt'])) {
					$sql .= ", :alt";
				}
				else {
					$sql .= ", NULL";
				}
				$sql .= "
					, :alarm_reset_status
					, :alarm_ack_status";
				if (isset($insertArray['assets_positioncol'])) {
					$sql .= ", :assets_positioncol";
				}
				else {
					$sql .= ", NULL";
				}
				if (isset($insertArray['imei'])) {
					$sql .= ", :imei)";
				}
				else {
					$sql .= ", NULL)";
				}

			$stm= $pdo->prepare($sql);
			if ($stm->execute($insertArray)){
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent2);
			}
		}
		catch (PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent2);
		}	
	}
	//	Case where there is a valid asset ID already, we update instead of insert
	else {

		//	Check if this data packet is greater than the current one on server. If this data packet is NOT the newest we can discard it
		if ($rows[0][1] < $insertArray['timestamp']){
			//	Check if there is a new deviceasset, if so we want to update the field
			try {
				$sql = "UPDATE 
					assets_position 
					SET `sensor_data_data_id` = :sensor_data_data_id
					, `data_datetime` = :timestamp";

				if ($rows[0][2] != $sanitisedArray['deviceasset_id']) {
					//	We update the alarm reset and acknowledge at this time as we are essentially initialising a new deviceasset/asset ID set
					$insertArray['alarm_reset_status'] = "YES";
					$insertArray['alarm_ack_status'] = "YES";
					$sql .= ", `deviceassets_deviceasset_id` = :deviceasset_id
						, alarm_reset_status = :alarm_reset_status
						, alarm_ack_status = :alarm_ack_status";
				}
				else {
					//	Unset this here to avoid SQL error
					unset($insertArray['deviceasset_id']);
				}
				//	If you've somehow supplied one of lat/long without the other throw error
				if (isset($insertArray['lat']) 
					|| isset($insertArray['long'])
					){
					if (isset($insertArray["lat"])
						&& isset($insertArray["long"])
						) {	
						$sql .= ", `lat` = :lat
							, `lng` = :long";
					}
					else {
						unset($insertArray["lat"]);
						unset($insertArray["long"]);
					}
				}
				if (isset($insertArray["alt"])) {
					$sql .= ", `alt` = :alt";
				}
				$sql .= " WHERE assets_asset_id = :asset_id AND id = " . $rows[0][0];

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
		}
	}
}

else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;


?>