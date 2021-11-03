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
		, devices.configuration_version
		, devices.geofences_version
		, devices.triggers_version
		, devices.active_status
		, devices.last_modified_by
		, devices.last_modified_datetime
		, devices.desired_version
		, devices.firmware_version
		, devices.update_authorized
		, devices.desired_stored_versions
		, devices.connectors_connector_id
		, devices.device_provisioning_device_provisioning_id
		, devicelicense.license_hash
		, devicelicense.expdatetime
		, devices.device_sn
		, devicelicense.devicelicense_id
		FROM devices
		LEFT JOIN devicelicense 
		ON devices.devicelicense_devicelicense_id = devicelicense.devicelicense_id 
		WHERE 1=1";

	if (isset($inputarray['device_id'])){
		$inputarray['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `devices`.`device_id` = '". $inputarray['device_id'] ."'";
	}

	if (isset($inputarray['device_sn'])){
		$inputarray['device_sn'] = sanitise_input($inputarray['device_sn'], "device_sn", $schemainfoArray['device_sn'], $API, $logParent); //sanitise_input($inputarray['device_sn'], "device_sn", null);
		$sql .= " AND `devices`.`device_sn` = '". $inputarray['device_sn'] ."'";
	}
	
	if (isset($inputarray['device_license_id'])){
		$inputarray['device_license_id'] = sanitise_input($inputarray['device_license_id'], "device_license_id", null, $API, $logParent);
		$sql .= " AND `devices`.`devicelicense_devicelicense_id` = '". $inputarray['device_license_id'] ."'";
	}
	
	if (isset($inputarray['active_status'])){
		$inputarray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `devices`.`active_status` = '". $inputarray['active_status'] ."'";
	}

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
	
	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_assets = array();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_asset = array(
			"device_id" => $dbrow[0]
			, "device_name" => $dbrow[1]
			, "configuration_version" => $dbrow[2]
			, "geofences_version" => $dbrow[3]
			, "triggers_version" => $dbrow[4]
			, "active_status" => $dbrow[5]
			, "last_modified_by" => $dbrow[6]
			, "last_modified_datetime" => $dbrow[7]
			, "desired_version" => $dbrow[8]
			//, "device_type" => $dbrow[9]
			, "firmware_version" => $dbrow[9]
			, "update_authorized" => $dbrow[10]
			, "desired_stored_versions" => $dbrow[11]
			, "provisioning_id" => $dbrow[13]
			, "license_id" => $dbrow[17]
			, "license_hash" => $dbrow[14]
			, "license_exp_datetime" => $dbrow[15]
			, "device_sn" => $dbrow[16]
			);
			$json_assets = array_merge($json_assets,array("device $outputid" => $json_asset));
			$outputid++;
		}
		$json = array("devices" => $json_assets);
		echo json_encode($json);
	}
	else{
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
	$insertArray = [];

	/*if(isset($inputarray['device_type'])){
		$device_type = strtoupper($inputarray['device_type']);
		switch ($device_type){
			case "PSM":
				$device_type = "PSM";
			break;
			case "GAM":
				$device_type = "GAM";
			break;
			case "MAP":
				$device_type = "MAP";
			break;
			case "GAM2":
				$device_type = "GAM2";
			break;
			default;
			die("MISSING_DEVICE_TYPE_PRAM");
		}
		$data["device_type"] = $device_type;
	}
	else{
			die("{\"error\":\"MISSING_DEVICE_TYPE_PRAM\"}");
		}*/

	if(isset($inputarray['device_name'])){
		$inputarray['device_name'] = sanitise_input($inputarray['device_name'],"device_name",$schemainfoArray['device_name'], $API, $logParent);
		$data["device_name"] = $inputarray['device_name'];
	}
	else{
			die("{\"error\":\"MISSING_DEVICE_NAME_PRAM\"}");
		}

	if(isset($inputarray['device_sn'])){
		/*if(empty($inputarray['devices_SN'])){	
			die("{\"error\":\"MISSING_DEVICE_SN_PRAM444TEST\"}");
		}		*/
	}
	else{
			die("{\"error\":\"MISSING_DEVICE_SN_PRAM\"}");
		}

	$devices_sn = sanitise_input($inputarray['device_sn'], "device_sn", $schemainfoArray['device_sn'], $API, $logParent);
	$devices_sn = $inputarray['device_sn'];
	$data["devices_sn"] = $inputarray['device_sn'];
	$sql = "SELECT device_sn FROM devices WHERE device_sn = ". "'" .$devices_sn . "'" ." AND active_status = 0";
	//echo $sql;
	$stm = $pdo->prepare($sql);
	$stm->execute();
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if(!isset($rows[0][0])){
		$data["devices_sn"] = $inputarray['device_sn'];
	}
	else{
		die("{\"error\":\"DEVICE_SN_ALREADY_IN_USED\"}");
	}	

	if(isset($inputarray['device_license_id'])){
		$devicelicense_id = sanitise_input($inputarray['device_license_id'],"device_license_id",$schemainfoArray['devicelicense_devicelicense_id'], $API, $logParent);
		if($devicelicense_id < 0){
			die("{\"error\":\"INVALID_DEVICELICENSE_ID_PRAM\"}");
		}
		$sql = "SELECT devicelicense_devicelicense_id FROM devices WHERE devicelicense_devicelicense_id = $devicelicense_id";
		//echo $sql;
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if(!isset($rows[0][0])){					
			$sql = "SELECT expdatetime FROM devicelicense WHERE devicelicense_id = $devicelicense_id";
			//echo $sql;
			$stm = $pdo->prepare($sql);
			$stm->execute();
			$devicelicenserows = $stm->fetchAll(PDO::FETCH_NUM);
			if(isset($devicelicenserows[0][0])){
				$current_datetime = gmdate("Y-m-d H:i:s");
				$expired_datetime = $devicelicenserows[0][0];
				$current_date = strtotime($current_datetime . " +0000");
				$expired_date = strtotime($expired_datetime . " +0000");

				if($current_date >= $expired_date){
					die("{\"error\":\"LICENSE_EXPIRED\"}");
				}
			}
				$data["devicelicense_id"] = $inputarray['device_license_id'];
		}
		else{
				$data["devicelicense_id"] = NULL;
		}
	}else{
			$data["devicelicense_id"] = NULL;
		}

	if(isset($inputarray['device_provisioning_id'])){				
		$device_provisioning_id = sanitise_input($inputarray['device_provisioning_id'],"device_provisioning_id",$schemainfoArray['device_provisioning_device_provisioning_id'], $API, $logParent);
		if($device_provisioning_id < 0){
			die("{\"error\":\"INVALID_DEVICE_PROVISIONING_ID\"}");
		}		
		$sql = "SELECT id FROM device_provisioning WHERE id = $device_provisioning_id AND active_status = 0";
		$stm = $pdo->prepare($sql);
		$stm->execute();
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if(isset($rows[0][0])){
			$data["device_provisioning_id"] = $inputarray['device_provisioning_id'];
		}
		else{
			die("{\"error\":\"INVALID_DEVICE_PROVISIONING_ID_PRAM\"}");
		}
	}
	else{
			die("{\"error\":\"MISSING_DEVICE_PROVISIONING_ID_PRAM\"}");
		}

	
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $device_provisioning_id)){
		$files = scandir($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $device_provisioning_id);	
		$fv = 0;
		foreach($files as $file){
			$file_array = explode( '.', $file);
		    if(isset($file_array[1])){
				if($file_array[1] == "hex"){
					$firmware = $file_array[0];
					$explode_fversion =  explode("_", $firmware);
					if ($fv < $explode_fversion[1]){
						$fv = $explode_fversion[1];
						$latest_firmware_version = $firmware;
					}
				}	
			}		
		}
		
		if(!isset($latest_firmware_version)){
			die("{\"error\":\"MISSING_LATEST_FIRMWARE_VERSION\"}");
		}
	}
	else{
		die("{\"error\":\"MISSING_FIRMWARE_FILES_PRAM\"}");
	}
			
	if(isset($inputarray['desired_version'])){
		$inputarray['desired_version'] = sanitise_input($inputarray['desired_version'],"desired_version",$schemainfoArray['desired_version'], $API, $logParent);
		$found = 0;				
		foreach($files as $file){
			$file_array = explode( '.', $file);
			$firmware_version = $file_array[0];
			if($file_array[1] == "hex"){
				if($inputarray['desired_version'] == $firmware_version){									      
					$found = 1;
				}
			}	
		}
		if($found == 0){
			die("{\"error\":\"FIRMWARE_VERSION_NOT_FOUND\"}");
		}
	}
	else{
		//$data["desired_version"] = $latest_firmware_version;
		$inputarray['desired_version'] = $latest_firmware_version;
	}

	if(isset($inputarray['desired_stored_versions'])){
		$inputarray['desired_stored_versions'] = sanitise_input($inputarray['desired_stored_versions'],"desired_stored_versions",$schemainfoArray['desired_stored_versions'], $API, $logParent);
		if(!is_array($inputarray['desired_stored_versions'])){
			$inputarray['desired_stored_versions'] = [$inputarray['desired_stored_versions']];
		}
							
		foreach ($inputarray['desired_stored_versions'] as $storeversion){
			$found = 0;			
			foreach($files as $file){
				$file_array = explode( '.', $file);											
				if(substr($file, -3) == "hex"){
					$firmware_version = $file_array[0];			
					if($storeversion == $firmware_version){									      
						$found = 1;
					}
				}	
			}					
		}
		if ($found == 1) {
			$found = 0;
		}else {
			die("{\"error\":\"DESIRED_STORED_VERSION_NOT_FOUND\"}");
		}
	}
	else{
		$inputarray['desired_stored_versions'] = [$latest_firmware_version];
	}

	$storeversions = implode(",",$inputarray['desired_stored_versions']);
	if(strpos(($storeversions),$inputarray['desired_version']) === false){
		die("{\"error\":\"INVALID_DESIRED_STORE_VERSIONS_PRAM\"}");
	}else{
		$data["desired_stored_versions"] = $storeversions;
		$data["desired_version"] = $inputarray['desired_version'];
	}

	if(isset($inputarray['update_authorized'])){
		$update_authorized = sanitise_input($inputarray['update_authorized'],"update_authorized",$schemainfoArray['update_authorized'], $API, $logParent);
		if ($update_authorized < 0){
			die("{\"error\":\"MISSING_UPDATE_AUTHORIZED_PRAM\"}");
		}
		$data["update_authorized"] = $update_authorized;
	}else{
			$data["update_authorized"] = 0;
		}

	if(isset($inputarray['active_status'])){
		$active_status = sanitise_input($inputarray['active_status'],"active_status",$schemainfoArray['active_status'], $API, $logParent);
		if ($active_status == "0" || $active_status == "1"){
		}else{
			die("{\"error\":\"MISSING_ACTIVE_STATUS_PRAM\"}");
		}
	}else {
		$active_status = 0; 
	}

	$data["active_status"] = $active_status;
	$data["last_modified_by"] = $user_id;
	$data["last_modified_datetime"] = $timestamp;
	$data["firmware_json"] = "0";
	$data["firmware_version"] = "0";
	$data["connectors_connector_id"] = "0";

	//print_r($data);
	$sql = "INSERT INTO devices(device_name,active_status,last_modified_by,last_modified_datetime,desired_version,update_authorized,
			desired_stored_versions,device_provisioning_device_provisioning_id,devicelicense_devicelicense_id,device_sn,
			firmware_json,firmware_version,connectors_connector_id)";
	$sql = $sql . " VALUES (:device_name, :active_status, :last_modified_by, :last_modified_datetime, :desired_version, 
					:update_authorized,:desired_stored_versions,:device_provisioning_id, :devicelicense_id,:devices_sn,
					:firmware_json,:firmware_version,:connectors_connector_id)";
	//echo $sql;
	$stmt= $pdo->prepare($sql);
	if($stmt->execute($data)){
		$data['device_id'] = $pdo->lastInsertId();
		$data ['error' ] = "NO_ERROR";
	}else{
		die("{\"error\":\"ERROR\"}");
	}  
	
	$sql = "SELECT 
		`device_custom_param`.`default_value`
		, '" . $data['device_id'] . "'
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
		AND device_provisioning.id = $device_provisioning_id;";
	
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		//print_r($dbrows);
		$sql = "INSERT INTO `device_custom_param_values` (`value`, `devices_device_id`, `device_custom_param_id`, `device_custom_param_group_id`) VALUES 
	";
	//(409, 'livedemo.usm.net.au', 'url', 11, 9, NULL),
		foreach($dbrows as $dbrow){
			if ($dbrow[3] == ""){
				$gid = "NULL";
			}else{
				$gid = $dbrow[3];
			}
			
			$sql .= "('" . $dbrow[0] . "', " . $dbrow[1] . ", " . $dbrow[2] . ", " . $gid . "),";
			
		}
		$sql = substr($sql, 0, -1);
	}
	
	//echo $sql;
	$stmt= $pdo->prepare($sql);
	if($stmt->execute()){
		//$data['device_id'] = $pdo->lastInsertId();
		//$data ['error' ] = "NO_ERROR";
		echo json_encode($data);
	}else{
		die("{\"error\":\"ERROR\"}");
	}  
	
	
}

	

// *******************************************************************************
// *******************************************************************************
// ***********************************update**************************************
// *******************************************************************************
// ******************************************************************************* 


elseif($sanitisedInput['action'] == "update"){
	$schemainfoArray = getMaxString ("devices", $pdo);
	$data = [];
	if(isset($inputarray['device_id'])){
		$device_id = sanitise_input($inputarray['device_id'],"device_id",$schemainfoArray['device_id'], $API, $logParent);
		if ($device_id < 0){
			die("{\"error\":\"INVALID_DEVICE_ID\"}");
		}
	}
	else{
			die("{\"error\":\"INVALID_DEVICE_ID\"}");
		}

	$sql = "SELECT device_provisioning_device_provisioning_id,desired_stored_versions, desired_version 
			FROM devices 
			WHERE device_id = $device_id AND active_status = 0";	
	$stm = $pdo->prepare($sql);
	$stm->execute();
	$dbdevicesrows = $stm->fetchAll(PDO::FETCH_NUM);
	if(isset($dbdevicesrows[0][0])){
			$device_provisioning_id = $dbdevicesrows[0][0];
			$db_desired_store_version = $dbdevicesrows[0][1];
			$db_desired_version = $dbdevicesrows[0][2];
	}else{
			die("{\"error\":\"INVALID_DEVICE_ID\"}");
	}

	/*if(isset($db_device_type)){
		$device_type = strtoupper($db_device_type);
		switch ($device_type){
			case "PSM":
				$device_type = "PSM";
			break;
			case "GAM":
				$device_type = "GAM";
			break;
			case "MAP":
				$device_type = "MAP";
			break;
			case "GAM2":
				$device_type = "GAM2";
			break;
			default;
			die("INVALID_DEVICE_TYPE_PRAM");
		}
	}
	else{
			die("{\"error\":\"INVALID_DEVICE_TYPE_PRAM\"}");
		}
*/
	
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $device_provisioning_id)){
		$files = scandir($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $device_provisioning_id);
		if(isset($inputarray['desired_version'])){
			$inputarray['desired_version'] = sanitise_input($inputarray['desired_version'],"desired_version",$schemainfoArray['desired_version'], $API, $logParent);
			$found = 0;				
			foreach($files as $file){
				$file_array = explode( '.', $file);
				//print_r($file_array);
				$firmware_version = $file_array[0];
			    if(isset($file_array[1])){
					if($file_array[1] == "hex"){
						if($inputarray['desired_version'] == $firmware_version){									      
							$found = true;
						}
					}	
				}
			}

			if($found == 0){
				die("{\"error\":\"FIRMWARE_VERSION_NOT_FOUND\"}");
			}
		}
	}
	else{
		die("{\"error\":\"MISSING_FIRMWARE_FILES_PRAM\"}");
	}

	
		if (isset($inputarray['desired_stored_versions'])){
			if(!is_array($inputarray['desired_stored_versions'])) {
				$inputarray['desired_stored_versions'] = [$inputarray['desired_stored_versions']];
			}
			$desired_stored_versions = [];
			foreach ($inputarray['desired_stored_versions'] as $desired_stored_versions){
				$filteredArray_desired_stored_versions[] = sanitise_input($desired_stored_versions,"desired_stored_versions",$schemainfoArray['desired_stored_versions'], $API, $logParent);
			}
		}

	if(isset($filteredArray_desired_stored_versions)){

		$storeversions = implode(",",$filteredArray_desired_stored_versions);
		foreach ($filteredArray_desired_stored_versions as $version){
			$found = 0;				
			foreach($files as $file){
				$file_array = explode( '.', $file);
				$firmware_version = $file_array[0];
				if(isset($file_array[1])){
					if($file_array[1] == "hex"){
						if($version == $firmware_version){									      
							$found = 1;
						}
					}	
				}						
			}
		}

		if ($found == 1) {
			$found = 0;
		}else {
			die("{\"error\":\"DESIRED_STORED_VERSION_NOT_FOUND\"}");
		}
	}

	$sqlupdate = "UPDATE devices SET";
	if(isset($inputarray['desired_stored_versions']) && isset($inputarray['desired_version'])){		
		$inputarray['desired_stored_versions'] = sanitise_input($inputarray['desired_stored_versions'],"desired_stored_versions",$schemainfoArray['desired_stored_versions'], $API, $logParent);
		$inputarray['desired_version'] = sanitise_input($inputarray['desired_version'],"desired_version",$schemainfoArray['desired_version'], $API, $logParent);
		if(strpos(($storeversions),$inputarray['desired_version']) === false){
			die("{\"error\":\"INVALID_DESIRED_STORE_VERSIONS\"}");
		}else{					
			$data["desired_stored_versions"] = $storeversions;
			$data["desired_version"] = $inputarray['desired_version'];
			$sqlupdate = $sqlupdate . " desired_version = :desired_version, desired_stored_versions = :desired_stored_versions,";
		}
	}else
		{
			if(isset($inputarray['desired_stored_versions'])){		
				$inputarray['desired_stored_versions'] = sanitise_input($inputarray['desired_stored_versions'],"desired_stored_versions",$schemainfoArray['desired_stored_versions'], $API, $logParent);
				if(strpos(($storeversions),$db_desired_version) === false){
					die("{\"error\":\"DESIRED_VERSION_NOT_FOUND\"}");
				}else{							
					$data["desired_stored_versions"] = $storeversions;
					$sqlupdate = $sqlupdate . " desired_stored_versions = :desired_stored_versions,";
				}
			}
			if(isset($inputarray['desired_version'])){
				$inputarray['desired_version'] = sanitise_input($inputarray['desired_version'],"desired_version",$schemainfoArray['desired_version'], $API, $logParent);
				if(strpos(($db_desired_store_version),$inputarray['desired_version']) === false){
					die("{\"error\":\"DESIRED_STORE_VERSIONS_NOT_FOUND\"}");
				}else{							
					$data["desired_version"] = $inputarray['desired_version'];
					$sqlupdate = $sqlupdate . " desired_version = :desired_version,";
				}
			}
		}

	if(isset($inputarray['update_authorized'])){
		$update_authorized = sanitise_input($inputarray['update_authorized'],"update_authorized",$schemainfoArray['update_authorized'], $API, $logParent);
		if ($update_authorized < 0){
			die("{\"error\":\"INVALID_UPDATE_AUTHORIZED\"}");
		}
		$data["update_authorized"] = $update_authorized;
		$sqlupdate = $sqlupdate . " update_authorized = :update_authorized,";
	}
	
	if(isset($inputarray['device_license_id'])){
		$data["device_license_id"] = sanitise_input($inputarray['device_license_id'],"device_license_id",$schemainfoArray['devicelicense_devicelicense_id'], $API, $logParent);
		$sqlupdate = $sqlupdate . " devicelicense_devicelicense_id = :device_license_id,";
	}

	if(count($data) > 0){
		$data["device_id"] = $device_id;
		$sqlupdate = substr($sqlupdate, 0, -1) . " WHERE device_id = :device_id";			
		try
		{	
			$stmt= $pdo->prepare($sqlupdate);                   
			if($stmt->execute($data)){
				$data ['error' ] = "NO_ERROR";
				echo json_encode($data);
			}else{
				die("{\"error\":\"ERROR\"}");
			}  
			
		}catch(\PDOException $e){
			echo "{\"error\":\"" . $e->getMessage() . "\"}";
			exit();
		}	
	}
	else{
			die("{\"error\":\"MISSING_UPDATE_PRAMS\"}"); 
		}						
}
else{
		die ("{\"error\":\"INVALID_REQUEST_PRAM\"}");
	}

	$pdo = null;
	$stm = null;
?>