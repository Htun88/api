<?php
$API = "DeviceProvisioningLink";
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
		device_provisioning_link.module_def_id, 
		module_def.module_name, 
		device_provisioning_link.module_value, 
		device_provisioning_link.param_def_id, 
		param_def.param_name, 
		device_provisioning_link.param_value,
		device_provisioning_link.sendvia, 
		device_provisioning_link.calib_def_id, 
		calibration_def.name, 
		device_provisioning_link.sensor_def_sd_id, 
		device_provisioning_link.sensor_def_data_type, 
		device_provisioning_link.id, 
		device_provisioning_link.device_provisioning_id
		FROM 
		device_provisioning_link,
		module_def, 
		param_def, 
		calibration_def 
		WHERE 
		 module_def.id = device_provisioning_link.module_def_id
		AND param_def.id = device_provisioning_link.param_def_id
		AND calibration_def.id = device_provisioning_link.calib_def_id";
	
	if (isset($inputarray['id'])) {		
		$sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`id` = '". $sanitisedInput['id'] ."'";
	} 

	if (isset($inputarray['active_status'])) {		
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`active_status` = '". $sanitisedInput['active_status'] ."'";
	}  
	
	if (isset($inputarray['provisioning_id'])) {		
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`device_provisioning_id` = '". $sanitisedInput['provisioning_id'] ."'";
	} 
		
	$sql .= " ORDER BY device_provisioning_link.id DESC";

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	//echo $sql;

	$stm = $pdo->query($sql);					
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0][0])) {
		$json_items = array();
		$outputid = 0;
		foreach($rows as $row) {
			$json_item = array(
				"module_def_id" => $row[0],
				"module_name" => $row[1],
				"module_value" => $row[2],
				"param_def_id" => $row[3],
				"param_name" => $row[4],
				"param_value" => $row[5],
				"sendvia" => $row[6],
				"calib_def_id" => $row[7],
				"name" => $row[8],
				"sensor_def_sd_id" => $row[9],
				"sensor_def_data_type" => $row[10],
				"id" => $row[11],
				"device_provisioning_id" => $row[12] );
			$json_items = array_merge(
				$json_items,
				array("response_$outputid" => $json_item)
			);
			$outputid++;
		}
		$json = array("responses" => $json_items);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	} else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************


elseif($sanitisedInput['action'] == 'insert'){

	$schemainfoArray = getMaxString ("device_provisioning_link", $pdo);
	$insertArray = [];
	
	if (isset($inputarray['module_id'])) {
		$insertArray['module_id'] = sanitise_input($inputarray['module_id'], "module_id", null, $API, $logParent);	

		$sql = "SELECT id FROM module_def WHERE id = '" . $insertArray['module_id'] . "'";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("module_id", $API, $logParent);
		}
	}
	else {
		errorMissing("module_id", $API, $logParent);
	}

	if (isset($inputarray['param_id'])) {
		$insertArray['param_id'] = sanitise_input($inputarray['param_id'], "param_id", null, $API, $logParent);	

		$sql = "SELECT param_def.id
				FROM param_def, module_def
				WHERE param_def.module_def_id = module_def.id
				AND param_def.module_def_id = '" . $insertArray['module_id'] . "'
				AND param_def.id = '" . $insertArray['param_id'] . "'";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("param_id", $API, $logParent);
		}			
	}
	else {
		errorMissing("param_id", $API, $logParent);
	}

	if (isset($inputarray['provisioning_id'])) {
		$insertArray['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);	

		$sql = "SELECT id FROM device_provisioning WHERE id = '" . $insertArray['provisioning_id'] . "'";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("provisioning_id", $API, $logParent);
		}
	}
	else {
		errorMissing("provisioning_id", $API, $logParent);
	}

	if((isset($inputarray['param_id'])) && (isset($inputarray['module_id']))){
		$sql = "SELECT module_def_id FROM device_provisioning_link
				WHERE module_def_id = '" . $insertArray['module_id'] . "'
				AND param_def_id = '" . $insertArray['param_id'] . "'
				AND device_provisioning_id = '" . $insertArray['provisioning_id'] . "'";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($rows[0][0])) {
			errorInvalid("param_id_and_module_id", $API, $logParent);
		}
	}
	
	if (isset($inputarray['param_value'])) {
		$insertArray['param_value'] = sanitise_input($inputarray['param_value'], "param_value", null, $API, $logParent);	
		$sql = "SELECT param_value FROM device_provisioning_link 
				WHERE device_provisioning_id = '" . $insertArray['provisioning_id'] . "'
				AND  module_def_id = '" . $insertArray['module_id']  . "'
				AND  param_value = '" . $insertArray['param_value']  . "'";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($rows[0][0])) {
			errorInvalid("param_value", $API, $logParent);
		}
	}
	else {
		errorMissing("param_value", $API, $logParent);
	}
	
	if (isset($inputarray['module_value'])) {
		$insertArray['module_value'] = sanitise_input($inputarray['module_value'], "module_value", null, $API, $logParent);	
		$sql = "SELECT module_def_id, module_value, param_value FROM device_provisioning_link 
				WHERE device_provisioning_id = '" . $insertArray['provisioning_id'] . "'";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($rows[0][0])) {
			foreach($rows as $row){
				$module_id = $row[0];
				$module_value = $row[1];
				$param_value = $row[2];
				//echo $module_id . " ";
				if($module_id == $insertArray['module_id'] 
					&& $module_value != $insertArray['module_value']){
						errorInvalid("module_value", $API, $logParent);
					}
				elseif($module_value == $insertArray['module_value'] 
						&& $param_value == $insertArray['param_value']){							
						errorInvalid("module_value", $API, $logParent);	
				   }
			}
		}
	}
	else {
		errorMissing("module_value", $API, $logParent);
	}

	if (isset($inputarray['sd_id'])) {
		$insertArray['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);	
		$sql = "SELECT 
				sensor_def_sd_id
				FROM 
				device_provisioning_link
				WHERE 
				param_def_id = '" . $insertArray['param_id']  . "'
				AND module_def_id = '" . $insertArray['module_id']  . "'
				AND sensor_def_sd_id = '" . $insertArray['sd_id']  . "'";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($rows[0][0])) {
			errorInvalid("sd_id", $API, $logParent);
		}

		$sql = "SELECT 
				sensor_def_data_type
				FROM 
				device_provisioning_link
				WHERE 
				device_provisioning_id = '" . $insertArray['provisioning_id']  . "'
				AND sensor_def_sd_id = '" . $insertArray['sd_id']  . "'";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($rows[0][0])) { 
			errorInvalid("sd_id", $API, $logParent);
		}

		$sql = "SELECT 
				bytelength
				FROM 
				sensor_def
				WHERE 
				sd_id = '" . $insertArray['sd_id']  . "'";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("sd_id", $API, $logParent);
		}

		$insertArray['data_type'] = $rows[0][0];
	}
	else {
		errorMissing("sd_id", $API, $logParent);
	}


	if (isset($inputarray['calib_id'])) {
		$insertArray['calib_id'] = sanitise_input($inputarray['calib_id'], "calib_id", null, $API, $logParent);	

		$sql = "SELECT id FROM calibration_def WHERE id = '" . $insertArray['calib_id'] . "'";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("calib_id", $API, $logParent);
		}
	}
	else {
		$insertArray['calib_id'] = 1;
	}

	//$insertArray['sendvia'] = sanitise_input($inputarray['sendvia'], "sendvia", $schemainfoArray["sendvia"], $API, $logParent);	
	//$insertArray['sendvia'] = "AUTO";
	
	if (isset($inputarray['active_status'])) {
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		$insertArray['active_status'] = 0;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO device_provisioning_link(
			`active_status`
			, `last_modified_by`
			, `last_modified_datetime`
			, `param_def_id`
			, `param_value`
			, `module_def_id`
			, `module_value`
			, `calib_def_id`
			, `sensor_def_sd_id`
			, `sensor_def_data_type`
			, `device_provisioning_id`) 
		VALUES (
			  :active_status
			,  $user_id
			, '$timestamp'
			, :param_id
			, :param_value
			, :module_id
			, :module_value
			, :calib_id
			, :sd_id
			, :data_type
			, :provisioning_id)";
		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}

		$data['last_modified_by'] = $user_id;
		$data['last_modified_datetime'] = $timestamp;

		$sql = "UPDATE devices SET 
				configuration_version = (configuration_version + 1), 
				last_modified_by = :last_modified_by,
				last_modified_datetime = :last_modified_datetime 
				WHERE device_provisioning_device_provisioning_id = '" . $insertArray['provisioning_id'] . "'
				AND active_status = 0";						
		$stmt= $pdo->prepare($sql);
		if($stmt->execute($data)){
		}else{
			die("{\"error\":\"ERROR\"}");
		}		
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}



// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************


elseif ($sanitisedInput['action'] == 'update'){

		$schemainfoArray = getMaxString("device_provisioning_link", $pdo);
		$updateArray = [];
		$updateString = "";	
		//$updateString = "UPDATE device_provisioning_link SET ";  

		if (isset($inputarray['id'])) {
			$updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);	

			$sql = "SELECT id FROM device_provisioning_link WHERE id = '" . $updateArray['id'] . "'";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorInvalid("id", $API, $logParent);
			}
		}
		else {
			errorMissing("id", $API, $logParent);
		}
		
		if (isset($inputarray['module_id'])) {
			$updateArray['module_id'] = sanitise_input($inputarray['module_id'], "module_id", null, $API, $logParent);	
	
			$sql = "SELECT id FROM module_def WHERE id = '" . $updateArray['module_id'] . "'";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorInvalid("module_id", $API, $logParent);
			}

			$updateString .= " `module_def_id` = :module_id,";
		}
		else {
			errorMissing("module_id", $API, $logParent);
		}
	
		if (isset($inputarray['param_id'])) {
			$updateArray['param_id'] = sanitise_input($inputarray['param_id'], "param_id", null, $API, $logParent);	
	
			$sql = "SELECT param_def.id
					FROM param_def, module_def
					WHERE param_def.module_def_id = module_def.id
					AND param_def.module_def_id = '" . $updateArray['module_id'] . "'
					AND param_def.id = '" . $updateArray['param_id'] . "'";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])){
				errorInvalid("param_id", $API, $logParent);
			}			

			$updateString .= " `param_def_id` = :param_id,";
		}
		else {
			errorMissing("param_id", $API, $logParent);
		}


		if (isset($inputarray['provisioning_id'])) {
			$updateArray['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);	

			$sql = "SELECT device_provisioning_link.device_provisioning_id FROM device_provisioning, device_provisioning_link
					WHERE device_provisioning.id = device_provisioning_link.device_provisioning_id
					AND device_provisioning.id = '" . $updateArray['provisioning_id'] . "'";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorInvalid("provisioning_id", $API, $logParent);
			}

			$updateString .= " `device_provisioning_id` = :provisioning_id,";
		}
		else {
			errorMissing("provisioning_id", $API, $logParent);
		}


		if((isset($inputarray['param_id'])) && (isset($inputarray['module_id']))){
			$sql = "SELECT module_def_id FROM device_provisioning_link
					WHERE module_def_id = '" . $updateArray['module_id'] . "'
					AND param_def_id = '" . $updateArray['param_id'] . "'
					AND device_provisioning_id = '" . $updateArray['provisioning_id'] . "'";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($rows[0][0])) {
				errorInvalid("param_id_and_module_id", $API, $logParent);
			}
		}
		

		if (isset($inputarray['calib_id'])) {
			$updateArray['calib_id'] = sanitise_input($inputarray['calib_id'], "calib_id", null, $API, $logParent);	
	
			$sql = "SELECT id FROM calibration_def WHERE id = '" . $updateArray['calib_id'] . "'";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorInvalid("calib_id", $API, $logParent);
			}

			$updateString .= " `calib_def_id` = :calib_id,";
		}
	
	
		if (isset($inputarray['sd_id'])) {
			$updateArray['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);	
				$sql = "SELECT 
					sensor_def_sd_id
					FROM 
					device_provisioning_link
					WHERE 
					param_def_id = '" . $updateArray['param_id']  . "'
					AND module_def_id = '" . $updateArray['module_id']  . "'
					AND sensor_def_sd_id = '" . $updateArray['sd_id']  . "'";

			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($rows[0][0])) {
				errorInvalid("sd_id", $API, $logParent);
			}

			$sql = "SELECT 
					sensor_def_data_type
					FROM 
					device_provisioning_link
					WHERE 
					device_provisioning_id = '" . $updateArray['provisioning_id']  . "'
					AND sensor_def_sd_id = '" . $updateArray['sd_id']  . "'";

			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($rows[0][0])) {
				errorInvalid("sd_id", $API, $logParent);
			}

			$sql = "SELECT 
					bytelength
					FROM 
					sensor_def
					WHERE 
					sd_id = '" . $updateArray['sd_id']  . "'";

			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorInvalid("sd_id", $API, $logParent);
			}

			$updateArray['data_type'] = $rows[0][0];
			$updateString .= " `sensor_def_sd_id` = :sd_id, `sensor_def_data_type` = :data_type,";

		}
		else {
			errorMissing("sd_id", $API, $logParent);
		}
		
		if (isset($inputarray['param_value'])) {
			$updateArray['param_value'] = sanitise_input($inputarray['param_value'], "param_value", null, $API, $logParent);	
			$sql = "SELECT param_value FROM device_provisioning_link 
					WHERE device_provisioning_id = '" . $updateArray['provisioning_id'] . "'
					AND  module_def_id = '" . $updateArray['module_id']  . "'
					AND  param_value = '" . $updateArray['param_value']  . "'";

					$stm = $pdo->query($sql);
					$rows = $stm->fetchAll(PDO::FETCH_NUM);
					if (isset($rows[0][0])) {
						errorInvalid("param_value", $API, $logParent);
					}

			$updateString .= " `param_value` = :param_value,";
		}
		else {
			errorMissing("param_value", $API, $logParent);
		}	
	
		if (isset($inputarray['module_value'])) {
			$updateArray['module_value'] = sanitise_input($inputarray['module_value'], "module_value", null, $API, $logParent);	
			$sql = "SELECT module_def_id, module_value, param_value FROM device_provisioning_link 
					WHERE device_provisioning_id = '" . $updateArray['provisioning_id'] . "'";
	
			$stm = $pdo->query($sql);		
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($rows[0][0])) {
				foreach($rows as $row){
					$module_id = $row[0];
					$module_value = $row[1];
					$param_value = $row[2];
					//echo $module_id . " ";
					if($module_id == $updateArray['module_id'] 
						&& $module_value != $updateArray['module_value']){
							errorInvalid("module_value", $API, $logParent);
						}
					elseif($module_value == $updateArray['module_value'] 
							&& $param_value == $updateArray['param_value']){							
							errorInvalid("module_value", $API, $logParent);	
					   }
				}
			}

			$updateString .= " `module_value` = :module_value,";
		}
		else {
			errorMissing("module_value", $API, $logParent);
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
					device_provisioning_link 
					SET". $updateString . " `last_modified_by` = $user_id
					, `last_modified_datetime` = '$timestamp'
					WHERE `id` = :id";

			//echo $sql;
			$stm= $pdo->prepare($sql);	
			if($stm->execute($updateArray)){
				$updateArray ['error'] = "NO_ERROR";
				echo json_encode($updateArray);
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
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


/*"asset": {
	"asset_id": "23",
	"sensor_def": [
	{
		"sd_id": "1",
		"gauge": "true",
		"trend": "false",
		"uom_to_id": "7",
		"chartlabel": "CO"
	},
	{
		"sd_id": "2",
		"gauge": "true",
		"trend": "true",
		"uom_to_id": "7",
		"chartlabel": "HS"
	},
	{
		"sd_id": "3",
		"gauge": "true",
		"trend": "true",
		"uom_to_id": "8",
		"chartlabel": "LEL"
	}
	]
} */

?>