<?php
	header('Content-Type: application/json');
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	//Udilicense
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
	$entitybody = file_get_contents('php://input');
	$inputarray = json_decode($entitybody, true);
	$action = "select";
	if (isset($inputarray['action'])){
 		$action = $inputarray['action'];
	}
	
	$stm = $pdo->query("SELECT * FROM information_schema.tables WHERE table_schema = '$databasename' AND table_name = 'udilicense' LIMIT 1");
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0])) {

	}
	else{
		$stm = $pdo->exec("CREATE TABLE `udilicense` (
		  `udilicense_id` int(11) NOT NULL AUTO_INCREMENT,
		  `license_hash` varchar(256) DEFAULT NULL,
		  PRIMARY KEY (`udilicense_id`)
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");
	}
	
	
	if($action == "select"){		
		$stm = $pdo->query("SELECT license_hash FROM udilicense;");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			$json_license_hashs = array();
			$outputid = 0;
			foreach($dbrows as $row){
				$json_license_hash = array(
				"license_hash"=>$row[0]
				);

				$json_license_hashs = array_merge($json_license_hashs,array("response_$outputid" => $json_license_hash));
				$outputid++;
			}

			$json = array("responses" => $json_license_hashs);
			echo json_encode($json);
		}else{
			die("{\"error\":\"NO_LICENSE_HASH_FOUND\"}");
		}
	}
	elseif($action == "insert"){
		$stm = $pdo->query("SELECT license_hash FROM udilicense;");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			die("{\"error\":\"LICENSE_HASH_ALREADY_STORED\"}");
		}
		
		if(isset($inputarray['licensehash'])){
			$data = [
			'license_hash' => $inputarray['licensehash']
			];

			$sql = "INSERT INTO udilicense (license_hash) 
			VALUES (:license_hash)";
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($data)){
				echo("{\"error\":\"NO_ERROR\"}");
			}
			else {
				die("{\"error\":\"ERROR\"}");
			}
		}else{
			die("{\"error\":\"INVALID_LICENSE_HASH\"}");
		}
		
	}
	elseif($action == "update"){
		$stm = $pdo->query("SELECT udilicense_id FROM udilicense;");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			if(isset($inputarray['licensehash'])){
				$licensehash =  $inputarray['licensehash'];
				$data = ['license_hash' => $licensehash];
				$udilicense_id = $dbrows[0][0];
				$sql = "UPDATE udilicense SET license_hash = :license_hash WHERE udilicense_id = $udilicense_id";
				$stmt= $pdo->prepare($sql);
				if($stmt->execute($data)){
				}
				else {
					die("{\"error\":\"ERROR\"}");
				}
				echo("{\"error\":\"NO_ERROR\"}");
			}else{
				die("{\"error\":\"INVALID_LICENSE_HASH\"}");
			}
		}
		else{
			die("{\"error\":\"NO_LICENSE_HASH_FOUND\"}");
		}

	}
	else {
		die("{\"error\":\"INVALID_ACTION\"}");
	}
	
	$pdo = null;
	$stm = null;
?>