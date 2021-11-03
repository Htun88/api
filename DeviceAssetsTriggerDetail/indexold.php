<?php
    $API = "DeviceAssetsTriggerDetail";
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
			trigger_groups.trigger_id,
			trigger_groups.trigger_name,
			trigger_groups.trigger_source,
			trigger_groups.geofencing_geofencing_id,
			deviceassets.deviceasset_id,
			deviceassets.devices_device_id,
			assets.asset_id
			FROM 

			(users
			, user_assets
			, assets
			, deviceassets_trigger_det
			, trigger_groups
			, deviceassets)

			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE 
			deviceassets_trigger_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id  
			AND trigger_groups.trigger_id = deviceassets_trigger_det.trigger_groups_trigger_id 
			AND deviceassets.assets_asset_id = assets.asset_id
			AND deviceassets.date_to IS NULL
			AND users.user_id = user_assets.users_user_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))   
			AND assets.active_status = 0
			AND deviceassets.active_status = 0
			AND trigger_groups.active_status = 0";

			
						//AND deviceassets.assets_asset_id = '5'
	if(isset($inputarray['trigger_id'])){											
		$trigger_id = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
		$sql .= " AND trigger_groups.trigger_id = '" . $trigger_id . "'";
	}

	if(isset($inputarray['deviceasset_id'])){											
		$deviceasset_id = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND deviceassets.deviceasset_id = '" . $deviceasset_id . "'";
	}
	
	if(isset($inputarray['asset_id'])){											
		$asset_id = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND deviceassets.assets_asset_id = '" . $asset_id . "'";
	}
	
	if(isset($inputarray['device_id'])){											
		$device_id = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND deviceassets.devices_device_id = '" . $device_id . "'";
	}

    if (isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " AND deviceassets_trigger_det.deviceassets_det IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
	}

	$sql .= " ORDER BY trigger_groups.trigger_id";    
	//echo $sql;
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stmt = $pdo->query($sql);
	$assetsrows = $stmt->fetchAll(PDO::FETCH_NUM);
	if (isset($assetsrows[0][0])){							
		$json_assets  = array();
		$outputid = 0;
		foreach($assetsrows as $assetsrow){
			$json_asset = array(
			"trigger_id"=>$assetsrow[0]
			, "trigger_name"=>$assetsrow[1]
			, "trigger_source"=>$assetsrow[2]
			, "geofencing_id"=>$assetsrow[3]
			, "deviceasset_id"=>$assetsrow[4]
			, "device_id"=>$assetsrow[5]
			, "asset_id"=>$assetsrow[6]);
			$json_assets = array_merge($json_assets,array("deviceassettriggerdetail $outputid" => $json_asset));
			$outputid++;
		}
		$json = array("deviceassettriggerdetails" => $json_assets);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		die ("{\"error\":\"NO_DATA\"}");
	}

}

// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************

elseif($sanitisedInput['action'] == "insert"){

    $sanitisedArray = [];

    if(isset($inputarray['asset_id'])){  
        $sanitisedArray['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);    

		
        $sql = "SELECT 
				deviceassets.deviceasset_id
				,devices_device_id
                FROM
                (users
                , user_assets
                , assets
                , deviceassets
                )
                LEFT JOIN userasset_details
                ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
                WHERE
                deviceassets.assets_asset_id = assets.asset_id
                AND deviceassets.date_to IS NULL
                AND deviceassets.assets_asset_id IN (" . implode( ', ',$sanitisedArray['asset_id'] ) . ") 
                AND users.user_id = user_assets.users_user_id
                AND user_assets.users_user_id = $user_id
                AND ((user_assets.asset_summary = 'some'
                AND assets.asset_id = userasset_details.assets_asset_id)
                OR (user_assets.asset_summary = 'all'))";   

        $stmt = $pdo->query($sql); 
        $dbrows = $stmt->fetchAll(PDO::FETCH_NUM);
		//if (!isset($dbrows[0][0])){   
			//errorInvalid("asset_id", $API, $logParent);
		//}   
		
		
		//	Case where nothing returns
		if (!isset(($dbrows[0][0]))) {
			errorInvalid("asset_id", $API, $logParent);
		}
	
		arrayExistCheck($sanitisedArray['asset_id'], $dbrows, "asset_id", $API, $logParent);
		
		foreach ($dbrows as $dbrow){
			$sanitisedArray['deviceasset_id'][] = $dbrow[0];
			$sanitisedArray['device_id'][] = $dbrow[1];
			//$device_id = $dbrow[1];
		}
	}
	else{
		errorMissing("asset_id", $API, $logParent);
	}
	
	//die(print_r($sanitisedArray['deviceasset_id']));


    if(isset($inputarray['trigger_id'])){  
        $sanitisedArray['trigger_id'] = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);                          
    }else{
        errorMissing("trigger_id", $API, $logParent);
    }           

    $sql = "SELECT deviceassets_trigger_det.deviceassets_det
            FROM deviceassets_trigger_det
            WHERE deviceassets_trigger_det.deviceassets_deviceasset_id IN (" . implode( ', ',$sanitisedArray['deviceasset_id'] ) . ") 
            AND  deviceassets_trigger_det.trigger_groups_trigger_id = '" . $sanitisedArray['trigger_id'] . "'";
    //echo $sql;
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_NUM);
    if(isset($rows[0][0])){ 
        errorInvalid("trigger_id", $API, $logParent);
    }    


    //$sql = "SELECT devices_device_id FROM deviceassets WHERE deviceasset_id = '" .  $sanitisedArray['deviceasset_id'] . "' and active_status = 0 and date_to Is Null";
    //$stmt = $pdo->prepare($sql);	
   // $stmt->execute();
   // $dbrows = $stmt->fetchAll(PDO::FETCH_NUM);
   // if(isset($dbrows[0][0])){                
       // $device_id = $dbrows[0][0];
   // }else{
        //errorInvalid("device_id", $API, $logParent);
   // }	

		//Sensor triggers
		$stmt= $pdo->prepare("SELECT trigger_groups.geofencing_geofencing_id
						FROM trigger_groups
						WHERE trigger_groups.trigger_id = '" . $sanitisedArray['trigger_id'] . "'
						and trigger_groups.trigger_source = 'Sensor'");
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		if(isset($rows[0][0])){
			foreach($rows as $row){ //trigger_id
				$trigger_id = $row[0];
				foreach ($sanitisedArray['deviceasset_id'] as $deviceasset_id){	//deviceasset_id
					$sql = "SELECT devices.device_id		
							FROM 
							  devices
							, device_provisioning_components
							, sensors_det
							, trigger_groups
							, deviceassets
							WHERE device_provisioning_components.device_component_type = 'Sensor'
							AND device_provisioning_components.device_provisioning_device_provisioning_id = devices.device_provisioning_device_provisioning_id
							AND sensors_det.sensors_sensor_id = device_provisioning_components.device_component_id
							AND trigger_groups.sensor_def_sd_id = sensors_det.sensor_def_sd_id
							AND deviceassets.devices_device_id = devices.device_id
							AND deviceassets.date_to IS null
							AND trigger_groups.trigger_id = $trigger_id 
							AND deviceassets.deviceasset_id = $deviceasset_id";

					$stmt = $pdo->prepare($sql);
					$stmt->execute();
					$rows = $stmt->fetchAll(PDO::FETCH_NUM);

					if(!isset($rows[0][0])){
						errorInvalid("trigger_id", $API, $logParent);
					}
				}
			}
		}



	
		//Geofence triggers
		$stmt= $pdo->prepare("SELECT trigger_groups.geofencing_geofencing_id
						FROM trigger_groups
						WHERE trigger_groups.trigger_id = '" . $sanitisedArray['trigger_id'] . "'
						and trigger_groups.trigger_source = 'Geofence'");
		$stmt->execute();
		$rows = $stmt->fetchAll(PDO::FETCH_NUM);
		if(isset($rows[0][0])){
			$geofenceAdd[] = $rows[0];
		}else{
			errorInvalid("trigger_id", $API, $logParent);
		}
		
		
		
	}
	
	if (isset($geofenceAdd)){
		$data = [
			'deviceasset_id' =>  $deviceasset_id,
			'geofence_id' =>  $rows[0][0]
			];

		try{
			$sql = "INSERT INTO deviceassets_geo_det (deviceassets_deviceasset_id, geofencing_geofencing_id)
					VALUES (:deviceasset_id, :geofence_id)";
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($data)){              
			}else{
				die("{\"error\":\"Error\"}");
			}
			
			
			
			
			
			
			
			
			
			
	//$sanitisedArray['device_id']
			//$updateArray['device_id'] = $device_id;
			$updateArray['last_modified_by'] = $user_id;
			$updateArray['last_modified_datetime'] = gmdate("Y-m-d H:i:s");

			$sql = "UPDATE devices SET geofences_version = geofences_version + 1
					, last_modified_by = :last_modified_by
					, last_modified_datetime = :last_modified_datetime 
					WHERE device_id IN (" . implode( ', ',$sanitisedArray['device_id'] ) . ") 
					and active_status = 0";						
			$stmt= $pdo->prepare($sql);
			if($stmt->execute($updateArray)){
			}
			else{
				die("{\"error\":\"Error\"}");
			}

		}catch(\PDOException $e){
			die ("{\"error\":\"" . $e . "\"}");
		}
	}
	  

  // error handling require 
    try{       
        $insertArray = [
                'deviceasset_id' => $sanitisedArray['deviceasset_id'],
                    'trigger_id' => $sanitisedArray['trigger_id']
        ];

        $sql = "INSERT INTO deviceassets_trigger_det 
                ( deviceassets_deviceasset_id, 
                  trigger_groups_trigger_id ) 
                VALUES (:deviceasset_id, :trigger_id)";
        $stmt= $pdo->prepare($sql);
        if($stmt->execute($insertArray)){
            $outputArray['deviceassets_det_id'] = $pdo->lastInsertId();
            $outputArray['deviceasset_id'] = $sanitisedArray['deviceasset_id'];
            $outputArray['asset_id'] = $sanitisedArray['asset_id'];
            $outputArray['trigger_id'] = $sanitisedArray['trigger_id'];
            $outputArray['device_id'] = $device_id;
            $outputArray['error' ] = "NO_ERROR";
            echo json_encode($outputArray);
            logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);

            //update the trigger version.
            updatetriggerVersion($sanitisedArray['deviceasset_id'], $device_id, $user_id);
        }              	
    }
    catch(PDOException $e){
        logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
    }
\
}



// *******************************************************************************
// *******************************************************************************
// *****************************DELETE********************************************
// *******************************************************************************
// *******************************************************************************

elseif($sanitisedInput['action'] == "delete"){
    if(isset($inputarray['asset_id'])){  
        $sanitisedArray['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);      
        $sql = "SELECT deviceassets.deviceasset_id, deviceassets_trigger_det.deviceassets_det
                FROM
                    (users
                    , user_assets
                    , assets
                    , deviceassets
                    , deviceassets_trigger_det
                    )
                    
                LEFT JOIN userasset_details
                ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
                WHERE
                deviceassets.assets_asset_id = assets.asset_id 
                AND deviceassets_trigger_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
                AND deviceassets.date_to IS NULL
                AND deviceassets.assets_asset_id = '" . $sanitisedArray['asset_id'] . "'
                AND users.user_id = user_assets.users_user_id
                AND user_assets.users_user_id = $user_id
                AND ((user_assets.asset_summary = 'some'
                AND assets.asset_id = userasset_details.assets_asset_id)
                OR (user_assets.asset_summary = 'all'))";   

        $stmt = $pdo->query($sql); 
        $dbrows = $stmt->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){   
            errorInvalid("asset_id", $API, $logParent);
        }   
        
        $sanitisedArray['deviceasset_id'] = $dbrows[0][0];
        $sanitisedArray['deviceassets_det'] = $dbrows[0][1];
    }
    else{
        errorMissing("asset_id", $API, $logParent);  
    }

    if(isset($inputarray['trigger_id'])){  
        $sanitisedArray['trigger_id'] = sanitise_input($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);   
        $sql = "SELECT deviceassets_trigger_det.deviceassets_deviceasset_id FROM deviceassets_trigger_det
                WHERE deviceassets_trigger_det.deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'
                AND deviceassets_trigger_det.trigger_groups_trigger_id = '" . $sanitisedArray['trigger_id'] . "'";   

        $stmt = $pdo->query($sql); 
        $dbrows = $stmt->fetchAll(PDO::FETCH_NUM);
        if (!isset($dbrows[0][0])){   
            errorInvalid("trigger_id", $API, $logParent);
        }                         
    }else{
        errorMissing("trigger_id", $API, $logParent);
    }   


    // find the device_id
    $sql = "SELECT devices_device_id FROM deviceassets WHERE deviceasset_id = '" .  $sanitisedArray['deviceasset_id'] . "' and active_status = 0 and date_to Is Null";
    $stmt = $pdo->prepare($sql);	
    $stmt->execute();
    $dbrows = $stmt->fetchAll(PDO::FETCH_NUM);
    if(isset($dbrows[0][0])){               
        $device_id = $dbrows[0][0];
    }else{
        errorInvalid("device_id", $API, $logParent);
    }
    
    $sql_delete = "DELETE FROM deviceassets_trigger_det
                    WHERE deviceassets_trigger_det.deviceassets_deviceasset_id = '" . $sanitisedArray['deviceasset_id'] . "'
                    AND deviceassets_trigger_det.trigger_groups_trigger_id = '" . $sanitisedArray['trigger_id'] . "'";

    //echo $sql_delete;
    try{
        $pdo -> beginTransaction();
        $stmt = $pdo->prepare($sql_delete);	              
        if($stmt->execute()){              
            $pdo->commit();   
            $outputArray ['deviceassets_det'] = $sanitisedArray['deviceassets_det'];         
            $outputArray ['trigger_id' ] = $sanitisedArray['trigger_id'];
            $outputArray ['deviceasset_id' ] = $sanitisedArray['deviceasset_id'];
            $outputArray ['asset_id'] = $sanitisedArray['asset_id'];            
            $outputArray ['device_id'] = $device_id;
            $outputArray ['error' ] = "NO_ERROR";
            echo json_encode($outputArray);
            logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);

            // update the trigger_version 
             updatetriggerVersion($sanitisedArray['deviceasset_id'], $device_id, $user_id);
        }

    }catch(\PDOException $e){
        $pdo->rollBack();
        logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
        die("{\"error\":\"$e\"}");
    }		  
}   
else{
    logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
    errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stmt = null;


function updatetriggerVersion($deviceasset_id, $device_id,$user_id){
    global $pdo; 
    //echo "ttrigger" . $device_id;
    $updateArray['device_id'] = $device_id;
    $updateArray['last_modified_by'] = $user_id;
    $updateArray['last_modified_datetime'] = gmdate("Y-m-d H:i:s");

    try{
        $sql = "UPDATE devices SET triggers_version = triggers_version + 1,";
        $sql = $sql . " last_modified_by = :last_modified_by, 
                        last_modified_datetime = :last_modified_datetime 
                        WHERE device_id = :device_id and active_status = 0";						
        $stmt= $pdo->prepare($sql);
        if($stmt->execute($updateArray)){
        }
    }catch(PDOException $e){
        die("{\"error\":\"" . $e . "\"}");
    } 		
}

