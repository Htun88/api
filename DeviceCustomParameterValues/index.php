<?php

$API = "DeviceCustomParameterValues";
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
		`device_custom_param_values`.`id`,
		`device_custom_param_values`.`value`,		
		`device_custom_param_values`.`devices_device_id`,
		`device_custom_param_values`.`device_custom_param_id`,
		`device_custom_param`.`name`,
		`device_custom_param`.`tag_name`
		FROM (device_custom_param_values, device_custom_param)
		WHERE device_custom_param_values.device_custom_param_id = device_custom_param.id";

		
	if (isset($inputarray['id'])) {
		$sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " AND `device_custom_param_values`.`id` = '". $sanitisedInput['id'] ."'";
	}

	if (isset($inputarray['device_id'])) {
		$sanitisedInput['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `device_custom_param_values`.`devices_device_id` = '". $sanitisedInput['device_id'] ."'";
	}

	if (isset($inputarray['param_id'])) {
		$sanitisedInput['param_id'] = sanitise_input($inputarray['param_id'], "param_id", null, $API, $logParent);
		$sql .= " AND `device_custom_param_values`.`device_custom_param_id` = '". $sanitisedInput['param_id'] ."'";
	}

	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	
	//echo $sql;

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$device_custom_param_values = array();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_asset = array(
			"id" => $dbrow[0]
			, "value" => $dbrow[1]
			, "device_id" => $dbrow[2]
			, "param_id" => $dbrow[3]
			, "param_name" => $dbrow[4]
			, "param_tag_name" => $dbrow[5]
			);
			$device_custom_param_values = array_merge($device_custom_param_values,array("response_$outputid" => $json_asset));
			$outputid++;
		}
		$json = array("responses" => $device_custom_param_values);
		echo json_encode($json);
	}
	else{
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
    	die ("{\"error\":\"NO_DATA\"}");
	}	

}

// *******************************************************************************
// *******************************************************************************
// ******************************INSERT*******************************************
// *******************************************************************************
// ******************************************************************************* 

elseif($sanitisedInput['action'] == 'insert'){

	$schemainfoArray = getMaxString("device_custom_param_values", $pdo);
	$insertArray = [];
	//$data = [];
	
	if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {

			$sql = "SELECT
					deviceassets.devices_device_id
					, deviceassets.deviceasset_id
					, deviceassets.assets_asset_id
				FROM (
					users
					, user_assets
					, assets
					, deviceassets
					)
				LEFT JOIN userasset_details
					ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
				WHERE users.user_id = user_assets.users_user_id
				AND deviceassets.assets_asset_id = assets.asset_id
				AND user_assets.users_user_id = $user_id
				AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
					OR (user_assets.asset_summary = 'all'))
				AND deviceassets.active_status = 0
				AND deviceassets.date_to IS NULL";

			if (isset($inputarray['deviceasset_id'])) {
				$sanitisedInput['deviceasset_id'] = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
				$sql .= " AND deviceassets.deviceasset_id = '" . $sanitisedInput['deviceasset_id'] . "'";
			}

			if (isset($inputarray['device_id'])) {
				$sanitisedInput['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
				$sql .= " AND deviceassets.devices_device_id = '" . $sanitisedInput['device_id'] . "'";
			}

			if (isset($inputarray['asset_id'])) {
				$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
				$sql .= " AND deviceassets.assets_asset_id = '" . $sanitisedInput['asset_id'] . "'";
			}

			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

			if (!isset($dbrows[0][0])){

				if(isset($sanitisedInput['device_id'])){

					$sql = "SELECT device_id
							FROM devices
							WHERE device_id = '" . $sanitisedInput['device_id'] . "'";
			
					$stm = $pdo->query($sql);
					$devicerows = $stm->fetchAll(PDO::FETCH_NUM);

					if(!isset($devicerows[0][0])){
						errorInvalid("device_id", $API, $logParent);
					}

					$insertArray['device_id'] = $devicerows[0][0];	
				} 
				else {
					errorInvalid("device_id", $API, $logParent);
				}			
			}
			else{
					$insertArray['device_id'] = $dbrows[0][0];	
			}		
		}
		else {
			errorMissing("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
		}

		if(isset($inputarray['param_values'])){
		}
		else{
			errorMissing("param_values");
		}

	foreach($inputarray['param_values'] as $param_values){

		//$insertArray['value'] = $param_values["value"];
		//$insertArray['param_id'] = $param_values["param_id"];

		if(isset($param_values["value"])){
			$insertArray['value'] = sanitise_input($param_values["value"], "value", $schemainfoArray['value'], $API, $logParent);
		}else{
			errorMissing("value", $API, $logParent);
		}
	
		if(isset($param_values["param_id"])){
			$insertArray['param_id'] = sanitise_input($param_values["param_id"], "param_id", null, $API, $logParent);
			$sql = "SELECT id
					FROM device_custom_param
					WHERE id = '" . $insertArray['param_id'] . "'";
	
			$stm = $pdo->query($sql);
			$paramrows = $stm->fetchAll(PDO::FETCH_NUM);
			if(!isset($paramrows[0][0])){
				errorInvalid("param_id", $API, $logParent);
			}
	
		}else{
			errorMissing("param_id", $API, $logParent);
		} 

		$data[] = "( '" . $insertArray["value"] . "', " . $insertArray['device_id'] . ", " .  $insertArray["param_id"]  . ")";    

		$value[] = $insertArray['value'];
		$paramidArray[] = $insertArray['param_id'];
	}

	$parameter_ids = implode(',', $paramidArray);
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO device_custom_param_values(
			`value`
			, `devices_device_id`
			, `device_custom_param_id`)
			VALUES " . implode(', ', $data) . "
			ON DUPLICATE KEY UPDATE
				  value = VALUES(value)
				, devices_device_id = VALUES(devices_device_id)
				, device_custom_param_id = VALUES(device_custom_param_id)";	  

		$stm= $pdo->prepare($sql);
		if($stm->execute()){

			$sql = "SELECT id
					FROM
					device_custom_param_values
					WHERE device_custom_param_id IN (" . $parameter_ids . ")
					AND devices_device_id = '". $insertArray['device_id'] . "'
					ORDER BY id ASC";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorGeneric("Issue", $API, $logParent);
			}

			updatedeviceconfigVersion($insertArray['device_id'], $user_id);

			$outputArray['value'] = $value;
			$outputArray['param_id'] = $paramidArray;
			$outputArray['device_id'] =  $insertArray['device_id'];
			$outputArray['id'] = array_column($rows, 0);
			$outputArray['error'] = "NO_ERROR";
			echo json_encode($outputArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}

}


// *******************************************************************************
// *******************************************************************************
// ******************************UPDATE*******************************************
// *******************************************************************************
// ******************************************************************************* 



elseif($sanitisedInput['action'] == 'update'){

	$schemainfoArray = getMaxString("device_custom_param_values", $pdo);
	$updateArray = [];
	$updateString = "UPDATE device_custom_param_values SET";


	if (isset($inputarray['id'])) 
        if (isset($inputarray['deviceasset_id'])
          || isset($inputarray['device_id'])
		  || isset($inputarray['asset_id'])
          ) {
             errorGeneric("Incompatable_Identification_params", $API, $logParent);
            }
	}
	else{
		
		if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {				
			$sql = "SELECT
					  deviceassets.devices_device_id
					, deviceassets.deviceasset_id
					, deviceassets.assets_asset_id
				FROM (
					users
					, user_assets
					, assets
					, deviceassets
					)
				LEFT JOIN userasset_details
					ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
				WHERE users.user_id = user_assets.users_user_id
				AND deviceassets.assets_asset_id = assets.asset_id
				AND user_assets.users_user_id = $user_id
				AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
					OR (user_assets.asset_summary = 'all'))
				AND deviceassets.active_status = 0
				AND deviceassets.date_to IS NULL";
	
			if (isset($inputarray['deviceasset_id'])) {
				$sanitisedInput['deviceasset_id'] = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
				$sql .= " AND deviceassets.deviceasset_id = '" . $sanitisedInput['deviceasset_id'] . "'";
			}
	
			if (isset($inputarray['device_id'])) {
				$sanitisedInput['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
				$sql .= " AND deviceassets.devices_device_id = '" . $sanitisedInput['device_id'] . "'";
			}
	
			if (isset($inputarray['asset_id'])) {
				$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
				$sql .= " AND deviceassets.assets_asset_id = '" . $sanitisedInput['asset_id'] . "'";
			}
	
			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	
			if (!isset($dbrows[0][0])){

				if(isset($sanitisedInput['device_id'])){

					$sql = "SELECT device_id
							FROM devices
							WHERE device_id = '" . $sanitisedInput['device_id'] . "'";
			
					$stm = $pdo->query($sql);
					$devicerows = $stm->fetchAll(PDO::FETCH_NUM);

					if(!isset($devicerows[0][0])){
						errorInvalid("device_id", $API, $logParent);
					}

					$insertArray['device_id'] = $devicerows[0][0];	
				} 
				else {
					errorInvalid("device_id", $API, $logParent);
				}			
			}
			else{
				$sanitisedInput['device_id'] = $dbrows[0][0];	
			}		
		}
		else {
			errorMissing("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
		}
    }

	if(isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql = "SELECT id
				FROM device_custom_param_values
				WHERE id IN (" . implode( ', ', $sanitisedInput['id'] ) . ")";

		$stm = $pdo->query($sql);
		$paramrows = $stm->fetchAll(PDO::FETCH_NUM);
		if(!isset($paramrows[0][0])){
			errorInvalid("id", $API, $logParent);
		}
	}
	

	if(isset($inputarray['param_values'])){
	}
	else{
		errorMissing("param_values");
	}

	//print_r($inputarray['param_values']);

	foreach($inputarray['param_values'] as $param_values){

		if(isset($param_values["value"])){
			$sanitisedInput['value'] = sanitise_input($param_values["value"], "value", $schemainfoArray['value'], $API, $logParent);
		}else{
			errorMissing("value", $API, $logParent);
		}

		//$valueArray[] = $sanitisedInput['value'];
	
		if(isset($param_values["param_id"])){
			$sanitisedInput['param_id'] = sanitise_input($param_values["param_id"], "param_id", null, $API, $logParent);
			$sql = "SELECT id
					FROM device_custom_param
					WHERE id = '" . $sanitisedInput['param_id'] . "'";
	
			$stm = $pdo->query($sql);
			$paramrows = $stm->fetchAll(PDO::FETCH_NUM);
			if(!isset($paramrows[0][0])){
				errorInvalid("param_id", $API, $logParent);
			}
		}

		//$data[] = "( '" . $insertArray["value"] . "', " . $insertArray['device_id'] . ", " .  $insertArray["param_id"]  . ")";    
	}

	//$parameter_ids = implode(',', $paramidArray);
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{

		if (count($inputarray['param_values']) < 1) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else{

			foreach($inputarray['param_values'] as $values){

				$updateArray["value"] = $values["value"];
			
				$sql = "UPDATE device_custom_param_values
						SET value = :value";			

				if (isset($sanitisedInput['id'])){
					//$updateArray["id"]  = $sanitisedInput['id'];
					$sql .= " WHERE `id` IN (" . implode( ', ', $sanitisedInput['id'] ) . ")";
				}
				else {
					$updateArray["device_id"]  = $sanitisedInput['device_id'];
					$updateArray["param_id"] = $values["param_id"];
					$sql .= " WHERE `devices_device_id` = :device_id AND device_custom_param_id = :param_id";

					$paramArray[] = $updateArray['param_id'];
					updatedeviceconfigVersion($updateArray["device_id"], $user_id);
				}

				//echo $sql; 
				//print_r($updateArray);

				$stm= $pdo->prepare($sql);
				if ($stm->execute($updateArray)){

				}
			   
				$valueArray[] = $updateArray['value'];								
			}

			if (isset($sanitisedInput['id'])){
				$outputArray['id'] = $sanitisedInput['id'];
			}else{
				$outputArray['device_id'] = $updateArray['device_id'];
				$outputArray['param_id'] = $paramArray;		
			}

			$outputArray['value'] = $valueArray;
			$outputArray['error'] = "NO_ERROR";
			echo json_encode($outputArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
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



function updatedeviceconfigVersion($device_id, $user_id){
	global $pdo; 

	$data['device_id'] = $device_id;
	$data['last_modified_by'] = $user_id;
	$data['last_modified_datetime'] = gmdate("Y-m-d H:i:s");

	$sql = "UPDATE devices SET configuration_version = (configuration_version + 1), last_modified_by = :last_modified_by,"; 
	$sql = $sql . " last_modified_datetime = :last_modified_datetime WHERE device_id = :device_id and active_status = 0";						
	$stmt= $pdo->prepare($sql);
	if($stmt->execute($data)){
	}else{
		logEvent($API . logText::invalidValue . strtoupper($data), logLevel::invalid, logType::error, $token, $logParent);
		errorInvalid("request", $API, $logParent);
	}	
} 



/*
{
   "action": "update",
   "asset_id": "1",
   "param_values": [
        {
                "param_id": "47",
                "value": "2"
        },
        {
                "param_id": "37",
                "value": "11" 
        }
   ]
}
*/

?>