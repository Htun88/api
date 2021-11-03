<?php
	$API = "Message";
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
	
	$schemainfoArray = getMaxString ("messages", $pdo);

	$sql = " SELECT 
	messages.message_id
	, messages.asset_id 
	, messages.`method`
	, messages.`type`
	, messages.message
	, message_codes.message
	, messages.number_index
	, messages.acknowledged
	, messages.iconpath
	, messages.`timestamp`
	FROM (users, user_assets, assets, messages, message_codes, deviceassets)
	LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
	WHERE 
	deviceassets.assets_asset_id = assets.asset_id  
	AND (deviceassets.date_from <= messages.timestamp 
	AND (deviceassets.date_to >= messages.timestamp 
	OR deviceassets.date_to Is NULL))
	AND message_codes.message_code_id = messages.message
	AND messages.asset_id = assets.asset_id
	AND users.user_id = user_assets.users_user_id 
	AND user_assets.users_user_id = $user_id
	AND ((user_assets.asset_summary = 'some' 
		AND assets.asset_id = userasset_details.assets_asset_id)
	OR (user_assets.asset_summary = 'all')) ";

	if (isset($inputarray['message_id'])){
		$sanitisedInput['message_id'] = sanitise_input_array($inputarray['message_id'], "message_id", null, $API, $logParent);
		$sql .= " AND `messages`.`message_id` IN (" . implode( ', ',$sanitisedInput['message_id'] ) . ")";
	}

	if (isset($inputarray['message_number'])){
		$sanitisedInput['message_number'] = sanitise_input_array($inputarray['message_number'], "message_number", null, $API, $logParent);
		$sql .= " AND `messages`.`message` IN (" . implode( ', ',$sanitisedInput['message_number'] ) . ")";
	}
	
	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `messages`.`asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}
	
	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if(isset($inputarray['acknowledged'])){
        $sanitisedInput['acknowledged'] = sanitise_input($inputarray['acknowledged'], "acknowledged", null, $API, $logParent);
        $sql .= " AND `messages`.`acknowledged` = '". $sanitisedInput['acknowledged'] ."'";
	}

	if(isset($inputarray['method'])){
        $sanitisedInput['method'] = sanitise_input($inputarray['method'], "method", null, $API, $logParent);
		$sql .= " AND `messages`.`method` = '". $sanitisedInput['method'] ."'";
	}

	if (isset($inputarray['timestamp_to'])){
		$sanitisedInput['timestamp_to'] = sanitise_input($inputarray['timestamp_to'], "timestamp_to", null, $API, $logParent);
		$sql .= " AND `messages`.`timestamp` <= '". $sanitisedInput['timestamp_to'] ."'";	
	}
	
	if (isset($inputarray['timestamp'])){
		$sanitisedInput['timestamp'] = sanitise_input($inputarray['timestamp'], "timestamp_to", null, $API, $logParent);
		$sql .= " AND `messages`.`timestamp` = '". $sanitisedInput['timestamp'] ."'";	
	}

	if (isset($inputarray['timestamp_from'])){
		$sanitisedInput['timestamp_from'] = sanitise_input($inputarray['timestamp_from'], "timestamp_from", null, $API, $logParent);
		$sql .= " AND `messages`.`timestamp` >= '". $sanitisedInput['timestamp_from'] ."'";	
	}

	if (isset($inputarray['timestamp_to'])
		&& isset($inputarray['timestamp_from'])
		){
		$fromDate = strtotime($sanitisedInput['timestamp_from'] . " +0000");
		$toDate = strtotime($sanitisedInput['timestamp_to'] . " +0000");
		if ($fromDate >= $toDate) {
			errorInvalid("timestamp_to", $API, $logParent);
		}
	}

	//	If there is a last_received_id associated with this select query, then we MUST NOT also be searching by message ID. 
	if (isset($inputarray['message_id'])
		&& isset($inputarray['last_received_id'])
		){
		logEvent($API . logText::requestIncompatable . str_replace('"', '\"', "{\"error\":\"INCOMPATIBLE_PRAMS\"}"), logLevel::invalid, logType::error, $token, $logParent);
		die("{\"error\":\"INCOMPATIBLE_PRAMS\"}");
	}

	//	If there is a last_received_id associated with this select query, then we MUST be also searching with maximum 1 asset ID. 
	if (isset($inputarray['asset_id'])) {
		if (count($sanitisedInput['asset_id']) != 1
			&& isset($inputarray['last_received_id'])
			){
			logEvent($API . logText::requestIncompatable . str_replace('"', '\"', "{\"error\":\"INCOMPATIBLE_PRAMS\"}"), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"INCOMPATIBLE_PRAMS\"}");
		}
	}

	if(isset($inputarray['last_received_id'])){
        $sanitisedInput['last_received_id'] = sanitise_input($inputarray['last_received_id'], "last_received_id", null, $API, $logParent);
		//	Check if we have specified a method previously, and if so, it MUST be 1.
		if (!isset($inputarray['method'])) {
			$sql .= " AND `messages`.`method` = 1";
		}
		else if (isset($inputarray['method']) 
			&& $sanitisedInput['method'] != 1) {
			errorInvalid("method", $API, $logParent);
		}
			//	If the last received ID is 0, then we are looking to return to the user the last message
		if ( $sanitisedInput['last_received_id'] == 0) {
			if (isset($inputarray['limit'])){
				errorInvalid("last_received_id", $API, $logParent);
			}
			$sql .=  " ORDER BY message_id DESC LIMIT 1";
		}
		//	If the last received id is > 0, then we are looking to return all messages since the last received id
		else {
			$sql .= " AND message_id > " . $sanitisedInput['last_received_id']
			. " ORDER BY message_id DESC";
		}
	}
	else {
		$sql.= " ORDER BY message_id DESC";	
	}

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		if(isset($inputarray['last_received_id'])
			&& $sanitisedInput['last_received_id'] == 0
			){
			//	Do nothing. We already added limit 1 in the last_received ID section. This is a later addition hack -Conor
		}
		else {
			$sql .= " LIMIT " . allFile::limit;
		}
	}
	//echo $sql;
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_parent = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"message_id" => $dbrow[0]
			, "asset_id" => $dbrow[1] 
			, "method" => $dbrow[2]
			, "type" => $dbrow[3]
			, "message_number" => $dbrow[4]
			, "message" => $dbrow[5]
			, "number_index" => $dbrow[6]
			, "acknowledged" => $dbrow[7]
			, "iconpath" => $dbrow[8]
			, "timestamp" => $dbrow[9]);
			$json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
			$outputid++;
		}
		$json = array("responses" => $json_parent);
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
// ******************************INSERT*******************************************
// *******************************************************************************
// *******************************************************************************


else if($sanitisedInput['action'] == "insert"){
	
	$schemainfoArray = getMaxString ("messages", $pdo);
	$insertArray = [];
	
	if(isset($inputarray['method'])){
        $insertArray['method'] = sanitise_input($inputarray['method'], "method", null, $API, $logParent);
	}
	else{
		errorMissing("method", $API, $logParent);
	}


	if(isset($inputarray['message_number'])){
        $sanitisedInput['message_number'] = sanitise_input($inputarray['message_number'], "message_number", null, $API, $logParent);
		//	Check if the input message number exists
		$sql = "SELECT
			message_code_id
			FROM ( message_codes
			)
			WHERE message_code_id = " . $sanitisedInput['message_number'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			$insertArray['message_number'] = $sanitisedInput['message_number'];
		}
		else {
			errorInvalid("message_number", $API, $logParent);
		}
	}
	else{
		errorMissing("message_number", $API, $logParent);
	}

    if (isset($inputarray['asset_id']) || isset($inputarray['device_id'])) {	

		$sql = "SELECT
			assets.asset_id,
			deviceassets.devices_device_id
			FROM (users
			, user_assets
			, assets
			, deviceassets
			)
			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			

			WHERE users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			and deviceassets.date_to Is null
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))";
			
		if (isset($inputarray['asset_id'])){
			$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
			$sql .= " AND assets.asset_id = '" . $sanitisedInput['asset_id'] . "'";
		}
		if (isset($inputarray['device_id'])){
			$sanitisedInput['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
			$sql .= " AND deviceassets.devices_device_id = '" . $sanitisedInput['device_id'] . "'";
		}
		
		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){
            errorInvalid("asset_id_or_device_id", $API, $logParent);
        }
		$insertArray['asset_id'] = $dbrows[0][0];
    }
	else {
		errorMissing("asset_id_or_device_id", $API, $logParent);
	}

	if(isset($inputarray['acknowledged'])){
        $insertArray['acknowledged'] = sanitise_input($inputarray['acknowledged'], "acknowledged", null, $API, $logParent);
	}
	else{
		$insertArray['acknowledged'] = 0;
	}

	if(isset($inputarray['iconpath'])){
        $insertArray['iconpath'] = sanitise_input($inputarray['iconpath'], "iconpath", $schemainfoArray['iconpath'], $API, $logParent);
	}
	else{
		$insertArray['iconpath'] = "speech-bubble.png";
	}

	if(isset($inputarray['type'])){
        $insertArray['type'] = sanitise_input($inputarray['type'], "type", null, $API, $logParent);
	}
	else{
		$insertArray['type'] = 1;
	}

	if(isset($inputarray['number_index'])){
        $insertArray['number_index'] = sanitise_input($inputarray['number_index'], "number_index", null, $API, $logParent);
	}

	try{
		$sql = "INSERT INTO messages(
			`method`
			, `message`
			, `asset_id`
			, `acknowledged`
			, `iconpath`
			, `type`
			, `number_index`
			, `timestamp`)
		VALUES (
			:method
			, :message_number
			, :asset_id
			, :acknowledged		
			, :iconpath
			, :type";
			if (isset($insertArray['number_index'])){
				$sql .= ", :number_index";
			}
			else {
				$sql .= ", NULL";
			}
			$sql .= "
			, '$timestamp')";
		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			if (!isset($insertArray['number_index'])) {
				$insertArray['number_index'] = null;
			}
			$insertArray['timestamp'] = $timestamp;
			$insertArray['message_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"" . $e . "\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// ******************************UPDATE*******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "update"){

	$updateArray = [];
	$updateString = "";

	$schemainfoArray = getMaxString ("messages", $pdo);

    if (isset($inputarray['message_id'])) {		
        $sanitisedInput['message_id'] = sanitise_input($inputarray['message_id'], "message_id", null, $API, $logParent);
        
        $sql = "SELECT
			messages.message_id
			, assets.asset_id
			FROM (users
			, user_assets
			, assets
			, messages
			)
			LEFT JOIN userasset_details 
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE messages.message_id = '" . $sanitisedInput['message_id'] . "'
			AND messages.asset_id = assets.asset_id
			AND users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))";

        $stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($dbrows[0][0])
			&& isset($dbrows[0][1])
			){
            $updateArray['message_id'] = $sanitisedInput['message_id'];
        }
        else {
			errorInvalid("message_id", $API, $logParent);
        }        
    }
	else {
		errorMissing("message_id", $API, $logParent);
	}

	if (isset($inputarray['acknowledged'])) {
		$updateArray['acknowledged'] = sanitise_input($inputarray['acknowledged'], "acknowledged", null, $API, $logParent);
		if ($updateArray['acknowledged'] != 0) {
			errorInvalid("acknowledged", $API, $logParent);
		}
		$updateString .= " `acknowledged` = :acknowledged";
	}
	else {
		errorMissing("acknowledged", $API, $logParent);
	}

    try { 
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$sql = "UPDATE 
				messages
				SET ". $updateString . " 
				WHERE `message_id` = :message_id";

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