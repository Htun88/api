<?php
	header('Content-Type: application/json');
	//devicelicensediscover
	include '../Includes/db.php';
	$entitybody = file_get_contents('php://input');
	//echo $entityBody;
	$inputarray = json_decode($entitybody, true);
	
	if (isset($inputarray['devicesn'])){
 		$device_SN = $inputarray['devicesn'];
	}
	else{
		die("{\"error\":\"INVALID_DEVICESN\"}");
	}
	
	$stm = $pdo->query("SELECT license_hash, expdatetime FROM devices, devicelicense where devicelicense_devicelicense_id = devicelicense.devicelicense_id and  devices_SN = '$device_SN'");

	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_parent = array ();
		foreach($dbrows as $dbrow){
			$json_child = array(
			"license_hash"=>$dbrow[0],
			"expdatetime"=>$dbrow[1]
			);
		}

		$response = json_encode($json_child);
		echo $response;
	}
	else {
		die("{\"error\":\"NO_LICENSE_HASH_FOUND\"}");
	}
	
	
	$pdo = null;
	$stm = null;
?>