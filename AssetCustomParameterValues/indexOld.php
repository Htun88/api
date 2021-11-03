<?php 
	$API = "AssetCustomParameterValues";
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
			`asset_custom_param_values`.`id`,
			`asset_custom_param_values`.`value`,
			`asset_custom_param_values`.`assets_asset_id`,
			`asset_custom_param_values`.`asset_custom_param_id`,
			`asset_custom_param_group_components`.asset_custom_param_group_id,
			`asset_custom_param`.`name`,
			`asset_custom_param`.`tag_name`,
			`asset_custom_param_group`.`name`,
			`asset_custom_param_group`.`tag_name`
			FROM `asset_custom_param_values`
			JOIN asset_custom_param_group_components ON asset_custom_param_group_components.asset_custom_param_id = asset_custom_param_values.asset_custom_param_id
			JOIN asset_custom_param ON asset_custom_param.id = asset_custom_param_group_components.asset_custom_param_id
			JOIN asset_custom_param_group ON asset_custom_param_group.id = asset_custom_param_group_components.asset_custom_param_group_id
			WHERE 1 = 1";


	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `assets`.`asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_parent = array();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"id"=>$dbrow[0]
			, "value" => $dbrow[1]
			, "asset_id" => $dbrow[2]
			, "asset_custom_param_id" => $dbrow[3]
			, "asset_custom_param_group_id" => $dbrow[4]
			, "asset_custom_param_name" => $dbrow[5]
			, "asset_custom_param_tag_name" => $dbrow[6]
			, "asset_custom_param_group_name" => $dbrow[7]
			, "asset_custom_param_group_tag_name" => $dbrow[8]);

			$json_parent = array_merge($json_parent,array("assetcustomparamvalue $outputid" => $json_child));
			$outputid++;
		}
		
		$json = array("assetcustomparamvalues" => $json_parent);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"NO_DATA\"}");
	} 
}


// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************


elseif($sanitisedInput['action'] == "insert"){
	
	$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);
	$insertArray = [];

	if(isset($inputarray['asset_id'])){
		$insertArray['assets_asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);		
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
			die("{\"error\":\"ASSET_ID_INACTIVE\"}");
		}					
	}
	else {
		errorMissing("asset_id", $API, $logParent);
	}
			
	if (isset($inputarray['asset_custom_param_id'])) {
		$insertArray['asset_custom_param_id'] = sanitise_input($inputarray['asset_custom_param_id'], "asset_custom_param_id", null, $API, $logParent);
		$stm = $pdo->query("SELECT * FROM asset_custom_param where id = '" . $insertArray['asset_custom_param_id'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){                    
		}
		else{
			errorInvalid("asset_custom_param_id", $API, $logParent);
		}
	}
	else {
		errorMissing("asset_custom_param_id", $API, $logParent);
	}

	if(isset($inputarray['value'])){
		$insertArray['value'] = sanitise_input($inputarray['value'], "value", $schemainfoArray['value'], $API, $logParent);
	}
	else{
			//get the default value from asset custom param table
			$sql ="SELECT 
					default_value
					FROM asset_custom_param			
					WHERE id = '" . $insertArray['asset_custom_param_id'] . "'";						
			$stm = $pdo->query($sql);	
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if(isset($rows[0][0])){
				$insertArray['value'] = $rows[0][0];
			} 
			else{
				errorInvalid("value", $API, $logParent);
			}
		}
	
		try{               
			$sql = "INSERT INTO asset_custom_param_values(
					`value`
					, `assets_asset_id`
					, `asset_custom_param_id`)
					VALUES (
							:value
						, :assets_asset_id
						, :asset_custom_param_id )";	
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($insertArray)){
				$insertArray['id'] = $pdo->lastInsertId();
				$insertArray ['error' ] = "NO_ERROR";
				echo json_encode($insertArray);
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
			}
					
		}catch(\PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"" . $e . "\"}");
		}
}


// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************

elseif($sanitisedInput['action'] == "update"){  

		$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);       
		$updateArray = [];

		if(isset($inputarray['id'])){
			$updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
			$stm = $pdo->query("SELECT id FROM asset_custom_param_values where id = '" . $updateArray['id'] . "'");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($dbrows[0][0])){  
				errorInvalid("id", $API, $logParent);      
			}	
		}
		else{
			errorMissing("id", $API, $logParent);  
		}

		if(isset($inputarray['value'])){
			$updateArray['value'] = sanitise_input($inputarray['value'], "value", $schemainfoArray['value'], $API, $logParent);
			$sql = "SELECT
					asset_custom_param_values.id
					FROM (users
					, user_assets
					, assets
					,asset_custom_param_values)
					LEFT JOIN userasset_details
					ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
					WHERE users.user_id = user_assets.users_user_id
					AND user_assets.users_user_id = $user_id
					AND ((user_assets.asset_summary = 'some'
						AND assets.asset_id = userasset_details.assets_asset_id)
					OR (user_assets.asset_summary = 'all'))
					AND asset_custom_param_values.assets_asset_id = assets.asset_id
					AND asset_custom_param_values.id = '" . $updateArray['id'] . "'";

			$stm = $pdo->query($sql);
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($dbrows[0][0])){  
				errorInvalid("value", $API, $logParent);      
			}	
		}
		else{
			errorMissing("value", $API, $logParent);
		}
	
		try{
			if (count($updateArray) < 2) {
				logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
				die("{\"error\":\"NO_UPDATED_PRAMS\"}");
			}	
			else {
				$sql = "UPDATE 
						asset_custom_param_values 
						SET  `value` = :value
						WHERE `id` = :id";
	
				$stm= $pdo->prepare($sql);	
				if($stm->execute($updateArray)){
					$updateArray ['error'] = "NO_ERROR";
					echo json_encode($updateArray);
					logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
				}
			}
		}catch(\PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"$e\"}");
		}					
}

// *******************************************************************************
// *******************************************************************************
// *****************************DELETE********************************************
// *******************************************************************************
// *******************************************************************************

elseif($sanitisedInput['action'] == "delete"){
	$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);
	try
	{	
		if(isset($inputarray['id'])){
			$deleteArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
			$stm = $pdo->query("SELECT id FROM asset_custom_param_values where id = '" . $deleteArray['id'] . "'");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($dbrows[0][0])){  
				errorInvalid("id", $API, $logParent);      
			}	
		}
		else{
			errorMissing("id", $API, $logParent);  
		}

		$sqldelete = "DELETE FROM `asset_custom_param_values` WHERE `id` = '" . $deleteArray['id'] . "'";
		$stmt= $pdo->prepare($sqldelete);                   
		if($stmt->execute()){
			$deleteArray['error' ] = "NO_ERROR";
			echo json_encode($deleteArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($deleteArray)), logLevel::response, logType::response, $token, $logParent);
		}
		
	}catch(\PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}	
}
else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

?>