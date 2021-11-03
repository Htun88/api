<?php 
    $API = "DeviceAssetsGeofenceDetail";
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
       		deviceassets_geo_det.deviceassets_det
       		, deviceassets_geo_det.deviceassets_deviceasset_id
       		, deviceassets_geo_det.geofencing_geofencing_id
			, deviceassets.assets_asset_id
			, deviceassets.devices_device_id
       		, assets.asset_name
        FROM (
        	users
        	, user_assets
        	, assets
        	, deviceassets
        	, deviceassets_geo_det)
        LEFT JOIN userasset_details
        ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
        WHERE deviceassets_geo_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
		AND users.user_id = user_assets.users_user_id
        AND deviceassets.assets_asset_id = assets.asset_id
        AND user_assets.users_user_id = $user_id
        AND ((user_assets.asset_summary = 'some'
            AND assets.asset_id = userasset_details.assets_asset_id)
        OR (user_assets.asset_summary = 'all'))
		AND deviceassets.active_status = 0
		AND deviceassets.date_to IS NULL"; 

	if (isset($inputarray['deviceassets_det'])){
		$sanitisedInput['deviceassets_det'] = sanitise_input_array($inputarray['deviceassets_det'], "deviceassets_det", null, $API, $logParent);
		$sql .= " AND `deviceassets_geo_det`.`deviceassets_det` IN (" . implode( ', ',$sanitisedInput['deviceassets_det'] ) . ")";
	}

	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `deviceassets`.`devices_device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if (isset($inputarray['asset_id'])){
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql .= " AND `assets`.`asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	}

	if (isset($inputarray['deviceasset_id'])){ 
		$sanitisedInput['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql .= " AND `deviceassets_geo_det`.`deviceassets_deviceasset_id` IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
	}

	if (isset($inputarray['geofence_id'])){
		$sanitisedInput['geofence_id'] = sanitise_input_array($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		$sql .= " AND `deviceassets_geo_det`.`geofencing_geofencing_id` IN (" . implode( ', ',$sanitisedInput['geofence_id'] ) . ")";
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
		$json_deviceasset_geo_det = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$json_child = array(
			"deviceassets_det" => $dbrow[0]
			, "deviceasset_id" => $dbrow[1]
			, "geofence_id" => $dbrow[2]
			,	"asset_id" => $dbrow[3]
			,	"device_id" => $dbrow[4]
			, "asset_name" => $dbrow[5]);
			$json_deviceasset_geo_det = array_merge($json_deviceasset_geo_det, array("response_$outputid" => $json_child));
			$outputid++;
		}

		$json = array("responses" => $json_deviceasset_geo_det);
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

	if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {
		if ((isset($inputarray['deviceasset_id'])
			&& ( isset($inputarray['device_id'])
			|| isset($inputarray['asset_id']))
			) || (isset($inputarray['device_id'])
			&& isset($inputarray['asset_id'])) 
			) {
			errorInvalid("incompatable_identifier", $API, $logParent);
		}
			
		$sql = "SELECT
				deviceassets.deviceasset_id
				, deviceassets.devices_device_id
				, deviceassets.assets_asset_id
			FROM (
				users
				, user_assets
				, assets
				, deviceassets
				)
			LEFT JOIN userasset_details
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'all'))
			AND deviceassets.active_status = 0
			AND deviceassets.date_to IS NULL";

		if (isset($inputarray['deviceasset_id'])) {
			$sanitisedInput['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
			$sql .= " AND deviceassets.deviceasset_id IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
			$countPointer = "deviceasset_id";
			$countNumb = 0;
		}

		if (isset($inputarray['device_id'])) {
			$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
			$sql .= " AND deviceassets.devices_device_id IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
			$countPointer = "device_id";
			$countNumb = 1;
		}

		if (isset($inputarray['asset_id'])) {
			$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
			$sql .= " AND deviceassets.assets_asset_id IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
			$countPointer = "asset_id";
			$countNumb = 2;
		}

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($dbrows[0][0])){
			errorInvalid("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
		}
		if (count($dbrows) != count($inputarray[$countPointer])) {
			arrayExistCheck ($sanitisedInput[$countPointer], array_column($dbrows, $countNumb), $countPointer, $API, $logParent);
		}
		
		$sanitisedInput['deviceasset_id'] = array_column($dbrows, 0);	
	}
	else {
		errorMissing("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
	}

	if (isset($inputarray['geofence_id'])){
		$sanitisedInput['geofence_id'] = sanitise_input_array($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		$sql = "SELECT
				geofencing_id
			FROM
				geofencing
			WHERE geofencing_id IN (" . implode( ', ',$sanitisedInput['geofence_id'] ) . ")";
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("geofence_id", $API, $logParent);
		}
		arrayExistCheck ($sanitisedInput['geofence_id'], array_column($dbrows, 0), "geofence_id", $API, $logParent);
	}
	else {
		errorMissing("geofence_id", $API, $logParent);
	}
				
	foreach ($sanitisedInput['deviceasset_id'] as $value) {
		$sql = "SELECT
				deviceassets_geo_det.geofencing_geofencing_id
			FROM 
				deviceassets_geo_det
			WHERE deviceassets_deviceasset_id = " . $value . "
			AND geofencing_geofencing_id IN (" . implode( ', ',$sanitisedInput['geofence_id'] ) . ")";
			
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
			//	If duplicates already exist then we remove them from the array
			$insertArray['deviceasset_id'][$value]['geofence_id'] = array_values(array_diff($sanitisedInput['geofence_id'], array_column($dbrows, 0)));
			//	Push any duplicates to a new array so we can log it correctly
			$insertArray['deviceasset_id'][$value]['pre-existing_geofences'] = array_column($dbrows, 0);
		}
		else {
			$insertArray['deviceasset_id'][$value]['geofence_id'] = $sanitisedInput['geofence_id'];
		}
		if (count($sanitisedInput['geofence_id']) == 0 ) {
			//errorInvalid("geofence_id", $API, $logParent);
			$insertArray['deviceasset_id'][$value]['geofence_id'] = "Nothing inserted";
		}

		else {

			foreach ($insertArray['deviceasset_id'][$value]['geofence_id'] as $value2) {
				$data[] = "( " . $value . ", " . $value2  . ")";
			}
		}
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO deviceassets_geo_det (
			`deviceassets_deviceasset_id`
			, `geofencing_geofencing_id`) 
			VALUES " . implode(', ', $data); 

		$stm= $pdo->prepare($sql);
		if($stm->execute()){
			//$insertArray['deviceassets_det'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}
	}

	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"" . $e . "\"}");
	}
	try{
		$sql = "UPDATE
				 deviceassets
				, devices
			SET devices.geofences_version = (devices.geofences_version + 1)
			WHERE 
			deviceassets.date_to IS NULL
			AND devices.device_id = deviceassets.devices_device_id
			AND deviceassets.deviceasset_id IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
		
		$stm= $pdo->prepare($sql);
		if($stm->execute()){
			logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
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

	if (((!isset($inputarray['deviceasset_id'])
		&& !isset($inputarray['device_id'])
		&& !isset($inputarray['asset_id'])
		) || !isset($inputarray['geofence_id'])
		) && !isset($inputarray['deviceassets_det'])
		) {
		errorMissing("update_identification", $API, $logParent);
	}

	//	TODO: Need to throw in something to check if you are updating in such a way to duplicate details

	if (isset($inputarray['deviceassets_det'])) {
		$sanitisedInput['deviceassets_det'] = sanitise_input($inputarray['deviceassets_det'], "deviceassets_det", null, $API, $logParent);
		$sql = "SELECT
				deviceassets_geo_det.deviceassets_det
				, deviceassets_geo_det.deviceassets_deviceasset_id
				, deviceassets_geo_det.geofencing_geofencing_id
			FROM (
				users
				, user_assets
				, assets
				, deviceassets
				, deviceassets_geo_det)
			LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE deviceassets_geo_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
			AND users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
				AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND deviceassets_geo_det.deviceassets_det = " . $sanitisedInput['deviceassets_det'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("deviceassets_det", $API, $logParent);
		}
		$updateArray['deviceassets_det'] = $sanitisedInput['deviceassets_det'];	
		//	Throw the deviceasset ID and geofence ID into an array to check for duplicates. These will get overwritten
		$sanitisedInput['deviceasset_id'] = $dbrows[0][1];
		$sanitisedInput['geofence_id'] = $dbrows[0][2];
	}
/*
	if (isset($inputarray['deviceasset_id'])) {
		$sanitisedInput['deviceasset_id'] = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
		$sql = "SELECT
				deviceassets.deviceasset_id
			FROM (
				users
				, user_assets
				, assets
				, deviceassets
				)
			LEFT JOIN userasset_details
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND deviceassets.deviceasset_id = " . $sanitisedInput['deviceasset_id'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("deviceasset_id", $API, $logParent);
		}
		$updateArray['deviceasset_id'] = $sanitisedInput['deviceasset_id'];	
		$updateString .= " `deviceassets_deviceasset_id` = :deviceasset_id,";
	}
	*/

	if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {
		$sql = "SELECT
				deviceassets.deviceasset_id
			FROM (
				users
				, user_assets
				, assets
				, deviceassets
				)
			LEFT JOIN userasset_details
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'all'))
			AND deviceassets.active_status = 0
			AND deviceassets.date_to IS NULL";

		if (isset($inputarray['deviceasset_id'])) {
			$sanitisedInput['deviceasset_id'] = sanitise_input($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
			$sql .= " AND deviceassets.deviceasset_id = " . $sanitisedInput['deviceasset_id']; 
		}

		if (isset($inputarray['device_id'])) {
			$sanitisedInput['device_id'] = sanitise_input($inputarray['device_id'], "device_id", null, $API, $logParent);
			$sql .= " AND deviceassets.devices_device_id = " . $sanitisedInput['device_id']; 
		}

		if (isset($inputarray['asset_id'])) {
			$sanitisedInput['asset_id'] = sanitise_input($inputarray['asset_id'], "asset_id", null, $API, $logParent);
			$sql .= " AND deviceassets.assets_asset_id = " . $sanitisedInput['asset_id']; 
		}

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
		}
		if (isset($compare['deviceasset_id'])) {
			if ($dbrows[0][0] != $compare['deviceasset_id']) {
				errorInvalid("deviceasset_id_or_device_id_or_asset_id", $API, $logParent);
			}
		}
		$updateArray['deviceasset_id'] = $dbrows[0][0];	
		$updateString .= " AND deviceassets_deviceasset_id = :deviceasset_id,";
	}

	if (isset($inputarray['geofence_id'])) {
		$sanitisedInput['geofence_id'] = sanitise_input($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		$sql = "SELECT
				geofencing_id
			FROM
				geofencing
			WHERE geofencing_id = " . $sanitisedInput['geofence_id'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("geofence_id", $API, $logParent);
		}
		$updateArray['geofence_id'] = $sanitisedInput['geofence_id'];	
		$updateString .= " `geofencing_geofencing_id` = :geofence_id,";
	}

	//	Do an sql query check here to see if we're doing an update resulting in a duplicate entry, failing if so
	$sql = "SELECT
			deviceassets_geo_det.deviceassets_det
		FROM (
			users
			, user_assets
			, assets
			, deviceassets
			, geofencing
			)
		LEFT JOIN userasset_details
			ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
		LEFT JOIN deviceassets_geo_det 
			ON deviceassets_geo_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
			AND deviceassets_geo_det.geofencing_geofencing_id = geofencing.geofencing_id
		WHERE users.user_id = user_assets.users_user_id
		AND deviceassets.assets_asset_id = assets.asset_id
		AND user_assets.users_user_id = $user_id
		AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
		AND deviceassets.deviceasset_id = '" . $sanitisedInput['deviceasset_id'] . "'
		AND geofencing.geofencing_id = " . $sanitisedInput['geofence_id'];

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (isset($dbrows[0][0])){
		//	If isset then a duplicate already exists
			errorInvalid("duplicate_exists", $API, $logParent);
		}


	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($updateArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try { 
        if (count($updateArray) < 2) {
			logEvent($API . logText::missingUpdate . str_replace('"', '\"', json_encode($updateArray)), logLevel::invalid, logType::error, $token, $logParent);
			die("{\"error\":\"NO_UPDATED_PRAM\"}");
		}
		else {
			$updateString = substr(substr($updateString, 4), 0, -1);
			$sql = "UPDATE 
				deviceassets_geo_det 
				SET ". $updateString . "
                WHERE `deviceassets_det` = :deviceassets_det";

			$stm= $pdo->prepare($sql);
            if($stm->execute($updateArray)){
                $updateArray ['error' ] = "NO_ERROR";
                echo json_encode($updateArray);
				logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
            }
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}
	//	Update config version
	try{

		$configArray['deviceassets_det'] = $updateArray['deviceassets_det'];

		$sql = "UPDATE
				deviceassets_geo_det
				, deviceassets
				, devices
			SET devices.geofences_version = (devices.geofences_version + 1)
			WHERE deviceassets.deviceasset_id = deviceassets_geo_det.deviceassets_deviceasset_id
			AND deviceassets.date_to IS NULL
			AND devices.device_id = deviceassets.devices_device_id
			AND deviceassets_geo_det.deviceassets_det = :deviceassets_det";
	
		$stm= $pdo->prepare($sql);
		if($stm->execute($configArray)){
			logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}
}

// *******************************************************************************
// *******************************************************************************
// *******************************DELETE******************************************
// *******************************************************************************
// ******************************************************************************* 

else if($sanitisedInput['action'] == "delete"){

	$deleteString = "";

	//	Two options. 
	//	Need deviceassets_det
	//		or
	//	Need at least one of deviceasset_id, device_id or asset_id AND geofence_id
	if (isset($inputarray['deviceassets_det'])) {
		if (isset($inputarray['deviceasset_id'])
			|| isset($inputarray['device_id'])
			|| isset($inputarray['asset_id'])
			|| isset($inputarray['geofence_id'])
			){
			errorInvalid("incompatable_identifier", $API, $logParent);
		}
	}
	else {
		if (!isset($inputarray['deviceasset_id'])
			&& !isset($inputarray['device_id'])
			&& !isset($inputarray['asset_id'])
			) {
			errorMissing("delete_identification", $API, $logParent);
		}
		if (!isset($inputarray['geofence_id'])) {
			errorMissing("geofence_id", $API, $logParent);
		}
	}

	if (isset($inputarray['deviceassets_det'])) {
		$sanitisedInput['deviceassets_det'] = sanitise_input_array($inputarray['deviceassets_det'], "deviceassets_det", null, $API, $logParent);
		$sql = "SELECT
				deviceassets_deviceasset_id
				, deviceassets_det
			FROM
				deviceassets_geo_det
			WHERE deviceassets_det IN (" . implode( ', ',$sanitisedInput['deviceassets_det'] ) . ")";
		
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		
		if (!isset($dbrows[0][0])){
			errorInvalid("deviceassets_det", $API, $logParent);
		}
		arrayExistCheck ($sanitisedInput['deviceassets_det'], array_column($dbrows, 1), "deviceassets_det", $API, $logParent);
		$sanitisedInput['deviceasset_id'] = array_column($dbrows, 0);
			
		$sql = "SELECT
				deviceassets.deviceasset_id
			FROM (
				users
				, user_assets
				, assets
				, deviceassets
				)
			LEFT JOIN userasset_details
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
			OR (user_assets.asset_summary = 'all'))
			AND deviceassets.active_status = 0
			AND deviceassets.date_to IS NULL
			AND deviceassets.deviceasset_id IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("deviceasset_id", $API, $logParent);
		}

		arrayExistCheck ($sanitisedInput['deviceasset_id'], array_column($dbrows, 0), "deviceassets_det", $API, $logParent);

		$deleteArray ['deviceassets_det' ] = $sanitisedInput['deviceassets_det'];
		foreach ($sanitisedInput['deviceassets_det'] as $value) {
			$data[] = "( deviceassets_det = " . $value . ")";
		}
		
	}

	if (isset($inputarray['deviceasset_id'])
		|| isset($inputarray['device_id'])
		|| isset($inputarray['asset_id'])
		) {
		if ((isset($inputarray['deviceasset_id'])
			&& ( isset($inputarray['device_id'])
			|| isset($inputarray['asset_id']))
			) || (isset($inputarray['device_id'])
			&& isset($inputarray['asset_id'])) 
			) {
			errorInvalid("conflict", $API, $logParent);
		}
			
		$sql = "SELECT
				deviceassets.deviceasset_id
				, deviceassets.devices_device_id
				, deviceassets.assets_asset_id
			FROM (
				users
				, user_assets
				, assets
				, deviceassets
				)
			LEFT JOIN userasset_details
				ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
			WHERE users.user_id = user_assets.users_user_id
			AND deviceassets.assets_asset_id = assets.asset_id
			AND user_assets.users_user_id = $user_id
			AND ((user_assets.asset_summary = 'some'
			AND assets.asset_id = userasset_details.assets_asset_id)
				OR (user_assets.asset_summary = 'all'))
			AND deviceassets.active_status = 0
			AND deviceassets.date_to IS NULL";

		if (isset($inputarray['deviceasset_id'])) {
			$sanitisedInput['deviceasset_id'] = sanitise_input_array($inputarray['deviceasset_id'], "deviceasset_id", null, $API, $logParent);
			$sql .= " AND deviceassets.deviceasset_id IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
			$countPointer = "deviceasset_id";
			$countNumb = 0;
		}

		if (isset($inputarray['device_id'])) {
			$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
			$sql .= " AND deviceassets.devices_device_id IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
			$countPointer = "device_id";
			$countNumb = 1;
		}

		if (isset($inputarray['asset_id'])) {
			$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
			$sql .= " AND deviceassets.assets_asset_id IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
			$countPointer = "asset_id";
			$countNumb = 2;
		}

		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($dbrows[0][0])){
			errorInvalid($countPointer, $API, $logParent);
		}
		if (count($dbrows) != count($inputarray[$countPointer])) {
			arrayExistCheck ($sanitisedInput[$countPointer], array_column($dbrows, $countNumb), $countPointer, $API, $logParent);
		}
		$deleteArray[$countPointer] = $sanitisedInput[$countPointer];
		$deleteArray['deviceasset_id'] = array_column($dbrows, 0);	
	}

	if (isset($inputarray['geofence_id'])){
		$sanitisedInput['geofence_id'] = sanitise_input($inputarray['geofence_id'], "geofence_id", null, $API, $logParent);
		$sql = "SELECT
				geofencing_id
			FROM
				geofencing
			WHERE geofencing_id = " . $sanitisedInput['geofence_id'];
		$stm = $pdo->query($sql);
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorInvalid("geofence_id", $API, $logParent);
		}
		$deleteArray['geofence_id'] = $sanitisedInput['geofence_id'];
	}

	//	If we are not deleting by deviceassets det then we need to do a select search on the deviceassets ID and geofence ID to make sure values exist where we are trying to delete
	if (!isset($inputarray['deviceassets_det'])) {
		foreach ($deleteArray['deviceasset_id'] as $value) {
			$data[] = "( deviceassets_deviceasset_id = " . $value . " AND geofencing_geofencing_id = " . $deleteArray['geofence_id'] . ")";
		}
		$sql = "SELECT 
				deviceassets_det
			FROM
				deviceassets_geo_det
				WHERE " . implode('OR ', $data); 
		$stm= $pdo->prepare($sql);
		$stm->execute();
		$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($dbrows[0][0])){
			errorGeneric("incompatable_parameters", $API, $logParent);
		}
		if (count($dbrows) != count($deleteArray['deviceasset_id'])) {
			arrayExistCheck ($deleteArray['deviceasset_id'], array_column($dbrows, 0), "hello", $API, $logParent);
		}

		$sanitisedInput['deviceassets_det']	= array_column($dbrows, 0);
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($deleteArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	
	try{
		$sql = "UPDATE
				deviceassets_geo_det
				, deviceassets
				, devices
			SET devices.geofences_version = (devices.geofences_version + 1)
			WHERE deviceassets.deviceasset_id = deviceassets_geo_det.deviceassets_deviceasset_id
			AND deviceassets.date_to IS NULL
			AND devices.device_id = deviceassets.devices_device_id
			AND deviceassets_geo_det.deviceassets_det IN (" . implode( ', ',$sanitisedInput['deviceassets_det'] ) . ")";  

		$stm= $pdo->prepare($sql);
		if($stm->execute()){
			try{
				$sql = "DELETE FROM
						deviceassets_geo_det
						WHERE " . implode('OR ', $data); 

				$stm= $pdo->prepare($sql);
				if($stm->execute()){
					$deleteArray ['error' ] = "NO_ERROR";
					echo json_encode($deleteArray);
					logEvent($API . logText::response . str_replace('"', '\"', json_encode($deleteArray)), logLevel::response, logType::response, $token, $logParent);
				}
			}
			catch(PDOException $e){
				logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
				die("{\"error\":\"$e\"}");
			}
			logEvent($API . logText::response . str_replace('"', '\"', "{\"configUpdate\":\"Success\"}"), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"ERROR - configUpdate\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}

}

else{
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stm = null;

?>