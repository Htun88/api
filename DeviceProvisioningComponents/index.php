<?php

$API = "DeviceProvisioningComponents";
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

  $schemainfoArray = getMaxString ("device_provisioning_components", $pdo);

  $sql = "SELECT 
			device_provisioning_components.device_provisioning_device_provisioning_id
			, device_provisioning_components.device_component_type
			, device_provisioning_components.device_component_id
			, CONCAT_WS('',sensors.sensor_name, device_custom_param.name, device_custom_param_group.name) AS name
			, device_provisioning_components.active_status
			, device_provisioning_components.last_modified_by
			, device_provisioning_components.last_modified_datetime
			, device_provisioning_components.id
		FROM
			device_provisioning_components
		LEFT JOIN sensors ON device_provisioning_components.device_component_id = sensors.sensor_id AND device_component_type = 'Sensor'
		LEFT JOIN device_custom_param ON device_provisioning_components.device_component_id = device_custom_param.id AND device_component_type = 'Parameter'
		LEFT JOIN device_custom_param_group ON device_provisioning_components.device_component_id = device_custom_param_group.id AND device_component_type = 'Group Parameter'
		WHERE 1=1";

	if (isset($inputarray['provisioning_component_id'])) {		
		$sanitisedInput['provisioning_component_id'] = sanitise_input_array($inputarray['provisioning_component_id'], "provisioning_component_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_components`.`device_provisioning_device_provisioning_id` IN ( '" . implode("', '", $sanitisedInput['id']) ."' )";
	}  
    
	if (isset($inputarray['provisioning_id'])) {		
		$sanitisedInput['provisioning_id'] = sanitise_input_array($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_components`.`device_provisioning_device_provisioning_id` IN ( '" . implode("', '", $sanitisedInput['provisioning_id']) ."' )";
	}  

	if (isset($inputarray['component_type'])) {		
		$sanitisedInput['component_type'] = sanitise_input_array($inputarray['component_type'], "component_type", $schemainfoArray['device_component_type'], $API, $logParent);
		$sql .= " AND `device_provisioning_components`.`device_component_type` IN  ( '" . implode("', '", $sanitisedInput['component_type']) . "' )";
	}  

	if (isset($inputarray['component_id'])) {		
		$sanitisedInput['component_id'] = sanitise_input_array($inputarray['component_id'], "component_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_components`.`device_component_id` IN ( '" . implode("', '", $sanitisedInput['component_id']) ."' )";
	}  

	if (isset($inputarray['active_status'])) {		
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `device_provisioning_components`.`active_status` = '" . $sanitisedInput['active_status'] . "'";
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
	if(isset($rows[0][0])){
		$jsonParent = array();
		$outputid = 0;
		foreach($rows as $row) {
		$jsonChild = array(
			"provisioning_component_id" => $row[7]
			, "provisioning_id" => $row[0]
			, "component_type" => $row[1]
			, "component_id" => $row[2]
			, "name" => $row[3]
			, "active_status" => $row[4]
			, "last_modified_by" => $row[5]
			, "last_modified_datetime" => $row[6] );
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


elseif($sanitisedInput['action'] == "insert"){

    $schemainfoArray = getMaxString ("device_provisioning_components", $pdo);
    $insertArray = [];

    if (isset($inputarray['provisioning_id'])) {		
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
		$sql = "SELECT 
				id 
			FROM 
				device_provisioning 
			WHERE id = " .  $sanitisedInput['provisioning_id'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){                    
			errorInvalid("provisioning_id", $API, $logParent);
		}
		$insertArray['provisioning_id'] = $sanitisedInput['provisioning_id'];
    }
    else{
      errorMissing("provisioning_id", $API, $logParent);
    }
 
    if (isset($inputarray['component_type'])) {		
		$insertArray['component_type'] = sanitise_input($inputarray['component_type'], "component_type", $schemainfoArray['device_component_type'], $API, $logParent);
		//	Inputs are int 0, 1, 2 or their corrosponding strings: "Sensor", "Parameter", "Group Parameter"
	}
    else{
      	errorMissing("component_type", $API, $logParent);
    }

    if (isset($inputarray['active_status'])){		
      	$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
    }  
    else{
      	$insertArray['active_status'] = 0;
    }

    if (isset($inputarray['component_id'])) {		
		$sanitisedInput['component_id'] = sanitise_input_array($inputarray['component_id'], "component_id", null, $API, $logParent);

		//	This section checks to find if these values already exist for this given device provisioning component, given the provisioning ID and component Type

		switch ($insertArray['component_type']){
			CASE "Sensor" :
				$sql = "SELECT sensor_id FROM sensors WHERE sensor_id IN ( '" . implode("', '", $sanitisedInput['component_id']) . "' )";
				BREAK;

			CASE "Parameter" :
				$sql = "SELECT id FROM device_custom_param WHERE id IN ( '" . implode("', '", $sanitisedInput['component_id']) . "' )";
				BREAK;

			CASE "Group Parameter" : 
				$sql = "SELECT id FROM device_custom_param_group WHERE id IN ( '" . implode("', '", $sanitisedInput['component_id']) . "' )";
				BREAK;
		}

		$stm = $pdo -> query($sql);
		$dbrows = $stm -> fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			//	str_replace to get rid of potential space character in Group Parameter
			errorInvalid("component_" . str_replace(" ", "_", $insertArray['component_type']) . "_id", $API, $logParent);
		}

		arrayExistCheck ($sanitisedInput['component_id'], array_column($dbrows, 0), "component_" . str_replace(" ", "_", $insertArray['component_type']) . "_id", $API, $logParent);

		$sql = "SELECT device_component_id  
				FROM device_provisioning_components
				WHERE device_provisioning_device_provisioning_id = '" . $insertArray['provisioning_id'] . "'
				AND device_component_type = '" . $insertArray['component_type'] . "'
				AND device_component_id IN ( '" . implode("', '", $sanitisedInput['component_id']) . "' )";       

		$stm = $pdo -> query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if (isset($dbrows[0][0])){
			//	This does work, but is not necessary as we are using the "insert, on duplicate keys update" method. 
			//	If that method changes, uncomment this. -Conor
			//arrayDoesntExistCheck ($sanitisedInput['component_id'], array_column($dbrows, 0), "duplicate_" . str_replace(" ", "_", $insertArray['component_type']) . "_id", $API, $logParent);
		}
		$data = array();
		foreach ($sanitisedInput['component_id'] as $component_id){
			$comIDArray[] = $component_id;
			$data[] .= "( " . $insertArray['provisioning_id'] . ", " . $component_id . ", '" .  $insertArray['component_type']  . "', " .  $insertArray['active_status']  . ", " . $user_id . ", '" . $timestamp . "')";      
		}
	}
    else{
     	errorMissing("component_id", $API, $logParent);
    }
  
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$sql = "INSERT INTO device_provisioning_components(
		`device_provisioning_device_provisioning_id`
		, `device_component_id`
		, `device_component_type`
		, `active_status`
		, `last_modified_by`
		, `last_modified_datetime`)
	VALUES " . implode(', ', $data) . "
	ON DUPLICATE KEY UPDATE
		active_status = VALUES(active_status)
		, last_modified_by = VALUES(last_modified_by)
		, last_modified_datetime = VALUES(last_modified_datetime)";	

	try{        
        $stmt= $pdo->prepare($sql);
        if($stmt->execute()){
         	$outputArray['provisioning_id'] = $insertArray['provisioning_id'];
         	$outputArray['component_type'] = $insertArray['component_type'];
         	$outputArray['component_id'] = $comIDArray;
         	$outputArray['active_status'] = $insertArray['active_status'];
         	$outputArray['last_modified_by'] = $user_id;
         	$outputArray['last_modified_datetime'] = $timestamp;
         	$outputArray['error'] = "NO_ERROR";
         	echo json_encode($outputArray);
         	$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];      
        }

	}catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
	
	//	Wipe $data array just in case something goes fucky
	unset($data); 

	// Update device version
	$data = [   
		'provisioning_id' => $insertArray['provisioning_id']
		, 'last_modified_by' => $user_id
		, 'last_modified_datetime' => gmdate("Y-m-d H:i:s")
	];

	$sql = "UPDATE 
			devices 
		SET configuration_version = (configuration_version + 1)
			, last_modified_by = :last_modified_by
			, last_modified_datetime = :last_modified_datetime 
		WHERE device_provisioning_device_provisioning_id = :provisioning_id 
		AND active_status = 0";		

	$stmt= $pdo->prepare($sql);
	if ($stmt->execute($data)){
	
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"device configuration version update failed\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}	
}

// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************


else if ($sanitisedInput['action'] == "update") {

	$updateArray = [];
	$updateString = "";
    $schemainfoArray = getMaxString ("device_provisioning_components", $pdo);

	//	Either update the actiev_status by the ID, or provide a unique provisioning ID and component type AND an array of component IDs
    if (isset($inputarray['provisioning_component_id'])){
        if (isset($inputarray['provisioning_id'])
            || isset($inputarray['component_type'])
			|| isset($inputarray['component_id'])
            ){
            errorInvalid("INCOMPATABLE_IDENTIFICATION_PARAMS", $API, $logParent);
        }
    }
    else {
        if (!isset($inputarray['provisioning_id'])){
            errorMissing("provisioning_id", $API, $logParent);
        }
        if (!isset($inputarray['component_type'])){
            errorMissing("component_type", $API, $logParent);
        }
		if (!isset($inputarray['component_id'])){
            errorMissing("component_id", $API, $logParent);
        }
	}

	if (isset($inputarray['provisioning_component_id'])){
		$sanitisedInput['provisioning_component_id'] = sanitise_input_array($inputarray['provisioning_component_id'], "provisioning_component_id", null, $API, $logParent);
		$sql = "SELECT id
				,  device_provisioning_device_provisioning_id
			FROM device_provisioning_components
			WHERE id IN ('" . implode("', '", $sanitisedInput['provisioning_component_id']) . "' )";
		
		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){  
            errorInvalid("provisioning_component_id", $API, $logParent);      
        }
		//	Check to make sure you're only updating one provisioning ID's value at a time
		if (count(array_unique(array_column($dbrows, 1))) != 1) {
			errorInvalid("provisioning_component_id", $API, $logParent);
		}
        arrayExistCheck ($sanitisedInput['provisioning_component_id'], array_column($dbrows, 0), "provisioning_component_id", $API, $logParent);
        $outputArray['provisioning_component_id'] = array_column($dbrows, 0);
		$outputArray['provisioning_id'] = $dbrows[0][1];
	}

	if (isset($inputarray['provisioning_id'])){
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
	}

	if (isset($inputarray['component_type'])){
		$sanitisedInput['component_type'] = sanitise_input($inputarray['component_type'], "component_type", $schemainfoArray["device_component_type"], $API, $logParent);
	}

	if (isset($inputarray['component_id'])){
		$sanitisedInput['component_id'] = sanitise_input_array($inputarray['component_id'], "component_id", null, $API, $logParent);
	}

	if (!isset($inputarray['provisioning_component_id'])){
		$sql = "SELECT 
			id
			, device_component_id 
			FROM device_provisioning_components
			WHERE device_provisioning_components.device_provisioning_device_provisioning_id = " . $sanitisedInput['provisioning_id'] ."
			AND device_provisioning_components.device_component_type = '" . $sanitisedInput['component_type'] . "'
			AND device_provisioning_components.device_component_id IN ('" . implode("', '", $sanitisedInput['component_id']) . "' )";

		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){  
            errorInvalid("component_id", $API, $logParent);      
        }
		
        arrayExistCheck ($sanitisedInput['component_id'], array_column($dbrows, 1), "component_id", $API, $logParent);
        $outputArray['id'] = array_column($dbrows, 0);
		$outputArray['provisioning_id'] = $sanitisedInput['provisioning_id'];
		$outputArray['component_type'] = $sanitisedInput['component_type'];
		$outputArray['component_id'] = $sanitisedInput['component_id'];
	}

    if (isset($inputarray['active_status'])) {
        $updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", $schemainfoArray["active_status"], $API, $logParent);
        $updateString .= " `active_status` = :active_status,"; 
		$outputArray['active_status'] = $updateArray['active_status'];
	}
    else {
        errorMissing("active_status", $API, $logParent);
    }

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($outputArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    try{ 
        if (count($updateArray) < 1) {
            logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($outputArray)), logLevel::invalid, logType::error, $token, $logParent);
            die("{\"error\":\"NO_UPDATED_PRAMS\"}");
        }	
		else {
			$sql = "UPDATE 
				device_provisioning_components 
				SET". $updateString . " `last_modified_by` = $user_id
				, `last_modified_datetime` = '$timestamp'
				WHERE `id` IN ( '" . implode("', '", $outputArray['id']) . "' )";
	       //echo $sql;
			$stm= $pdo->prepare($sql);	
			if($stm->execute($updateArray)){
				$outputArray ['error'] = "NO_ERROR";
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);
				//	Unset this for a cleaner output message
				if (isset($inputarray['provisioning_id'])){
					unset($outputArray['component_id']);
				}
				
				echo json_encode($outputArray);
			}
		}
    }
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}	

	// Update device version
	$data = [   
		'provisioning_id' => $outputArray['provisioning_id']
		, 'last_modified_by' => $user_id
		, 'last_modified_datetime' => gmdate("Y-m-d H:i:s")
	];

	$sql = "UPDATE 
			devices 
		SET configuration_version = (configuration_version + 1)
			, last_modified_by = :last_modified_by
			, last_modified_datetime = :last_modified_datetime 
		WHERE device_provisioning_device_provisioning_id = :provisioning_id 
		AND active_status = 0";		

	$stmt= $pdo->prepare($sql);
	if ($stmt->execute($data)){
	
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"device configuration version update failed\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}	
}

else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>