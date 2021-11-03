<?php
	$API = "Device";
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

	$schemainfoArray = getMaxString ("devices", $pdo); 

	$sql = "SELECT 
		devices.device_id
		, devices.device_name
		, devices.device_sn
		, devicelicense.devicelicense_id	
		, devicelicense.license_hash
		, devicelicense.expdatetime
		, devices.configuration_version
		, devices.geofences_version
		, devices.triggers_version
		, devices.desired_version
		, devices.desired_stored_versions
		, devices.device_provisioning_device_provisioning_id
		, devices.active_status	
		, devices.last_modified_by
		, devices.last_modified_datetime

		FROM devices
		LEFT JOIN devicelicense 
		ON devices.devicelicense_devicelicense_id = devicelicense.devicelicense_id 
		WHERE 1=1";

	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `devices`.`device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if (isset($inputarray['device_name'])){
		$sanitisedInput['device_name'] = sanitise_input_array($inputarray['device_name'], "device_name", $schemainfoArray['device_name'], $API, $logParent);
		foreach ($sanitisedInput['device_name'] as $value) {
			$sanitisedInput['device_name_prepared'][] = " `devices`.`device_name` LIKE '%" . $value . "%'";
		}
		$sql .= " AND (" . implode( ' OR ',$sanitisedInput['device_name_prepared'] ) . ")";
	}

	if (isset($inputarray['license_id'])){
		$sanitisedInput['license_id'] = sanitise_input_array($inputarray['license_id'], "license_id",  null, $API, $logParent);
		$sql .= " AND `devices`.`devicelicense_devicelicense_id` IN (" . implode( ', ',$sanitisedInput['license_id'] ) . ")";
	}

	if (isset($inputarray['license_hash'])){
		$schemainfoArray2 = getMaxString ("devicelicense", $pdo); 
		$sanitisedInput['license_hash'] = sanitise_input_array($inputarray['license_hash'], "license_hash",  $schemainfoArray2['license_hash'], $API, $logParent);
		foreach ($sanitisedInput['license_hash'] as $value) {
			$sanitisedInput['license_hash_prepared'][] = " `devicelicense`.`license_hash` = '$value'";
		}
		$sql .= " AND (" . implode( ' OR ',$sanitisedInput['license_hash_prepared'] ) . ")";
	}

	if (isset($inputarray['configuration_version'])){
		$sanitisedInput['configuration_version'] = sanitise_input_array($inputarray['configuration_version'], "configuration_version", null, $API, $logParent);
		$sql .= " AND `devices`.`configuration_version` IN (" . implode( ', ',$sanitisedInput['configuration_version'] ) . ")";
	}

	if (isset($inputarray['geofences_version'])){
		$sanitisedInput['geofences_version'] = sanitise_input_array($inputarray['geofences_version'], "geofences_version", null, $API, $logParent);
		$sql .= " AND `devices`.`geofences_version` IN (" . implode( ', ',$sanitisedInput['geofences_version'] ) . ")";
	}

	if (isset($inputarray['triggers_version'])){
		$sanitisedInput['triggers_version'] = sanitise_input_array($inputarray['triggers_version'], "triggers_version", null, $API, $logParent);
		$sql .= " AND `devices`.`triggers_version` IN (" . implode( ', ',$sanitisedInput['triggers_version'] ) . ")";
	}

	if (isset($inputarray['desired_version'])){
		$sanitisedInput['desired_version'] = sanitise_input_array($inputarray['desired_version'], "desired_version", $schemainfoArray['desired_version'], $API, $logParent);
		foreach ($sanitisedInput['desired_version'] as $value) {
			$sanitisedInput['desired_version_prepared'][] = " `devices`.`desired_version` LIKE '%" . $value . "%'";
		}
		$sql .= " AND (" . implode( ' OR ',$sanitisedInput['desired_version_prepared'] ) . ")";
	}

	if (isset($inputarray['desired_stored_versions'])){
		$sanitisedInput['desired_stored_versions'] = sanitise_input_array($inputarray['desired_stored_versions'], "desired_stored_versions", $schemainfoArray['desired_stored_versions'], $API, $logParent);
		foreach ($sanitisedInput['desired_stored_versions'] as $value) {
			$sanitisedInput['desired_stored_versions_prepared'][] = " `devices`.`desired_stored_versions` LIKE '%" . $value . "%'";
		}
		$sql .= " AND (" . implode( ' OR ',$sanitisedInput['desired_stored_versions_prepared'] ) . ")";
	}

	if (isset($inputarray['provisioning_id'])){
		$sanitisedInput['provisioning_id'] = sanitise_input_array($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
		$sql .= " AND `devices`.`device_provisioning_device_provisioning_id` IN (" . implode( ', ',$sanitisedInput['provisioning_id'] ) . ")";
	}

	if (isset($inputarray['active_status'])){
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `devices`.`active_status` = '". $sanitisedInput['active_status'] ."'";
	}

	if (isset($inputarray['device_sn'])){
		$sanitisedInput['device_sn'] = sanitise_input_array($inputarray['device_sn'], "device_sn", $schemainfoArray['device_sn'], $API, $logParent);
		$sql .= " AND `devices`.`device_sn` IN (" . implode( ', ',$sanitisedInput['device_sn'] ) . ")";
	}

	$sql .= "  ORDER BY `devices`.`device_name` DESC ";


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
		$json_assets = array();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$sql= "SELECT sensor_data.user_agent from deviceassets, sensor_data 
			 WHERE 
			 deviceassets.deviceasset_id = sensor_data.deviceassets_deviceasset_id
			 and
			 devices_device_id = ". $dbrow[0] . " 
			 ORDER BY sensor_data.data_datetime DESC LIMIT 1";
 
			$stm = $pdo->query($sql);
			$dbrowsuser_agent = $stm->fetchAll(PDO::FETCH_NUM);
			
			if (isset($dbrowsuser_agent[0][0])){
				$user_agent =  $dbrowsuser_agent[0][0];
			}else{
				$user_agent =  "unknown";
			}
			
			$json_asset = array(
			"device_id" => $dbrow[0]
			, "device_name" => $dbrow[1]
			, "device_sn" => $dbrow[2]
			, "license_id" => $dbrow[3]
			, "license_hash" => $dbrow[4]
			, "license_expiry" => $dbrow[5]
			, "configuration_version" => $dbrow[6]
			, "geofences_version" => $dbrow[7]
			, "triggers_version" => $dbrow[8]
			, "desired_version" => $dbrow[9]
			, "desired_stored_versions" => $dbrow[10]
			, "provisioning_id" => $dbrow[11]
			, "active_status" => $dbrow[12]
			, "last_modified_by" => $dbrow[13]
			, "last_modified_datetime" => $dbrow[14]
			, "user_agent" => $user_agent
			);
			$json_assets = array_merge($json_assets,array("response_$outputid" => $json_asset));
			$outputid++;
		}
		$json = array("responses" => $json_assets);
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

else if ($sanitisedInput['action'] == "insert"){

	$schemainfoArray = getMaxString("devices", $pdo);
	$schemainfoArray2 = getMaxString("devicelicense", $pdo);
	$insertArray = [];

	if(isset($inputarray['device_name'])){
		$insertArray['device_name'] = sanitise_input($inputarray['device_name'],"device_name",$schemainfoArray['device_name'], $API, $logParent);
	}
	else {
		errorMissing("device_name", $API, $logParent);
	}

	if(isset($inputarray['device_sn'])){
		$sanitisedInput['device_sn'] = sanitise_input($inputarray['device_sn'],"device_sn", $schemainfoArray['device_sn'], $API, $logParent);
		$sql = "SELECT 
				device_sn
			FROM 
				devices
			WHERE device_sn = '" . $sanitisedInput['device_sn'] . "'";

		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])) {
			//	Devices are now allowed to be non-unique
			//errorGeneric("device_sn_already_in_use", $API, $logParent);
		}
		$insertArray['device_sn'] = $sanitisedInput['device_sn'];
	}
	else {
		//errorMissing("device_sn", $API, $logParent);
	}

	if(isset($inputarray['license_id'])){
		$sanitisedInput['license_id'] = sanitise_input($inputarray['license_id'],"license_id", null, $API, $logParent);
		$sql = "SELECT 
				devicelicense.expdatetime
				, devices.devicelicense_devicelicense_id
				, devicelicense.license_hash
			FROM 
				devicelicense
			LEFT JOIN devices ON devices.devicelicense_devicelicense_id = devicelicense.devicelicense_id
			WHERE devicelicense.devicelicense_id = " . $sanitisedInput['license_id'];

		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if(!isset($dbrows[0][0])){
			// license does not exist	
			errorInvalid("license_id", $API, $logParent);
		}
		if (isset($dbrows[0][1])) {
			//	License exists and is already in use
			errorGeneric("License_id_already_in_use", $API, $logParent);
		}
		//	If exists and not in use, check it is not expired
		$currentDate = strtotime(gmdate("Y-m-d H:i:s"));
		$licenseExpiry = strtotime($dbrows[0][0]);
		if ($currentDate >= $licenseExpiry) {
			errorGeneric("license_expired", $API, $logParent);
		}
		//	Store the
		$compare['license_hash'] = $dbrows[0][2];
		$insertArray['license_id'] = $sanitisedInput['license_id'];
	}

	if (isset($inputarray['license_hash'])){
		$sanitisedInput['license_hash'] = sanitise_input($inputarray['license_hash'],"license_hash", $schemainfoArray2['license_hash'], $API, $logParent);
		
		$sql = "SELECT 
				devicelicense.expdatetime
				, devices.devicelicense_devicelicense_id
				, devicelicense.license_hash
				, devicelicense.devicelicense_id
				FROM 
					devicelicense
				LEFT JOIN devices ON devices.devicelicense_devicelicense_id = devicelicense.devicelicense_id
				WHERE devicelicense.license_hash = '" . $sanitisedInput['license_hash']. "'";
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if(!isset($dbrows[0][0])){
			// license does not exist	
			errorInvalid("license_hash", $API, $logParent);
		}
		if (isset($dbrows[0][1])) {
			//	License exists and is already in use
			errorGeneric("License_hash_already_in_use", $API, $logParent);
		}
		//	If exists and not in use, check it is not expired
		$currentDate = strtotime(gmdate("Y-m-d H:i:s"));
		$licenseExpiry = strtotime($dbrows[0][0]);
		if ($currentDate >= $licenseExpiry) {
			errorGeneric("license_expired", $API, $logParent);
		}
		if (isset($inputarray['license_id'])){
			if ($insertArray['license_id'] != $dbrows[0][3]
				|| $compare['license_hash'] != $dbrows[0][2]
				) {
				errorInvalid("license_id_and_license_hash", $API, $logParent);
			}
		}
		else {
			$insertArray['license_id'] = $dbrows[0][3];	
		}
	}

	if(isset($inputarray['provisioning_id'])){				
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
	
		$sql = "SELECT 
				id 
				FROM 
				device_provisioning
				WHERE active_status = 0
				AND id = " . $sanitisedInput['provisioning_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if(!isset($dbrows[0][0])){
			// provisioning id does not exist or is inactive	
			errorInvalid("provisioning_id", $API, $logParent);
		}
		
		$insertArray['provisioning_id'] = $sanitisedInput['provisioning_id'];
		
		//	Given provisioning ID find the firmware files and latest version
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $insertArray['provisioning_id'])){
			$files = scandir($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $insertArray['provisioning_id']);
			$firmwareHexArray = array();	
			foreach($files as $file){
				$file_array = explode( '.', $file);
				if(isset($file_array[1])){
					if($file_array[1] == "hex"){
						//	For all .hex files push to array, we will use this again later 
						$firmwareHexArray[] = $file_array[0];
					}	
				}		
			}
			if (empty($firmwareHexArray)) {
				errorGeneric("Firmware_files_not_found", $API, $logParent);
			}
			$fv = 0;
			foreach ($firmwareHexArray as $hexFile) {
				$explode_fversion =  explode("_", $hexFile);
				if ($fv < $explode_fversion[1]){
					$fv = $explode_fversion[1];
					$sanitisedInput['latest_firmware'] = $hexFile;
				}
			}
			if(!isset($sanitisedInput['latest_firmware'])){
				errorGeneric("Latest_firmware_version_not_found", $API, $logParent);
			}
		}
		else{
			errorGeneric("Firmware_files_not_found", $API, $logParent);
		}
	}
	else {
		errorMissing("provisioning_id", $API, $logParent);
	}
			
	if(isset($inputarray['desired_version'])){
		$sanitisedInput['desired_version'] = sanitise_input($inputarray['desired_version'], "desired_version", $schemainfoArray['desired_version'], $API, $logParent);
		if (!in_array($sanitisedInput['desired_version'], $firmwareHexArray)) {
			errorGeneric("Desired_firmware_version_not_found", $API, $logParent);
		}
		$insertArray['desired_version'] = $sanitisedInput['desired_version'];
	}
	else{
		$insertArray['desired_version'] = $sanitisedInput['latest_firmware'];
	}

	if(isset($inputarray['desired_stored_versions'])){
		//	Possible input is an array of versions or a string of versions seperated by ","
		//	If input is not an array, first throw it into an array with explode
		if (!is_array($inputarray['desired_stored_versions'])) {
			$inputarray['desired_stored_versions'] = explode(",", $inputarray['desired_stored_versions']);
		}
		//	Note we sanitise it with stringlength based on a single desired version, ie. 10 characters max
		$sanitisedInput['desired_stored_versions'] = sanitise_input_array($inputarray['desired_stored_versions'],"desired_stored_versions",$schemainfoArray['desired_version'], $API, $logParent);
		//	If the desired version is not included in the desired stored versions then we need to add it in
		if (!in_array($insertArray['desired_version'], $sanitisedInput['desired_stored_versions'])){
			$sanitisedInput['desired_stored_versions'][] = $insertArray['desired_version'];
		}
		//	Sort alphabetically
		sort($sanitisedInput['desired_stored_versions']);
		$errorArray = array_values(array_diff($sanitisedInput['desired_stored_versions'], $firmwareHexArray));
		if (count($errorArray) != 0){
			$output = json_encode(
				array(
				'error' => "INVALID_" . strtoupper("desired_stored_versions") . "_PRAM",
				'error_detail' => $errorArray
				));
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);		
		}
		$insertArray['desired_stored_versions'] = implode(",", $sanitisedInput['desired_stored_versions']);
	}
	else{
		$insertArray['desired_stored_versions'] = $insertArray['desired_version'];
	}

	if (isset($inputarray['active_status'])){
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		$insertArray['active_status'] = 0;
	}

	$insertArray['configuration_version'] = 1;
	$insertArray['geofences_version'] = 1;
	$insertArray['triggers_version'] = 1;
	$insertArray["last_modified_by"] = $user_id;
	$insertArray["last_modified_datetime"] = $timestamp;

	//print_r($insertArray);
	//die();

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO devices (
			`device_name`
			, `configuration_version`
			, `geofences_version`
			, `triggers_version`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`
			, `desired_version`
			, `desired_stored_versions`
			, `device_provisioning_device_provisioning_id`
			, `devicelicense_devicelicense_id`
			, `device_sn`)
		VALUES (
			:device_name
			, :configuration_version
			, :geofences_version
			, :triggers_version
			, :active_status
			, :last_modified_by
			, :last_modified_datetime
			, :desired_version
			, :desired_stored_versions
			, :provisioning_id";
			if (isset($insertArray['license_id'])){
				$sql .= ", :license_id";
			}
			else {
				$sql .= ", NULL";
			}
			if (isset($insertArray['device_sn'])){
				$sql .= ", :device_sn)";
			}
			else {
				$sql .= ", NULL)";
			}

		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['device_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"" . $e . "\"}");
	}
	
	$sql = "SELECT 
		`device_custom_param`.`default_value`
		, '" . $insertArray['device_id'] . "'
		,`device_custom_param`.`id`
		,`device_custom_param_group_components`.`device_custom_param_group_id`

		FROM 
		(device_provisioning
		,device_provisioning_components)

		LEFT JOIN device_custom_param_group_components
		ON device_custom_param_group_components.device_custom_param_group_id = device_provisioning_components.device_component_id
		AND device_provisioning_components.device_component_type = 'Group Parameter'

		LEFT JOIN device_custom_param
		ON (device_custom_param.id=device_provisioning_components.device_component_id AND device_provisioning_components.device_component_type = 'Parameter')
		OR (device_custom_param.id= device_custom_param_group_components.device_custom_param_id AND device_provisioning_components.device_component_type = 'Group Parameter')
		WHERE 
		(device_provisioning_components.device_component_type = 'Group Parameter'
		OR device_provisioning_components.device_component_type = 'Parameter')

		AND device_provisioning_components.device_provisioning_device_provisioning_id = device_provisioning.id
		AND device_provisioning.id = " . $insertArray['provisioning_id'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		// print_r($dbrows);
	if (isset($dbrows[0][0])){
		try {
			$sql = "INSERT INTO `device_custom_param_values` 
				(`value`
				, `devices_device_id`
				, `device_custom_param_id`) 
				VALUES  ";

			foreach($dbrows as $dbrow){
				$sql .= "('" . $dbrow[0] . "', " . $dbrow[1] . ", " . $dbrow[2] . "),";
			}

			$sql = substr($sql, 0, -1);
			// echo $sql;
			$loggingArray['action'] = "insert";
			$loggingArray['table'] = "device_custom_param_values";
			$loggingArray['request'] = $sql;
			$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($loggingArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

			$stm = $pdo->prepare($sql);
			$stm->execute();
		}
		catch(PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die ("{\"error\":\"" . $e . "\"}");
		}

		$loggingArray['error'] = "NO_ERROR";
		$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($loggingArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
	}
}


// *******************************************************************************
// *******************************************************************************
// ***********************************UPDATE**************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "update"){

	$schemainfoArray = getMaxString ("devices", $pdo);
	$updateArray = [];
	$updateString = "";

	if(isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input($inputarray['device_id'],"device_id",$schemainfoArray['device_id'], $API, $logParent);
		//	SELECT query needs to pull down desired stored versions and provisioning ID as the value 
		$sql = "SELECT 
				  device_provisioning_device_provisioning_id
				, desired_version
				, desired_stored_versions
				, configuration_version
				, deviceasset_id
			FROM 
				devices
			LEFT JOIN deviceassets ON deviceassets.devices_device_id = devices.device_id
			AND deviceassets.date_to IS NULL
			WHERE device_id = " . $sanitisedInput['device_id'];

		$stm = $pdo->prepare($sql);
		$stm->execute();
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);		
		if (!isset($dbrows[0][0])){
			errorInvalid("device_id", $API, $logParent); 
		}
		//	Store the provisioning ID for later use if necessary
		$sanitisedInput['provisioning_id'] = $dbrows[0][0];
		$sanitisedInput['desired_version'] = $dbrows[0][1];
		$sanitisedInput['desired_stored_versions'] = $dbrows[0][2];
		$sanitisedInput['deviceasset_id'] = $dbrows[0][4];
		$updateString .= " `configuration_version` = :configuration_version,";
		$updateArray['device_id'] = $sanitisedInput['device_id'];
		$updateArray['configuration_version'] = $dbrows[0][3] + 1;

	}
	else {
		errorMissing("device_id", $API, $logParent);
	}

	if (isset($inputarray['device_name'])) {
		$updateArray['device_name'] = sanitise_input($inputarray['device_name'], "device_name", $schemainfoArray['device_name'], $API, $logParent);
		$updateString .= " `device_name` = :device_name,";
	}

	if (isset($inputarray['device_sn'])) {
		$sanitisedInput['device_sn'] = sanitise_input($inputarray['device_sn'], "device_sn", $schemainfoArray['device_sn'], $API, $logParent);
		if ($sanitisedInput['device_sn'] < 0) {
			if ($sanitisedInput['device_sn'] != -1) {
				errorInvalid("device_sn", $API, $logParent);
			}
			$output['device_sn'] = "NULL";
			$updateString .= " `device_sn` = NULL,";
		}
		else {
			$updateArray['device_sn'] = $sanitisedInput['device_sn'];
			$updateString .= " `device_sn` = :device_sn,";
		}
	}

	if (isset($inputarray['license_id'])) {
		$sanitisedInput['license_id'] = sanitise_input($inputarray['license_id'], "license_id", null, $API, $logParent);
		if ($sanitisedInput['license_id'] < 0) {
			if ($sanitisedInput['license_id'] != -1) {
				errorInvalid("license_id", $API, $logParent);
			}
			$output['license_id'] = "NULL";
			$updateString .= " `devicelicense_devicelicense_id` = NULL,";
		}
		else {
			$sql = "SELECT 
					devicelicense.expdatetime
					, devices.devicelicense_devicelicense_id
					, devicelicense.license_hash
				FROM 
					devicelicense
				LEFT JOIN devices ON devices.devicelicense_devicelicense_id = devicelicense.devicelicense_id
				WHERE devicelicense.devicelicense_id = " . $sanitisedInput['license_id'];
	
			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	
			if(!isset($dbrows[0][0])){
				// license does not exist	
				errorInvalid("license_id", $API, $logParent);
			}
			if (isset($dbrows[0][1])) {
				//	License exists and is already in use
				errorGeneric("License_id_already_in_use", $API, $logParent);
			}
			//	If exists and not in use, check it is not expired
			$currentDate = strtotime(gmdate("Y-m-d H:i:s"));
			$licenseExpiry = strtotime($dbrows[0][0]);
			if ($currentDate >= $licenseExpiry) {
				errorGeneric("license_expired", $API, $logParent);
			}
			$compare['license_hash'] = $dbrows[0][2];
			$updateArray['license_id'] = $sanitisedInput['license_id'];
			$updateString .= " `devicelicense_devicelicense_id` = :license_id,";
		}
	}

	if (isset($inputarray['license_hash'])) {
		$schemainfoArray2 = getMaxString ("devicelicense", $pdo);
		$sanitisedInput['license_hash'] = sanitise_input($inputarray['license_hash'], "license_hash", $schemainfoArray2['license_hash'], $API, $logParent);
		if ($sanitisedInput['license_hash'] < 0) {
			if ($sanitisedInput['license_hash'] != -1) {
				errorInvalid("license_hash", $API, $logParent);
			}
			if (isset($inputarray['license_id'])){
				if ($sanitisedInput['license_id'] != $sanitisedInput['license_hash'] ) {
					errorInvalid("license_id_and_license_hash", $API, $logParent);
				}
			}
			$output['license_id'] = "NULL";
			$updateString .= " `devicelicense_devicelicense_id` = NULL,";
		}
		else {
			$sql = "SELECT 
					devicelicense.expdatetime
					, devices.devicelicense_devicelicense_id
					, devicelicense.license_hash
					, devicelicense.devicelicense_id
				FROM 
					devicelicense
				LEFT JOIN devices ON devices.devicelicense_devicelicense_id = devicelicense.devicelicense_id
				WHERE devicelicense.license_hash = '" . $sanitisedInput['license_hash']. "'";
	
			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	
			if(!isset($dbrows[0][0])){
				// license does not exist	
				errorInvalid("license_hash", $API, $logParent);
			}
			if (isset($dbrows[0][1])) {
				//	License exists and is already in use
				errorGeneric("License_hash_already_in_use", $API, $logParent);
			}
			//	If exists and not in use, check it is not expired
			$currentDate = strtotime(gmdate("Y-m-d H:i:s"));
			$licenseExpiry = strtotime($dbrows[0][0]);
			if ($currentDate >= $licenseExpiry) {
				errorGeneric("license_expired", $API, $logParent);
			}
			if (isset($inputarray['license_id'])){
				if ($sanitisedInput['license_id'] != $dbrows[0][3]
					|| $compare['license_hash'] != $dbrows[0][2]
					) {
					errorInvalid("license_id_and_license_hash", $API, $logParent);
				}
			}
			else {
				$updateArray['license_id'] = $dbrows[0][3];
				$updateString .= " `devicelicense_devicelicense_id` = :license_id,";
			}
		}
	}

	if(isset($inputarray['provisioning_id'])){				
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
	
		$sql = "SELECT 
				id 
				FROM 
				device_provisioning
				WHERE active_status = 0
				AND id = " . $sanitisedInput['provisioning_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if(!isset($dbrows[0][0])){
			// provisioning id does not exist or is inactive	
			errorInvalid("provisioning_id", $API, $logParent);
		}

		$updateArray['provisioning_id'] = $sanitisedInput['provisioning_id'];
		$updateString .= " `device_provisioning_device_provisioning_id` = :provisioning_id,";
		//	Need to check if the desired versions and desired stored versions exist for this provisioning ID
	}

	if(isset($inputarray['desired_version'])){
		$sanitisedInput['desired_version'] = sanitise_input($inputarray['desired_version'], "desired_version", $schemainfoArray['desired_version'], $API, $logParent);
		$updateString .= " `desired_version` = :desired_version,";
	}

	if(isset($inputarray['desired_stored_versions'])){

		//	Possible input is an array of versions or a string of versions seperated by ","
		//	If input is not an array, first throw it into an array with explode
		if (!is_array($inputarray['desired_stored_versions'])) {
			$inputarray['desired_stored_versions'] = explode(",", $inputarray['desired_stored_versions']);
		}
		$sanitisedInput['desired_stored_versions'] = sanitise_input_array($inputarray['desired_stored_versions'],"desired_stored_versions", $schemainfoArray['desired_version'], $API, $logParent);
		foreach ($sanitisedInput['desired_stored_versions'] as $value) {
			if ($value < 0) {
				if ($value != -1) {
					errorInvalid("desired_stored_versions", $API, $logParent);
				}
				unset($sanitisedInput['desired_stored_versions']);
				$sanitisedInput['desired_stored_versions'] = array();
			}
		}
		
		$updateString .= " `desired_stored_versions` = :desired_stored_versions,";
	}
	
	//	If we are updating anything to do with firmware versions or provisioning ID we will need to sort things
	if (isset($inputarray['desired_version'])
		|| isset($inputarray['desired_stored_versions'])
		|| isset($inputarray['provisioning_id'])
		){
		//	Given provisioning ID find the firmware files and latest version
		//	Note: $sanitisedInput['provisioning_id'] will be the newly supplied provisioning ID if exists, else the stored value
		if (file_exists($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $sanitisedInput['provisioning_id'])){
			$files = scandir($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $sanitisedInput['provisioning_id']);
			$firmwareHexArray = array();	
			foreach($files as $file){
				$file_array = explode( '.', $file);
				if(isset($file_array[1])){
					if($file_array[1] == "hex"){
						//	For all .hex files push to array, we will use this again later 
						$firmwareHexArray[] = $file_array[0];
					}	
				}		
			}
			if (empty($firmwareHexArray)) {
				errorGeneric("Firmware_files_not_found", $API, $logParent);
			}
			$fv = 0;
			foreach ($firmwareHexArray as $hexFile) {
				$explode_fversion =  explode("_", $hexFile);
				if ($fv < $explode_fversion[1]){
					$fv = $explode_fversion[1];
					$sanitisedInput['latest_firmware'] = $hexFile;
				}
			}
			if(!isset($sanitisedInput['latest_firmware'])){
				errorGeneric("Latest_firmware_version_not_found", $API, $logParent);
			}
		}
		else{
			errorGeneric("Firmware_files_not_found", $API, $logParent);
		}

		if (isset($inputarray['desired_version'])) {
			if (!in_array($sanitisedInput['desired_version'], $firmwareHexArray)) {
				errorGeneric("Desired_firmware_version_not_found", $API, $logParent);
			}
			$updateArray['desired_version'] = $sanitisedInput['desired_version'];
		}
		else{
			if (isset($inputarray['provisioning_id'])) {
				$sanitisedInput['desired_version'] = $sanitisedInput['latest_firmware'];
				$updateArray['desired_version'] = $sanitisedInput['desired_version'];
				$updateString .= " `desired_version` = :desired_version,";
			}
		}

		if (isset($inputarray['desired_stored_versions'])) {
			if (!in_array($sanitisedInput['desired_version'], $sanitisedInput['desired_stored_versions'])){
				$sanitisedInput['desired_stored_versions'][] = $sanitisedInput['desired_version'];
			}
			sort($sanitisedInput['desired_stored_versions']);
			$errorArray = array_values(array_diff($sanitisedInput['desired_stored_versions'], $firmwareHexArray));
			if (count($errorArray) != 0){
				$output = json_encode(
					array(
					'error' => "INVALID_" . strtoupper("desired_stored_versions") . "_PRAM",
					'error_detail' => $errorArray
					));
				logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
				die ($output);		
			}
			$updateArray['desired_stored_versions'] = implode(",", $sanitisedInput['desired_stored_versions']);
		}
		else{
			//	If we are not updating the desired stored versions but we ARE updating the provisioning ID
			//	We need to check that the existing desired stored versions exist for the new provisioning ID
			if (isset($inputarray['provisioning_id'])) {
				$sanitisedInput['desired_stored_versions'] = explode(",", $sanitisedInput['desired_stored_versions']);
				$errorArray = array_values(array_diff($sanitisedInput['desired_stored_versions'], $firmwareHexArray));
				if (count($errorArray) != 0){
					$output = json_encode(
						array(
							'error' => "INVALID_" . strtoupper("desired_stored_versions") . "_PRAM",
							'error_detail' => $errorArray
						));
					logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
					die ($output);		
				}
				if (!in_array($sanitisedInput['desired_version'], $sanitisedInput['desired_stored_versions'])){
					$sanitisedInput['desired_stored_versions'][] = $sanitisedInput['desired_version'];
					$updateString .= " `desired_stored_versions` = :desired_stored_versions,";
				}
			}
		}

		if (isset($inputarray['provisioning_id'])){
			if (isset($sanitisedInput['deviceasset_id'])){
				//	Provisioning ID modification not allowed if the device is currently associated with an asset
				errorGeneric("provisioning_id_locked", $API, $logParent);
			}
			//	Delete existing triggers from device
			try {
				$deleteArray ['action'] = "delete";
				$deleteArray ['table'] = "device_custom_param_value";
				$deleteArray ['device_id'] = $updateArray['device_id'];

				$sql = "DELETE FROM device_custom_param_values 
					WHERE devices_device_id = " . $updateArray["device_id"];

				$logParent2 = logEvent($API . logText::request . str_replace('"', '\"', json_encode($deleteArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

				$stm= $pdo->prepare($sql);
				$stm->execute();
			}
			catch(PDOException $e){
				logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent2);
				die("{\"error\":\"$e\"}");
			}
			$deleteArray ['error'] = "NO_ERROR";
			$logParent2 = logEvent($API . logText::response . str_replace('"', '\"', json_encode($deleteArray)), logLevel::response, logType::response, $token, $logParent2)['event_id'];
			
			//	Re-associate sensors and default values based on the new provisioning ID
		
			$sql = "SELECT 
			`device_custom_param`.`default_value`
			, '" . $updateArray['device_id'] . "'
			,`device_custom_param`.`id`
			,`device_custom_param_group_components`.`device_custom_param_group_id`
	
			FROM 
			(device_provisioning
			,device_provisioning_components)
	
			LEFT JOIN device_custom_param_group_components
			ON device_custom_param_group_components.device_custom_param_group_id = device_provisioning_components.device_component_id
			AND device_provisioning_components.device_component_type = 'Group Parameter'
	
			LEFT JOIN device_custom_param
			ON (device_custom_param.id=device_provisioning_components.device_component_id AND device_provisioning_components.device_component_type = 'Parameter')
			OR (device_custom_param.id= device_custom_param_group_components.device_custom_param_id AND device_provisioning_components.device_component_type = 'Group Parameter')
			WHERE 
			(device_provisioning_components.device_component_type = 'Group Parameter'
			OR device_provisioning_components.device_component_type = 'Parameter')
	
			AND device_provisioning_components.device_provisioning_device_provisioning_id = device_provisioning.id
			AND device_provisioning.id = " . $updateArray['provisioning_id'];
			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	
			if (isset($dbrows[0][0])){
				try {
					$sql = "INSERT INTO `device_custom_param_values` 
						(`value`
						, `devices_device_id`
						, `device_custom_param_id`
						) 
						VALUES  ";
		
					foreach($dbrows as $dbrow){
						$sql .= "('" . $dbrow[0] . "', " . $dbrow[1] . ", " . $dbrow[2] . "),";
					}

					$sql = substr($sql, 0, -1);
		
					$loggingArray['action'] = "insert";
					$loggingArray['table'] = "device_custom_param_values";
					$loggingArray['request'] = $sql;
		
					$logParent2 = logEvent($API . logText::request . str_replace('"', '\"', json_encode($loggingArray)), logLevel::request, logType::request, $token, $logParent2)['event_id'];
		
					$stm = $pdo->prepare($sql);
					$stm->execute();
				}
				catch(PDOException $e){
					logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent2);
					die ("{\"error\":\"" . $e . "\"}");
				}
				$loggingArray['error'] = "NO_ERROR";
				$logParent2 = logEvent($API . logText::response . str_replace('"', '\"', json_encode($loggingArray)), logLevel::response, logType::response, $token, $logParent2)['event_id'];

			}
		}
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
		else {
			$sql = "UPDATE 
					devices 
				SET ". $updateString . " `last_modified_by` = $user_id
                , `last_modified_datetime` = '$timestamp'
                WHERE `device_id` = :device_id";
				//echo $sql;
				//print_r($updateArray);
				$stm= $pdo->prepare($sql);
            if($stm->execute($updateArray)){
				if (isset($output['device_sn'])){
					$updateArray ['device_sn' ] = $output['device_sn'];
				}
				if (isset($output['license_id'])){
					$updateArray ['license_id' ] = $output['license_id'];
				}
                $updateArray ['error' ] = "NO_ERROR";
                echo json_encode($updateArray);
				$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
            }
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
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