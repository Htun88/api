<?php 
	header('Content-Type: application/json');
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
	include '../Includes/sanitise.php';

	$entitybody = file_get_contents('php://input');
	$inputarray = json_decode($entitybody, true);

	if ($_SERVER['REQUEST_METHOD'] == "GET") {
		$inputarray = null;
		$inputarray['action'] = "select";
	}
	
	checkKeys($inputarray, "AccessCustomParam");

	if (isset($inputarray['action'])){
        $inputarray['action'] = sanitise_input($inputarray['action'], "action", 7);
	}
	else {
		errorInvalid("request");
	}
	
	if($inputarray['action'] == "select"){
		$sql = "SELECT 
			`asset_custom_param`.`id`,
			`asset_custom_param`.`name`,
			`asset_custom_param`.`tag_name`,
			`asset_custom_param`.`active_status`,
			`asset_custom_param`.`last_modified_by`,
			`asset_custom_param`.`last_modified_datetime`
		FROM `asset_custom_param`
		WHERE 1 = 1 ";

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
				, "active_status" => $dbrow[3]
				, "last_modified_by" => $dbrow[4]
				, "last_modified_datetime" => $dbrow[5]
				);
				$json_parent = array_merge($json_parent,array("assetcustomparam $outputid" => $json_child));
				$outputid++;
			}
			$json = array("assetcustomparams" => $json_parent);
			echo json_encode($json);
		}
		else {
			die("{\"error\":\"NO_DATA\"}");
		} 
	}
// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************
	elseif($inputarray['action'] == "insert"){
		
		$schemainfoArray = getMaxString("asset_custom_param", $pdo);
		$insertArray = [];
		
		if(isset($inputarray['name'])){
			$insertArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name']);
		}else {
			errorMissing("name");
		}

		if(isset($inputarray['tag_name'])){
			$insertArray['tag_name'] = sanitise_input($inputarray['tag_name'], "tag_name", $schemainfoArray['tag_name']);
		}else {
			errorMissing("tag_name");
		}
		
		if (isset($inputarray['active_status'])) {
			$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null);
		}
		else {
			errorMissing("active_status");
		}
		
		$insertArray['last_modified_by'] = $user_id;
		$insertArray['last_modified_datetime'] = $timestamp;
	
		     try{             
					$sql = "INSERT INTO asset_custom_param(
						`name`
						, `tag_name`
						, `active_status`
						, `last_modified_by`
						, `last_modified_datetime`)
					VALUES (
							:name
							, :tag_name
							, :active_status
							, :last_modified_by
							, :last_modified_datetime)";	
					$stmt= $pdo->prepare($sql);
					if($stmt->execute($insertArray)){
						$insertArray ['id'] = $pdo->lastInsertId(); 
						$insertArray ['error' ] = "NO_ERROR";
						echo json_encode($insertArray);
					}
					else {
						die("{\"error\":\"ERROR\"}");
					}		
			
				}catch(\PDOException $e){
					die("{\"error\":\"" . $e . "\"}");
					exit();
				}
	}
	elseif(isset($inputarray['action']) == "update"){           
		try{    
			
			$updateArray = array();
			$sql = "UPDATE asset_custom_param SET ";
			
			if(isset($inputarray['id'])){
				$insertArray['id'] = sanitise_input($inputarray['id'], "id", null);
			}
			else{
				errorMissing("id");
			}
								
			if(isset($inputarray['name'])){
				$updateArray["name"] = sanitise_input($inputarray['name'], "name", null);	
				$sql = $sql . "name= :name,";
			}

			if(isset($inputarray['tag_name'])){
				$updateArray["tag_name"] =  sanitise_input($inputarray['tag_name'], "tag_name", null);			
				$sql = $sql . "tag_name = :tag_name,";
			}

			if(isset($inputarray['active_status'])){
				$updateArray["active_status"] =  sanitise_input($inputarray['active_status'], "active_status", null);	
				$sql = $sql . "active_status= :active_status,";
			}

			$updateArray['last_modified_by'] = $user_id;
			$updateArray['last_modified_datetime'] = $timestamp;
	
			$sql = $sql . " name = :name, tag_name = :tag_name, 
							active_status = :active_status,
							last_modified_by = :last_modified_by,
							last_modified_datetime = :last_modified_datetime 
							where id = :id";
            
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($updateArray)){	
				$updateArray ['id'] = $id; 
				$updateArray ['error' ] = "NO_ERROR";
				echo json_encode($updateArray);				
			}
			else {
				die("{\"error\":\"ERROR\"}");
			}

		}catch(\PDOException $e){
			die("{\"error\":\"" . $e . "\"}");
			exit();
		}								
	}
	else{
		die("{\"error\":\"INVALID_REQUEST_PRAM\"}");
	}

  
?>