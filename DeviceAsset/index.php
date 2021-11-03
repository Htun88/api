<?php
	//	DeviceAsset	
	$API = "DeviceAsset";
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

	
if($inputarray['action'] == "select"){

	$schemainfoArray = getMaxString ("deviceassets", $pdo);

	$sql = "SELECT 
		deviceassets.deviceasset_id
		, deviceassets.date_from
		, deviceassets.date_to
		, deviceassets.devices_device_id
		, deviceassets.assets_asset_id
		, assets.asset_name
		, deviceassets.asset_task
		, deviceassets.active_status
		, deviceassets.last_modified_by
		, deviceassets.last_modified_datetime
		, devices.device_name
		, devices.device_provisioning_device_provisioning_id
		FROM (
		users
		, user_assets
		, assets
		, deviceassets
		, devices)
		LEFT JOIN userasset_details
		ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		WHERE 
		devices.device_id = deviceassets.devices_device_id
		AND users.user_id = user_assets.users_user_id
		AND deviceassets.assets_asset_id = assets.asset_id
		AND user_assets.users_user_id = $user_id
		AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
		OR (user_assets.asset_summary = 'all'))";
	
	if (isset($inputarray['provisioning_id'])){
		$sanitisedInput['provisioning_id'] = sanitise_input_array($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
		$sql .= " AND `devices`.`device_provisioning_device_provisioning_id` IN (" . implode( ', ',$sanitisedInput['provisioning_id'] ) . ")";
	}	

	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`assets_asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['deviceasset_id'])){
		$sanitisedInput['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`deviceasset_id` IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
	}

	if (isset($inputarray['active_status'])){
		$inputarray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`active_status` = '". $inputarray['active_status'] ."'";
	}

	$sql .= " ORDER BY `deviceasset_id` ASC";	

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
	$deviceassetsrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($deviceassetsrows[0][0])){
		$json_assets = array ();
		$outputid = 0;
		foreach($deviceassetsrows as $deviceassetsrow){
			$json_asset = array(
			"deviceasset_id" => $deviceassetsrow[0]
			, "device_id" => $deviceassetsrow[3]
			, "device_name" => $deviceassetsrow[10]
			, "asset_id" => $deviceassetsrow[4]
			, "asset_name" => $deviceassetsrow[5]
			, "asset_task" => $deviceassetsrow[6]
			, "active_status" => $deviceassetsrow[7]						
			, "date_from" => $deviceassetsrow[1]
			, "date_to" => $deviceassetsrow[2]
			, "last_modified_by" => $deviceassetsrow[8]
			, "last_modified_datetime" => $deviceassetsrow[9]
			, "provisioning_id" => $deviceassetsrow[11]);
			$json_assets = array_merge($json_assets,array("response_$outputid" => $json_asset));
			$outputid++;
		}
		$json = array("responses" => $json_assets);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else{
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// ***********************************INSERT**************************************
// *******************************************************************************
// ******************************************************************************* 

else if($inputarray['action'] == "insert"){

	$schemainfoArray = getMaxString ("deviceassets", $pdo);
	$insertArray = [];

	if(isset($inputarray['device_id'])){
		$inputarray['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql = "SELECT
			devices.device_id
				, deviceassets.deviceasset_id
			FROM (
				devices)
			LEFT JOIN deviceassets
				ON deviceassets.devices_device_id = devices.device_id
			AND deviceassets.date_to IS NULL
			WHERE devices.device_id = " . $inputarray['device_id'];

		$stm = $pdo->query($sql);
		$deviceassetsrows = $stm->fetchAll(PDO::FETCH_NUM);

		if(!isset($deviceassetsrows[0][0])){
			errorInvalid("device_id", $API, $logParent);
		}
		if(isset($deviceassetsrows[0][1])){
			logEvent($API . logText::genericError . str_replace('"', '\"', "{\"error\":\"DEVICE_ID_IN_USE\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"DEVICE_ID_IN_USE\"}");
		}
		
		$insertArray['device_id'] = $inputarray['device_id'];
	}
	else {
		errorMissing("device_id", $API, $logParent);
	}

	if(isset($inputarray['asset_id'])){
		$inputarray['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);		
		$sql ="SELECT 
			assets.asset_id 
			, assets.active_status
			FROM (users
			, user_assets
			, assets)
			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND assets.asset_id = '" . $inputarray['asset_id'] . "'";						
		$stm = $pdo->query($sql);	
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("asset_id", $API, $logParent);
		}
		if ($rows[0][1] == 1){
			logEvent($API . logText::genericError . str_replace('"', '\"', "{\"error\":\"ASSET_ID_INACTIVE\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"ASSET_ID_INACTIVE\"}");
		}					
		$sql = "SELECT 
				assets_asset_id 
				FROM deviceassets 
				WHERE date_to IS NULL
				AND assets_asset_id = '" . $inputarray['asset_id'] . "'";
		$stm = $pdo->query($sql);
		$deviceassetsrows = $stm->fetchAll(PDO::FETCH_NUM);
		if(isset($deviceassetsrows[0][0])){
			logEvent($API . logText::genericError . str_replace('"', '\"', "{\"error\":\"ASSET_ID_IN_USE\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"ASSET_ID_IN_USE\"}");			// ASSET_ID_ALREADY_IN_USED // CONOR NOTE TODO Can only have 1 associated deviceasset at a time. Must clear prior ones before inserting new ones
		}
		$insertArray['asset_id'] = $inputarray['asset_id'];
	}
	else {
		errorMissing("asset_id", $API, $logParent);
	}

	if (isset($inputarray['asset_task'])){
		$insertArray['asset_task'] = sanitise_input($inputarray['asset_task'], "asset_task", $schemainfoArray['asset_task'], $API, $logParent);
	}
	else {
		errorMissing("asset_task", $API, $logParent);
	}

	$triggerArray = array();
	if (isset($inputarray['trigger_id'])){
		$sanitisedInput['trigger_id'] = sanitise_input_array($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		$sql = "SELECT 
				trigger_id
				,trigger_source
			FROM
				trigger_groups
			WHERE
				trigger_id IN (" . implode( ', ',$sanitisedInput['trigger_id'] ) . ")";
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		//	Case where nothing returns
		if (!isset(($dbrows[0][0]))) {
			errorInvalid("trigger_id", $API, $logParent);
		}
		arrayExistCheck($sanitisedInput['trigger_id'], $dbrows, "trigger_id", $API, $logParent);

		$countGeofence = 0;
		$countSensor = 0;
		//$triggerArray = array();

		for ($i = 0; $i < count($dbrows); $i++){
			if ($dbrows[$i][1] == "Sensor"){
				$countSensor ++;
				$triggerArray['Sensor'][] = $dbrows[$i][0];
			}
			else if ($dbrows[$i][1] == "Geofence"){
				$countGeofence ++;
				$triggerArray['Geofence'][] = $dbrows[$i][0];
			}
			else {
				//	Catch case if a trigger_source is not input correctly. 
				//	Shouldnt hit this
				errorInvalid("trigger_id_source", $API, $logParent);
			}
		}
	}

	$insertArray['active_status'] = 0;
	$insertArray['date_from'] = gmdate("Y-m-d H:i:s");	

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)) . str_replace('"', '\"', json_encode($triggerArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$pdo->beginTransaction();

		$sql = "INSERT INTO deviceassets(
			date_from
			, devices_device_id
			, assets_asset_id
			, asset_task
			, active_status
			, last_modified_by
			, last_modified_datetime) 
		VALUES (
			:date_from
			, :device_id
			, :asset_id
			, :asset_task
			, :active_status
			, $user_id
			, '$timestamp')";

		$stm = $pdo->prepare($sql);
		
		if($stm->execute($insertArray)){
			$insertArray['deviceasset_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";

			if (isset($inputarray['trigger_id'])){
				if(isset($triggerArray['Sensor'])) {
					$stringSensor = "\n( " . implode( ", " . $insertArray['deviceasset_id'] . " ), ( ", $triggerArray['Sensor'] ) . ", " . $insertArray['deviceasset_id'] . " )";
					
					$sql = "INSERT INTO deviceassets_trigger_det(
						trigger_groups_trigger_id
						, deviceassets_deviceasset_id)
					VALUES " . $stringSensor;
					$stm = $pdo->prepare($sql);
					if ($stm->execute()) {
						$insertArray ['error_t' ] = "NO_ERROR";
						logEvent($API . logText::response . "Success Linking 'Sensor' trigger ID - " . str_replace('"', '\"', json_encode($triggerArray['Sensor'])), logLevel::response, logType::response, $token, $logParent);
					}
					else {
						$insertArray ['error_t' ] = "ERROR";
						logEvent($API . logText::responseError . "ERROR Linking 'Sensor' trigger ID - " . str_replace('"', '\"', json_encode($triggerArray['Sensor'])), logLevel::responseError, logType::responseError, $token, $logParent);
					}
				}
	
				if(isset($triggerArray['Geofence'])) {
					$stringGeofence = "\n( " . implode( ", " . $insertArray['deviceasset_id'] . " ), ( ", $triggerArray['Geofence'] ) . ", " . $insertArray['deviceasset_id'] . " )";
	
					$sql = "INSERT INTO deviceassets_geo_det(
						geofencing_geofencing_id
						, deviceassets_deviceasset_id)
					VALUES " . $stringGeofence;
					$stm = $pdo->prepare($sql);
					if ($stm->execute()) {
						$insertArray ['error_gt' ] = "NO_ERROR";
						logEvent($API . logText::response . "Success Linking 'Geofence' trigger ID - " . str_replace('"', '\"', json_encode($triggerArray['Geofence'])), logLevel::response, logType::response, $token, $logParent);
					}
					else {
						$insertArray ['error_t' ] = "ERROR";
						logEvent($API . logText::responseError . "ERROR Linking 'Geofence' trigger ID - " . str_replace('"', '\"', json_encode($triggerArray['Geofence'])), logLevel::responseError, logType::responseError, $token, $logParent);
					}
				}
			}
			echo json_encode($insertArray);
			$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
		}

		//	Update config versions
		$sql = "UPDATE
				trigger_groups
				, deviceassets_trigger_det
				, deviceassets
				, devices
			SET 
				devices.configuration_version = (devices.configuration_version + 1)
				, devices.geofences_version = (devices.geofences_version + 1)
				, devices.triggers_version = (devices.triggers_version + 1)
			WHERE deviceassets_trigger_det.trigger_groups_trigger_id = trigger_groups.trigger_id
			AND deviceassets.deviceasset_id = deviceassets_trigger_det.deviceassets_deviceasset_id
			AND deviceassets.date_to IS NULL
			AND devices.device_id = " . $insertArray['device_id'];
			
		$stm= $pdo->prepare($sql);
		if($stm->execute()){
			logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
		}
		else {
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"Failed\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		}
		$pdo->commit();
	}
	catch (PDOException $e){
		$pdo->rollback();
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// **********************************UPDATE***************************************
// *******************************************************************************
// ******************************************************************************* 


else if($inputarray['action'] == "update"){

	$schemainfoArray = getMaxString ("deviceassets", $pdo);
	$updateArray = [];
	$updateString = "";

	if(isset($inputarray['deviceasset_id'])){
		$inputarray['deviceasset_id'] = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql = "SELECT 
				deviceassets.deviceasset_id
				, deviceassets.active_status
				, deviceassets.date_to
			FROM ( 
				deviceassets
				, user_assets
				, assets)
			LEFT JOIN userasset_details
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE deviceassets.deviceasset_id = '" . $inputarray['deviceasset_id'] . "' 
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some' 
			AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'ALL'))";

		$stm = $pdo->prepare($sql);
		$stm->execute();
		$rows = $stm->fetchAll(PDO::FETCH_NUM);				
		if (!isset($rows[0][0])){
			errorInvalid("deviceasset_id", $API, $logParent); 				// Invalid asset permission TODO note for API doc
		}
		if ($rows[0][1] == 1
			|| $rows[0][2] != null) {
			logEvent($API . logText::genericError . str_replace('"', '\"', "{\"error\":\"DEVICEASSET_ID_INACTIVE\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"DEVICEASSET_ID_INACTIVE\"}");
		}
		$updateArray['deviceasset_id'] = $inputarray['deviceasset_id'];
    }
	else {
		errorMissing("deviceasset_id", $API, $logParent);
	}

	//	TODO Optional functionality: Allow the user to edit the task description string if the task is still active

	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		if ($updateArray['active_status'] != 1) {
			errorInvalid("active_status", $API, $logParent);
		}
		$updateString .= " `active_status` = :active_status,";	
		//	If succesfully inserting the active status then automatically insert the date
		//	This is included within the active_status parameter to avoid date_to autoupdating if another parameter is updatable in future.
		$updateArray['date_to'] = gmdate("Y-m-d H:i:s");
		$updateString .= " `date_to` = :date_to,";
	}
	else {
		errorMissing("active_status", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try {
		if (count($updateArray) < 2) {
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$sql = "UPDATE 
				deviceassets 
				SET". $updateString . " `last_modified_by` = $user_id
				, `last_modified_datetime` = '$timestamp' 
				WHERE `deviceasset_id` = :deviceasset_id";

			$stm= $pdo->prepare($sql);	
			if($stm->execute($updateArray)){
				$updateArray ['error' ] = "NO_ERROR";
				echo json_encode($updateArray);
				$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];

				//	Update config versions
				$sql = "UPDATE
					trigger_groups
					, deviceassets_trigger_det
					, deviceassets
					, devices
				SET 
					devices.configuration_version = (devices.configuration_version + 1)
					, devices.geofences_version = (devices.geofences_version + 1)
					, devices.triggers_version = (devices.triggers_version + 1)
				WHERE deviceassets_trigger_det.trigger_groups_trigger_id = trigger_groups.trigger_id
				AND deviceassets.deviceasset_id = deviceassets_trigger_det.deviceassets_deviceasset_id
				AND devices.device_id = deviceassets.devices_device_id
				AND deviceassets.deviceasset_id = " . $updateArray['deviceasset_id'];
			
				$stm= $pdo->prepare($sql);
				if($stm->execute()){
					logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
				}
				else {
					logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"Failed\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
				}
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