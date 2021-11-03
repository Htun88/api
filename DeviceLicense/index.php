<?php
	$API = "DeviceLicense";
	$licenseserver = "https://license02.usm.net.au/api/v1/license/";
	header('Content-Type: application/json');
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
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
	//checkKeys($inputarray, $API, $logParent);

	if (isset($inputarray['action'])){
        //$sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, $API, $logParent);
		$sanitisedInput['action'] = $inputarray['action'];
		$logParent = logEvent($API . logText::action . ucfirst($sanitisedInput['action']), logLevel::action, logType::action, $token, $logParent)['event_id'];
	}
	else{
		errorInvalid("request", $API, $logParent);
	}

	
	//$stm = $pdo->query("SELECT * FROM information_schema.tables WHERE table_schema = '$databasename' AND table_name = 'devicelicense' LIMIT 1");
	//$rows = $stm->fetchAll(PDO::FETCH_NUM);
	//if (isset($rows[0])) {

	//}
	//else{
		//$stm = $pdo->exec("CREATE TABLE `devicelicense` (
		  //`devicelicense_id` int(11) NOT NULL AUTO_INCREMENT,
		 // `license_hash` varchar(256) DEFAULT NULL,
		 // `expdatetime` DATETIME DEFAULT NULL,
		//  PRIMARY KEY (`devicelicense_id`)
		//) ENGINE=InnoDB AUTO_INCREMENT=4031 DEFAULT CHARSET=utf8");
	//}

//var_dump($sanitisedInput);
	if ($sanitisedInput['action'] == "select"){

		$schemainfoArray = getMaxString ("devicelicense", $pdo);

		$sql="SELECT 
				devicelicense_id
				, license_hash
				, expdatetime
				, device_id 
		FROM devicelicense
		LEFT JOIN devices ON devicelicense.devicelicense_id = devices.devicelicense_devicelicense_id 
		WHERE 1 = 1";


		if (isset($inputarray['license_hash'])){
			$sanitisedInput['license_hash'] = sanitise_input_array($inputarray['license_hash'], "license_hash", $schemainfoArray['license_hash'], $API, $logParent);
			$sql .= " AND license_hash IN ( '" . implode("', '", $inputarray['license_hash']) . "' )";
		}

		if (isset($inputarray['device_id'])){

			//	Need to push to array if not already
			if(!is_array($inputarray['device_id'])) {
				$inputarray['device_id'] = [$inputarray['device_id']];
			}

			if (in_array(-1, $inputarray['device_id'])) {
				//	Sanitise the array for log purposes. The negative 1 will break sanitisation so we unset it, sanitise the array then return it
				//	We also need to check that we by unsetting it we are not removing the only value in the array

				$inputarray['device_id'] = array_diff($inputarray['device_id'], [-1]);
				$val = count($inputarray['device_id']);
				if ($val != 0){
					$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
				}
				$inputarray['device_id'][] = -1;
				$sql .= " AND device_id IS NULL";
			}
			else {
				$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
				$sql .= " AND device_id IN ( '" . implode("', '", $sanitisedInput['device_id']) . "' )";
			}
		}

		$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			$json_license_hashs = array();
			$outputid = 0;
			foreach($dbrows as $row){
				$json_license_hash = array(
				"license_id"=>$row[0],	
				"license_hash"=>$row[1],
				"expdatetime"=>$row[2],
				"device_id"=>$row[3]
				);

				$json_license_hashs = array_merge($json_license_hashs,array("response_$outputid" => $json_license_hash));
				$outputid++;
			}

			$json = array("responses" => $json_license_hashs);
			echo json_encode($json);
		}else{
			die("{\"error\":\"NO_DATA\"}");
		}
	}

// *******************************************************************************
// *******************************************************************************
// ***********************************UPDATE**************************************
// *******************************************************************************
// ******************************************************************************* 


else if($sanitisedInput['action'] == "update"){

	$stm = $pdo->query("SELECT license_hash FROM udilicense;");
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$udihash = $dbrows[0][0];
	}
	else {
		errorGeneric("INVALID_LICENSE_HASH", $API, $logParent);
	}

	$postdata =  array(
		'udihash' => $udihash
	);
	
	$postdata = json_encode($postdata);
	//echo $postdata;

	$opts = array('http' => 
		array(
			'method'  => 'POST',
			'header'  => 'Content-Type: application/json',
			'content' => $postdata
		)
	);

	$context  = stream_context_create($opts);

	$result = file_get_contents($licenseserver, false, $context);
	
	$json = json_decode($result,true);
	
	//echo $result;
	//print_r ($json);
	
	if (isset($json["error"])){
		//die("{\"error\":\"" . $json["error"] . "\"}");
		errorGeneric($json["error"], $API, $logParent);

	}
	$licensehashs = array();
	foreach($json as $root){
		foreach($root as $license ){
			$licensehash = $license['licensehash'];
			$licensehashs[] = $licensehash;
			$expdatetime = $license['expdatetime'];
			
			$stm = $pdo->query("SELECT license_hash, expdatetime FROM devicelicense where license_hash = '$licensehash';");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($dbrows[0][0])){
				if ($expdatetime != $dbrows[0][1]){
					$data = ['expdatetime' => $expdatetime];
					$udilicense_id = $dbrows[0][0];
					$sql = "UPDATE devicelicense SET expdatetime = :expdatetime WHERE license_hash = '$licensehash'";
					$stmt= $pdo->prepare($sql);
					if (!$stmt->execute($data)) {
						errorGeneric("ERROR", $API, $logParent);
						//die("{\"error\":\"ERROR\"}");
					}
				}
			}
			else{
				$data = [
				'license_hash' => $licensehash,
				'expdatetime' =>  $expdatetime
				];

				$sql = "INSERT INTO devicelicense (license_hash, expdatetime) 
				VALUES (:license_hash, :expdatetime)";
				$stmt= $pdo->prepare($sql);
				if($stmt->execute($data)){
				}
				else {
					errorGeneric("ERROR", $API, $logParent);
					//die("{\"error\":\"ERROR\"}");
				}
			}
		}
	}
	
	$sql = "UPDATE 
		devicelicense
		, devices
		SET devices.devicelicense_devicelicense_id = null
		WHERE 
		devicelicense.devicelicense_id = devices.devicelicense_devicelicense_id";

		if(isset($licensehashs)){
			$sql .= " AND devicelicense.license_hash NOT IN ('" . implode( "', '", $licensehashs ) . "')";
		}
		
		
		$stmt= $pdo->prepare($sql);
		if (!$stmt->execute()) {
			errorGeneric("ERROR", $API, $logParent);
			//die("{\"error\":\"ERROR\"}");
		}
		
	
	$sql = "DELETE FROM 
		devicelicense";

		if(isset($licensehashs)){
			$sql .= " WHERE devicelicense.license_hash NOT IN ('" . implode( "', '", $licensehashs ) . "')";
		}
		//echo $sql;
		$stmt= $pdo->prepare($sql);
		if (!$stmt->execute()) {
			errorGeneric("ERROR", $API, $logParent);
			//die("{\"error\":\"ERROR\"}");
		}
			
	echo("{\"error\":\"NO_ERROR\"}");
}
else {

	errorGeneric("INVALID_ACTION", $API, $logParent);
	//die("{\"error\":\"INVALID_ACTION\"}");
}

$pdo = null;
$stm = null;
?>