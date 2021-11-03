<?php
	header('Content-Type: application/json');
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
    global $pdo;
    global $action;

	$entitybody = file_get_contents('php://input');
	$inputarray = json_decode($entitybody, true);
	$action = "select";
	if (isset($inputarray['action'])){
 		$action = $inputarray['action'];
   }
     
     if(isset($inputarray['geofence_id'])){
        $geofence_id = $inputarray['geofence_id'];
     }else{
		die("{\"error\":\"INVAILD_GEOFENCE_ID\"}");
	 }

     if($action == "select"){
            /*$assets_array = getassets($geofence_id);
            $json = array("assets" => $assets_array);
            echo json_encode($json);*/
			$sql = "SELECT s.asset_id, s.asset_name, 
					gd.deviceassets_deviceasset_id, s.active_status 
					FROM geofencing g 
					INNER JOIN deviceassets_geo_det gd 
					ON g.geofencing_id = gd.geofencing_geofencing_id 
					INNER JOIN deviceassets ds 
					ON gd.deviceassets_deviceasset_id = ds.deviceasset_id 
					INNER JOIN assets s 
					ON ds.assets_asset_id = s.asset_id 
					WHERE g.geofencing_id = '". $geofence_id ."'";
	
			$stm = $pdo->query($sql);
			$assetsrows = $stm->fetchAll(PDO::FETCH_NUM);
			if(isset($assetsrows[0][0])){               
				$outputid = 0;
				$json_assets  = array();
				foreach($assetsrows as $assetsrow){
					$json_asset = array(
					"asset_id"=>$assetsrow[0],
					"asset_name"=>$assetsrow[1],
					"deviceasset_id"=>$assetsrow[2],
					"active_status"=>$assetsrow[3]
					);

					$json_assets = array_merge($json_assets,array("response_$outputid" => $json_asset));
					$outputid++;
				}

				$json = array("responses" => $json_assets);
				echo json_encode($json);
			}else{
				die("{\"error\":\"NO_DATA\"}");
			}                                                      
        }
		else if($action == "update"){
        	if(isset($inputarray['linkedassets'])){ 
				$new_assets_ids_array = $inputarray['linkedassets'];	
				$new_asset_count = count($new_assets_ids_array);	
				$asset_ids = implode(",", $new_assets_ids_array); 
				$existing_assetIds= getexistinglinkedassetIds($asset_ids, $geofence_id);  
				$existing_assets_count = count($existing_assetIds);
				$count = 0;

				try{
					$pdo -> beginTransaction();
					if($existing_assets_count > 0){
						deletedeviceassetsGeoDets($asset_ids,$geofence_id);
						foreach($new_assets_ids_array as $asset_id){
							$deviceasset_id = getdeviceassetId($asset_id);
							if($deviceasset_id < 0){
								die("{\"error\":\"INVALID_DEVICEASSET_ID\"}");
							}
	
							$stm = $pdo->prepare("DELETE FROM deviceassets_trigger_det 
												  WHERE deviceassets_deviceasset_id = $deviceasset_id");	
							$stm->execute();
						 }
					}else{
					}

					foreach($new_assets_ids_array as $asset_id){
						$count++;
						$deviceasset_id = getdeviceassetId($asset_id);					
						if($deviceasset_id < 0){
							die("{\"error\":\"INVALID_DEVICEASSET_ID\"}");
						}

						$stm = $pdo->query("SELECT deviceassets_deviceasset_id 
											FROM deviceassets_geo_det 
											WHERE deviceassets_deviceasset_id = $deviceasset_id 
											AND geofencing_geofencing_id = $geofence_id");
						$rows = $stm->fetchAll(PDO::FETCH_NUM);
						if(isset($rows[0][0])){                         
						}else{
							insertdeviceassetsGeoDet($deviceasset_id,$geofence_id);
						}			
					} 
				
					updategeofenceVersion($asset_ids, $user_id, $timestamp);

					if(count($new_asset_count) > 0){
						echo "{\"error\":\"NO_ERROR\"}";
					}

					$pdo->commit();
				}catch(\PDOException $e){
					$pdo->rollBack();
					die("{\"error\":\"" . $e . "\"}");
					exit();
				}		

			}
			else{
					die("{\"error\":\"INVAILD_ASSETS\"}");
				}		  
		}
		else if($action == "insert"){
			if(isset($inputarray['linkedassets'])){ 
				$new_assets_ids_array = $inputarray['linkedassets'];	
				$new_asset_count = count($new_assets_ids_array);	
				$asset_ids = implode(",", $new_assets_ids_array); 

					try{
						$pdo -> beginTransaction();
						foreach($new_assets_ids_array as $asset_id){
							$count++;
							$deviceasset_id = getdeviceassetId($asset_id);					
							if($deviceasset_id < 0){
								die("{\"error\":\"INVALID_DEVICEASSET_ID\"}");
							}
							$stm = $pdo->query("SELECT deviceassets_deviceasset_id 
												FROM deviceassets_geo_det 
												WHERE deviceassets_deviceasset_id = $deviceasset_id 
												AND geofencing_geofencing_id = $geofence_id");
							$rows = $stm->fetchAll(PDO::FETCH_NUM);
							if(isset($rows[0][0])){                         
							}else{
								insertdeviceassetsGeoDet($deviceasset_id,$geofence_id);
							}			
						} 

						updategeofenceVersion($asset_ids, $user_id, $timestamp);

						if(count($new_asset_count) > 0){
							echo "{\"error\":\"NO_ERROR\"}";
						}

						$pdo->commit();
					}catch(\PDOException $e){
						$pdo->rollBack();
						die("{\"error\":\"" . $e . "\"}");
						exit();
					}							
			}
			else{
					die("{\"error\":\"INVAILD_ASSETS\"}");
				}
		}
		else{
				die("{\"error\":\"INVAILD_ACTION\"}");
			}
        
	/*function getassets($geofence_id){
		global $pdo;  
		global $action;
		$sql = "SELECT s.asset_id, s.asset_name, gd.deviceassets_deviceasset_id, s.active_status FROM geofencing g INNER JOIN deviceassets_geo_det gd ON g.geofencing_id = gd.geofencing_geofencing_id INNER JOIN";
		$sql = $sql . " deviceassets ds ON gd.deviceassets_deviceasset_id = ds.deviceasset_id INNER JOIN assets s ON ds.assets_asset_id = s.asset_id WHERE g.geofencing_id = '". $geofence_id ."'";
		
		$stm = $pdo->query($sql);
		$assetsrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($assetsrows[0][0])){               
			$outputid = 0;
			$json_assets  = array();
			$assets_array = array();

			foreach($assetsrows as $assetsrow){
				$json_asset = array(
				"asset_id"=>$assetsrow[0],
				"asset_name"=>$assetsrow[1],
				"deviceasset_id"=>$assetsrow[2],
				"active_status"=>$assetsrow[3]
				);

				$assets_array = array($json_asset);
				$json_assets = array_merge($json_assets,array("assets $outputid" => $json_asset));
				$outputid++;
			}

			if($action == "select"){
				return $json_assets;
			}else{
				return $assets_array;
			}

		}else{
			die("{\"error\":\"NO_ASSETS_FOUND\"}");
		}  
	}*/

	function getdeviceassetId($asset_id){		
		global $pdo; 
		$stm = $pdo->prepare("SELECT deviceasset_id FROM deviceassets WHERE assets_asset_id = :asset_id and active_status = 0 and date_to is NULL");	
		$stm->bindParam(':asset_id',$asset_id);
		$stm->execute();		
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		if(isset($rows[0][0])){
			$deviceasset_id = $rows[0][0]; // run time error if no deviceassets
		}
		return $deviceasset_id;
	}

	function insertdeviceassetsGeoDet($deviceasset_id,$geofence_id){
		global $pdo; 
		$data = [
		'deviceasset_id' => $deviceasset_id,
		'geofence_id' => $geofence_id
		];

		$sql = "INSERT INTO deviceassets_geo_det (deviceassets_deviceasset_id, geofencing_geofencing_id) 
		VALUES (:deviceasset_id, :geofence_id)";
		$stmt= $pdo->prepare($sql);
		$stmt->execute($data);
	}

	function getexistinglinkedassetIds($asset_ids,$geofence_id){
		global $pdo;  
		$sql = "SELECT assets_asset_id FROM deviceassets WHERE assets_asset_id NOT IN (". $asset_ids .") 
				AND deviceassets.active_status = 0 AND deviceassets.date_to IS NULL AND deviceasset_id IN (
				SELECT deviceassets_deviceasset_id FROM deviceassets_geo_det WHERE geofencing_geofencing_id = :geofence_id)";
		//echo $sql;
		$stm = $pdo->prepare($sql);	
		$stm->bindParam(':geofence_id',$geofence_id);
		$stm->execute();		
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if(isset($dbrows[0][0])){               
			$outputid = 0;
			$assets_rows = array();
			foreach($dbrows as $dbrow){
				$assets_row = array(
				$dbrow[0]
				);

				$assets_rows = array_merge($assets_rows,$assets_row);
				$outputid++;
			}
			return $assets_rows;
		}
	}

	function deletedeviceassetsGeoDets($asset_id,$geofence_id){
		global $pdo; 
		try{
			$sql = "DELETE FROM deviceassets_geo_det WHERE deviceassets_deviceasset_id IN (SELECT deviceasset_id FROM deviceassets 
					WHERE deviceassets.assets_asset_id NOT IN (". $asset_id .") AND deviceassets.active_status = 0 AND deviceassets.date_to IS NULL) 
					AND deviceassets_geo_det.geofencing_geofencing_id = :geofence_id";
			$stm = $pdo->prepare($sql);	
			$stm->bindParam(':geofence_id', $geofence_id);
			$stm->execute();
		}catch(\PDOException $e){
			die("{\"error\":\"" . $e . "\"}");
		} 		
	}

	function updategeofenceVersion($asset_ids, $user_id, $timestamp){
		global $pdo; 
		$stm = $pdo->query("SELECT deviceassets.devices_device_id,devices.geofences_version 
						FROM deviceassets,devices 
						WHERE deviceassets.devices_device_id = devices.device_id 
						AND deviceassets.assets_asset_id IN (". $asset_ids .") 
						AND devices.active_status = 0 AND deviceassets.active_status = 0");
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if(isset($rows[0][0])){
			$device_id = $rows[0][0]; 
			$db_geofences_version = $rows[0][1];                   
		}else{
			die("{\"error\":\"INVAILD_DEVICE_ID\"}");
		}

		$geofences_version = $db_geofences_version + 1;
		$data = [
		'device_id' => $device_id,
		'geofence_version' => $geofences_version,
		'last_modified_by' => $user_id,
		'last_modified_datetime' => $timestamp
		];

		$sql = "UPDATE devices SET geofences_version = :geofence_version, last_modified_by = :last_modified_by,"; 
		$sql = $sql . " last_modified_datetime = :last_modified_datetime WHERE device_id = :device_id and active_status = 0";						
		$stmt= $pdo->prepare($sql);
		if($stmt->execute($data)){
		}else{
			die("{\"error\":\"ERROR\"}");
		}	
	}

/*
		function updategeofenceVersion($device_id,$geofences_version, $user_id, $timestamp){
			global $pdo; 
			if(isset($geofences_version)){
				$geofence_ver = $geofences_version + 1;
			}

			$data = [
				'device_id' => $device_id,
				'geofence_version' => $geofence_ver,
				'last_modified_by' => $user_id,
				'last_modified_datetime' => $timestamp
				];

				$sql = "UPDATE devices SET geofences_version = :geofence_version, last_modified_by = :last_modified_by,"; 
				$sql = $sql . " last_modified_datetime = :last_modified_datetime WHERE device_id = :device_id and active_status = 0";						
				$stmt= $pdo->prepare($sql);
				if($stmt->execute($data)){

				}else{
					die("{\"error\":\"ERROR\"}");
				}	
	    }


	function getgeofencesVersion($device_id){
		global $pdo;  
		$sql = "SELECT geofences_version FROM devices WHERE device_id ='". $device_id ."' and active_status = 0";
		$stm = $pdo->query($sql);	
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if (isset($dbrows[0][0])){               
			$outputid = 0;
			$json_geofences  = array();
			foreach($dbrows as $dbrow){
				$json_geofence = array(
				"geofences_version"=>$dbrow[0]
				);
							
				$json_geofences = array_merge($json_geofences,array("$outputid" => $json_geofence));
				$outputid++;
			}

			return $json_geofences;
	    }
	}

	function getdeviceId($deviceasset_id){
		global $pdo;  
		$sql = "SELECT devices_device_id FROM deviceassets WHERE deviceasset_id ='". $deviceasset_id ."' and active_status = 0";
		$stm = $pdo->query($sql);	
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if(isset($dbrows[0][0])){               			
			  $device_id=>$rows[0][0];
			}
			return $device_id;
		}
	}*/

	$pdo = null;
	$stm = null;

?>