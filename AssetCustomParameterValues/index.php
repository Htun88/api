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

//	TODO. Currently its possible to insert duplicate information into the insert and update array. 
//	eg. { "btime" : //something, "btime" : //something else}
//	Fix w/ array unique? -Conor

if ($sanitisedInput['action'] == "select"){
	
	$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);
	$schemainfoArray2 = getMaxString ("asset_custom_param", $pdo);

	$sql = "SELECT
			asset_custom_param_values.id
			, asset_custom_param_values.value
			, asset_custom_param_values.assets_asset_id
			, asset_custom_param.name
			, asset_custom_param.tag_name
			, asset_custom_param_group.id
			, asset_custom_param_group.name
			, asset_custom_param_group.tag_name	
		FROM (
			asset_custom_param_values
			, users
			, user_assets
			, assets )
		LEFT JOIN asset_custom_param ON asset_custom_param.id = asset_custom_param_values.asset_custom_param_id
		LEFT JOIN asset_custom_param_group_components ON asset_custom_param_group_components.asset_custom_param_id = asset_custom_param_values.asset_custom_param_id
		LEFT JOIN asset_custom_param_group ON asset_custom_param_group.id = asset_custom_param_group_components.asset_custom_param_group_id
		LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			
		WHERE users.user_id = user_assets.users_user_id
		AND user_assets.users_user_id = $user_id
		AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
		OR (user_assets.asset_summary = 'all'))
		AND asset_custom_param_values.assets_asset_id = assets.asset_id";

	if (isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " AND `asset_custom_param_values`.`id` IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
	}

	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `asset_custom_param_values`.`assets_asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['custom_param_id'])){
		$sanitisedInput['custom_param_id'] = sanitise_input_array($inputarray['custom_param_id'], "custom_param_id", null, $API, $logParent);
		$sql .= " AND `asset_custom_param_values`.`asset_custom_param_id` IN (" . implode( ', ',$sanitisedInput['custom_param_id'] ) . ")";
	}

	if (isset($inputarray['tag_name'])) {
		$sanitisedInput['tag_name'] = sanitise_input_array($inputarray['tag_name'], "custom_param_value", $schemainfoArray2['tag_name'], $API, $logParent);
		$sql .= " AND `asset_custom_param`.`tag_name` IN ('" . implode( "', '",$sanitisedInput['tag_name'] ) . "')";
	}

	if (isset($inputarray['value'])){
		$sanitisedInput['value'] = sanitise_input_array($inputarray['value'], "value",  $schemainfoArray['value'], $API, $logParent);
		$sql .= " AND `asset_custom_param_values`.`value` IN (" . implode( ', ',$sanitisedInput['value'] ) . ")";
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
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_parent = array();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"id"=>$dbrow[0]
			, "asset_id" => $dbrow[2]
			, "value" => $dbrow[1]
			, "name" => $dbrow[3]
			, "tag" => $dbrow[4]
			, "group_id" => $dbrow[5]
			, "group_name" => $dbrow[6]
			, "group_tag" => $dbrow[7]);

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


else if ($sanitisedInput['action'] == "insert"){

	$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);
	$schemainfoArray2 = getMaxString ("asset_custom_param", $pdo);

	$insertArray = [];

	if(isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);		
		$sql ="SELECT 
				assets.asset_id 
				, assets.active_status
				FROM (
					  users
					, user_assets
					, assets)
				LEFT JOIN userasset_details
					ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
				WHERE users.user_id = user_assets.users_user_id
				AND user_assets.users_user_id = $user_id
				AND ((user_assets.asset_summary = 'some'
					AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'all'))
				AND assets.asset_id = " . $sanitisedInput['asset_id'];			

		$stm = $pdo->query($sql);	
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("asset_id", $API, $logParent);
		}

		if ($rows[0][1] == 1){
			errorGeneric("ASSET_ID_INACTIVE", $API, $logParent);
		}	
		$insertArray['asset_id'] = $sanitisedInput['asset_id'];
		$configUpdate['asset_id'] = $sanitisedInput['asset_id'];				
	}
	else {
		errorMissing("asset_id", $API, $logParent);
	}
			
	if(isset($inputarray['value'])){
		$sanitisedInput['value'] = sanitise_input_array(array_keys($inputarray['value']), "value", $schemainfoArray2['tag_name'], $API, $logParent);
		//	This looks unnecessary but its not. 
		//	We need to sanitise both the keys and their values. Values are sanitised later in the loop, keys are sanitised here. 
		//	The sanitiseation above rips the keys out of the input array, sanitises them and stores them in the sanitised input array. 
		//	These are then paired with their unsanitised values from the input array, and the loop proceeds
		$sanitisedInput['value'] = array_combine($sanitisedInput['value'], $inputarray['value']);

		$sql = "SELECT 
				id
				, tag_name
				, default_value
			FROM
				asset_custom_param ";
		//	We can either input value as strings OR as the ID values, but we don't want to mix 'n match cause that makes the SQL gross. 
		//	Here we make sure that the user is only inputting one type or the other
		$countVal = 0;
		$countChar = 0;
		foreach ($sanitisedInput['value'] as $key => $value){
			if (is_numeric($key)) {
				$countVal++;
			}
			else {
				$countChar++;
			}
		}
		//	Figure out if the user has input a series of INT or series of STRINGS. Combining them is not allowed
		if ($countVal == count($sanitisedInput['value'])){			
			$sql .= "WHERE id IN ('" . implode("', '", array_keys($sanitisedInput['value'])) . "')";
			//	Rowcheck is a variable needed to check things with array check. It points to which returned array column to check
			$rowCheck = 0;
		}
		else if ($countChar == count($sanitisedInput['value'])){
			$sql .= "WHERE tag_name IN ('" . implode("', '", array_keys($sanitisedInput['value'])) . "')";
			$rowCheck = 1;
		}
		else {
			errorGeneric("conflicting_value_types", $API, $logParent);
		}
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("value", $API, $logParent);
		}
		arrayExistCheck(array_keys($sanitisedInput['value']), array_column($rows, $rowCheck), "value", $API, $logParent);

		//	Push the variables into two arrays, one keyed by tag_name, the other keyed by ID number.
		//	We use these to compare against below, avoiding foreach loops
		foreach ($rows as $key => $value) {
			$defaultsChar[$value[1]]['id'] = $value[0];
			$defaultsChar[$value[1]]['tag_name'] = $value[1];
			$defaultsChar[$value[1]]['default'] = $value[2];

			$defaultsInt[$value[0]]['id'] = $value[0];
			$defaultsInt[$value[0]]['tag_name'] = $value[1];
			$defaultsInt[$value[0]]['default'] = $value[2];
		}

		//	Todo nicen this up. Theres a better way to do this than make multiple arrays, but rn brain not worky. It works though -Conor
		//	Although we are pushing things to data, we still want to also keep the insert array format for logging purposes
		$i = 0;
		foreach ($sanitisedInput['value'] as $key => $value) {
			//	Sanitise the value
			$value = sanitise_input($value, "custom_param_value", $schemainfoArray['value'], $API, $logParent);
			//	Split this two ways. Either you are dealing with a numeric input, or you aren't. 
			//	Both have slight variations, so we just deal with them completely seperately
			
			if (is_numeric($key)){
				if ($value == "Default" ) {
					$value = $defaultsInt[$key]['default'];
				}
				$insertArray["value"][$i]['id'] =  $defaultsInt[$key]['id'];
				$finalVal = $defaultsInt[$key]['id'];
			}
			else {
				if ($value == "Default" ) {
					$value = $defaultsChar[$key]['default'];
				}
				$insertArray["value"][$i]['id'] =  $defaultsChar[$key]['id'];	
				$finalVal = $defaultsChar[$key]['id'];
			}
			$data[] = "(" . $value . ", " . $insertArray['asset_id'] . ", " . $finalVal . ")";

			$insertArray["value"][$i]['tag_name'] = $key;
			$insertArray["value"][$i]['id'] =  $finalVal;
			$insertArray["value"][$i]['value'] = $value;	
			$i++;
		}
	}
	else {
		errorMissing("value", $API, $logParent);
	}


	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	try{    
		
		//	TODO need to add code to check if you're allowed to insert/update for this asset
		$sql = "INSERT INTO asset_custom_param_values(
				`value`
				, `assets_asset_id`
				, `asset_custom_param_id`)
			VALUES " . implode(", ", $data) . "
			ON DUPLICATE KEY UPDATE
				`value` = VALUES(`value`)";

		$stmt= $pdo->prepare($sql);
		if($stmt->execute()){
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

else if($sanitisedInput['action'] == "update"){  

	$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);  
	$schemainfoArray2 = getMaxString ("asset_custom_param", $pdo);
     
	$updateArray = [];
	$updateString = "";

	/*

	//	Updating by id introduces a number of concerns and edge cases. 
	//	Would need to rewrite the whole value section to accomodate it. 
	//		as it is currently written for the { "id" : "value" } method
	//	As such, have disabled updating by ID for the moment, as running out of time. 
	//	-Conor 21-9-2021

	if(isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
		$sql = "SELECT 
				asset_custom_param_values.id
				, asset_custom_param.tag_name
				, asset_custom_param.default_value
				, assets.asset_id
			FROM (
				users
				, user_assets
				, assets )
			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			LEFT JOIN asset_custom_param_values ON asset_custom_param_values.assets_asset_id = assets.asset_id
				AND asset_custom_param_values.id = " . $sanitisedInput['id'] . "
			LEFT JOIN asset_custom_param ON asset_custom_param.id = asset_custom_param_values.asset_custom_param_id
			WHERE users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND asset_custom_param_values.assets_asset_id = assets.asset_id";
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){  
			errorInvalid("id", $API, $logParent);      
		}
		$updateArray['id'] = $sanitisedInput['id'];
		$sanitisedInput['tag_name'] = $dbrows[0][1];
		$sanitisedInput['default'] = $dbrows[0][2];
		$sanitisedInput['asset_id'] = $dbrows[0][3];
		$configUpdate['asset_id'] = $dbrows[0][3];
	}
	*/

	if(isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
	}
	else {
		errorMissing("asset_id", $API, $logParent);
	}

	if(isset($inputarray['value'])){
		$sanitisedInput['value'] = sanitise_input_array(array_keys($inputarray['value']), "value", $schemainfoArray2['tag_name'], $API, $logParent);
		$sanitisedInput['value'] = array_combine($sanitisedInput['value'], $inputarray['value']);

		$sql = "SELECT 
				asset_custom_param.id
				, asset_custom_param.tag_name
				, asset_custom_param.default_value
				, assets.asset_id
			FROM (
				users
				, user_assets
				, assets )
			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			LEFT JOIN asset_custom_param_values ON asset_custom_param_values.assets_asset_id = assets.asset_id
			LEFT JOIN asset_custom_param ON asset_custom_param.id = asset_custom_param_values.asset_custom_param_id
			WHERE users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND asset_custom_param_values.assets_asset_id = assets.asset_id
			AND assets.asset_id = ". $sanitisedInput['asset_id'];

		//	We can either input value as strings OR as the ID values, but we don't want to mix 'n match cause that makes the SQL gross. 
		//	Here we make sure that the user is only inputting one type or the other
		$countVal = 0;
		$countChar = 0;
		foreach ($sanitisedInput['value'] as $key => $value){
			if (is_numeric($key)) {
				$countVal++;
			}
			else {
				$countChar++;
			}
		}
		//	Figure out if the user has input a series of INT or series of STRINGS. Combining them is not allowed
		if ($countVal == count($sanitisedInput['value'])){			
			$sql .= " AND asset_custom_param.id IN ('" . implode("', '", array_keys($sanitisedInput['value'])) . "')";
			//	Rowcheck is a variable needed to check things with array check. It points to which returned array column to check
			$rowCheck = 0;
		}
		else if ($countChar == count($sanitisedInput['value'])){
			$sql .= " AND asset_custom_param.tag_name IN ('" . implode("', '", array_keys($sanitisedInput['value'])) . "')";
			$rowCheck = 1;
		}
		else {
			errorGeneric("conflicting_value_types", $API, $logParent);
		}

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("value", $API, $logParent);
		}
		arrayExistCheck(array_keys($sanitisedInput['value']), array_column($rows, $rowCheck), "value", $API, $logParent);
		$updateArray['asset_id'] = $rows[0][3];
		$configUpdate['asset_id'] = $rows[0][3];
		//	Push the variables into two arrays, one keyed by tag_name, the other keyed by ID number.
		//	We use these to compare against below, avoiding foreach loops
		foreach ($rows as $key => $value) {
			$defaultsChar[$value[1]]['id'] = $value[0];
			$defaultsChar[$value[1]]['tag_name'] = $value[1];
			$defaultsChar[$value[1]]['default'] = $value[2];

			$defaultsInt[$value[0]]['id'] = $value[0];
			$defaultsInt[$value[0]]['tag_name'] = $value[1];
			$defaultsInt[$value[0]]['default'] = $value[2];
		}
		//	Todo nicen this up. Theres a better way to do this than make multiple arrays, but rn brain not worky. It works though -Conor
		//	Although we are pushing things to data, we still want to also keep the insert array format for logging purposes
		$i = 0;
		$updateStringTag = "( ";
		$updateStringID = "( ";
		foreach ($sanitisedInput['value'] as $key => $value) {
			//	Sanitise the value
			$value = sanitise_input($value, "custom_param_value", $schemainfoArray['value'], $API, $logParent);
			//	Split this two ways. Either you are dealing with a numeric input, or you aren't. 
			//	Both have slight variations, so we just deal with them completely seperately
			
			if (is_numeric($key)){
				if ($value == "Default" ) {
					$value = $defaultsInt[$key]['default'];
				}
				$updateArray["value"][$i]['id'] =  $defaultsInt[$key]['id'];
				$finalVal = $defaultsInt[$key]['id'];
				$updateStringID.= " '" . $defaultsInt[$key]['id'] . "', ";
			}
			else {
				if ($value == "Default" ) {
					$value = $defaultsChar[$key]['default'];
				}
				$updateArray["value"][$i]['id'] =  $defaultsChar[$key]['id'];	
				$finalVal = $defaultsChar[$key]['id'];
				$updateStringTag .= " '" . $defaultsChar[$key]['tag_name'] . "', ";
			}
			$data[] = "(" . $value . ", " . $updateArray['asset_id'] . ", " . $finalVal . ")";

			$updateArray["value"][$i]['tag_name'] = $key;
			$updateArray["value"][$i]['id'] =  $finalVal;
			$updateArray["value"][$i]['value'] = $value;	
			$i++;
		}
		$updateStringTag = substr($updateStringTag, 0, -2) . " )";
		$updateStringID = substr($updateStringID, 0, -2) . " )";
	}
	else {
		errorMissing("value", $API, $logParent);
	}


	$sql = "SELECT DISTINCT
			asset_custom_param.tag_name
			, asset_custom_param_values.asset_custom_param_id
			, asset_custom_param_values.id
		FROM	
			asset_custom_param
		LEFT JOIN asset_custom_param_values ON asset_custom_param_values.asset_custom_param_id = asset_custom_param.id
		WHERE asset_custom_param_values.assets_asset_id = " . $updateArray['asset_id'];

	if (isset($inputarray['id'])){
		$sql .= " AND asset_custom_param_values.id IN " . $updateArray['id'];
		$rowCheck = 2;
		$checkAgainst = $updateArray['id'];
	}
	else {
		if ($countVal > $countChar){			
			$sql .= " AND asset_custom_param.id IN " . $updateStringID;
			$rowCheck = 0;
			$checkAgainst = array_keys($sanitisedInput['value']);
		}
		else {
			$sql .= " AND asset_custom_param.tag_name IN " . $updateStringTag;
			$rowCheck = 1;
			$checkAgainst = array_keys($sanitisedInput['value']);
		}		
	}

	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (!isset($rows[0][0])) {
		errorInvalid("value", $API, $logParent);
	}
	arrayExistCheck($checkAgainst, array_column($rows, $rowCheck), "value", $API, $logParent);
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	try{    
		
		//	This should be a standard update, but can't brain the right way to word it to update multiple rows based on unique keys.
		$sql = "INSERT INTO asset_custom_param_values(
				`value`
				, `assets_asset_id`
				, `asset_custom_param_id`)
			VALUES " . implode(", ", $data) . "
			ON DUPLICATE KEY UPDATE
				`value` = VALUES(`value`)";

		$stmt= $pdo->prepare($sql);
		if($stmt->execute()){
			$updateArray ['error' ] = "NO_ERROR";
			echo json_encode($updateArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
		}
				
	}catch(\PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"" . $e . "\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *****************************DELETE********************************************
// *******************************************************************************
// *******************************************************************************

elseif($sanitisedInput['action'] == "delete"){

	$schemainfoArray = getMaxString ("asset_custom_param_values", $pdo);  
	$schemainfoArray2 = getMaxString ("asset_custom_param", $pdo);


	if (isset($inputarray['id'])) {
		if (isset($inputarray['asset_id'])
			|| isset($inputarray['value'])){
			errorGeneric("Incompatible_identification_params", $API, $logParent);
		}
	}
	else {
		if (!isset($inputarray['asset_id'])){
			errorMissing("asset_id", $API, $logParent);
		}
		else if (!isset($inputarray['value'])){
			errorMissing("value", $API, $logParent);
		}
	}

	if(isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql = "SELECT 
				asset_custom_param_values.id
				, assets.asset_id
			FROM (
				users
				, user_assets
				, assets )
			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			LEFT JOIN asset_custom_param_values ON asset_custom_param_values.assets_asset_id = assets.asset_id
				AND asset_custom_param_values.id IN ( " . implode(", ", $sanitisedInput['id']) . ") 
			LEFT JOIN asset_custom_param ON asset_custom_param.id = asset_custom_param_values.asset_custom_param_id
			WHERE users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND asset_custom_param_values.assets_asset_id = assets.asset_id";
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($dbrows[0][0])){  
			errorInvalid("id", $API, $logParent);      
		}

		arrayExistCheck($sanitisedInput['id'], array_column($dbrows, 1), "id", $API, $logParent);

		$deleteArray['id'] = array_column($dbrows, 1);
		$configUpdate['asset_id'] = $dbrows[0][1];
	}

	if(isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
	}

	if (isset($inputarray['value'])) {

		//	This is an array of ID Int or tag_name strings for the asset_custom_param. 
		//	Need to check a) if they exist and b) are valid for the asset_id
		$sanitisedInput['value'] = sanitise_input_array($inputarray['value'], "value", $schemainfoArray2['tag_name'], $API, $logParent);

		$sql = "SELECT 
				asset_custom_param_values.id
				, asset_custom_param.id
				, asset_custom_param.tag_name
			FROM (
				users
				, user_assets
				, assets
				,asset_custom_param_values
				)
			LEFT JOIN asset_custom_param ON asset_custom_param.id = asset_custom_param_values.asset_custom_param_id
			LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND asset_custom_param_values.assets_asset_id = assets.asset_id
			AND asset_custom_param_values.assets_asset_id = ". $sanitisedInput['asset_id'];

				//	TODO 
				//	This sql is not working correctly. Unsure whats broke, but its pulling in all data, not working correctly

		$countVal = 0;
		$countChar = 0;
		foreach ($sanitisedInput['value'] as $key){
			if (is_numeric($key)) {
				$countVal++;
			}
			else {
				$countChar++;
			}
		}

		//	Figure out if the user has input a series of INT or series of STRINGS. Combining them is not allowed
		if ($countVal == count($sanitisedInput['value'])){			
			$sql .= " AND asset_custom_param.id IN ('" . implode("', '", $sanitisedInput['value']) . "')";
			//	Rowcheck is a variable needed to check things with array check. It points to which returned array column to check
			$rowCheck = 1;
		}
		else if ($countChar == count($sanitisedInput['value'])){
			$sql .= " AND asset_custom_param.tag_name IN ('" . implode("', '", $sanitisedInput['value']) . "')";
			$rowCheck = 2;
		}
		else {
			errorGeneric("conflicting_value_types", $API, $logParent);
		}
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($rows[0][0])) {
			errorInvalid("value", $API, $logParent);
		}
		arrayExistCheck($sanitisedInput['value'], array_column($rows, $rowCheck), "value", $API, $logParent);
		$configUpdate['asset_id'] = $sanitisedInput['asset_id'];
		$deleteArray['id'] = array_column($rows, 0);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($deleteArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try
	{	
		$sql = "DELETE FROM 
				`asset_custom_param_values` 
			WHERE `id` IN (" . implode (", ", $deleteArray['id']) . ")";

		$stmt= $pdo->prepare($sql);                   
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


//	We need to +1 the config version for all current deviceassets associated with the user for all calls (barring select)
//	As such, we're doing it here at the end of the script to cascade onto.
if ($sanitisedInput['action'] == "insert"
	|| $sanitisedInput['action'] == "update"
	|| $sanitisedInput['action'] == "delete"){

	try {
		$logArray = array();
		$logArray['action'] = "update";
		$logArray['updateTable'] = "devices";
		$logArray['context'] = "config version +1";
		$updateArray_2['asset_id'] = $configUpdate['asset_id'];
		$logParent = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($updateArray_2)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$sql ="UPDATE 
				assets
				, devices
				, deviceassets
			SET devices.configuration_version = (devices.configuration_version + 1)
			WHERE devices.device_id = deviceassets.devices_device_id
			AND deviceassets.date_to IS NULL
			AND deviceassets.assets_asset_id = " . $configUpdate['asset_id'];

		$stm= $pdo->prepare($sql);	
		if($stm->execute()){
			$updateArray_2 ['error'] = "NO_ERROR";
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray_2)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}	
}

$pdo = null;
$stm = null;

?>