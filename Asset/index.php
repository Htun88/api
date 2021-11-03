<?php
	$API = "Asset";
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

	$schemainfoArray = getMaxString ("assets", $pdo);
	
	if (isset($inputarray['user_id'])) {
		$sanitisedInput['user_id'] = sanitise_input_array($inputarray['user_id'], "user_id", null, $API, $logParent);
	}else{
		$sanitisedInput['user_id'][] = $user_id;
	}
	
	

	$sql = "SELECT 
			  filtered.asset_id	
			, filtered.asset_type
            , filtered.asset_name
            , filtered.asset_marker_image
            , filtered.asset_marker_gray
            , filtered.asset_marker_gif
            , filtered.asset_marker_color 
            , filtered.html_colorcode
            , filtered.active_status
            , filtered.last_modified_by
            , filtered.last_modified_datetime	
FROM  (user_assets, (

SELECT        assets.asset_id
			, assets.asset_type
            , assets.asset_name
            , assets.asset_marker_image
            , assets.asset_marker_gray
            , assets.asset_marker_gif
            , assets.asset_marker_color 
            , assets.html_colorcode
            , assets.active_status
            , assets.last_modified_by
            , assets.last_modified_datetime
			";
            

	if (isset($inputarray['trigger_id'])){
		$sanitisedInput['trigger_id'] = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		$sql .= "
			FROM (users
			, user_assets
			, assets
			, deviceassets
			, deviceassets_trigger_det
			, trigger_groups)
			LEFT JOIN userasset_details 
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id

			WHERE trigger_groups.trigger_id = '" . $sanitisedInput['trigger_id'] . "'
			AND trigger_groups.trigger_id = deviceassets_trigger_det.trigger_groups_trigger_id
			AND deviceassets_trigger_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND deviceassets.active_status = 0
			AND deviceassets.date_to IS NULL
			AND users.user_id = user_assets.users_user_id 
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some' 
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			) AS filtered)
			 LEFT JOIN userasset_details 
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id  

			WHERE user_assets.users_user_id IN (" . implode( ', ',$sanitisedInput['user_id'] ) . ") 
			AND ((user_assets.asset_summary = 'some' 
				AND filtered.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))";
	}
	else {
		$sql .= "				
			FROM (
			assets
			,users
			, user_assets
			)
			LEFT JOIN userasset_details 
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id  

			WHERE users.user_id = user_assets.users_user_id 
			AND  user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some' 
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			) AS filtered) 
			 LEFT JOIN userasset_details 
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id  

			WHERE user_assets.users_user_id IN (" . implode( ', ',$sanitisedInput['user_id'] ) . ") 
			AND ((user_assets.asset_summary = 'some' 
				AND filtered.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))";
	}



	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['asset_name'])){
		$sanitisedInput['asset_name'] = sanitise_input($inputarray['asset_name'], "asset_name", $schemainfoArray['asset_name'], $API, $logParent);
		$sql .= " AND `asset_name` = '". $sanitisedInput['asset_name'] ."'";
	}

	if(isset($inputarray['asset_type'])){
		$sanitisedInput['asset_type'] = sanitise_input($inputarray['asset_type'], "asset_type", $schemainfoArray['asset_type'], $API, $logParent);
		$sql .= " AND `asset_type` = '". $sanitisedInput['asset_type'] ."'";
	}

	if (isset($inputarray['active_status'])) {
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `assets`.`active_status` = '". $sanitisedInput['active_status'] ."'";
	}

	$sql .= "  ORDER BY asset_name DESC ";

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	//echo $sql;
	$stm = $pdo->query($sql);
	$assetsrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($assetsrows[0][0])){							
		$json_assets  = array();
		$outputid = 0;
		foreach($assetsrows as $assetsrow){
			$json_asset = array(
			"asset_id" => $assetsrow[0]
			, "asset_type" => $assetsrow[1]
			, "asset_name" => $assetsrow[2]
			, "asset_marker_image" => $assetsrow[3]
			, "asset_marker_gray" => $assetsrow[4]
			, "asset_marker_gif" => $assetsrow[5]
			, "asset_marker_color" => $assetsrow[6]
			, "html_colorcode" => $assetsrow[7]
			, "active_status" => $assetsrow[8]
			, "last_modified_by" => $assetsrow[9]
			, "last_modified_datetime" => $assetsrow[10]);
			$json_assets = array_merge($json_assets,array("response_$outputid" => $json_asset));
			$outputid++;
		}
		$json = array("responses" => $json_assets);
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
// ***********************************INSERT**************************************
// *******************************************************************************
// ******************************************************************************* 

else if($inputarray['action'] == "insert"){

	$schemainfoArray = getMaxString ("assets", $pdo);
	$insertArray = [];

	if(isset($inputarray['asset_type'])){
		$insertArray['asset_type'] = sanitise_input($inputarray['asset_type'], "asset_type", $schemainfoArray['asset_type'], $API, $logParent);
    }
	else {
		errorMissing("asset_type", $API, $logParent);
	}

	if (isset($inputarray['asset_name'])){
		$insertArray['asset_name'] = sanitise_input($inputarray['asset_name'], "asset_name", $schemainfoArray['asset_name'], $API, $logParent);
	}
	else {
		errorMissing("asset_name", $API, $logParent);
	}

	if (isset($inputarray['asset_marker_image'])){
		$insertArray['asset_marker_image'] = sanitise_input($inputarray['asset_marker_image'], "asset_marker_image", $schemainfoArray['asset_marker_image'], $API, $logParent);
	}

	if (isset($inputarray['asset_marker_gray'])){
		$insertArray['asset_marker_gray'] = sanitise_input($inputarray['asset_marker_gray'], "asset_marker_gray", $schemainfoArray['asset_marker_gray'], $API, $logParent);
	}	

	if (isset($inputarray['asset_marker_gif'])){
		$insertArray['asset_marker_gif'] = sanitise_input($inputarray['asset_marker_gif'], "asset_marker_gif", $schemainfoArray['asset_marker_gif'], $API, $logParent);
	}

	if (isset($inputarray['asset_marker_color'])){
		$insertArray['asset_marker_color'] = sanitise_input($inputarray['asset_marker_color'], "asset_marker_color", $schemainfoArray['asset_marker_color'], $API, $logParent);
	}
	else {
		$insertArray['asset_marker_color'] = "Green";
	}

	if (isset($inputarray['html_colorcode'])){
		$insertArray['html_colorcode'] = sanitise_input($inputarray['html_colorcode'], "html_colorcode", $schemainfoArray['html_colorcode'], $API, $logParent);
	}
	else {
		$insertArray['html_colorcode'] = "#000000";
	}

	if (isset($inputarray['active_status'])){
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		errorMissing("active_status", $API, $logParent);
	}
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO assets(
			`asset_type`
			, `asset_name`
			, `asset_marker_image`
			, `asset_marker_gray`
			, `asset_marker_gif`
			, `asset_marker_color`
			, `html_colorcode`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`) 
		VALUES (
			:asset_type
			, :asset_name";
		if (isset($insertArray['asset_marker_image'])){
			$sql .= ", :asset_marker_image";
		}
		else {
			$sql .= ", NULL";
		}
		if (isset($insertArray['asset_marker_gray'])){
			$sql .= ", :asset_marker_gray";
		}
		else {
			$sql .= ", NULL";
		}
		if (isset($insertArray['asset_marker_gif'])){
			$sql .= ", :asset_marker_gif";
		}
		else {
			$sql .= ", NULL";
		}
		$sql .= "
			, :asset_marker_color
			, :html_colorcode
			, :active_status
			, $user_id
			, '$timestamp')";
		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['asset_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch (PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// **********************************UPDATE***************************************
// *******************************************************************************
// ******************************************************************************* 

else if($inputarray['action'] == "update"){

	$schemainfoArray = getMaxString ("assets", $pdo);
	$updateArray = [];
	$updateString = "";

	if (isset($inputarray['asset_id'])){
		$inputarray['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$stm = $pdo->query("SELECT
				assets.asset_id
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
            AND assets.asset_id = '" . $inputarray['asset_id'] . "'");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($dbrows[0][0])){
            $updateArray['asset_id'] = $inputarray['asset_id'];
        }
        else {
			errorInvalid("asset_id", $API, $logParent);
        }     
	}
	else {
		errorMissing("asset_id", $API, $logParent);
	}


	if(isset($inputarray['asset_type'])){
		$updateArray['asset_type'] = sanitise_input($inputarray['asset_type'], "asset_type", $schemainfoArray['asset_type'], $API, $logParent);
		$updateString .= " `asset_type` = :asset_type,";
    }

	if (isset($inputarray['asset_name'])){
		$updateArray['asset_name'] = sanitise_input($inputarray['asset_name'], "asset_name", $schemainfoArray['asset_name'], $API, $logParent);
		$updateString .= " `asset_name` = :asset_name,";
	}

	if (isset($inputarray['asset_marker_image'])){
		$updateArray['asset_marker_image'] = sanitise_input($inputarray['asset_marker_image'], "asset_marker_image", $schemainfoArray['asset_marker_image'], $API, $logParent);
		$updateString .= " `asset_marker_image` = :asset_marker_image,";
	}

	if (isset($inputarray['asset_marker_gray'])){
		$updateArray['asset_marker_gray'] = sanitise_input($inputarray['asset_marker_gray'], "asset_marker_gray", $schemainfoArray['asset_marker_gray'], $API, $logParent);
		$updateString .= " `asset_marker_gray` = :asset_marker_gray,";
	}

	if (isset($inputarray['asset_marker_gif'])){
		$updateArray['asset_marker_gif'] = sanitise_input($inputarray['asset_marker_gif'], "asset_marker_gif", $schemainfoArray['asset_marker_gif'], $API, $logParent);
		$updateString .= " `asset_marker_gif` = :asset_marker_gif,";
	}

	if (isset($inputarray['asset_marker_color'])){
		$inputarray['asset_marker_color'] = sanitise_input($inputarray['asset_marker_color'], "asset_marker_color", $schemainfoArray['asset_marker_color'], $API, $logParent);
		$updateString .= " `asset_marker_color` = :asset_marker_color,";
	}

	if (isset($inputarray['html_colorcode'])){
		$updateArray['html_colorcode'] = sanitise_input($inputarray['html_colorcode'], "html_colorcode", $schemainfoArray['html_colorcode'], $API, $logParent);
		$updateString .= " `html_colorcode` = :html_colorcode,";
	}
		
	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$updateString .= " `active_status` = :active_status,";
	}

	try {
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$sql = "UPDATE 
				assets 
				SET". $updateString . " `last_modified_by` = $user_id
				, `last_modified_datetime` = '$timestamp' 
				WHERE `asset_id` = :asset_id";

			$stm= $pdo->prepare($sql);	
			if($stm->execute($updateArray)){
				$updateArray ['error' ] = "NO_ERROR";
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

?>