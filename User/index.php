<?php

	//Note for this API. Speak to Jessica, it needs to happen as a transaction

	header('Content-Type: application/json');
	//user
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
	$entitybody = file_get_contents('php://input');
	global $pdo;
		
	//echo $entityBody;
	$inputarray = json_decode($entitybody, true);
	$action = "select";
	if (isset($inputarray['action'])){
 		$action = $inputarray['action'];
	 }

	if ($action == "select"){
		$stm = $pdo->query("SELECT 
		user_id,
		user_login_id,
		user_name,
		user_emailid,
		user_roles,
		active_status,
		last_modified_by,
		last_modified_datetime
		FROM users;
		");
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			$json_parent = array ();
			$outputid = 0;
			foreach($dbrows as $dbrow){
				$json_child = array(
					"user_id"=>$dbrow[0],
					"user_login_id"=>$dbrow[1],
					"user_name"=>$dbrow[2],
					"user_emailid"=>$dbrow[3],
					"user_roles"=>$dbrow[4],
					"active_status"=>$dbrow[5],
					"last_modified_by"=>$dbrow[6],
					"last_modified_datetime"=>$dbrow[7]
				);
				$json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
				$outputid++;
			}
			$json = array("responses" => $json_parent);
			echo json_encode($json);
		}
	}
	else if ($action == "insert"){
		if ($user_roles == 1){
		
		}
		else{
			die( "{\"error\":\"SECURITY_LEVEL\"}");
		}

		if (isset($inputarray['user_login_id'])){
	 		$user_login_id = $inputarray['user_login_id'];
		}
	 	else {
	 		die( "{\"error\":\"INVALID_USER_LOGIN_ID\"}");
	 	}			
			
		if (isset($inputarray['user_name'])){
			$user_name = $inputarray['user_name'];
		}
		else {
			die( "{\"error\":\"INVALID_USER_NAME\"}");
		}	
				
		if (isset($inputarray['password'])){
			$password = $inputarray['password'];
		}
		else {
			die("{\"error\":\"INVALID_PASSWORD\"}");
		}
				
		$user_emailid = "";
		if (isset($inputarray['user_emailid'])){
			$user_emailid = $inputarray['user_emailid'];
		}
		
		$active_status = "0";
		if (isset($inputarray['active_status'])){
			$active_status = $inputarray['active_status'];
		}	

		$user_roles = "1";
		if (isset($inputarray['user_roles'])){
			$user_roles = $inputarray['user_roles'];
		}
		
	//print_r($assets);
		
   try{
		//$dbh->commit/rollback here fixes the issue;
		$pdo -> beginTransaction(); 
		
		$stm = $pdo->query("SELECT user_login_id FROM users where user_login_id = '$user_login_id' and active_status = 0");
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($rows[0][0])){
			echo "{\"error\":\"INVALID_USER ID ALREADY EXISTS\"}";
		}
		else {
			$data = [
			'user_login_id' => $user_login_id,
			'user_name' => $user_name,
			'user_password' => password_hash($password, PASSWORD_DEFAULT),
			'user_emailid' => $user_emailid,
			'user_roles' => $user_roles,
			'active_status' => $active_status,
			'last_modified_by' => $user_id,
			'last_modified_datetime' => $timestamp
			];
			$sql = "INSERT INTO users (user_login_id, user_name, user_password, user_emailid, user_roles, active_status, last_modified_by, last_modified_datetime) 
			VALUES (:user_login_id, :user_name, :user_password, :user_emailid, :user_roles, :active_status, :last_modified_by, :last_modified_datetime)";
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($data)){
				echo "{\"error\":\"NO_ERROR\"}";
			}

			$userid = $pdo->lastInsertId();

			if (isset($inputarray['assets'])){
				$assets = $inputarray['assets'];

				if(isset($userid) && $userid != "1"){
					 $asset_summary = "Some";
				}

				$data = [
					'date_from' => $timestamp,
					'date_to' => NULL,
					'users_user_id' => $userid,
					'asset_summary' => $asset_summary,
					'active_status' => $active_status,
					'last_modified_by' => $user_id,
					'last_modified_datetime' => $timestamp
					];
				
					$sql = "INSERT INTO user_assets (date_from, date_to, users_user_id, asset_summary, active_status, last_modified_by, last_modified_datetime) 
					VALUES (:date_from, :date_to, :users_user_id, :asset_summary, :active_status, :last_modified_by, :last_modified_datetime)";
					$stmt= $pdo->prepare($sql);
					if($stmt->execute($data)){
						
					}

					$user_asset_id = $pdo->lastInsertId();

					foreach ($assets as $value){												
						$data = [
							'user_asset_id' => $user_asset_id,
							'assets_asset_id' => $value,
							'active_status' => $active_status,
							'last_modified_by' => $user_id,
							'last_modified_datetime' => $timestamp
							];

							$sql = "INSERT INTO userasset_details (user_assets_user_asset_id, assets_asset_id, active_status, last_modified_by, last_modified_datetime) 
							VALUES (:user_asset_id, :assets_asset_id, :active_status, :last_modified_by, :last_modified_datetime)";
							$stmt= $pdo->prepare($sql);
							if($stmt->execute($data)){
								
							}else{
								die("{\"error\":\"ERROR\"}");
							 }			
					}
		       }
		   }

		   $pdo->commit();

		}catch(\PDOException $e){

			$pdo->rollBack();
			//http_response_code(500);
			//header("Content-type: text/plain");
			//echo $e;
			echo "{\"error\":\"$e\"}";
			exit();
		}
	}
	else if ($action == "update"){
		$sql = "last_modified_by = :last_modified_by, last_modified_datetime = :last_modified_datetime";
		$data = [];
		$data['last_modified_datetime'] = $timestamp;
		$data['last_modified_by'] = $user_id;
		$pramfound = 0;
		$userid = 0;
		if (isset($inputarray['user_id'])){
			$data['user_id'] = $inputarray['user_id'];
			$userid = $inputarray['user_id'];
		}
	 	else {
	 		die( "{\"error\":\"INVALID_USER_ID\"}");
	 	}
		
		if ($user_roles != 1){
			if ($data['user_id'] != $user_id){
				die( "{\"error\":\"SECURITY_LEVEL\"}");
			}
		}
		
		if (isset($inputarray['user_login_id'])){
	 		$sql = $sql . ", user_login_id = :user_login_id";
			$data['user_login_id'] = $inputarray['user_login_id'];
			$pramfound = 1;
		}	

		if (isset($inputarray['user_name'])){
	 		$sql = $sql . ", user_name = :user_name";
			$data['user_name'] = $inputarray['user_name'];
			$pramfound = 1;
		}		

		if (isset($inputarray['password'])){
	 		$sql = $sql . ", user_password = :password";
			$data['password'] = password_hash($inputarray['password'], PASSWORD_DEFAULT);
			$pramfound = 1;
		}	

		if (isset($inputarray['user_emailid'])){
	 		$sql = $sql . ", user_emailid = :user_emailid";
			$data['user_emailid'] = $inputarray['user_emailid'];
			$pramfound = 1;
		}

		if (isset($inputarray['active_status'])){
	 		$sql = $sql . ", active_status = :active_status";
			$data['active_status'] = $inputarray['active_status'];
			$active_status = $inputarray['active_status'];
			$pramfound = 1;
		}	

		if (isset($inputarray['user_roles'])){
	 		$sql = $sql . ", user_roles = :user_roles";
			$data['user_roles'] = $inputarray['user_roles'];
			$pramfound = 1;
		}	
	
  try{
		$pdo -> beginTransaction(); 

		if ($pramfound == 1){
			$sql = "UPDATE users SET $sql where user_id = :user_id";
			//echo $sql;
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($data)){
				echo "{\"error\":\"NO_ERROR\"}";
			}
			else {
				echo "{\"error\":\"ERROR\"}";
			}
		}else{
			echo "{\"error\":\"ERROR\"}";
		}

		if(isset($inputarray['assets'])){

		     	$assets = $inputarray['assets'];
				if(isset($userid) && $userid != "1"){
					$asset_summary = "Some";
				}
					
				if(isset($inputarray['active_status'])){
					    $active_status = $inputarray['active_status'];
			   }else{          
				        $active_status = 0;			
			   }

				$data = [
					'userid' => $userid,
					'date_from' => $timestamp,
					'date_to' => NULL,
					'users_user_id' => $userid,
					'asset_summary' => $asset_summary,
					'active_status' => $active_status,
					'last_modified_by' => $user_id,
					'last_modified_datetime' => $timestamp
					];
			
				$sql = "UPDATE user_assets SET date_from = :date_from, date_to = :date_to, users_user_id = :users_user_id,";
				$sql = $sql . " asset_summary = :asset_summary, active_status = :active_status, last_modified_by = :last_modified_by,";
				$sql = $sql . " last_modified_datetime = :last_modified_datetime WHERE users_user_id = :userid";
				$stmt= $pdo->prepare($sql);
				if($stmt->execute($data)){					

				}else{
					die("{\"error\":\"ERROR\"}");
				}

				$user_asset_id = getuserassetId($userid);

		   if (isset($user_asset_id)){

				$stm = $pdo->prepare("SELECT userasset_detail_id FROM userasset_details WHERE user_assets_user_asset_id = :user_asset_id and active_status = 0");	
				$stm->bindParam(':user_asset_id', $user_asset_id);
				$stm->execute();		
				$row = $stm->fetchAll(PDO::FETCH_NUM);
				$json_parent = array();
				
				if (isset($row[0][0])){

				 $asset_id = count($assets);
				 
			     $new_assets_count = count($assets);
				 $old_assets_count = count($row);

			if(isset($new_assets_count) >  isset($old_assets_count)){

				for($i=0; $i < count($assets); $i++){				
					
				  if(isset($row[$i][0])){
					$userasset_detail_id = $row[$i][0];
					$asset_id = $assets[$i];
					
					$new_assets_count--;
					$old_assets_count--;	

					updateuserassetDetails($userasset_detail_id, $asset_id, $active_status, $user_id, $timestamp);

					}else{
							 $user_asset_id = getuserassetId($userid);
							 $asset_id = $assets[$i];

							 insertuserassetDetails($asset_id,$user_asset_id,$active_status,$user_id,$timestamp);
					    }
					}

				}else{
				  
					for($i=0; $i < count($row); $i++){	
						if(isset($row[$i][0])){

							$userasset_detail_id = $row[$i][0];					
							$new_assets_count--;
							$old_assets_count--;	
						
					        if(isset($new_assets_count) < 0){
								deleteuserassetDetails($userasset_detail_id);
							}else{
								$asset_id = $assets[$i];
								updateuserassetDetails($userasset_detail_id, $asset_id, $active_status, $user_id, $timestamp);
							}
						}
					}
				}					
			}else{
					  for($i=0; $i < count($assets); $i++){								  
						     $asset_id = $assets[$i];
					       insertuserassetDetails($asset_id,$user_asset_id,$active_status,$user_id,$timestamp); 
						}
				}
				
				if(count($assets > 0) && $pramfound == 0){
					          echo "{\"error\":\"NO_ERROR\"}";
				} /*else {
		         	     echo "{\"error\":\"ERROR_INVALID_PRAM\"}";
			  }*/
			  
			}else{
					echo "{\"error\":\"INVALID_USER_ASSET_ID\"}";
				}
		  }	
	   
		  $pdo->commit();

	 }catch(\PDOException $e){

			$pdo->rollBack();
			//http_response_code(500);
			//header("Content-type: text/plain");
			//echo $e;
			echo "{\"error\":\"$e\"}";
			exit();
       }
	}else{
             echo "{\"error\":\"INVALID_ACTION\"}";
 }
 //echo "yep";
	$pdo = null;
	$stm = null;

	function getuserassetId($user_id){    
		global $pdo; 
		$stm = $pdo->prepare("SELECT user_asset_id FROM user_assets WHERE users_user_id = :userid and active_status = 0");	
		$stm->bindParam(':userid', $user_id);
		$stm->execute();		
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		$user_asset_id = $rows[0][0];

		return $user_asset_id;
   }

   function insertuserassetDetails($asset_id,$user_asset_id,$active_status,$user_id,$timestamp){
	    global $pdo; 
		$data = [
		'user_asset_id' => $user_asset_id,
		'assets_asset_id' => $asset_id,
		'active_status' => $active_status,
		'last_modified_by' => $user_id,
		'last_modified_datetime' => $timestamp
		];

		$sql = "INSERT INTO userasset_details (user_assets_user_asset_id, assets_asset_id, active_status, last_modified_by, last_modified_datetime) 
		VALUES (:user_asset_id, :assets_asset_id, :active_status, :last_modified_by, :last_modified_datetime)";
		$stmt= $pdo->prepare($sql);
		$stmt->execute($data);

   }

   function deleteuserassetDetails($userasset_detail_id){
		global $pdo; 
		$stm = $pdo->prepare("DELETE FROM userasset_details WHERE userasset_detail_id = :userasset_detail_id");	
		$stm->bindParam(':userasset_detail_id', $userasset_detail_id);
		$stm->execute();		
   }

   function updateuserassetDetails($userasset_detail_id, $asset_id,$active_status, $user_id, $timestamp){
	global $pdo; 
	$data = [
		'userasset_detail_id' => $userasset_detail_id,
		'assets_asset_id' => $asset_id,
		'active_status' => $active_status,
		'last_modified_by' => $user_id,
		'last_modified_datetime' => $timestamp
		];

		$sql = "UPDATE userasset_details SET assets_asset_id = :assets_asset_id, active_status = :active_status, last_modified_by = :last_modified_by,"; 
		$sql = $sql . " last_modified_datetime = :last_modified_datetime WHERE userasset_detail_id = :userasset_detail_id";						
		$stmt= $pdo->prepare($sql);
		$stmt->bindParam(':userasset_detail_id', $userasset_detail_id);
		if($stmt->execute($data)){

		 }else{
			die("{\"error\":\"ERROR\"}");
		 }	
    }
?>


