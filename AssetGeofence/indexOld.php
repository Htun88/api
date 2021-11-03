<?php
	header('Content-Type: application/json');
	include '../includes/db.php';
	include '../includes/checktoken.php';
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
            $assets_array = getassets($geofence_id);
            $json = array("assets" => $assets_array);
            echo json_encode($json);                           
        }
	else if($action == "update"){

        if(isset($inputarray['linkedassets'])){ 
    
			$assets_new_ids_array = $inputarray['linkedassets'];	
			$asset_new_count = count($assets_new_ids_array);	

			for($i=0; $i<$asset_new_count; $i++){
				$asset_new_id = $assets_new_ids_array[$i];
				$deviceasset_array = getdeviceassetId($asset_new_id);
				$deviceasset_count = count($deviceasset_array);
				$deviceasset_id = $deviceasset_array[0]['deviceasset_id'];

				if(isset($deviceasset_id)){
					$existing_deviceassets_array = getexistingdeviceassetId($deviceasset_id,$geofence_id);
					$existing_deviceassets_count =  count($existing_deviceassets_array);

					if($existing_deviceassets_count > 0){
						//echo "exist";
						deletedeviceassetsGeoDet($deviceasset_id,$geofence_id);
						//deletedeviceassetsTriggerDets($asset_id,$geofence_id);
					}										
					else{
						//echo "not exist";
						insertdeviceassetsGeoDet($deviceasset_id,$geofence_id);
					}		

					$device_array = getdeviceId($deviceasset_id);
					$device_count = count($device_array);
					$device_id = $device_array[0]['device_id']; 
					$geofence_ver_array = getgeofencesVersion($device_id);
					$geofence_ver_count = count($geofence_ver_array);
					$geofence_version = $geofence_ver_array[0]['geofences_version']; 
		
					if(isset($device_id) && isset($geofence_version)){
						updategeofenceVersion($device_id,$geofence_version,$user_id,$timestamp);
					}         
				} 
			} 

			if(count($asset_new_count) > 0){
				echo "{\"error\":\"NO_ERROR\"}";
			}
        }
      
    }else{
      die("{\"error\":\"INVAILD_ACTION\"}");
  }
        
	function getassets($geofence_id){
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
	}

        function getdeviceassetId($asset_new_id){
            global $pdo;  
            $sql = "SELECT deviceasset_id FROM deviceassets WHERE assets_asset_id = '". $asset_new_id ."'";
            $stm = $pdo->query($sql);	
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        
            if (isset($dbrows[0][0])){               
                $outputid = 0;
                $json_assets  = array();
                foreach($dbrows as $dbrow){
                    $json_asset = array(
                    "deviceasset_id"=>$dbrow[0]
                    );
                                
                    $json_assets = array_merge($json_assets,array("$outputid" => $json_asset));
                    $outputid++;
                }

                return $json_assets;
           }
        }   

	function getexistingdeviceassetId($deviceasset_id,$geofence_id){
        global $pdo;  
		$sql = "SELECT deviceassets_deviceasset_id FROM deviceassets_geo_det WHERE deviceassets_deviceasset_id = '". $deviceasset_id ."'";
		$sql = $sql . " and geofencing_geofencing_id = '". $geofence_id ."'";
		$stm = $pdo->query($sql);	
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	
		if (isset($dbrows[0][0])){               
			$outputid = 0;
			$json_assets  = array();
			foreach($dbrows as $dbrow){
				$json_asset = array(
				  "deviceasset_id"=>$dbrow[0]
				);
							
				$json_assets = array_merge($json_assets,array("$outputid" => $json_asset));
				$outputid++;
			}

			return $json_assets;
	   }
    }
    
    function getdeviceId($deviceasset_id){
        global $pdo;  
		$sql = "SELECT devices_device_id FROM deviceassets WHERE deviceasset_id ='". $deviceasset_id ."' and active_status = 0";
		$stm = $pdo->query($sql);	
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	
		if (isset($dbrows[0][0])){               
			$outputid = 0;
			$json_assets  = array();
			foreach($dbrows as $dbrow){
				$json_asset = array(
				  "device_id"=>$dbrow[0]
				);
							
				$json_assets = array_merge($json_assets,array("$outputid" => $json_asset));
				$outputid++;
			}

			return $json_assets;
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

/*function deletedeviceassetsGeoDet($deviceasset_id,$geofence_id){
	global $pdo; 
	$stm = $pdo->prepare("DELETE FROM deviceassets_geo_det WHERE geofencing_geofencing_id = :geofence_id and deviceassets_deviceasset_id = :deviceasset_id");	
	$stm->bindParam(':geofence_id', $geofence_id);
	$stm->bindParam(':deviceasset_id', $deviceasset_id);
	$stm->execute();		
}*/


function deletedeviceassetsTriggerDets($asset_id,$geofence_id){
    global $pdo; 
    try{
        $sql = "DELETE FROM deviceassets_geo_det WHERE deviceassets_deviceasset_id IN (SELECT deviceasset_id FROM deviceassets 
        WHERE deviceassets.assets_asset_id NOT IN (". $asset_id .") AND deviceassets.active_status = 0 AND deviceassets.date_to IS NULL) 
        AND deviceassets_geo_det.geofencing_geofencing_id = :geofence_id";
       $stm = $pdo->prepare($sql);	
       $stm->bindParam(':geofence_id', $geofence_id);
       //$stm->bindParam(':deviceasset_id', $deviceasset_id);
       $stm->execute();
    }catch(\PDOException $e){
       die("{\"error\":\"" . $e . "\"}");
     } 		
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

$pdo = null;
$stm = null;

?>