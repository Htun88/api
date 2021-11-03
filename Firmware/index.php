<?php
	$API = "Firmware";
	$updateHost = "http://update.dev.usm.net.au";
	
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

	$sql = "SELECT
			id
		FROM
			device_provisioning
		WHERE 1=1";

	if (isset($inputarray['device_provisioning_id'])){
		$sanitisedInput['device_provisioning_id'] = sanitise_input_array($inputarray['device_provisioning_id'], "device_provisioning_id", null, $API, $logParent);
		$sql .= " AND `id` IN (" . implode( ', ', $sanitisedInput['device_provisioning_id'] ) . ")";
	}
	$sql .= " ORDER BY `id` ASC";

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stm = $pdo->query($sql);
	$sensordatasrows = $stm->fetchAll(PDO::FETCH_NUM);
	//print_r($sensordatasrows);
	if (isset($sensordatasrows[0][0])){	
		$files = [];
		$foundDirectory = false;	
		if (!isset($inputarray['device_provisioning_id'])){
			foreach ($sensordatasrows as $row) {
				$sanitisedInput['device_provisioning_id'][] = $row[0];
			}
		}	
		foreach ($sanitisedInput['device_provisioning_id'] as $value) {
			
			//	Check if the directory exists
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $value)){
				//	Bool to check if the returning no data because directory doesnt exist or if .hex file doesn't exist
				$foundDirectory = true;
				//	Get all files in the directory
				$value2 = scandir($_SERVER['DOCUMENT_ROOT'].'/Firmware/'. $value);
				//	For each file in the directory
				foreach ($value2 as $value3) {
					//	If the last 3 characters in filename are hex add to array
					if (substr($value3, -3) == "hex") {
						$files[] = $value3;
					}
				}
			}
		}
		//	If no directories were found
		if (!$foundDirectory) {
			logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA_NO_DIRECTORY\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		}
		//	If nothing was added to the $files array (ie. no .hex in directory)
		if (empty($files)) {
			logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA_NO_HEX_IN_DIRECTORY\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die ("{\"error\":\"NO_DATA\"}");
		}
		$outputid = 0;
		$json  = array();
		foreach($files as $file){
			$file_array = explode( '.', $file );
			$file_name = $file_array[0];
			$json_row = array(
				"name"=>$file_name
			);
			$json = array_merge($json,array("response_$outputid" => $json_row));
			$outputid++;
		}
		$json = array("responses" => $json);
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
// *****************************update********************************************
// *******************************************************************************
// *******************************************************************************

else if ($sanitisedInput['action'] == "update"){

	$context_options = array(
		'http' => array(
			'header'  => "Content-type: application/json\r\n",
			'method'  => 'GET',
			'content' => ""
		)
	);

	$context = stream_context_create($context_options);
	$fp = fopen("$updateHost/Firmware/", 'r', false, $context);
	$buffer = '';
	if ($fp) {
		while (!feof($fp)) {
			$buffer .= fgets($fp, 5000);
		}
		fclose($fp);
	}
	$fw_array =  json_decode($buffer, true);
	//print_r($fw_array);
	
	$error = 0;
	foreach($fw_array as $pid => $pcomp){
		//print_r($pid);
		if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/Firmware/$pid")) {
			mkdir($_SERVER['DOCUMENT_ROOT']."/Firmware/$pid", 0771, true);
			chmod($_SERVER['DOCUMENT_ROOT']."/Firmware/$pid", 0771);
		}
		foreach($pcomp['fw_files'] as $file){
			//echo $file;
			$url = "$updateHost/Firmware/$pid/$file";
			$filename = $_SERVER['DOCUMENT_ROOT']."/Firmware/$pid/$file";
			if (!file_exists($filename)) {
				if(file_put_contents($filename ,file_get_contents($url))) {
					//echo "File downloaded successfully";
					chmod($filename, 0771);
				}
				else {
					//echo "File downloading failed.";
					$error = 1;
				}
			}
		}
		if (isset($pcomp['languages'])){
			//print_r($pcomp['languages']);
			if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/Firmware/$pid/Languages")) {
				mkdir($_SERVER['DOCUMENT_ROOT']."/Firmware/$pid/Languages", 0771, true);
				chmod($_SERVER['DOCUMENT_ROOT']."/Firmware/$pid/Languages", 0771);
			}
			foreach($pcomp['languages'] as $file){
				
				$url = "$updateHost/Firmware/$pid/Languages/$file";
				$filename = $_SERVER['DOCUMENT_ROOT']."/Firmware/$pid/Languages/$file";
				if (!file_exists($filename)) {
					if(file_put_contents($filename ,file_get_contents($url))) {
						//echo "File downloaded successfully";
						chmod($filename, 0771);
					}
					else {
						//echo "File downloading failed.";
						$error = 1;
					}
				}
			}
		}
	}
	if ($error==1){
		echo ("{\"error\":\"DOWNLOAD_FAILED\"}");
	}
	else{
		$fw_array['error'] = "NO_ERROR";
		//echo ("{\"error\":\"NO_ERROR\"}");
		echo json_encode($fw_array);
	}
}

else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>