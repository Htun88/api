<?php
	//	Asset Position
	header('Content-Type: application/json');
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
	include '../Includes/sanitise.php';

	$entitybody = file_get_contents('php://input');
	$inputarray = json_decode($entitybody, true);
	$action = "select";
	if (isset($inputarray['action'])){
		$action = sanitise_input($inputarray['action'], "action", 10);
	}

	//$action = sanitise_input($inputarray['action'], "action", 10);

	$schemainfoArray = [];

	$stm = $pdo->query("SELECT 
		COLUMN_NAME
		, CHARACTER_MAXIMUM_LENGTH
		FROM information_schema.columns
		WHERE table_schema = DATABASE() 
		AND table_name = 'assets'");

	$dbinformation_schema = $stm->fetchAll(PDO::FETCH_NUM);
	$schemainfoArray = [];
	foreach($dbinformation_schema as $dbrows){
		if (isset($dbrows[0][0])) {
			$schemainfoArray[$dbrows[0]] = $dbrows[1];
		}
	}

	$stm = $pdo->query("SELECT asset_summary FROM user_assets WHERE users_user_id = $user_id");
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0][0])){
		if ($rows[0][0]=="All"){			
			$sql = "SELECT 
				assets_position.id
				, assets_position.deviceassets_deviceasset_id
				, assets_position.assets_asset_id
				, assets_position.sensor_data_data_id
				, assets_position.data_datetime
				, assets_position.lat
				, assets_position.lng
				, assets_position.alt
				, assets_position.alarm_reset_status
				, assets_position.alarm_ack_status
				, assets_position.assets_positioncol
				, assets_position.imei
				, assets.asset_name
				, alt_range.level_name
				, alt_range.alt_range_id
				, asset_type
				FROM (assets_position
				, assets) 
				LEFT JOIN alt_range 
				ON assets_position.alt >= alt_range.alt_from
				AND assets_position.alt <= alt_range.alt_to
				WHERE assets_position.assets_asset_id  = assets.asset_id 
				AND assets.active_status = 0";
		}
		elseif($rows[0][0]=="Some"){
			$sql = "SELECT 
				assets_position.id
				, assets_position.deviceassets_deviceasset_id
				, assets_position.assets_asset_id
				, assets_position.sensor_data_data_id
				, assets_position.data_datetime
				, assets_position.lat
				, assets_position.lng
				, assets_position.alt
				, assets_position.alarm_reset_status
				, assets_position.alarm_ack_status
				, assets_position.assets_positioncol
				, assets_position.imei
				, assets.asset_name
				, alt_range.level_name
				, alt_range.alt_range_id
				, asset_type
				FROM (
				assets_position
				, userasset_details
				, user_assets
				, users
				, assets)
				LEFT JOIN alt_range 
				ON assets_position.alt >= alt_range.alt_from
				AND assets_position.alt <= alt_range.alt_to
				WHERE assets_position.assets_asset_id  = asset_id
				AND assets.active_status = 0
				AND user_assets.users_user_id = users.user_id
				AND userasset_details.user_assets_user_asset_id = user_asset_id
				AND userasset_details.assets_asset_id = assets.asset_id
				AND users.active_status = 0
				AND userasset_details.active_status = 0
				AND users.user_id = $user_id";	
		}
		else{
			die ("{\"error\":\"NO_DATA\"}");
		}
	
		if (isset($inputarray['id'])) {
			$inputarray['id'] = sanitise_input($inputarray['id'], "id", null);
			$sql .= " AND `id` = '". $inputarray['id'] ."'";
		}

		if (isset($inputarray['deviceassets_deviceasset_id'])) {
			$inputarray['deviceassets_deviceasset_id'] = sanitise_input($inputarray['deviceassets_deviceasset_id'], "deviceassets_deviceasset_id", null);
			$sql .= " AND `deviceassets_deviceasset_id` = '". $inputarray['deviceassets_deviceasset_id'] ."'";
		}

		if (isset($inputarray['assets_asset_id'])){
			$inputarray['assets_asset_id'] = sanitise_input($inputarray['assets_asset_id'], "assets_asset_id", null);
			$stm = $pdo->query("SELECT * FROM assets where `asset_id` = '" . $inputarray['assets_asset_id'] . "'");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($dbrows[0][0])){
				$sql .= " AND `assets_asset_id` = '". $inputarray['assets_asset_id'] ."'";
			}
			else {
				die("{\"error\":\"INVALID_ASSET_ID_DOES_NOT_EXIST_PRAM\"}");
			}
		}

		if (isset($inputarray['imei'])) {
			$inputarray['imei'] = sanitise_input($inputarray['imei'], "imei", null);
			$sql .= " AND `imei` = '". $inputarray['imei'] ."'";
		}

		if (isset($inputarray['sensor_data_data_id'])) {
			$inputarray['sensor_data_data_id'] = sanitise_input($inputarray['sensor_data_data_id'], "sensor_data_data_id", null);
			$sql .= " AND `sensor_data_data_id` = '". $inputarray['sensor_data_data_id'] ."'";
		}

		if(isset($inputarray['asset_type'])){
			$inputarray['asset_type'] = sanitise_input($inputarray['asset_type'], "asset_type", $schemainfoArray['asset_type']);
			$sql .= " AND `asset_type` = '". $inputarray['asset_type'] ."'";
		}

		$sql .= " ORDER BY assets.asset_name";
		$stm = $pdo->query($sql);
		$assetsrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($assetsrows[0][0])){
			$json_assetpositions  = array ();
			$outputid = 0;
			foreach($assetsrows as $assetsrow){
				$json_assetposition = array(
				"id" => $assetsrow[0]
				, "deviceassets_deviceasset_id" => $assetsrow[1]
				, "assets_asset_id" => $assetsrow[2]
				, "sensor_data_data_id" => $assetsrow[3]
				, "data_datetime" => $assetsrow[4]
				, "lat" => $assetsrow[5]
				, "long" => $assetsrow[6]
				, "alt" => $assetsrow[7]
				, "alarm_reset_status" => $assetsrow[8]
				, "alarm_ack_status" => $assetsrow[9]
				, "assets_positioncol" => $assetsrow[10]
				, "imei" => $assetsrow[11]
				, "asset_name" => $assetsrow[12]
				, "level_name" => $assetsrow[13]
				, "alt_range_id" => $assetsrow[14]
				, "asset_type" => $assetsrow[15]);
				$json_assetpositions = array_merge($json_assetpositions,array("response_$outputid" => $json_assetposition));
				$outputid++;
			}
			$json = array("responses" => $json_assetpositions);
			echo json_encode($json);
		}
		else{
			die ("{\"error\":\"NO_DATA\"}");
		}
	}
	else{
		die ("{\"error\":\"NO_DATA\"}");
	}
	$pdo = null;
	$stm = null;
?>