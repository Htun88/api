<?php
	$API = "SyslogMessage";
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

	$schemainfoArray = getMaxString("syslog_messages", $pdo);

	$sql = "SELECT 
			id
			, message_id 
			, message_description
			FROM syslog_messages 
			WHERE 1=1";

	if (isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " AND `syslog_messages`.`id` IN ( '" . implode("', '", $sanitisedInput['id']) . "' )";
	}

	if (isset($inputarray['message_id'])){
		$sanitisedInput['message_id'] = sanitise_input_array($inputarray['message_id'], "syslog_message_id", $schemainfoArray["message_id"], $API, $logParent);
		$sql .= " AND `syslog_messages`.`message_id` IN ( '" . implode("', '", $sanitisedInput['message_id']) . "' )";
	}

	$sql.= " ORDER BY id DESC";	

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
		$json_parent = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"id" => $dbrow[0]
			, "message_id" => $dbrow[1] 
			, "messages_description" => $dbrow[2]
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
		die ("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// ******************************INSERT*******************************************
// *******************************************************************************
// *******************************************************************************


else if($sanitisedInput['action'] == "insert"){
	
	$schemainfoArray = getMaxString("syslog_messages", $pdo);
	$insertArray = [];
	
	if(isset($inputarray['message_id'])){
        $sanitisedInput['message_id'] = sanitise_input($inputarray['message_id'], "syslog_message_id", $schemainfoArray["message_id"], $API, $logParent);		
		//	Check if the input message number exists
		$sql = "SELECT message_id
				FROM syslog_messages
				WHERE message_id = '" . $sanitisedInput['message_id'] . "'";

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			errorInvalid("message_id", $API, $logParent);
		}

		$insertArray['message_id'] = $sanitisedInput['message_id'];
	}
	else{
		errorMissing("message_id", $API, $logParent);
	}

	if(isset($inputarray['message_description'])){
        $sanitisedInput['message_description'] = sanitise_input($inputarray['message_description'], "message_description", $schemainfoArray["message_description"], $API, $logParent);
		$insertArray['message_description'] = $sanitisedInput['message_description'];
	}
	else{
		errorMissing("message_description", $API, $logParent);
	}

	 try{
			$sql = "INSERT INTO syslog_messages(
					`message_id`
					, `message_description`)
					VALUES (
					:message_id
					, :message_description
					)";
						
			$stm= $pdo->prepare($sql);
			if($stm->execute($insertArray)){			
				$insertArray['id'] = $pdo->lastInsertId();
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

	$schemainfoArray = getMaxString ("syslog_messages", $pdo);

	if(isset($inputarray['id'])){
        $updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);		
		//	Check if the input message number exists
		$sql = "SELECT id
				FROM syslog_messages
				WHERE id = " . $updateArray['id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("id", $API, $logParent);
		}
	}
	else{
		errorMissing("id", $API, $logParent);
	}

	if(isset($inputarray['message_id'])){
        $updateArray['message_id'] = sanitise_input($inputarray['message_id'], "syslog_message_id", $schemainfoArray["message_id"], $API, $logParent);		
		//Check if the input message number exists

		$sql = "SELECT syslog_messages.message_id, syslog.message_id
				FROM syslog_messages
				LEFT JOIN syslog ON syslog.message_id = syslog_messages.message_id
				WHERE syslog_messages.message_id =  '" . $updateArray['message_id'] . "'";

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			errorInvalid("message_id", $API, $logParent);
		}

		if ((isset($dbrows[0][0])) && (isset($dbrows[0][1]))){
			errorInvalid("message_id", $API, $logParent);
		}

		$updateString .= "message_id = :message_id,";
	}

	if(isset($inputarray['message_description'])){
        $updateArray['message_description'] = sanitise_input($inputarray['message_description'], "message_description", $schemainfoArray["message_description"], $API, $logParent);
		$updateString .= "message_description = :message_description,";
	}

	//print_r($updateArray);

    try { 
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$sql = "UPDATE 
					syslog_messages
					SET ". rtrim($updateString,',') . " 
					WHERE `id` = :id";

		    //echo $sql;
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