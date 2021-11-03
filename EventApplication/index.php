<?php
	$API = "EventApplication";
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

	$sql = "SELECT distinct `events`.`application` FROM `events` WHERE 1=1";
            


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

	$sql .= " ORDER BY event_id DESC";

	if (isset($inputarray['limit'])){
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
		$json_events  = array();
		$outputid = 0;
		foreach($returnrows as $row){
			$json_event = array(
				"application" => $row[0]
				);
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
else{
	errorInvalid("request", $API, null);
} 

$pdo = null;
$stm = null;
?>