<?php
	$API = "SensorDefinition";
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

	$schemainfoArray = getMaxString ("sensor_def", $pdo);

	if (isset($inputarray['asset_id'])) {
		$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		$sql = "SELECT `devices_device_id` FROM deviceassets 
		        WHERE deviceassets.date_to IS NULL
				    AND deviceassets.active_status = 0
				    AND `deviceassets`.`assets_asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
		try {
			$stm = $pdo->query($sql);
			$results = $stm->fetchAll(PDO::FETCH_NUM);
		} catch(PDOException $e){
			die("{\"error\":\"" . $e->getMessage() . "\"}");
		}
		if (count($results) > 0) {
			foreach($results as $id) {
				$device_id[] = $id[0];
			}
			$inputarray['device_id'] = $device_id;
		} else {
			die("{\"error\":\"NO_DATA\"}");
		}
	}

	$sql = "SELECT distinct
			sd_id
			, sd_name
			, colorcode
			, iconpath
			, chartlabel
			, sd_deactivated_data
			, sd_overload_data
			, sd_data_min
			, sd_data_max
			, sd_graph_min
			, sd_graph_max
			, sd_uom_id
			, chart
			, bytelength
			, sensor_def.active_status
			, sensor_def.last_modified_by
			, sensor_def.last_modified_datetime
		FROM (
			sensor_def)";


	//if (isset($inputarray['asset_id'])){
		//$sanitisedInput['asset_id'] = sanitise_input_array($inputarray['asset_id'], "asset_id", null, $API, $logParent);
		//$sql .= " 
			//inner join deviceassets ON
				//deviceassets.date_to IS NULL
				//AND deviceassets.active_status = 0
		 		//AND `deviceassets`.`assets_asset_id` IN (" . implode( ', ',$sanitisedInput['asset_id'] ) . ")";
	//}
	
	if (isset($inputarray['device_id']) || isset($inputarray['device_sn'])){
			$sql .= "inner JOIN sensors_det ON sensor_def.sd_id = sensors_det.sensor_def_sd_id
			inner JOIN device_provisioning_components ON device_provisioning_components.device_component_id = sensors_det.sensors_sensor_id AND device_provisioning_components.device_component_type = 'Sensor'
			inner JOIN devices ON (device_provisioning_components.device_provisioning_device_provisioning_id = devices.device_provisioning_device_provisioning_id AND devices.active_status = 0) ";
	}
	
	if (isset($inputarray['device_id'])){
		$sanitisedInput['device_id'] = sanitise_input_array($inputarray['device_id'], "device_id", null, $API, $logParent);
		$sql .= " AND `devices`.`device_id` IN (" . implode( ', ',$sanitisedInput['device_id'] ) . ")";
	}

	if (isset($inputarray['device_sn'])){
		$schemainfoArray = getMaxString ("devices", $pdo);
		$sanitisedInput['device_sn'] = sanitise_input($inputarray['device_sn'], "device_sn", $schemainfoArray['device_sn'], $API, $logParent);
		$sql .= " AND `devices`.`device_sn` = '". $sanitisedInput['device_sn'] ."'";
	}
	
	if (isset($inputarray['sd_id'])){
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql .= " WHERE `sensor_def`.`sd_id` IN (" . implode( ', ',$sanitisedInput['sd_id'] ) . ")";
	}
	
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	$sql .= " ORDER BY sd_id ASC";

	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}
	
	//echo $sql;
	$stm = $pdo->query($sql);
	$dbrows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($dbrows[0][0])){
		$jsonParent = array ();
		$outputid = 0;
		foreach($dbrows as $dbrow){
			$jsonChild = array(
			"sd_id" => $dbrow[0]
			, "sd_name" => $dbrow[1]
			, "html_colorcode" => $dbrow[2]
			, "iconpath" => $dbrow[3]
			, "chartlabel" => $dbrow[4]
			, "sd_deactivated_data" => $dbrow[5]
			, "sd_overload_data" => $dbrow[6]
			, "sd_data_min" => $dbrow[7]
			, "sd_data_max" => $dbrow[8]
			, "sd_graph_min" => $dbrow[9]
			, "sd_graph_max" => $dbrow[10]
			, "sd_uom_id" => $dbrow[11]
			, "chart" => $dbrow[12]
			, "bytelength" => $dbrow[13]
			, "active_status" => $dbrow[14]
			, "last_modified_by" => $dbrow[15]
			, "last_modified_datetime" => $dbrow[16]);
			$jsonParent = array_merge($jsonParent,array("response_$outputid" => $jsonChild));
			$outputid++;
		}
		$json = array("responses" => $jsonParent);
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
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************

else if($sanitisedInput['action'] == "insert"){

	$insertArray = [];
	$schemainfoArray = getMaxString ("sensor_def", $pdo);
	
	if (isset($inputarray['name'])) {
		$insertArray['name'] = sanitise_input($inputarray['name'], "sd_name", $schemainfoArray['sd_name'], $API, $logParent);
	}
	else {
		errorMissing("name", $API, $logParent);
	}

	if (isset($inputarray['html_colorcode'])) {
		$insertArray['html_colorcode'] = sanitise_input($inputarray['html_colorcode'], "html_colorcode", $schemainfoArray['colorcode'], $API, $logParent);
	}
	else {
		$insertArray['html_colorcode'] = "#000000";
	}

	if (isset($inputarray['iconpath'])) {
		$insertArray['iconpath'] = sanitise_input($inputarray['iconpath'], "iconpath", $schemainfoArray['iconpath'], $API, $logParent);
	}
	
	if (isset($inputarray['chartlabel'])) {
		$insertArray['chartlabel'] = sanitise_input($inputarray['chartlabel'], "chartlabel", $schemainfoArray['chartlabel'], $API, $logParent);
	}
	
	if (isset($inputarray['data_min'])) {
		$sanitisedInput['data_min'] = sanitise_input($inputarray['data_min'], "sd_data_min", $schemainfoArray['sd_data_min'], $API, $logParent);
	}
	else {
		errorMissing("data_min", $API, $logParent);
	}
	
	if (isset($inputarray['data_max'])) {
		$sanitisedInput['data_max'] = sanitise_input($inputarray['data_max'], "sd_data_max", $schemainfoArray['sd_data_max'], $API, $logParent);
	}
	else {
		errorMissing("data_max", $API, $logParent);
	}
	
	if (isset($inputarray['graph_min'])) {
		$sanitisedInput['graph_min'] = sanitise_input($inputarray['graph_min'], "sd_graph_min", $schemainfoArray['sd_graph_min'], $API, $logParent);
	}
	
	if (isset($inputarray['graph_max'])) {
		$sanitisedInput['graph_max'] = sanitise_input($inputarray['graph_max'], "sd_graph_max", $schemainfoArray['sd_graph_max'], $API, $logParent);
	}

	//	If you're inserting one or the other, you have to have both
	if (isset($inputarray['graph_min'])
		|| isset($inputarray['graph_max'])
		) {
		if (!isset($inputarray['graph_min'])) {
			errorMissing("graph_min", $API, $logParent);
		}
		if (!isset($inputarray['graph_max'])) {
			errorMissing("graph_max", $API, $logParent);
		}
	}
		
	if (isset($inputarray['deactivated_data'])) {
		$sanitisedInput['deactivated_data'] = sanitise_input($inputarray['deactivated_data'], "sd_deactivated_data", null, $API, $logParent);
	}	
	else {
		errorMissing("deactivated_data", $API, $logParent);
	}

	//	Need to check that data min < data max, graph min < graph max, and that the data values fall within the range of the graph values
	
	if (isset($inputarray['data_min'])) {
		if (isset($inputarray['data_max'])
			&& $sanitisedInput['data_min'] >= $sanitisedInput['data_max']
			) {
			errorInvalid("data_min", $API, $logParent);
		}
		if (isset($inputarray['graph_min'])
			&& $sanitisedInput['data_min'] < $sanitisedInput['graph_min']
			) {
			errorInvalid("data_min", $API, $logParent);
		}
		$insertArray['data_min'] = $sanitisedInput['data_min'];
	}

	if (isset($inputarray['data_max'])) {
		if (isset($inputarray['data_min'])
			&& $sanitisedInput['data_max'] <= $sanitisedInput['data_min']
			) {
			errorInvalid("data_max", $API, $logParent);
		}
		if (isset($inputarray['graph_max'])
			&& $sanitisedInput['data_max'] > $sanitisedInput['graph_max']
			) {
			errorInvalid("data_max", $API, $logParent);
		}
		$insertArray['data_max'] = $sanitisedInput['data_max'];
	}

	if (isset($inputarray['graph_min'])) {
		if (isset($inputarray['graph_max'])
			&& $sanitisedInput['graph_min'] >= $sanitisedInput['graph_max']
			) {
			errorInvalid("graph_min", $API, $logParent);
		}
		$insertArray['graph_min'] = $sanitisedInput['graph_min'];
	}
		
	//	This one is not strictly necessary, as the above case should have resolved any potential conflict
	//	Feels odd to not write the other one, and it doesn't hurt, so will include for now. -Conor
	if (isset($inputarray['graph_max'])) {
		if (isset($inputarray['graph_min'])
			&& $sanitisedInput['graph_max'] <= $sanitisedInput['graph_min']
			) {
			errorInvalid("graph_max", $API, $logParent);
		}
		$insertArray['graph_max'] = $sanitisedInput['graph_max'];
	}

	if (isset($inputarray['deactivated_data'])) {
		if (isset($inputarray['data_min']) 
			&& isset($inputarray['data_max'])
			) {
			if ($sanitisedInput['deactivated_data'] >= $sanitisedInput['data_min']
				&& $sanitisedInput['deactivated_data'] <= $sanitisedInput['data_max']
				) {	
				errorInvalid("deactivated_data_data_range", $API, $logParent);
			}
		}
		if (isset($inputarray['graph_min']) 
			&& isset($inputarray['graph_max'])
			) { 
			if ($sanitisedInput['deactivated_data'] >= $sanitisedInput['graph_min']
				&& $sanitisedInput['deactivated_data'] <= $sanitisedInput['graph_max']
				) {	
				errorInvalid("deactivated_data_graph_range", $API, $logParent);
			}
		}
		$insertArray['deactivated_data'] = $sanitisedInput['deactivated_data'];
	}
	
	if (isset($inputarray['uom_id'])) {
		$sanitisedInput['uom_id'] = sanitise_input($inputarray['uom_id'], "sd_uom_id", null, $API, $logParent);
		$sql = "SELECT
				id
			FROM
				uom
			WHERE
				id = " . $sanitisedInput['uom_id'];
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("uom_id", $API, $logParent);
		}
		$insertArray['uom_id'] = $sanitisedInput['uom_id'];
	}
	else {
		errorMissing("uom_id", $API, $logParent);
	}
	
	if (isset($inputarray['chart'])) {
		$insertArray['chart'] = sanitise_input($inputarray['chart'], "chart", null, $API, $logParent);
	}
	else {
		errorMissing("chart", $API, $logParent);
	}
	
	if (isset($inputarray['bytelength'])) {
		$insertArray['bytelength'] = sanitise_input($inputarray['bytelength'], "bytelength", $schemainfoArray['bytelength'], $API, $logParent);
	}
	else {
		errorMissing("bytelength", $API, $logParent);
	}
	
	if (isset($inputarray['active_status'])) {
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		$insertArray['active_status'] = "1";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{

		$insertArray ['last_modified_by'] = $user_id;
		$insertArray ['last_modified_datetime'] = $timestamp;

		$sql = "INSERT INTO sensor_def (
				sd_name
				, colorcode
				, iconpath
				, chartlabel
				, sd_data_min
				, sd_data_max
				, sd_deactivated_data
				, sd_graph_min
				, sd_graph_max
				, sd_uom_id
				, chart
				, bytelength
				, active_status
				, last_modified_by
				, last_modified_datetime )
			VALUES (
				:name
				, :html_colorcode";
				if (isset($insertArray['iconpath'])){
					$sql .= "
					, :iconpath";
				}
				else {
					$sql .= "
					, NULL";
				}
				if (isset($insertArray['chartlabel'])){
					$sql .= "
					, :chartlabel";
				}
				else {
					$sql .= "
					, NULL";
				}
			$sql .= "
				, :data_min
				, :data_max";
				if (isset($insertArray['deactivated_data'])){
					$sql .= "
					, :deactivated_data";
				}
				else {
					$sql .= "
					, NULL";
				}
				if (isset($insertArray['graph_min'])){
					$sql .= "
					, :graph_min";
				}
				else {
					$sql .= "
					, NULL";
				}
				if (isset($insertArray['graph_max'])){
					$sql .= "
					, :graph_max";
				}
				else {
					$sql .= "
					, NULL";
				}			
			$sql .= "
				, :uom_id
				, :chart
				, :bytelength
				, :active_status
				, :last_modified_by
				, :last_modified_datetime )";	

		$stm = $pdo->prepare($sql);
		if ($stm -> execute($insertArray)) {
			$insertArray['sd_id'] = $pdo->lastInsertId();
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			$logParent = logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent)['event_id'];
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}	
}	

// *******************************************************************************
// *******************************************************************************
// ******************************UPDATE*******************************************
// *******************************************************************************
// ******************************************************************************* 


else if($sanitisedInput['action'] == "update"){
	
	$updateArray = [];
	$updateString = "";

	$schemainfoArray = getMaxString ("sensor_def", $pdo);

	if (isset($inputarray['sd_id'])) {
		$sanitisedInput['sd_id'] = sanitise_input($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		//	Todo
		//	Might need a more indepth sql? On that limits updates to only approved users?
		$sql = "SELECT
				sd_id
			FROM
				sensor_def
			WHERE
				sd_id = " . $sanitisedInput['sd_id'];
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("sd_id", $API, $logParent);
		}
		$updateArray['sd_id'] = $sanitisedInput['sd_id'];
	}
	else {
		errorMissing("sd_id", $API, $logParent);
	}

	if (isset($inputarray['name'])) {
		$updateArray['name'] = sanitise_input($inputarray['name'], "sd_name", $schemainfoArray['sd_name'], $API, $logParent);
		$updateString .= " `sd_name` = :name,";
	}

	if (isset($inputarray['html_colorcode'])) {
		$updateArray['html_colorcode'] = sanitise_input($inputarray['html_colorcode'], "html_colorcode", $schemainfoArray['colorcode'], $API, $logParent);
		$updateString .= " `colorcode` = :html_colorcode,";
	}

	if (isset($inputarray['iconpath'])) {
		$updateArray['iconpath'] = sanitise_input($inputarray['iconpath'], "iconpath", $schemainfoArray['iconpath'], $API, $logParent);
		$updateString .= " `iconpath` = :iconpath,";
	}
	
	if (isset($inputarray['chartlabel'])) {
		$updateArray['chartlabel'] = sanitise_input($inputarray['chartlabel'], "chartlabel", $schemainfoArray['chartlabel'], $API, $logParent);
		$updateString .= " `chartlabel` = :chartlabel,";
	}

	if (isset($inputarray['data_type'])) {
		$updateArray['data_type'] = sanitise_input($inputarray['data_type'], "sd_data_type", $schemainfoArray['sd_data_type'], $API, $logParent);
		$updateString .= " `sd_data_type` = :data_type,";
	}



	//	Buckle up this next bit is going to take a hot minute...

	//	Testing to see if input data min is greater than input or current max, and vice versa
	//	Graph min max must also encompasses data min max, ie. data max <= graph max, and data min >= graph min
	//	Also checks if the deactivated data is erroneously within the range of the other values

	//	If any of the values are included enter loop
	if (isset($inputarray['data_min'])
		|| isset($inputarray['data_max'])
		|| isset($inputarray['graph_min'])
		|| isset($inputarray['graph_max'])
		|| isset($inputarray['deactivated_data'])
		){
		
		if (isset($inputarray['data_min'])) {
			$sanitisedInput['data_min'] = sanitise_input($inputarray['data_min'], "sd_data_min", $schemainfoArray['sd_data_min'], $API, $logParent);
		}

		if (isset($inputarray['data_max'])) {
			$sanitisedInput['data_max'] = sanitise_input($inputarray['data_max'], "sd_data_max", $schemainfoArray['sd_data_max'], $API, $logParent);
		}

		if (isset($inputarray['graph_min'])) {
			$sanitisedInput['graph_min'] = sanitise_input($inputarray['graph_min'], "sd_graph_min", $schemainfoArray['sd_graph_min'], $API, $logParent);
		}

		if (isset($inputarray['graph_max'])) {
			$sanitisedInput['graph_max'] = sanitise_input($inputarray['graph_max'], "sd_graph_max", $schemainfoArray['sd_graph_max'], $API, $logParent);
		}
		
		if (isset($inputarray['deactivated_data'])) {
			$sanitisedInput['deactivated_data'] = sanitise_input($inputarray['deactivated_data'], "sd_deactivated_data", null, $API, $logParent);
		}

		//	First get any database values that we have and store them in a new 'compare' array
		$compare = array();
		if (!isset($sanitisedInput['data_min'])
			|| !isset($sanitisedInput['data_max'])
			|| !isset($sanitisedInput['graph_min'])
			|| !isset($sanitisedInput['graph_max'])
			|| !isset($sanitisedInput['deactivated_data'])
			){
			$sql = "SELECT 
					sd_data_min
					, sd_data_max
					, sd_graph_min
					, sd_graph_max
					, sd_deactivated_data
				FROM 
					sensor_def
				WHERE
					sd_id = " . $updateArray['sd_id'];
			$stm = $pdo->query($sql);
			$rows = $stm->fetchAll(PDO::FETCH_NUM);
			$compare['data_min'] = $rows[0][0];
			$compare['data_max'] = $rows[0][1];
			$compare['graph_min'] = $rows[0][2];
			$compare['graph_max'] = $rows[0][3];
			$compare['deactivated_data'] = $rows[0][4];
		}
		//	If we are entering any new values then our new values will overwrite the database ones in the compare array
		if (isset($sanitisedInput['data_min'])) {
			$compare['data_min'] = $sanitisedInput['data_min'];
		}
		if (isset($sanitisedInput['data_max'])) {
			$compare['data_max'] = $sanitisedInput['data_max'];
		}
		if (isset($sanitisedInput['graph_min'])) {
			$compare['graph_min'] = $sanitisedInput['graph_min'];
		}
		if (isset($sanitisedInput['graph_max'])) {
			$compare['graph_max'] = $sanitisedInput['graph_max'];
		}
		if (isset($sanitisedInput['deactivated_data'])) {
			$compare['deactivated_data'] = $sanitisedInput['deactivated_data'];
		}
 
		//	Finished setting up the compare array. Time to compare

		//	If value is set
		if (isset($inputarray['data_min'])) {
			//	If equivalent data_max exists, and value >= data_max throw error
			if (isset($compare['data_max'])
				&& $compare['data_min'] >= $compare['data_max']
				) {
				errorInvalid("data_min", $API, $logParent);
			}
			//	If equivalent graph min exists, and value < graph min throw error
			if (isset($compare['graph_min']) 
				&& $compare['data_min'] < $compare['graph_min']
				) {
				errorInvalid("data_min", $API, $logParent);
			}
			if (isset($compare['deactivated_data']) 
				&& $compare['data_min'] <= $compare['deactivated_data']
				&& $compare['data_max'] >= $compare['deactivated_data']		//	TODO check this line is correct
				) {
				errorInvalid("data_min", $API, $logParent);
			}
			//	Else proceed normally
			$updateArray['data_min'] = $sanitisedInput['data_min'];
			$updateString .= " `sd_data_min` = :data_min,";
		}

		if (isset($inputarray['data_max'])) {
			if (isset($compare['data_min'])
				&& $compare['data_max'] <= $compare['data_min']
				) {
				errorInvalid("data_max", $API, $logParent);
			}
			if (isset($compare['graph_max'])
				&& $compare['data_max'] > $compare['graph_max']
				) {
				errorInvalid("data_max", $API, $logParent);
			}
			if (isset($compare['deactivated_data']) 
				&& $compare['data_max'] >= $compare['deactivated_data']
				&& $compare['data_min'] <= $compare['deactivated_data']		//	TODO check this line is correct
				) {
				errorInvalid("data_max", $API, $logParent);
			}
			$updateArray['data_max'] = $sanitisedInput['data_max'];
			$updateString .= " `sd_data_max` = :data_max,";
		}

		if (isset($inputarray['graph_min'])) {
			//	If there isn't already a graph max in database or in update query, then throw error. If you have one, you need both. 
			//	This catches edge cases of having one value in database but not the other, not strictly necessary.
			if (!isset($compare['graph_max'])) {
				errorMissing("graph_max", $API, $logParent);
			}
			//	If equivalent graph_max exists, and value >= graph_max throw error
			if (isset($compare['graph_max'])
				&& $compare['graph_min'] >= $compare['graph_max']
				) {
				errorInvalid("graph_min", $API, $logParent);
			}
			//	If equivalent data_min exists, and value > data_min throw error
			if (isset($compare['data_min'])
				&& $compare['graph_min'] > $compare['data_min']
				) {
				errorInvalid("graph_min", $API, $logParent);
			}
			//	If equivalent data_max exists, and value > data_max throw error
			if (isset($compare['data_max']) 
				&& $compare['graph_min'] > $compare['data_max']
				) {
				errorInvalid("graph_min", $API, $logParent);
			}
			//	If there is a deactivated data value the check that the new graph_min does not include it within its new range
			if (isset($compare['deactivated_data']) 
				&& $compare['graph_min'] <= $compare['deactivated_data']
				&& $compare['graph_max'] >= $compare['deactivated_data']
				) {
				errorInvalid("graph_min", $API, $logParent);
			}
			//	Else proceed normally
			$updateArray['graph_min'] = $sanitisedInput['graph_min'];
			$updateString .= " `sd_graph_min` = :graph_min,";
		}

		if (isset($inputarray['graph_max'])) {
			if (!isset($compare['graph_min'])) {
				errorMissing("graph_min", $API, $logParent);
			}
			if (isset($compare['graph_min']) 
				&& $compare['graph_max'] <= $compare['graph_min']
				) {
				errorInvalid("graph_max", $API, $logParent);
			}
			if (isset($compare['data_min'])
				&& $compare['graph_max'] < $compare['data_min']
				) {
				errorInvalid("graph_max", $API, $logParent);
			}
			if (isset($compare['data_max'])
				&& $compare['graph_max'] < $compare['data_max']
				) {
				errorInvalid("graph_max", $API, $logParent);
			}
			if (isset($compare['deactivated_data']) 
				&& $compare['graph_min'] <= $compare['deactivated_data']
				&& $compare['graph_max'] >= $compare['deactivated_data']
				) {
				errorInvalid("graph_max", $API, $logParent);
			}
			$updateArray['graph_max'] = $sanitisedInput['graph_max'];
			$updateString .= " `sd_graph_max` = :graph_max,";
		}

		if (isset($inputarray['deactivated_data'])) {

			//	Check to see if the deactivated_data value is outside the range of the graph and data min/max
			//	Can't just use data as it may still be within graph bounds, and can't just use graph bounds as they may be null
				
			//	Checking using data min max

			if (isset($compare['data_min']) 
				&& isset($compare['data_max'])
				) {
				if ($compare['deactivated_data'] >= $compare['data_min']
					&& $compare['deactivated_data'] <= $compare['data_max']
					) {	
					errorInvalid("deactivated_data_data_range", $API, $logParent);
				}
			}
			else if (isset($compare['data_min'])
				&& $compare['deactivated_data'] >= $compare['data_min']
				) {
				errorInvalid("deactivated_data_data_range", $API, $logParent);
			}
			else if (isset($compare['data_max'])
				&& $compare['deactivated_data'] <= $compare['data_max']
				) {
				errorInvalid("deactivated_data_data_range", $API, $logParent);
			}

			//	Checking using graph min max

			if (isset($compare['graph_min']) 
				&& isset($compare['graph_max'])
				) { 
				if ($compare['deactivated_data'] >= $compare['graph_min']
					&& $compare['deactivated_data'] <= $compare['graph_max']
					) {	
					errorInvalid("deactivated_data_graph_range", $API, $logParent);
				}
			}
			else if (isset($compare['graph_min'])
				&& $compare['deactivated_data'] >= $compare['graph_min']
				) {
				errorInvalid("deactivated_data_graph_range", $API, $logParent);
			}
			else if (isset($compare['graph_max'])
				&& $compare['deactivated_data'] <= $compare['graph_max']
				) {
				errorInvalid("deactivated_data_graph_range", $API, $logParent);
			}
			$updateArray['deactivated_data'] = $sanitisedInput['deactivated_data'];
			$updateString .= " `sd_deactivated_data` = :deactivated_data,";
		}
	}
	//	Big statement: Fin

	if (isset($inputarray['uom_id'])) {
		$sanitisedInput['uom_id'] = sanitise_input($inputarray['uom_id'], "sd_uom_id", null, $API, $logParent);
		$sql = "SELECT
				id
			FROM
				uom
			WHERE
				id = " . $sanitisedInput['uom_id'];
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])){
			errorInvalid("uom_id", $API, $logParent);
		}
		$updateArray['uom_id'] = $sanitisedInput['uom_id'];
		$updateString .= " `sd_uom_id` = :uom_id,";
	}

	if (isset($inputarray['chart'])) {
		$updateArray['chart'] = sanitise_input($inputarray['chart'], "chart", null, $API, $logParent);
		$updateString .= " `chart` = :chart,";
	}

	if (isset($inputarray['bytelength'])) {
		$updateArray['bytelength'] = sanitise_input($inputarray['bytelength'], "bytelength", $schemainfoArray['bytelength'], $API, $logParent);
		$updateString .= " `bytelength` = :bytelength,";
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
		else {

			$updateArray ['last_modified_by'] = $user_id;
			$updateArray ['last_modified_datetime'] = $timestamp;
			$updateString .= "`last_modified_by` = :last_modified_by
				, `last_modified_datetime` = :last_modified_datetime";
			
			$sql = "UPDATE 
					sensor_def 
				SET  $updateString  
				WHERE `sd_id` = :sd_id";

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

	try {
		$logArray = array();
		$logArray['action'] = "update";
		$logArray['updateTable'] = "devices";
		$updateArray_2['sd_id'] = $updateArray['sd_id'];
		$logParent = logEvent($API . logText::request . substr(str_replace('"', '\"', json_encode($logArray)),0 , -1) . "," . substr(str_replace('"', '\"', json_encode($updateArray_2)), 1), logLevel::request, logType::request, $token, $logParent)['event_id'];

		$sql =" UPDATE 
				sensor_def
				, sensors_det
				, sensors
				, device_provisioning_components
				, devices
			SET devices.configuration_version = (devices.configuration_version + 1)
			WHERE sensor_def.sd_id = sensors_det.sensor_def_sd_id
			AND sensors.sensor_id = sensors_det.sensors_sensor_id
			AND device_provisioning_components.device_component_id = sensors.sensor_id
			AND device_provisioning_components.device_component_type = 'Sensor'
			AND device_provisioning_components.device_provisioning_device_provisioning_id = devices.device_provisioning_device_provisioning_id
			AND sensor_def.sd_id = :sd_id";

		$stm= $pdo->prepare($sql);	
		if($stm->execute($updateArray_2)){
			$updateArray_2 ['error'] = "NO_ERROR";
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray_2)), logLevel::response, logType::response, $token, $logParent);
		}
	}
	catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}	
}

else {	
	logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
	errorInvalid("request", $API, $logParent);
}

$pdo = null;
$stm = null;

?>