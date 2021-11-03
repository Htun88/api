<?php
$API = "DeviceAssetsChart";
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
		deviceassets.deviceasset_id
		, deviceassets.devices_device_id
		, assets.asset_name
		, assets.asset_id 
		, deviceassets_chart.sd_id
		, deviceassets_chart.gauge
		, deviceassets_chart.trend
		, deviceassets_chart.uom_show_id
		FROM
		assets 
		, deviceassets 
		, deviceassets_chart 
		, user_assets
		LEFT JOIN userasset_details 
		ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		WHERE deviceassets_chart.deviceassets_deviceasset_id = deviceassets.deviceasset_id
		AND deviceassets.assets_asset_id = assets.asset_id
		AND  user_assets.users_user_id = $user_id
		AND ((user_assets.asset_summary = 'some' 
			AND assets.asset_id = userasset_details.assets_asset_id
			AND userasset_details.user_assets_user_asset_id = user_asset_id)
		OR (user_assets.asset_summary = 'all'))";


	//this seems point less
	if (isset($inputarray['active_status'])){
		$inputarray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`active_status` = '" . $inputarray['active_status'] . "'";
		if ($inputarray['active_status'] == 0) {
			$sql .= " AND `deviceassets`.`date_to` IS NULL
				AND `assets`.`active_status` = 0 ";
		}
	}
	else {
		$sql .= " AND `deviceassets`.`date_to` IS NULL
			AND `deviceassets`.`active_status` = 0
			AND `assets`.`active_status` = 0 ";
	}

	if (isset($inputarray['deviceasset_id'])){
		$inputarray['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets_chart`.`deviceassets_deviceasset_id` IN (" . implode( ', ',$inputarray['deviceasset_id'] ) . ")";
	}

	if (isset($inputarray['device_id'])){
		$inputarray['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ',$inputarray['device_id'] ) . ")";
	}

	if (isset($inputarray['asset_id'])){
		$inputarray['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`assets_asset_id` IN (" . implode( ', ',$inputarray['asset_id'] ) . ")";
	}

	if (isset($inputarray['sd_id'])){
		$inputarray['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql .= " AND `deviceassets_chart`.`sd_id` IN (" . implode( ', ',$inputarray['sd_id'] ) . ")";
	}

	$sql .= " ORDER BY `deviceassets_chart`.`deviceassets_deviceasset_id` DESC";

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	//die($sql);
	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$jsons  = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json = array(
			"deviceasset_id" => $dbrow[0]
			,"device_id" => $dbrow[1]
			, "asset_name" => $dbrow[2]
			, "asset_id" => $dbrow[3]
			, "sd_id" => $dbrow[4]
			, "gauge" => $dbrow[5]
			, "trend" => $dbrow[6]
			, "uom_show_id" => $dbrow[7] );
			$jsons = array_merge($jsons,array("response_$outputid" => $json));
			$outputid++;
		}
		$json = array("responses" => $jsons);
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

	$data = [];

	if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {			
			$sql = "SELECT
					deviceassets.deviceasset_id
					, deviceassets.devices_device_id
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
				errorInvalid("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
			}

			$sanitisedInput['deviceasset_id'] = $dbrows[0][0];	
		}
		else {
			errorMissing("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
		}

		if (isset($inputarray['chart_options'])){
			//$updateArray["sensor_def"] = $inputarray['sensor_def'];
		}
		else {
			errorMissing("chart_options", $API, $logParent);
		}


	foreach($inputarray["chart_options"] as $inputarray["sd_id"] => $values){
		//echo "reached";
		if(isset($inputarray['sd_id'])){
			$sanitisedInput['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		}

		if(isset($values['gauge'])){
			$sanitisedInput['gauge'] = sanitise_input($values['gauge'], "gauge", null, $API, $logParent);
		}
	
		if(isset($values['trend'])){
			$sanitisedInput['trend'] = sanitise_input($values['trend'], "trend", null, $API, $logParent);
		}
	
		if(isset($values['uom_to_id'])){
			$sanitisedInput['uom_to_id'] = sanitise_input($values['uom_to_id'], "uom_to_id", null, $API, $logParent);
		}

		//	Check if the sensor exists on this device, and if the value you are converting it to is valid

		$sql = "SELECT				
				sensor_def.sd_uom_id
				,uom_conversions.uom_id_to
				,sensor_def.sd_id
				,deviceassets.deviceasset_id
				FROM (
				deviceassets
				, `devices`
				, device_provisioning_components
				, sensors
				, sensor_def
				, sensors_det
				)
				LEFT JOIN uom_conversions ON uom_conversions.uom_id_from = sensor_def.sd_uom_id AND uom_conversions.uom_id_to = '" . $sanitisedInput['uom_to_id'] . "'				
				WHERE devices.device_id = deviceassets.devices_device_id
				AND device_provisioning_components.device_provisioning_device_provisioning_id = devices.device_provisioning_device_provisioning_id
				AND device_provisioning_components.device_component_type = 'Sensor'
				AND sensors.sensor_id = device_provisioning_components.device_component_id
				AND sensors_det.sensors_sensor_id = sensors.sensor_id
				AND sensor_def.sd_id = sensors_det.sensor_def_sd_id				
				AND sensor_def.sd_id = '" . $sanitisedInput['sd_id'] . "'
				AND deviceassets.deviceasset_id = '" . $sanitisedInput['deviceasset_id'] . "'";


		//echo $sql . "\n"; exit;

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		//	If not set, then there is not convertable data

		//If not set, sensor doesn't exist or not associate with device
		if(!isset($rows[0][2])){
			errorInvalid("sd_id", $API, $logParent);
		}

		if(!isset($rows[0][3])){
			errorInvalid("deviceasset_id", $API, $logParent);
		}

		if( (!isset($rows[0][0])) && (!isset($rows[0][1])) ){
			errorInvalid("uom_to_id", $API, $logParent);
		}

		if((isset($rows[0][0])) && $rows[0][0] != $sanitisedInput['uom_to_id']){
				
			if((isset($rows[0][1])) && $rows[0][1] == $sanitisedInput['uom_to_id']){

			}else{
				errorInvalid("uom_to_id", $API, $logParent);
			}
		}		

		$sql = "SELECT chart FROM sensor_def
				WHERE sd_id = '" . $sanitisedInput['sd_id'] . "' and active_status = 0";

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if(isset($dbrows[0][0])){	
			 	if($dbrows[0][0] == 0){

					$sanitisedInput['gauge'] = 0;
					$sanitisedInput['trend'] = 0;
				}

				if($dbrows[0][0] == 1 && $sanitisedInput['gauge'] == 0 && $sanitisedInput['trend'] == 0){					
					errorInvalid("gauge_and_trend", $API, $logParent);
				}

		}					
     		
		//echo $rows[0][1] . " " . $sanitisedInput['uom_to_id'] . "..............\n\n";
		$data[] = "( " . $sanitisedInput['deviceasset_id'] . ", " . $sanitisedInput['sd_id'] . ", '" .  $sanitisedInput['gauge']  . "', " .  $sanitisedInput['trend']  . ", " .  $sanitisedInput['uom_to_id']  . ", " . $user_id . ", '" . $timestamp . "')"; 										

		$sdid[] = $sanitisedInput['sd_id'];
		$gauge[] = $sanitisedInput['gauge'];
		$trend[] = $sanitisedInput['trend'];
		$uom_to_id[] = $sanitisedInput['uom_to_id'];			
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO deviceassets_chart(
				`deviceassets_deviceasset_id`
				, `sd_id`
				, `gauge`
				, `trend`
				, `uom_show_id`
				, `last_modified_by`
				, `last_modified_datetime`)
				VALUES " . implode(', ', $data) . "
				ON DUPLICATE KEY UPDATE
					sd_id = VALUES(sd_id)
					, gauge = VALUES(gauge)
					, trend = VALUES(trend)	
					, uom_show_id = VALUES(uom_show_id)		
					, last_modified_by = VALUES(last_modified_by)
					, last_modified_datetime = VALUES(last_modified_datetime)";	
					
			$stmt= $pdo->prepare($sql);
			if($stmt->execute()){

				$outputArray['deviceasset_id'] = $sanitisedInput['deviceasset_id'];
				$outputArray['sd_id'] = $sdid;
				$outputArray['gauge'] = $gauge;
				$outputArray['trend'] = $trend;
				$outputArray['uom_to_id'] = $uom_to_id;
				$outputArray['last_modified_by'] = $user_id;
				$outputArray['last_modified_datetime'] = $timestamp;
				$outputArray['error'] = "NO_ERROR";
				echo json_encode($outputArray);
				$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];   
				 						
			}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}	
}

// *******************************************************************************
// *******************************************************************************
// ***********************************UPDATE**************************************
// *******************************************************************************
// ******************************************************************************* 

else if ($sanitisedInput['action'] == "update"){

		$updateArray = [];	
		$updateData = [];

	 if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {

			$sql = "SELECT
					deviceassets.deviceasset_id
					, deviceassets.devices_device_id
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
				errorInvalid("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
			}

			$sanitisedArray['deviceasset_id'] = $dbrows[0][0];	
		}
		else {
			errorMissing("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
		}

		if (isset($inputarray['chart_options'])){
			//$updateArray["sensor_def"] = $inputarray['sensor_def'];
		}
		else {
			errorMissing("chart_options", $API, $logParent);
		}

		$count = 0;
		foreach($inputarray["chart_options"] as $inputarray["sd_id"] => $values){
		
			if(isset($values['gauge'])){
				$sanitisedArray['gauge'] = sanitise_input($values['gauge'], "gauge", null, $API, $logParent);
			}
		
			if(isset($values['trend'])){
				$sanitisedArray['trend'] = sanitise_input($values['trend'], "trend", null, $API, $logParent);
			}
		
			if(isset($values['uom_to_id'])){
				$sanitisedArray['uom_to_id'] = sanitise_input($values['uom_to_id'], "uom_to_id", null, $API, $logParent);
			}

			if(isset($inputarray['sd_id'])){
				$values['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);
				//$values['sd_id'] = $sanitisedArray['sd_id'];
			}

			//	Check if the sensor exists on this device, and if the value you are converting it to is valid
			$sql = "SELECT						
					  sensor_def.sd_uom_id
					, uom_conversions.uom_id_to
					, sensor_def.sd_id
					, deviceassets.deviceasset_id
					FROM (
						deviceassets
						, `devices`
						, device_provisioning_components
						, sensors
						, sensor_def
						, sensors_det
					)
					LEFT JOIN uom_conversions ON uom_conversions.uom_id_from = sensor_def.sd_uom_id AND uom_conversions.uom_id_to = '" . $sanitisedArray['uom_to_id'] . "'				
					WHERE devices.device_id = deviceassets.devices_device_id
					AND device_provisioning_components.device_provisioning_device_provisioning_id = devices.device_provisioning_device_provisioning_id
					AND device_provisioning_components.device_component_type = 'Sensor'
					AND sensors.sensor_id = device_provisioning_components.device_component_id
					AND sensors_det.sensors_sensor_id = sensors.sensor_id
					AND sensor_def.sd_id = sensors_det.sensor_def_sd_id				
					AND sensor_def.sd_id = '" . $values['sd_id'] . "'
					AND deviceassets.deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'";

			//echo $sql;

			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);

			//If not set, sensor doesn't exist or not associate with device			
			if(!isset($rows[0][2])){
				errorInvalid("sd_id", $API, $logParent);
			}

			if(!isset($rows[0][3])){
				errorInvalid("deviceasset_id", $API, $logParent);
			}
			
			if( (!isset($rows[0][0])) && (!isset($rows[0][1])) ){
				errorInvalid("uom_to_id", $API, $logParent);
			}
	
			if((isset($rows[0][0])) && $rows[0][0] != $sanitisedArray['uom_to_id']){
					
				if((isset($rows[0][1])) && $rows[0][1] == $sanitisedArray['uom_to_id']){

				}else{
					errorInvalid("uom_to_id", $API, $logParent);
				}
			}

			
			$sql = "SELECT chart FROM sensor_def
					WHERE sd_id = '" . $values['sd_id'] . "' and active_status = 0";

			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if(isset($dbrows[0][0])){	
				if($dbrows[0][0] == 0){

					$sanitisedArray['gauge'] = 0;
					$sanitisedArray['trend'] = 0;
				}

				if($dbrows[0][0] == 1 && $sanitisedArray['gauge'] == 0 && $sanitisedArray['trend'] == 0){					
					errorInvalid("gauge_and_trend", $API, $logParent);
				}
			}					

			$sql = "SELECT id
					FROM deviceassets_chart
					WHERE deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'
					AND sd_id = '" .$values['sd_id'] . "'";
	
			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if(isset($dbrows[0][0])){
				$updateData[] = $values;	
			}	
									
		}	

		//print_r($updateData);

		$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
		$updateString = "";

		try{		
			if(count($updateData) < 1){
				logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_UPDATED_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
				die("{\"error\":\"NO_UPDATED_DATA\"}");
			}
			else{

				foreach($updateData as $updateArray){

					if(isset($updateArray["uom_to_id"])){
						$updateString .= " `uom_show_id` = :uom_to_id,";
					}

					if(isset($updateArray["gauge"])){
						$updateString .= " `gauge` = :gauge,";
					}

					if(isset($updateArray["trend"])){
						$updateString .= " `trend` = :trend,";
					}

					$sql = "UPDATE deviceassets_chart 
							SET". $updateString . " `last_modified_by` = $user_id
							, `last_modified_datetime` = '$timestamp'
							WHERE `deviceassets_deviceasset_id` = '" . $sanitisedArray['deviceasset_id'] . "' AND `sd_id` = :sd_id";

					$stm= $pdo->prepare($sql);	
					if($stm->execute($updateArray)){
						$outputArray['deviceasset_id'] =  $sanitisedArray['deviceasset_id'];
						$outputArray['sd_id'] =  $updateArray['sd_id'];
						$outputArray['gauge'] =  $updateArray['gauge'];
						$outputArray['trend'] =  $updateArray['trend'];
						$outputArray['uom_to_id'] =  $updateArray['uom_to_id'];
						$outputArray['last_modified_by'] = $user_id;
						$outputArray['last_modified_datetime'] = $timestamp;
						$outputArray['error'] = "NO_ERROR";
						logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);
						echo json_encode($outputArray);
					}

					unset($updateString);
					$updateString = "";
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

/*
{
	"action": "update",
	"deviceasset_id" : 29,
	"chart_options": {
			"41": {
					"uom_to_id": "11",
					"gauge": "0",
					"trend": "0"
				}
		}
}
*/

?>


