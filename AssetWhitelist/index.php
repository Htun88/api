<?php 
    $API = "AssetWhitelist";
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
    $schemainfoArray = getMaxString ("asset_whitelist", $pdo);
    $sql = 
        "SELECT asset_whitelist.id
        , asset_whitelist.asset_number
        , asset_whitelist.assets_asset_id
        , asset_whitelist.active_status
        , asset_whitelist.last_modified_by
        , asset_whitelist.last_modified_datetime 
        FROM (users
        , user_assets
        , assets
        , asset_whitelist)
        LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
        WHERE asset_whitelist.assets_asset_id = assets.asset_id
        AND users.user_id = user_assets.users_user_id
        AND user_assets.users_user_id = $user_id
        AND ((user_assets.asset_summary = 'some' 
            AND assets.asset_id = userasset_details.assets_asset_id)
        OR (user_assets.asset_summary = 'all'))";


    if(isset($inputarray['whitelist_id'])){
        $sanitisedArray['whitelist_id'] = sanitise_input_array($inputarray['whitelist_id'], "whitelist_id", null, $API, $logParent);
        $sql .= " AND `id` IN ( '". implode("', '", $sanitisedArray['whitelist_id']) . "' )";
    }
    
    if (isset($inputarray['active_status'])) {
        $sanitisedArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
        $sql .= " AND `asset_whitelist`.`active_status` = '". $sanitisedArray['active_status'] ."'";
	}

    if (isset($inputarray['asset_id'])) {		
        $sanitisedArray['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
        $sql .= " AND `assets`.`asset_id` IN ( '". implode("', '", $sanitisedArray['asset_id']) . "' )";
    }

    if (isset($inputarray['asset_number'])) {		
        $sanitisedArray['asset_number'] = sanitise_input_array($inputarray['asset_number'], "whitelist_asset_number", $schemainfoArray['asset_number'], $API, $logParent);
        $sql .= " AND `asset_number` IN ( '". implode("', '", $sanitisedArray['asset_number']) . "' )";
	}

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    $stm = $pdo->query($sql);
    $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
    if (isset($dbrows[0][0])){
        $json_parent = array ();
        $outputid = 0;
        foreach($dbrows as $dbrow){
            $json_child = array(
            "whitelist_id"=>$dbrow[0]
            , "asset_id"=>$dbrow[2]
            , "asset_number"=>$dbrow[1]
            , "active_status"=>$dbrow[3]
            , "last_modified_by"=>$dbrow[4]
            , "last_modified_datetime"=>$dbrow[5]);
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
// *******************************INSERT******************************************
// *******************************************************************************
// ******************************************************************************* 

elseif($inputarray['action'] == "insert"){
    
    $insertArray = [];
    $schemainfoArray = getMaxString ("asset_whitelist", $pdo);

    if (isset($inputarray['asset_number'])){		
        $insertArray['asset_number'] = sanitise_input_array($inputarray['asset_number'], "whitelist_asset_number", $schemainfoArray['asset_number'], $API, $logParent);
    }
	else {
		errorMissing("asset_number", $API, $logParent);      
	}

    if (isset($inputarray['asset_id'])){		
        $sanitisedArray['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
        $sql = "SELECT
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
            AND assets.asset_id = '" . $sanitisedArray['asset_id'] . "'";

        $stm = $pdo->query($sql);       //TODO add asset to user
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){         
            errorInvalid("asset_id", $API, $logParent);
        }
        $insertArray['asset_id'] = $sanitisedArray['asset_id'];
    }
	else {
        errorMissing("asset_id", $API, $logParent);    
	}

    if (isset($inputarray['active_status'])){
		$sanitisedArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
        $insertArray['active_status'] = $sanitisedArray['active_status'];
	}
	else {
        $insertArray['active_status'] = 0;  
	}

    $insertArray['last_modified_by'] = $user_id;
    $insertArray['last_modified_datetime'] = $timestamp;

    foreach ($insertArray['asset_number'] as $value) {
		$data[] = "( '" . $value . "', " .  $insertArray['asset_id'] . ", " . $insertArray['active_status'] . ", " . $insertArray['last_modified_by'] . ", '" . $insertArray['last_modified_datetime'] . "')";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO asset_whitelist(
            `asset_number`
            , `assets_asset_id`
            , `active_status`
            , `last_modified_by`
            , `last_modified_datetime`)
        VALUES " . implode(', ', $data) . "
        ON DUPLICATE KEY UPDATE
			active_status = VALUES(active_status)
			, last_modified_by = VALUES(last_modified_by)
			, last_modified_datetime = VALUES(last_modified_datetime)";	
        
		$stmt= $pdo->prepare($sql);
		if($stmt->execute()){
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
            logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}catch(\PDOException $e){
        logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// ******************************UPDATE*******************************************
// *******************************************************************************
// *******************************************************************************

else if($inputarray['action'] == "update"){
	$updateArray = [];
	$updateString = "";
    $schemainfoArray = getMaxString ("asset_whitelist", $pdo);

    //  Two options here: Update with whitelist ID OR assset ID AND asset number
    //  The only update action is to set the active status to active or inactive
    //  If you wish to change your whitelist number for an asset without the whitelist id, you must update the current one to inactive first, then insert a new one. 
    
    if (isset($inputarray['whitelist_id'])){
        if (isset($inputarray['asset_id'])
            || isset($inputarray['asset_number'])
            ){
            errorInvalid("INCOMPATABLE_IDENTIFICATION_PARAMS", $API, $logParent);
        }
    }
    else {
        if (!isset($inputarray['asset_id'])){
            errorMissing("asset_id", $API, $logParent);
        }
        if (!isset($inputarray['asset_number'])){
            errorMissing("asset_number", $API, $logParent);
        }
    }

    if (isset($inputarray['asset_number'])) {
        $sanitisedInput['asset_number'] = sanitise_input_array($inputarray['asset_number'], "whitelist_asset_number", $schemainfoArray['asset_number'], $API, $logParent);     
    }

    if (isset($inputarray['whitelist_id'])){		
        $sanitisedInput['whitelist_id'] = sanitise_input_array($inputarray['whitelist_id'], "whitelist_id", null, $API, $logParent);        
        $sql = "SELECT 
                asset_whitelist.id
                , asset_whitelist.asset_number
                , asset_whitelist.active_status
            FROM (
                asset_whitelist
                , users
                , user_assets
                , assets)
            LEFT JOIN userasset_details
            ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
            WHERE users.user_id = user_assets.users_user_id
            AND user_assets.users_user_id = $user_id
            AND ((user_assets.asset_summary = 'some'
                AND assets.asset_id = userasset_details.assets_asset_id)
            OR (user_assets.asset_summary = 'all'))
            AND assets.asset_id = asset_whitelist.assets_asset_id
            AND asset_whitelist.id IN ( '" . implode("', '", $sanitisedInput['whitelist_id']) . "' )";

        $stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){          
            errorGeneric("UNAUTHORISED_WHITELIST_ID_PRAM", $API, $logParent);
        }
        arrayExistCheck ($sanitisedInput['whitelist_id'], array_column($dbrows, 0), "whitelist_id", $API, $logParent);
        $sanitisedInput['whitelist_id'] = array_column($dbrows, 0);
    }

    if (isset($inputarray['asset_id'])) {		
        $sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
        
        $sql = "SELECT 
                asset_whitelist.id
                , asset_whitelist.asset_number
                , asset_whitelist.active_status
            FROM (
                asset_whitelist
                , users
                , user_assets
                , assets)
            LEFT JOIN userasset_details
            ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
            WHERE users.user_id = user_assets.users_user_id
            AND user_assets.users_user_id = $user_id
            AND ((user_assets.asset_summary = 'some'
                AND assets.asset_id = userasset_details.assets_asset_id)
            OR (user_assets.asset_summary = 'all'))
            AND assets.asset_id = asset_whitelist.assets_asset_id
            AND asset_whitelist.asset_number IN ( '" . implode("', '", $sanitisedInput['asset_number']) . "' )
            AND asset_whitelist.assets_asset_id = " . $sanitisedInput['asset_id'];

        $stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){  
            errorInvalid("asset_number", $API, $logParent);      
        }
        arrayExistCheck ($sanitisedInput['asset_number'], array_column($dbrows, 1), "asset_number", $API, $logParent);
        $sanitisedInput['whitelist_id'] = array_column($dbrows, 0);
	}
   
    if (isset($inputarray['active_status'])) {
        $updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", $schemainfoArray["active_status"], $API, $logParent);
        $updateString .= " `active_status` = :active_status,"; 
	}
    else {
        errorMissing("active_status", $API, $logParent);
    }

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    try{ 
        if (count($updateArray) < 1) {
            logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
            die("{\"error\":\"NO_UPDATED_PRAMS\"}");
        }	
		else {
			$sql = "UPDATE 
				asset_whitelist 
				SET". $updateString . " `last_modified_by` = $user_id
				, `last_modified_datetime` = '$timestamp'
				WHERE `id` IN ( '" . implode("', '", $sanitisedInput['whitelist_id']) . "' )";

			$stm= $pdo->prepare($sql);	
			if($stm->execute($updateArray)){
                $updateArray['whitelist_id'] = $sanitisedInput['whitelist_id'];
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

else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>