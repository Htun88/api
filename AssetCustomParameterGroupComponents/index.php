<?php 
	$API = "AssetCustomParameterGroupComponents";
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

	if($sanitisedInput['action'] == "select"){
		$sql = "SELECT 
			`id`,
			`asset_custom_param_group_id`,
			`asset_custom_param_id`,
			`active_status`,
			`last_modified_by`,
			`last_modified_datetime`
		FROM `asset_custom_param_group_components`
		WHERE 1 = 1";

		if (isset($inputarray['group_id'])){
			$sanitisedArray['group_id'] = sanitise_input($inputarray['group_id'], "group_id", null, $API, $logParent);
			$sql .= " AND `asset_custom_param_group_components`.`asset_custom_param_group_id` = '". $sanitisedArray['group_id'] ."'";
		}  

		if (isset($inputarray['param_id'])){
			$sanitisedArray['param_id'] = sanitise_input($inputarray['param_id'], "param_id", null, $API, $logParent);
			$sql .= " AND `asset_custom_param_group_components`.`asset_custom_param_id` = '". $sanitisedArray['param_id'] ."'";
		}  

		if (isset($inputarray['active_status'])){
			$sanitisedArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
			$sql .= " AND `asset_custom_param_group_components`.`active_status` = '". $sanitisedArray['active_status'] ."'";
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
			$json_parent = array();
			$outputid = 0;
			foreach($dbrows as $dbrow){
				$json_child = array(
				"id"=>$dbrow[0]
				, "asset_custom_param_group_id" => $dbrow[1]
				, "asset_custom_param_id" => $dbrow[2]
				, "active_status" => $dbrow[3]
				, "last_modified_by" => $dbrow[4]
				, "last_modified_datetime" => $dbrow[5]
				);
				
				$json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
				$outputid++;
			}
			$json = array("responses" => $json_parent);
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
				
		$insertArray = [];
		
		if(isset($inputarray['group_id'])){
			$insertArray['group_id'] = sanitise_input($inputarray['group_id'], "group_id", null, $API, $logParent);
			$stm = $pdo->query("SELECT * FROM asset_custom_param_group where id = '" . $insertArray['group_id'] . "'");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($dbrows[0][0])){                    
			}
			else{
				errorInvalid("group_id", $API, $logParent);
			}
		}else {
			errorMissing("group_id", $API, $logParent);
		}

		if(isset($inputarray['param_id'])){
			$insertArray['param_id'] = sanitise_input_array($inputarray['param_id'], "param_id", null, $API, $logParent);			
			$stm = $pdo->query("SELECT * FROM asset_custom_param where id IN (" . implode( ', ', $insertArray['param_id'] ) . ")");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($dbrows[0][0])){                    
			}
			else{
				errorInvalid("param_id", $API, $logParent);
			}
		}else {
			errorMissing("param_id", $API, $logParent);
		}
		
		if (isset($inputarray['active_status'])) {
			$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		}
		else {
			errorMissing("active_status", $API, $logParent);
		}
		
		$insertArray['last_modified_by'] = $user_id;
		$insertArray['last_modified_datetime'] = $timestamp;

		$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$paramGroupComponentValues = array();
		foreach ($insertArray['param_id'] as $param_id){
		  $paramidArray[] = $param_id;
		  $paramGroupComponentValues[] .= "( " . $insertArray['group_id'] . ", " . $param_id . ", " .  $insertArray['active_status']  . ", " . $user_id . ", '" . $timestamp . "')";      
		}

		$parameter_ids = implode(',', $paramidArray);

		$sql = "INSERT INTO asset_custom_param_group_components(
            `asset_custom_param_group_id`
          , `asset_custom_param_id`
          , `active_status`
          , `last_modified_by`
          , `last_modified_datetime`)
          VALUES " . implode(', ', $paramGroupComponentValues) . "
          ON DUPLICATE KEY UPDATE
            asset_custom_param_id = VALUES(asset_custom_param_id)
          ,asset_custom_param_group_id = VALUES (asset_custom_param_group_id)";

        //echo $sql;
        
		try{ 
			$stmt= $pdo->prepare($sql);
			if($stmt->execute()){
				
			$sql = "SELECT id
					FROM
					asset_custom_param_group_components
					WHERE asset_custom_param_id IN (" . $parameter_ids . ")
					AND asset_custom_param_group_id = '". $insertArray['group_id'] . "'
					ORDER BY id ASC";
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])) {
				errorGeneric("Issue", $API, $logParent);
			}

			$outputArray['id'] = array_column($rows, 0);
			$outputArray['group_id'] = $insertArray['group_id'];
			$outputArray['parameter_id'] = $parameter_ids;
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
	}


	// *******************************************************************************
	// *******************************************************************************
	// *****************************UPDATE********************************************
	// *******************************************************************************
	// *******************************************************************************


	else if($sanitisedInput['action'] == "update"){       

		$updateString = "";

			if (isset($inputarray['id'])) {
				if (isset($inputarray['group_id']) || isset($inputarray['param_id']))
				 {
					errorGeneric("Incompatable_Identification_params", $API, $logParent);
				 }
			}
			else{
				if (!isset($inputarray['group_id'])) {
					errorMissing("group_id", $API, $logParent);
				}
				if (!isset($inputarray['param_id'])) {
					errorMissing("param_id", $API, $logParent);
				}
			}
  
		if (isset($inputarray['id'])) {
			$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
			$sql = "SELECT id
					FROM asset_custom_param_group_components
					WHERE id IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
		
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($rows[0][0])){
			 errorInvalid("id", $API, $logParent);
			}
		}

		if (isset($inputarray['group_id'])) {
			$sanitisedInput['group_id'] = sanitise_input($inputarray['group_id'], "group_id", null, $API, $logParent);
			$stm = $pdo->query("SELECT id FROM asset_custom_param_group where id = '" .  $sanitisedInput['group_id'] . "'");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($dbrows[0][0])){                    
				errorInvalid("group_id", $API, $logParent);
			}
		}

		if (isset($inputarray['param_id'])){
			$sanitisedInput['param_id'] = sanitise_input_array($inputarray['param_id'], "param_id", null, $API, $logParent);
			$stm = $pdo->query("SELECT * FROM asset_custom_param where id IN (" . implode( ', ',$sanitisedInput['param_id'] ) . ")");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (!isset($dbrows[0][0])){                    
				errorInvalid("param_id", $API, $logParent);
			}
		}

		if(isset($inputarray['active_status'])){
			$updateArray["active_status"] =  sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);	
			$updateString .= " active_status= :active_status,";
		}
		else{
			errorMissing("active_status", $API, $logParent);
		}
    
     	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

     try{ 
			if (count($updateArray) < 1) {
				logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
				die("{\"error\":\"NO_UPDATED_PRAM\"}");
			}
			else {
					$updateArray ['last_modified_by'] = $user_id;
					$updateArray ['last_modified_datetime'] = $timestamp;
					$updateString .= "`last_modified_by` = :last_modified_by
							        , `last_modified_datetime` = :last_modified_datetime";
					
					$sql = "UPDATE asset_custom_param_group_components 
							SET " . $updateString;
					if (isset($inputarray['id'])){
						$sql .= " WHERE `id` IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
					}
					else {
						$updateArray["group_id"]  = $sanitisedInput['group_id'];
						$sql .= " WHERE `asset_custom_param_group_id` = :group_id AND asset_custom_param_id IN (" . implode( ', ', $sanitisedInput['param_id'] ) . ")";
					}

					$stm= $pdo->prepare($sql);
					if ($stm->execute($updateArray)){
						if (isset($inputarray['id'])){
							$updateArray['id'] = $sanitisedInput['id']; 
						}
						else {
							$updateArray['group_id'] = $sanitisedInput['group_id'];
						}

						$updateArray['error' ] = "NO_ERROR";
						echo json_encode($updateArray);
						logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
					}
				}
     	}
		catch(PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"$e\"}");
		}

		$pdo = null;
		$stm = null;
	}
?>