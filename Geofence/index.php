<?php 
    $API = "Geofence";
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

	$schemainfoArray = getMaxString ("geofencing", $pdo);

	//	If searching by either asset_id, device_id or deviceasset_id the $sql query changes. If any of these are present, then the new sql is presented below
	if(isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		|| isset($inputarray['deviceasset_id'])
		){
		$sql = "SELECT 
			`geofencing`.`geofencing_id`
			, `geofencing`.`name`
			, `geofencing`.`points`
			, `geofencing`.`radius`
			, `geofencing`.`safe_zone`
			, `geofencing`.`color`
			, `geofencing`.`active_status`
			, `geofencing`.`last_modified_by`
			, `geofencing`.`last_modified_datetime`
			FROM
			(users
			, user_assets
			, assets
			, devices
			, deviceassets
			, deviceassets_geo_det
			, geofencing )
			LEFT JOIN userasset_details ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE devices.device_id = deviceassets.devices_device_id
			AND deviceassets.date_to IS NULL
			AND deviceassets_geo_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
			AND geofencing.geofencing_id = deviceassets_geo_det.geofencing_geofencing_id
			AND assets.asset_id = deviceassets.assets_asset_id
			AND assets.active_status = 0 
			AND devices.active_status = 0
			AND deviceassets.active_status = 0  
			AND users.user_id = user_assets.users_user_id 
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some' 
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))";
	}
	else {
		$sql = "SELECT 
		`geofencing_id`
		, `name`
		, `points`
		, `radius`
		, `safe_zone`
		, `color`
		, `active_status`
		, `last_modified_by`
		, `last_modified_datetime`
		FROM (geofencing
		)
		WHERE 1 = 1";
	}

	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `devices`.`device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `assets`.`asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['deviceasset_id'])){
		$sanitisedInput['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`deviceasset_id` IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
	}

	if (isset($inputarray['geofence_id'])){
		$sanitisedInput['geofence_id'] = sanitise_input_array($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		$sql .= " AND `geofencing`.`geofencing_id` IN (" . implode( ', ',$sanitisedInput['geofence_id'] ) . ")";
	}

	if (isset($inputarray['name'])) {
		$sanitisedInput['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
		$sql .= " AND `name` = '". $sanitisedInput['name'] ."'";
	}

	if (isset($inputarray['active_status'])) {
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `geofencing`.`active_status` = '". $sanitisedInput['active_status'] ."'";
	}

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$json_geofences  = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"geofence_id" => $dbrow[0]
			, "name" => $dbrow[1]
			, "points" => $dbrow[2]
			, "radius" => $dbrow[3]
			, "safe_zone" => $dbrow[4]
			, "color" => $dbrow[5]
			, "active_status" => $dbrow[6]
			, "last_modified_by" => $dbrow[7]
			, "last_modified_datetime" => $dbrow[8]);
			$json_geofences = array_merge($json_geofences, array("response_$outputid" => $json_child));
			$outputid++;
		}
	$json = array("responses" => $json_geofences);
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
// *******************************INSERT******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "insert"){

	$insertArray = [];
	$schemainfoArray = getMaxString ("geofencing", $pdo);
	
	if (isset($inputarray['name'])){
		$insertArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
	}
	else {
		errorMissing("name", $API, $logParent);
	}

	if (isset($inputarray['points'])){
		$insertArray['points'] = sanitise_input($inputarray['points'], "points", null, $API, $logParent);
	}
	else {
		errorMissing("points", $API, $logParent);
	}
	
	if (isset($inputarray['radius'])){
		$inputarray['radius'] = sanitise_input($inputarray['radius'], "radius", null, $API, $logParent);
        //	If there are more than one geofence points, radius must be null
		if(strpos($inputarray['points'], '|') !== false) {
			if ($inputarray['radius'] != null
				&& $inputarray['radius'] != 0) 
				{
				errorInvalid("incompatable_points_and_radius", $API, $logParent);
			}
		}
		//	If there is only one geofence point, radius must not be null 
		else {
			if ($inputarray['radius'] == 0) {
				errorInvalid("incompatable_points_and_radius", $API, $logParent);
			}
		}
        if ($inputarray['radius']!= 0){
            $insertArray['radius'] = $inputarray['radius'];
        }
	}
	else {
		//	If the user has input more than one geofence input, user does not require a radius, and it defaults to null
		if(strpos($inputarray['points'], '|') !== false) {
			//	Intentionally left blank to trip the else condition
		}
		else {
			//	If the user has input a single geofence, the radius is now a mandatory field.
			errorMissing("radius", $API, $logParent);
		}
	}

	if (isset($inputarray['safe_zone'])){
		$insertArray['safe_zone'] = sanitise_input($inputarray['safe_zone'], "safe_zone", null, $API, $logParent);
	}
	else {
		errorMissing("safe_zone", $API, $logParent);
	}

	if (isset($inputarray['color'])){
		$insertArray['color'] = sanitise_input($inputarray['color'], "color", null, $API, $logParent);
	}
	else {
		errorMissing("color", $API, $logParent);
	}

	if (isset($inputarray['active_status'])){
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		errorMissing("active_status", $API, $logParent);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];


	try{
		$sql = "INSERT INTO geofencing (
			`name`
			, `points`
			, `radius`
			, `safe_zone`
			, `color`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`) 
		VALUES (
			:name
			, :points";	
		//	This is to avoid issues of trying to input NULL as a value. 
		if (isset($insertArray['radius'])){
			$sql .= ", :radius";
		}
		else {
			$sql .= ", NULL";
		}
		$sql .= "
			, :safe_zone
			, :color
			, :active_status
			, $user_id
			, '$timestamp')";

		$stm= $pdo->prepare($sql);
		if($stm->execute($insertArray)){
			$insertArray['geofence_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"" . $e . "\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *******************************UPDATE******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "update"){
	
	$updateArray = [];
	$updateString = "";

	$schemainfoArray = getMaxString ("geofencing", $pdo);

	if (isset($inputarray['geofence_id'])){
		$sanitisedInput['geofence_id'] = sanitise_input($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		
		/*
		//	TODO
		//	Check if user_id is allowed to modify geofence
		//	Speak to Tim about this one. 
		*/

		//	This checks to see if any alarms have been triggered for this geofence. If so, cannot modify.

		$sql = "SELECT 
				geofencing.geofencing_id
				, alarm_events.alarm_events_id
			FROM 
				geofencing
			LEFT JOIN trigger_groups ON trigger_groups.geofencing_geofencing_id = geofencing.geofencing_id
			LEFT JOIN alarm_events ON alarm_events.trigger_groups_trigger_id = trigger_groups.trigger_id
			WHERE geofencing.geofencing_id = '" . $sanitisedInput['geofence_id'] . "'
			ORDER BY alarm_events.alarm_events_id DESC LIMIT 1";
		
		$stm = $pdo->query($sql);
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		//	Case if geofence ID is not a valid geofence
		if (!isset(($dbrows[0][0]))) {
			errorInvalid("geofence_id", $API, $logParent);
		}
		//	Case where alarms have been triggered for this geofence
        if (isset($dbrows[0][1])){
			$locked = 1;
			//logEvent($API . logText::invalidValue . str_replace('"', '\"', "{\"error\":\"GEOFENCE_LOCKED\"}"), logLevel::invalid, logType::error, $token, $logParent);
            //die("{\"error\":\"GEOFENCE_LOCKED\"}");
			
        }
		else {
			$locked = 0;
		}
		$updateArray['geofence_id'] = $sanitisedInput['geofence_id'];
	} 
	else {
		errorMissing("geofence_id", $API, $logParent);
	}

	if (isset($inputarray['name'])) {
		$updateArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
		$updateString .= " `name` = :name,";
	}

	//	Complicated
	//	Any confusion ask Tim
	//	The idea is that there are four possible states. Single point or multi point, and null radius or int radius.
	//	Single point mandates an int radius, while multi point mandates a null radius. 
	//	Boils down to checking the user is setting these correctly if updating both points and radius, or that they are not attempting to update incorrectly, 
	//		ie. update a radius to valid from null when the points server side are multi points
	//	Goes over both the $inputarray points AND radius fields.
	if (isset($inputarray['points'])) {
		$sanitisedInput['points'] = sanitise_input($inputarray['points'], "points", null, $API, $logParent);
		//	Case where there have NOT been any alarms triggered for this geofence
		if (isset($inputarray['radius'])) {
			if(strpos($sanitisedInput['points'], '|') !== false) {
				if ($inputarray['radius'] != 0) { 
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
			}
			else {
				if ($inputarray['radius'] == 0) { 
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
			}
			$updateArray['points'] = $sanitisedInput['points'];
			$updateString .= " `points` = :points,";
		}
		//	Check if single input
		else {
			$stm = $pdo->query("SELECT 										
				radius 
				FROM 
				geofencing 
				WHERE geofencing_id = '" . $sanitisedInput['geofence_id'] . "'
				AND radius IS NOT NULL");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			//	Case where there IS a radius value on the server
			if (isset($dbrows[0][0])){
				if(strpos($sanitisedInput['points'], '|') !== false) {
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
			}
			//	Case there is no radius set in this update, or radius != nullRadius
			else{
				if(!strpos($sanitisedInput['points'], '|') !== false) {
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
			}
			$updateArray['points'] = $sanitisedInput['points'];
			$updateString .= " `points` = :points,";
		}
	}

	if (isset($inputarray['radius'])){
		$sanitisedInput['radius'] = sanitise_input($inputarray['radius'], "radius", null, $API, $logParent);
		if (isset($inputarray['points'])) {
			if(strpos($inputarray['points'], '|') !== false) {
				if ($sanitisedInput['radius'] != 0) {
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
				else if ($sanitisedInput['radius'] == 0) {
					$updateString .= " `radius` = NULL, ";
				}
			}
			//	If there is only one geofence input, radius must exist and not be null
			else { 
				if ($sanitisedInput['radius'] == 0){
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
				else {
					$updateArray['radius'] = $sanitisedInput['radius'];
					$updateString .= " `radius` = :radius,";
				}
			}
		}
		else {
			//	If the user is not updating their points as well, need to check how many points are registered with this geofence ID
			//	Returns error if the number of points registered != 1, as you cannot set a radius to a fence with more than one point.
			$stm = $pdo->query("SELECT 
				(LENGTH(points) - LENGTH( REPLACE ( points, '|', '') ))  + 1   
				AS num_points
				FROM geofencing
				WHERE geofencing_id = '" . $sanitisedInput['geofence_id'] . "'
				HAVING num_points != 1");
			$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
			if (isset($dbrows[0][0])){
				errorInvalid("incompatable_points_and_radius", $API, $logParent);
			}
			else {
				if ($sanitisedInput['radius'] == 0) {
					errorInvalid("incompatable_points_and_radius", $API, $logParent);
				}
				$updateArray['radius'] = $sanitisedInput['radius'];
				$updateString .= " `radius` = :radius,";
			}
		}
	}

	if (isset($inputarray['safe_zone'])) {
		$updateArray['safe_zone'] = sanitise_input($inputarray['safe_zone'], "safe_zone", null, $API, $logParent);
		$updateString .= " `safe_zone` = :safe_zone,";
	}

	if (isset($inputarray['color'])) {
		$updateArray['color'] = sanitise_input($inputarray['color'], "color", null, $API, $logParent);
		$updateString .= " `color` = :color,";
	}

	if (isset($inputarray['active_status'])) {
		$updateArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$updateString .= " `active_status` = :active_status,";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];



    try { 
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		if ($locked == 1){
			foreach ($updateArray as $key => $val){
				//echo $key;
				if ($key != "geofence_id" && $key != "name" && $key != "active_status"){
					logEvent($API . logText::invalidValue . str_replace('"', '\"', "{\"error\":\"TRIGGER_LOCKED\"}"), logLevel::invalid, logType::error, $token, $logParent);
					die("{\"error\":\"TRIGGER_LOCKED\"}");
				}
			}
		}
		
		
		$sql = "UPDATE 
			geofencing 
			SET ". $updateString . " `last_modified_by` = $user_id
			, `last_modified_datetime` = '$timestamp'
			WHERE `geofencing_id` = :geofence_id";

		$stm= $pdo->prepare($sql);
		if($stm->execute($updateArray)){
			$updateArray ['error' ] = "NO_ERROR";
			echo json_encode($updateArray);
			$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
		}
		
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
	//	Update config version
	try{
		$configArray['geofence_id'] = $updateArray['geofence_id'];

		$sql = "UPDATE
				geofencing
				, deviceassets_geo_det
				, deviceassets
				, devices
			SET devices.geofences_version = (devices.geofences_version + 1)
			WHERE deviceassets_geo_det.geofencing_geofencing_id  = geofencing.geofencing_id
			AND deviceassets.deviceasset_id = deviceassets_geo_det.deviceassets_deviceasset_id
			AND deviceassets.date_to IS null
			AND devices.device_id = deviceassets.devices_device_id
			AND geofencing.geofencing_id = :geofence_id";

		$stm= $pdo->prepare($sql);
		if($stm->execute($configArray)){
			logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
}

else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stm = null;

?>