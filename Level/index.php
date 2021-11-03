<?php
	$API = "Level";
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

	$schemainfoArray = getMaxString("alt_range", $pdo);
	$sql = "SELECT 
			*		
		FROM 
			alt_range
		WHERE 1 = 1";

	if (isset($inputarray['level_id'])){
		$sanitisedInput['level_id'] = sanitise_input_array($inputarray['level_id'], "level_id", null, $API, $logParent);
		$sql .= " AND `alt_range_id` IN (" . implode( ', ',$sanitisedInput['level_id'] ) . ")";
	}

	if (isset($inputarray['alt_from'])){
		$sanitisedInput['alt_from'] = sanitise_input_array($inputarray['alt_from'], "alt_from", null, $API, $logParent);
		$sql .= " AND `alt_from` IN (" . implode( ', ',$sanitisedInput['alt_from'] ) . ")";
	}

	if (isset($inputarray['alt_to'])){
		$sanitisedInput['alt_to'] = sanitise_input_array($inputarray['alt_to'], "alt_to", null, $API, $logParent);
		$sql .= " AND `alt_to` IN (" . implode( ', ',$sanitisedInput['alt_to'] ) . ")";
	}

	if (isset($inputarray['level_name'])){
		$sanitisedInput['level_name'] = sanitise_input($inputarray['level_name'], "level_name", $schemainfoArray['level_name'], $API, $logParent);
		$sql .= " AND `level_name` = " . $sanitisedInput['level_name'];
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

	//print_r($dbrows);
	if (isset($dbrows[0][0])){
		$json_levels  = array ();
		$outputid = 0;
		foreach($dbrows as $levelsrow){
			$json_level = array(
			"level_id" => $levelsrow[0]
			, "alt_from" => $levelsrow[1]
			, "alt_to" => $levelsrow[2]
			, "level_name" => $levelsrow[3]
			, "last_modified_by" => $levelsrow[4]
			, "last_modified_datetime" => $levelsrow[5]);
			$json_levels = array_merge($json_levels,array("response_$outputid" => $json_level));
			$outputid++;
		}
	$json = array("responses" => $json_levels);
	echo json_encode($json);
	logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else{
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *******************************INSERT******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "insert"){

	$insertArray = [];
	$schemainfoArray = getMaxString ("alt_range", $pdo);

	if (isset($inputarray['alt_from'])){
		$insertArray['alt_from'] = sanitise_input($inputarray['alt_from'], "alt_from", null, $API, $logParent);
	}
	else {
		errorMissing("alt_from", $API, $logParent);
	}

	if (isset($inputarray['alt_to'])){
		$insertArray['alt_to'] = sanitise_input($inputarray['alt_to'], "alt_to", null, $API, $logParent);
	}
	else {
		errorMissing("alt_to", $API, $logParent);
	}
	

	//	Throw error if alt from is bigger than or equal to alt to
	if ($insertArray['alt_from'] >= $insertArray['alt_to']) {
		errorInvalid("altitude_range", $API, $logParent);
	}

	if(isset($inputarray['level_name'])){
		$insertArray['level_name'] = sanitise_input( $inputarray['level_name'], "level_name", $schemainfoArray['level_name'], $API, $logParent); 
	}
	else{
		errorMissing("level_name", $API, $logParent);
	}

	$sql = "SELECT
			alt_range_id
		FROM
			alt_range
		WHERE (alt_from > '" . $inputarray['alt_from'] . "' AND alt_from < '" . $inputarray['alt_to'] . "' )
		OR (alt_to > '" . $inputarray['alt_from'] . "' AND alt_to < '" . $inputarray['alt_to'] . "' )";

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if(isset($dbrows[0][0])){					
		errorGeneric("altitude_range_conflict", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO alt_range (
				alt_from
				, alt_to
				, level_name
				, last_modified_by
				, last_modified_datetime) 
			VALUES (
				:alt_from
				, :alt_to
				, :level_name
				, $user_id
				, '$timestamp')";

		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['level_id'] = $pdo->lastInsertId();
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
// *******************************UPDATE******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "update"){
	
	$updateArray = [];
	$updateString = "";

	$schemainfoArray = getMaxString ("alt_range", $pdo);

	if(isset($inputarray['level_id'])){
		$sanitisedInput['level_id'] = sanitise_input($inputarray['level_id'], "alt_range_id", null, $API, $logParent);
		$sql = "SELECT 
				alt_from
				, alt_to
			FROM
				alt_range
			WHERE alt_range_id = " . $sanitisedInput['level_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if(!isset($dbrows[0][0])){					
			errorInvalid("level_id", $API, $logParent);
		}
		$sanitisedInput['alt_from'] = $dbrows[0][0];
		$sanitisedInput['alt_to'] = $dbrows[0][1];
		$updateArray['level_id'] = $sanitisedInput['level_id'];
	}
	else {
		errorMissing("level_id", $API, $logParent);
	}
	
	if (isset($inputarray['level_name'])){
		$updateArray['level_name'] = sanitise_input($inputarray['level_name'], "level_name", $schemainfoArray['level_name'], $API, $logParent);
		$updateString .= " `level_name` = :level_name,";
	}

	if (isset($inputarray['alt_from'])){
		$sanitisedInput['alt_from'] = sanitise_input($inputarray['alt_from'], "alt_from", null, $API, $logParent);
		$updateArray['alt_from'] = $sanitisedInput['alt_from'];
		$updateString .= " `alt_from` = :alt_from,";
	}

	if (isset($inputarray['alt_to'])){
		$sanitisedInput['alt_to'] = sanitise_input($inputarray['alt_to'], "alt_to", null, $API, $logParent);
		$updateArray['alt_to'] = $sanitisedInput['alt_to'];
		$updateString .= " `alt_to` = :alt_to,";
	}

	

	if (isset($inputarray['alt_from'])
		|| isset($inputarray['alt_to'])
		) {
		//	Throw error if alt from is bigger than or equal to alt to
		if ($sanitisedInput['alt_from'] >= $sanitisedInput['alt_to']) {
			errorInvalid("altitude_range", $API, $logParent);
		}
		//	Check if theres a conflict with values already in db
		$sql = "SELECT
				alt_range_id
			FROM
				alt_range
			WHERE ((alt_from > '" . $sanitisedInput['alt_from'] . "' AND alt_from < '" . $sanitisedInput['alt_to'] . "' )
			OR (alt_to > '" . $sanitisedInput['alt_from'] . "' AND alt_to < '" . $sanitisedInput['alt_to'] . "' ))
			AND alt_range_id != " . $updateArray['level_id'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if(isset($dbrows[0][0])){					
			errorGeneric("altitude_range_conflict", $API, $logParent);
		}
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try { 
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$updateString = substr($updateString, 0, -1);
			$sql = "UPDATE 
				alt_range 
				SET ". $updateString . "
                WHERE `alt_range_id` = :level_id";

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

// *******************************************************************************
// *******************************************************************************
// *******************************DELETE******************************************
// *******************************************************************************
// ******************************************************************************* 

else if ($sanitisedInput['action'] == "delete"){

	if(isset($inputarray['level_id'])){
		$deleteArray['level_id'] = sanitise_input_array($inputarray['level_id'], "level_id", null, $API, $logParent);
	}
	else {
		errorMissing("level_id", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($deleteArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try {	
		$sql = "DELETE FROM 
				`alt_range` 
			WHERE `alt_range_id` IN (" . implode( ', ', $deleteArray['level_id'] ) . ")";

		$stm= $pdo->prepare($sql);
		if($stm->execute()){
			$deleteArray ['error'] = "NO_ERROR";
			echo json_encode($deleteArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($deleteArray)), logLevel::response, logType::response, $token, $logParent);
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