<?php
	$API = "Event";
	header('Content-Type: application/json');
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
	include '../Includes/sanitise.php';
	include '../Includes/functions.php';


	//remove old events
	$expiryminutes = 20160;
	$expirytime = new DateTime();
	$expirytime->setTimezone(new DateTimezone("gmt"));
	$expirytime->sub(new DateInterval('PT' . $expiryminutes . 'M'));
	$expirytime = $expirytime->format("Y/m/d H:i:s");
	$stm = $pdo->exec("DELETE FROM events WHERE datetime < '$expirytime'");



	$entitybody = file_get_contents('php://input');
	$inputarray = json_decode($entitybody, true);

	if ($_SERVER['REQUEST_METHOD'] == "GET") {
		$inputarray = null;
		$inputarray['action'] = "select";
	}

	checkKeys($inputarray, $API, null);

	if (isset($inputarray['action'])){
        $inputarray['action'] = sanitise_input($inputarray['action'], "action", 7, $API, null);
	}
	else{
		errorInvalid("request", $API, null);
	}
	
if($inputarray['action'] == "select"){

	$schemainfoArray = getMaxString ("events", $pdo);

	$sql = "SELECT
		`events`.`event_id`
		, `events`.`event_parent`
		FROM 
		`events`
		WHERE 1=1";
            

	if (isset($inputarray['event_id'])){
		$inputarray['event_id'] = sanitise_input($inputarray['event_id'], "event_id", null, $API, null);
		$sql .= " AND `event_id` = '". $inputarray['event_id'] ."'";
	}

	if (isset($inputarray['timestamp_to'])){
		$inputarray['timestamp_to'] = sanitise_input($inputarray['timestamp_to'], "timestamp_to", $schemainfoArray['datetime'], $API, null);
		$sql .= " AND `datetime` <= '". $inputarray['timestamp_to'] ."'";
	}
	
	if (isset($inputarray['timestamp_from'])){
		$inputarray['timestamp_from'] = sanitise_input($inputarray['timestamp_from'], "timestamp_from", $schemainfoArray['datetime'], $API, null);
		$sql .= " AND `datetime` >= '". $inputarray['timestamp_from'] ."'";
	}

	if (isset($inputarray['timestamp_to'])
		&& isset($inputarray['timestamp_from'])
		){
		$fromDate = strtotime($inputarray['timestamp_from'] . " +0000");
		$toDate = strtotime($inputarray['timestamp_to'] . " +0000");
		if ($fromDate >= $toDate) {
			errorInvalid("timestamp_to", $API, null);
		}
	}

	if(isset($inputarray['ip_address'])){
		$inputarray['ip_address'] = sanitise_input($inputarray['ip_address'], "ip_address", $schemainfoArray['ip_address'], $API, null);
		$sql .= " AND `ip_address` = '". $inputarray['ip_address'] ."'";
	}

	if(isset($inputarray['event_parent'])){
		$inputarray['event_parent'] = sanitise_input($inputarray['event_parent'], "event_parent", null, $API, null);
		$sql .= " AND `event_parent` = '". $inputarray['event_parent'] ."'";
	}

	if(isset($inputarray['user_id'])){
		$inputarray['user_id'] = sanitise_input_array($inputarray['user_id'], "user_id", null, $API, null);
		$sql .= " AND `user_assets`.`users_user_id` in (" . implode( ', ',$filteredArray ) . ")";
	}

	if (isset($inputarray['application'])) {
		$inputarray['application'] = sanitise_input($inputarray['application'], "application", $schemainfoArray['application'], $API, null);
		$sql .= " AND `events`.`application` = '". $inputarray['application'] ."'";
	}
	
	if (isset($inputarray['event_data'])) {
		$inputarray['event_data'] = sanitise_input($inputarray['event_data'], "event_data", null, $API, null);
		$sql .= " AND `events`.`event_data` like '%". $inputarray['event_data'] ."%'";
	}

	$sql .= " ORDER BY event_id DESC";

	if (isset($inputarray['limit'])){
		//$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, null);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	//echo $sql;
	
	$event_id = array();
	$event_parent_id = array();
	
	$stm = $pdo->query($sql);
	$returnrows = $stm->fetchAll(PDO::FETCH_NUM);
	
	if (isset($returnrows[0][0])){
		
		foreach($returnrows as $row){	
			if (!in_array($row[0],$event_id)){
				$event_id[] = $row[0];
			}
			if (isset($row[1])){
				if (!in_array($row[1],$event_parent_id)){
					$event_parent_id[] = $row[1];
				}
			}
			
		}
		
		while (isset($event_parent_id[0])) {
			//print_r($event_parent_id);
			$sql = "SELECT
			`events`.`event_id`
			, `events`.`event_parent`
			FROM 
			`events`
			WHERE `events`.`event_id` in (" . implode( ', ',$event_parent_id ) . ")";
			//echo $sql;
			$stm = $pdo->query($sql);
			$returnrows = $stm->fetchAll(PDO::FETCH_NUM);
			$event_parent_id = array();
			foreach($returnrows as $row){
				if (!in_array($row[0],$event_id)){
					$event_id[] = $row[0];
				}
				if (isset($row[1])){
					if (!in_array($row[1],$event_parent_id)){
						$event_parent_id[] = $row[1];
					}
				}
			}
			//print_r($event_parent_id);
			//if (!isset($event_parent_id)){
				//break;
			//}
			
		}
		
		if (isset($event_id)) {
			$sql = "SELECT
				`events`.`event_id`
				, `events`.`event_parent`
				, `events`.`event_level`
				, `events`.`datetime`
				, `events`.`ip_address`
				, `events`.`user_user_id`
				, `events`.`application`
				, `events`.`event_data`
				, `events`.`event_parent`
				, `events`.`event_type`
				FROM 
				`events`
				WHERE `events`.`event_id` in (" . implode( ', ',$event_id ) . ")";
		
			$sql .= " ORDER BY event_id DESC";

			$stm = $pdo->query($sql);
			$returnrows = $stm->fetchAll(PDO::FETCH_NUM);
			
			if (isset($returnrows[0][0])){
				$json_events  = array();
				$outputid = 0;
				foreach($returnrows as $row){
					$json_event = array(
					"event_id" => $row[0]
					, "event_parent" => $row[1]
					, "event_level" => $row[2]
					, "event_type" => $row[9]
					, "datetime" => $row[3]
					, "ip_address" => $row[4]
					, "user_id" => $row[5]
					, "application" => $row[6]
					, "event_data" => $row[7]
					, "event_parent" => $row[8]);
					$json_events = array_merge($json_events,array("response_$outputid" => $json_event));
					$outputid++;
				}
				$json = array("responses" => $json_events);

				echo str_replace("\\\\", "", json_encode($json));
			}
			else {
				die("{\"error\":\"NO_DATA\"}");
			}
		}
		else {
			die("{\"error\":\"NO_DATA\"}");
		}
	}
	else {
		die("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// ***********************************INSERT**************************************
// *******************************************************************************
// ******************************************************************************* 

else if($inputarray['action'] == "insert"){
	$schemainfoArray = getMaxString ("events", $pdo);
	$insertArray = [];
	
	if(isset($inputarray['application'])){
		$insertArray['application'] = sanitise_input($inputarray['application'], "application", $schemainfoArray['application'], $API, null);
    }
	else {
		errorMissing("application", $API, null);
	}

	if (isset($inputarray['event_data'])){
		if (strlen($inputarray['event_data']) >= 16777215 ) {
			//	Explode the event data around the "-" to retrieve the $API and 
			$API = explode("-", $inputarray['event_data'], 2 );
			$inputarray['event_data'] = $API[0] . logText::responseLarge . strlen($inputarray['event_data']);
		}
		$insertArray['event_data'] = sanitise_input($inputarray['event_data'], "event_data", $schemainfoArray['event_data'], $API, null);
	}
	else {
		errorMissing("event_data", $API, null);
	}

	if (isset($inputarray['event_type'])){
		$insertArray['event_type'] = sanitise_input($inputarray['event_type'], "event_type", null, $API, null);
	}

	if (isset($inputarray['event_level'])){
		$insertArray['event_level'] = sanitise_input($inputarray['event_level'], "event_level", null, $API, null);
	}

	if (isset($inputarray['event_parent'])){
		$insertArray['event_parent'] = sanitise_input($inputarray['event_parent'], "event_parent", null, $API, null);
	}

	$insertArray['ip_address'] = getClientIP();

	try{
		$sql = "INSERT INTO events(
			`datetime`
			, `user_user_id`
			, `application`
			, `event_data`
			, `event_type`
			, `ip_address`
			, `event_level`
			, `event_parent`) 
		VALUES (
			'$timestamp'
			, $user_id
			, :application
			, :event_data";

			if (isset($insertArray['event_type'])){
				$sql .= ", :event_type";
			}
			else {
				$sql .= ", NULL";
			}
			if (isset($insertArray['ip_address'])){
				$sql .= ", :ip_address";
			}
			else {
				$sql .= ", NULL";
			}
			if (isset($insertArray['event_level'])){
				$sql .= ", :event_level";
			}
			else {
				$sql .= ", NULL";
			}
			if (isset($insertArray['event_parent'])){
				$sql .= ", :event_parent";
			}
			else {
				$sql .= ", NULL";
			}
			$sql .= ")";

		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['event_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
		}
	}
	catch (PDOException $e){
		die("{\"error\":\"$e\"}");
	}
}

else{
	errorInvalid("request", $API, null);
} 

$pdo = null;
$stm = null;
?>