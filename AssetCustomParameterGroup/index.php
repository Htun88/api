<?php 
	$API = "AssetCustomParameterGroup";
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
	$schemainfoArray = getMaxString("asset_custom_param_group", $pdo);

	if (isset($inputarray['action'])){
        $sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, $API, $logParent);
		$logParent = logEvent($API . logText::action . ucfirst($sanitisedInput['action']), logLevel::action, logType::action, $token, $logParent)['event_id'];
	}
	else {
		errorInvalid("request", $API, $logParent);
	}
	
	if($sanitisedInput['action'] == "select"){

		$schemainfoArray = getMaxString ("asset_custom_param_group", $pdo);

		$sql = "SELECT 
				`id`,
				`name`,
				`tag_name`
				FROM `asset_custom_param_group`
				WHERE 1 = 1";

		if (isset($inputarray['id'])) {		
			$sanitisedInput['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
			$sql .= " AND `id` = '". $sanitisedInput['id'] ."'";
		}

		if (isset($inputarray['name'])) {		
			$sanitisedInput['name'] = sanitise_input($inputarray['name'], "name",  $schemainfoArray["name"], $API, $logParent);
			$sql .= " AND `name` = '". $sanitisedInput['name'] ."'";
		}

		if (isset($inputarray['tag_name'])) {		
			$sanitisedInput['tag_name'] = sanitise_input($inputarray['tag_name'], "tag_name",  $schemainfoArray["tag_name"], $API, $logParent);
			$sql .= " AND `tag_name` = '". $sanitisedInput['tag_name'] ."'";
		}

		$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){

			$json_parent = array();
			$outputid = 0; 

			foreach($dbrows as $dbrow){
				$json_child = array(
				"id"=>$dbrow[0]
				, "name" => $dbrow[1]
				, "tag_name" => $dbrow[2]
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
			die("{\"error\":\"NO_DATA\"}");
		} 
	}


	// *******************************************************************************
	// *******************************************************************************
	// *****************************INSERT********************************************
	// *******************************************************************************
	// *******************************************************************************


	elseif($sanitisedInput['action'] == "insert"){

		$insertArray = [];
		
		if(isset($inputarray['name'])){
			$insertArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
		}else {
			errorMissing("name", $API, $logParent);
		}

		if(isset($inputarray['tag_name'])){
			$insertArray['tag_name'] = sanitise_input($inputarray['tag_name'], "tag_name", $schemainfoArray['tag_name'], $API, $logParent);
		}else {
			errorMissing("tag_name", $API, $logParent);
		}

		$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	
		try{    

			$stm = $pdo->query("SELECT id FROM asset_custom_param_group where name = '" . $insertArray["name"] . "' OR tag_name = '". $insertArray['tag_name'] ."'");
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){     
				$sql = "INSERT INTO asset_custom_param_group(
						`name`, `tag_name`) VALUES (:name, :tag_name)";	
				//echo $sql;
				$stmt= $pdo->prepare($sql);
				if($stmt->execute($insertArray)){
					$insertArray ['id'] = $pdo->lastInsertId(); 
					$insertArray ['error' ] = "NO_ERROR";
					echo json_encode($insertArray);
					logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
				}
			}
			else{
				$insertArray ['id'] = $dbrows[0][0]; 
				$insertArray ['error' ] = "Data_Already_Exist";
				echo json_encode($insertArray);
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
			}
			
		}catch(\PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"" . $e . "\"}");
			//exit();
		}
	}


    // *******************************************************************************
    // *******************************************************************************
    // *****************************UPDATE********************************************
    // *******************************************************************************
    // *******************************************************************************



	elseif(isset($sanitisedInput['action']) == "update"){           
		         						
		$updateArray = array();
		$updateString = "UPDATE asset_custom_param_group SET ";

		if(isset($inputarray['id'])){
			$updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
		}
		else{
			errorMissing("id", $API, $logParent);
		}
							
		if(isset($inputarray['name'])){
			$updateArray["name"] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);	
			$updateString = $updateString . "name= :name,";
		}

		if(isset($inputarray['tag_name'])){
			$updateArray["tag_name"] =  sanitise_input($inputarray['tag_name'], "tag_name", $schemainfoArray['tag_name'], $API, $logParent);
			/*  $stm = $pdo->query("SELECT * FROM asset_custom_param_group where tag_name = '" . $updateArray["tag_name"] . "'");
				$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
				if (isset($dbrows[0][0])){       
					errorInvalid("tag_name", $API, $logParent);             
				}	*/

			$updateString = $updateString . "tag_name = :tag_name";
		}
		
		$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];	
        
		try{ 
			if (count($updateArray) < 1) {
				logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
				die("{\"error\":\"NO_UPDATED_PRAM\"}");
			}
			else{  				
					$sql = rtrim($updateString,',')  . " where id = :id";	
					$stmt= $pdo->prepare($sql);
					if($stmt->execute($updateArray)){	
						$updateArray ['id'] = $updateArray['id']; 
						$updateArray ['error' ] = "NO_ERROR";
						echo json_encode($updateArray);				
					}
				}

		}catch(\PDOException $e){
			logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
			die("{\"error\":\"" . $e . "\"}");
			//exit();
		}								
	}
	else{
			logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
			errorInvalid("request", $API, $logParent);
	    }

	$pdo = null;
	$stm = null;
  
?>