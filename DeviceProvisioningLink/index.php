<?php
$API = "DeviceProvisioningLink";
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

	$schemainfoArrayParam = getMaxString ("param_def", $pdo);
	$schemainfoArrayModule = getMaxString ("module_def", $pdo);

    $sql = "SELECT 
			device_provisioning_link.module_def_id
			, module_def.module_name
			, device_provisioning_link.module_value
			, device_provisioning_link.param_def_id
			, param_def.param_name
			, device_provisioning_link.param_value
			, device_provisioning_link.sendvia
			, device_provisioning_link.calib_def_id
			, calibration_def.name
			, device_provisioning_link.sensor_def_sd_id
			, device_provisioning_link.sensor_def_data_type
			, device_provisioning_link.id
			, device_provisioning_link.device_provisioning_id
			, device_provisioning_link.active_status
			, device_provisioning_link.last_modified_by
			, device_provisioning_link.last_modified_datetime
			, sensor_def.sd_name
		FROM 
			device_provisioning_link
			, module_def
			, param_def
			, calibration_def 
			, sensor_def
		WHERE module_def.id = device_provisioning_link.module_def_id
		AND param_def.id = device_provisioning_link.param_def_id
		AND calibration_def.id = device_provisioning_link.calib_def_id
		AND sensor_def.sd_id = device_provisioning_link.sensor_def_sd_id";
	
	if (isset($inputarray['provisioning_link_id'])) {		
		$sanitisedInput['provisioning_link_id'] = sanitise_input_array($inputarray['provisioning_link_id'], "provisioning_link_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`id` IN ( '" . implode("', '", $sanitisedInput['provisioning_link_id']) . "' )";
	} 

	if (isset($inputarray['active_status'])) {		
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`active_status` = '". $sanitisedInput['active_status'] ."'";
	}  
	
	if (isset($inputarray['provisioning_id'])) {		
		$sanitisedInput['provisioning_id'] = sanitise_input_array($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`device_provisioning_id` IN ( '" . implode("', '", $sanitisedInput['provisioning_id']) . "' )";
	}

	if (isset($inputarray['param_id'])) {		
		$sanitisedInput['param_id'] = sanitise_input_array($inputarray['param_id'], "param_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`param_def_id` IN ( '" . implode("', '", $sanitisedInput['param_id']) . "' )";
	}

	if (isset($inputarray['param_value'])) {		
		$sanitisedInput['param_value'] = sanitise_input_array($inputarray['param_value'], "param_value", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`param_value` IN ( '" . implode("', '", $sanitisedInput['param_value']) . "' )";
	}

	if (isset($inputarray['param_name'])) {		
		$sanitisedInput['param_name'] = sanitise_input_array($inputarray['param_name'], "param_name", $schemainfoArrayParam['param_name'], $API, $logParent);
		$sql .= " AND `param_def`.`param_name` IN ( '" . implode("', '", $sanitisedInput['param_name']) . "' )";
	}

	if (isset($inputarray['module_id'])) {		
		$sanitisedInput['module_id'] = sanitise_input_array($inputarray['module_id'], "module_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`module_def_id` IN ( '" . implode("', '", $sanitisedInput['module_id']) . "' )";
	}

	if (isset($inputarray['module_name'])) {		
		$sanitisedInput['module_name'] = sanitise_input_array($inputarray['module_name'], "module_name", $schemainfoArrayModule['module_name'], $API, $logParent);
		$sql .= " AND `module_def`.`module_name` IN ( '" . implode("', '", $sanitisedInput['module_name']) . "' )";
	}

	if (isset($inputarray['module_value'])) {		
		$sanitisedInput['module_value'] = sanitise_input_array($inputarray['module_value'], "module_value", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`module_value` IN ( '" . implode("', '", $sanitisedInput['module_value']) . "' )";
	}

	if (isset($inputarray['sd_id'])) {		
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql .= " AND `device_provisioning_link`.`sd_id` IN ( '" . implode("', '", $sanitisedInput['sd_id']) . "' )";
	}

	$sql .= " ORDER BY device_provisioning_link.id DESC";
	
	if (isset($inputarray['limit'])){
		$sanitisedInput['limit'] = sanitise_input($inputarray['limit'], "limit", null, $API, $logParent);
		$sql .= " LIMIT ". $sanitisedInput['limit'];
	}
	else {
		$sql .= " LIMIT " . allFile::limit;
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];
	//echo $sql;

	$stm = $pdo->query($sql);					
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0][0])) {
		$json_items = array();
		$outputid = 0;
		foreach($rows as $row) {
			$json_item = array(
				"provisioning_link_id" => $row[11]
				, "provisioning_id" => $row[12]
				, "module_value" => $row[2]
				, "module_id" => $row[0]
				, "module_name" => $row[1]
				, "param_value" => $row[5]
				, "param_id" => $row[3]
				, "param_name" => $row[4]
				, "sd_id" => $row[9]
				, "sd_name" => $row[16]
				, "sd_data_type" => $row[10]
				, "sendvia" => $row[6]
				, "calib_id" => $row[7]
				, "calib_name" => $row[8]
				, "active_status" => $row[13]
				, "last_modified_by" => $row[14]
				, "last_modified_datetime" => $row[15]);
			$json_items = array_merge(
				$json_items,
				array("response_$outputid" => $json_item)
			);
			$outputid++;
		}
		$json = array("responses" => $json_items);
		echo json_encode($json);
		logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
	} else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die ("{\"error\":\"NO_DATA\"}");
	}
}

// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************

	//	NOTES FOR THIS API
	/*
	Unique key constraints: 
	a) 
		module_def_id
		param_def_id
		sensor_def_sd_id
		device_provisioning_id
	
	b)	
		param_value
		module_value
		device_provisioning_id

	c)
		sensor_def_sd_id
		device_provisioning_id

	Module def ID and param ID are tied together on the param_def table.
	Module value is a unique module id per device provisioning number. 
		eg. If we add a new module #5 sensor, then the module value will be whatever the current module 5 number is. 
			If we are adding a brand new module to a device provisioning, then we need to find the highest module value number for the provisioning ID and ++ for the new module value number
	*/

elseif($sanitisedInput['action'] == 'insert'){

	$schemainfoArray = getMaxString ("device_provisioning_link", $pdo);

	$insertArray = [];

	if (isset($inputarray['provisioning_id'])) {
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);	
		//	Do a select query here. 
		//	Check if provisioning ID exists, and if so, pull down all the info we got on it?
		//	Looking for module values and param values primarily
	}
	else {
		errorMissing("provisioning_id", $API, $logParent);
	}	

	
	//	Long so we declare it here first
	if (!isset($inputarray['values'])){
		errorMissing("values", $API, $logParent);
	}
	else{
		//	Declare storage array
		$sanitisedInput['sd_id'] = array();
		$sanitisedInput['module_id'] = array();
		$sanitisedInput['full_module_id'] = array();
		$sanitisedInput['module_value'] = array();
		$sanitisedInput['full_module_value'] = array();
		$sanitisedInput['module_value_to_module_id'] = array();
		$sanitisedInput['module_id_to_module_value'] = array();
		$sanitisedInput['param_id'] = array();
		$sanitisedInput['param_value'] = array();
		$sanitisedInput['param_value_by_module_id'] = array();
		$sanitisedInput['active_status'] = array();
		$sanitisedInput['sendvia'] = array();
		$sanitisedInput['calib_id'] = array();
		$sanitisedInput['bytelength'] = array();

		foreach ($inputarray['values'] as $keyArray) {

			//	print_r($keyArray);

			//	TODO 
			//	We use $keyArray values a lot here.
			//	This potentially skips sanitisation and needs to be fixed
			//	26-10-21 -Conor

			//	Make sure mandatory information has been supplied
			isset($keyArray['sd_id'])?: errorMissing("sd_id", $API, $logParent);
			isset($keyArray['module_id'])?: errorMissing("module_id", $API, $logParent);
			//isset($keyArray['param_id'])?: errorMissing("param_id", $API, $logParent);

			//	Sanitise the inputs
			//	sd_id is special as we want to run an sql on it, so we also need to put it in its own array spot

			//	Mandatory values
			$sanitisedInput['sd_id'][] = sanitise_input($keyArray['sd_id'], "sd_id", null, $API, $logParent);
			$sanitisedInput['module_id'][$keyArray['sd_id']] = sanitise_input($keyArray['module_id'], "module_id", null, $API, $logParent);

			//	We also need a full array of the values the user is putting in for module and param id for later
			//	Its important to save them in this [$longstring] format for referencing later
			$sanitisedInput['full_module_id'][$sanitisedInput['module_id'][$keyArray['sd_id']]] = $sanitisedInput['module_id'][$keyArray['sd_id']];

			//*******MODULE VALUE******* */

			//	If an input has a module value given then we push it to an array for later use. 
			//	This will be combined with the values already in the database to create a whole list of what the 'free' module_value INTs are for allocation if needed
			//	We also need to check if: 
			//		a) a module ID contains more than one module value -> redefinition -> error
			//		b) a module value is used for more than one module ID -> redefinition -> error
		

			isset($keyArray['module_value'])?
				$sanitisedModuleValue = sanitise_input($keyArray['module_value'], "module_value", null, $API, $logParent)
				: $sanitisedModuleValue= "NOT_SUPPLIED";


			//	Assign to the module value array w/ sd_id key
			$sanitisedInput['module_value'][$keyArray['sd_id']] = $sanitisedModuleValue;

			$sanitisedInput['module_id_to_module_value'][$keyArray['module_id']][$sanitisedModuleValue][$keyArray['sd_id']] = $keyArray['sd_id'];

			$sanitisedInput['module_value_to_module_id'][$sanitisedModuleValue][$keyArray['module_id']][$keyArray['sd_id']] = $keyArray['sd_id'];

			if ($sanitisedModuleValue != "NOT_SUPPLIED"){
				$sanitisedInput['full_module_value'][$keyArray['module_id']][$sanitisedModuleValue] = $sanitisedModuleValue;
			}


				/*
				
				//	Check for a) module ID being a redefined a new module value
				if (isset($sanitisedInput['full_module_value'][$keyArray['module_id']])){
					//	If this value is already assigned in the full module value table
					if ($sanitisedInput['full_module_value'][$keyArray['module_id']] !=  $sanitisedModuleValue){
					//	If this module value is NOT the expected value already in request for this module ID then throw error

						//	If this value in error array does not already exist then create it
						isset($errorArray['duplicate_module_value'][$sanitisedInput['module_id'][$keyArray['sd_id']]])?
							: $errorArray['duplicate_module_value'][$sanitisedInput['module_id'][$keyArray['sd_id']]] = array();

						//	Cast the new error to the array
						$errorArray['duplicate_module_value'][$sanitisedInput['module_id'][$keyArray['sd_id']]][$keyArray['sd_id']] = $sanitisedModuleValue;
						//	Cast the original error to the array as well


						//	TODO 
						//	Right now this gets the module id instead of the sd_id as the key value
						
						$errorArray['duplicate_module_value'][$sanitisedInput['module_id'][$keyArray['sd_id']]][$sanitisedInput['full_module_value'][$keyArray['module_id']]] = $sanitisedInput['full_module_value'][$keyArray['module_id']];

					}
				}
				else {
					//	Else if this is not already in the table, then we proceed to assign it as standard
					$sanitisedInput['full_module_value'][$keyArray['module_id']] = $sanitisedModuleValue;
				}

				*/






			//	Push the module value to an array organised by the sd_id AND module ID

			//*******PARAM ID******* */

			isset($keyArray['param_id'])?
				$sanitisedParamID = sanitise_input($keyArray['param_id'], "param_id", null, $API, $logParent)
				: $sanitisedParamID= "NOT_SUPPLIED";

			$sanitisedInput['param_id'][$keyArray['sd_id']] = $sanitisedParamID;
			$sanitisedInput['param_id_by_sd_id'][$sanitisedParamID][$keyArray['sd_id']] = $keyArray['sd_id'];

			//	As param_ID values must be unique this will either add the new param_ID to the $santisedArray if its a first occurance or send it to an error array if its a duplicate
			$sanitisedInput['full_param_id'][$sanitisedParamID] = $sanitisedParamID;


			//********PARAM VALUE****** */
			/*
			//	We want to push to two arrays, one sorted by the sd_id and one sorted by the module ID
			if (isset($keyArray['param_value'])){
				$sanitisedParamValue = sanitise_input($keyArray['param_value'], "param_value", null, $API, $logParent);

				//	Push param_value to the sanitised array,
				$sanitisedInput['param_value'][$keyArray['sd_id']] = $sanitisedParamValue;

				//	If we don't already have the 'param_value_by_module_id' input as an array already then create it
				isset($sanitisedInput['param_value_by_module_id'][$keyArray['module_id']])?:
					$sanitisedInput['param_value_by_module_id'][$keyArray['module_id']] = array();


				if (isset($sanitisedInput['param_value_by_module_id'][$keyArray['module_id']][$sanitisedParamValue])) {
					//	If this is a duplicate, push it to the error array
					//	If this error array doesn't exist yet, create it
					isset($errorArray['duplicate_param_value'])?
						: $errorArray['duplicate_param_value'] = array();

					//	If this spot in the error array doesnt already exist then create it and push the error to it
					!isset($errorArray['duplicate_param_value'][$keyArray['module_id']][$sanitisedParamValue])?
						$errorArray['duplicate_param_value'][$keyArray['module_id']][$sanitisedParamValue] = $sanitisedParamValue
						: $errorArray['duplicate_param_value'][$keyArray['module_id']][$sanitisedParamValue] = $sanitisedParamValue;
				}
				//$sanitisedInput['param_value_by_module_id'][$keyArray['module_id']][$sanitisedParamValue][$keyArray['sd_id']] = $keyArray['sd_id'];
				$sanitisedInput['param_value_by_module_id'][$keyArray['module_id']][$sanitisedParamValue][$keyArray['sd_id']] = $keyArray['sd_id'];
				//print_r($sanitisedInput['param_value_by_module_id']);
			}
			else {
				//	If value not supplied then push default value
				$sanitisedInput['param_value'][$keyArray['sd_id']] = "NOT_SUPPLIED";
			}
			*/
			//	We want to push to two arrays, one sorted by the sd_id and one sorted by the module ID
			if (isset($keyArray['param_value'])){
				$sanitisedParamValue = sanitise_input($keyArray['param_value'], "param_value", null, $API, $logParent);
			}
			else {
				$sanitisedParamValue = "NOT_SUPPLIED";
			}


			//	Push param_value to the sanitised array,
			$sanitisedInput['param_value'][$keyArray['sd_id']] = $sanitisedParamValue;

			//	If we don't already have the 'param_value_by_module_id' input as an array already then create it
			isset($sanitisedInput['param_value_by_module_id'][$keyArray['module_id']])?:
				$sanitisedInput['param_value_by_module_id'][$keyArray['module_id']] = array();


			if (isset($sanitisedInput['param_value_by_module_id'][$keyArray['module_id']][$sanitisedParamValue])
				&& $sanitisedParamValue != "NOT_SUPPLIED"
				) {
				//	If this is a duplicate, push it to the error array
				//	If this error array doesn't exist yet, create it
				isset($errorArray['duplicate_param_value'])?
					: $errorArray['duplicate_param_value'] = array();

				//	If this spot in the error array doesnt already exist then create it and push the error to it
				isset($errorArray['duplicate_param_value'][$keyArray['module_id']][$sanitisedParamValue])?

					: $errorArray['duplicate_param_value'][$keyArray['module_id']][$sanitisedParamValue] = $sanitisedParamValue;
			}
			//$sanitisedInput['param_value_by_module_id'][$keyArray['module_id']][$sanitisedParamValue][$keyArray['sd_id']] = $keyArray['sd_id'];
			$sanitisedInput['param_value_by_module_id'][$keyArray['module_id']][$sanitisedParamValue][$keyArray['sd_id']] = $keyArray['sd_id'];
			//print_r($sanitisedInput['param_value_by_module_id']);
	


			//*************************** */

			isset($keyArray['active_status'])? 
				$sanitisedInput['active_status'][$keyArray['sd_id']] = sanitise_input($keyArray['active_status'], "active_status", null, $API, $logParent)
				: $sanitisedInput['active_status'][$keyArray['sd_id']] = 0;
			isset($keyArray['sendvia'])? 
				$sanitisedInput['sendvia'][$keyArray['sd_id']] =sanitise_input($keyArray['sendvia'], "sendvia", $schemainfoArray['sendvia'], $API, $logParent)
				: $sanitisedInput['sendvia'][$keyArray['sd_id']] = "AUTO";
			isset($keyArray['calib_id'])? 
				$sanitisedInput['calib_id'][$keyArray['sd_id']] = sanitise_input($keyArray['calib_id'], "calib_id", null, $API, $logParent)
				: $sanitisedInput['calib_id'][$keyArray['sd_id']] = 1;

		}

		//*******************ERROR HANDLING*********************** */

		//	Check to see if module value are used for multiple module ID
		foreach($sanitisedInput['module_value_to_module_id'] as $module_value => $module_id) {
			if($module_value != "NOT_SUPPLIED"){
				if (count($sanitisedInput['module_value_to_module_id'][$module_value]) != 1) {
					$errorArray['module_value_duplicate_module_id'][$module_value] = $module_id;
				}
			}
		}
		//print_r($sanitisedInput['module_id_to_module_value']);
		//	Check to see if module ID has multiple module value
		foreach ($sanitisedInput['module_id_to_module_value'] as $module_id => $module_value) {
			//	Check to find out if we are expecting a "NOT_SUPPLIED" value, and change the expected number of module values appropriately
			isset($sanitisedInput['module_id_to_module_value'][$module_id]["NOT_SUPPLIED"])?
				$expected_number = 2
				: $expected_number = 1;
			if (count($sanitisedInput['module_id_to_module_value'][$module_id]) > $expected_number) {
				$errorArray['duplicate_module_value'][$module_id] = $module_id;
			}
		}

		//	Check to see if param value are used for multiple module ID
		foreach ($sanitisedInput['param_id_by_sd_id'] as $param_id => $sd_id) {
			if($param_id != "NOT_SUPPLIED"){
				if (count($sanitisedInput['param_id_by_sd_id'][$param_id]) != 1) {
					foreach ($sanitisedInput['param_id_by_sd_id'][$param_id] as $key => $sd_id) {
						$errorArray['duplicate_param_id'][$param_id][$sd_id] = $sd_id;
					}
				}				
			}
		}

		$errorMsg = "";
		//	There are multiple possible errors here from the above section. 
		//	Multiple errors can be triggered in a single query
		//	They need to be triggered in order of 'seniority'
		//	We could do a big in depth tree of else / else if to identify which is the most 'senior' error that has been triggered but that will take a minute
		//		The solution is to call them in order from least -> most senior, setting a variable each time.
		//		This way even if a lower seniority message is triggered the highest senior will be the actual triggere / displayed to user / logged. 
		//	This format is carried on throughout the code
		!isset($errorArray['duplicate_param_id'])?: $errorMsg = "DUPLICATE_PARAM_ID";
		!isset($errorArray['duplicate_param_value'])?: $errorMsg = "MODULE_ID_HAS_DUPLICATE_PARAM_VALUES";
		!isset($errorArray['duplicate_module_value'])?: $errorMsg = "MODULE_ID_HAS_DUPLICATE_MODULE_VALUES";
		!isset($errorArray['module_value_duplicate_module_id'])?: $errorMsg = "MULTIPLE_MODULE_ID_HAVE_THE_SAME_MODULE_VALUE";

		if ($errorMsg != ""){
			switch ($errorMsg) {

				CASE "MODULE_ID_HAS_DUPLICATE_PARAM_VALUES":

					foreach ($errorArray['duplicate_param_value'] as $module_id => $throwaway){
						foreach ($errorArray['duplicate_param_value'][$module_id] as $param_value){
							foreach ($sanitisedInput['param_value_by_module_id'][$module_id][$param_value] as $sd_id){
								$errorArray['output']["Module ID: " . $module_id]["Param value: " . $param_value][] = "sd_id: " . $sd_id;
							}
						}
					}

					BREAK;

				CASE "MULTIPLE_MODULE_ID_HAVE_THE_SAME_MODULE_VALUE":

					foreach ($errorArray['module_value_duplicate_module_id'] as $module_value => $throwaway) {
						foreach ($errorArray['module_value_duplicate_module_id'][$module_value] as $module_id => $throwaway){
							foreach ($errorArray['module_value_duplicate_module_id'][$module_value][$module_id] as $sd_id){
								$errorArray['output']["Module value: " . $module_value]["Module ID: " . $module_id][] = "sd_id: " . $sd_id;
							}
						}
					}

					BREAK;

				CASE "MODULE_ID_HAS_DUPLICATE_MODULE_VALUES":

					foreach ($errorArray['duplicate_module_value'] as $module_id => $throwaway) {
						foreach ($sanitisedInput['module_id_to_module_value'][$module_id] as $module_value => $throwaway){
							if ($module_value != "NOT_SUPPLIED"){
								foreach ($sanitisedInput['module_id_to_module_value'][$module_id][$module_value] as $key => $sd_id) {
									$errorArray['output']["Module ID: " . $module_id]["Module value: " . $module_value][] = "sd_id: " . $sd_id;
								}
							}
						}
					}

					BREAK;

				CASE "DUPLICATE_PARAM_ID":

					foreach ($errorArray['duplicate_param_id'] as $param_id => $throwaway) {
						foreach ($errorArray['duplicate_param_id'][$param_id] as $key => $sd_id) {
							$errorArray['output']["Param ID: " . $param_id][] = "sd_id: " . $sd_id;
						}
					}

					BREAK;
			}

			$output = json_encode(
				array(
				'error' => $errorMsg,
				'error_detail' => $errorArray['output']
				));
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}

	//	echo "\n*****************LOADING DETAILS SECTION PASSED***************\n";

		//***********************/
		//*********SD_ID*********/
		//***********************/

		//	Check if:
		//		a) sd_id are valid sd_id
		//		b) get the bytelength for sd_id
		//		c) check if this sd_id is already used 
		
		//	First check for any duplicate values being inserted, ie. sd_id [1, 2, 2, 3]
		if (count(array_unique($sanitisedInput['sd_id'])) < count ($sanitisedInput['sd_id'])){
			//	This searches through the input sd_id values and finds the offending duplicate sd_id values and pushes them to an error array to output to the user
			$errorArray = array();
			$errorArray = array_diff_key( $sanitisedInput['sd_id'], array_unique($sanitisedInput['sd_id']));
			$errorArray = array_values(array_unique($errorArray));

			$output = json_encode(
				array(
				'error' => "DUPLICATE_REQUEST_SD_ID_PRAM",
				'error_detail' => $errorArray
				));
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}

		
		$sql = "SELECT 
				sensor_def.sd_id
				, sensor_def.bytelength
				, device_provisioning_link.device_provisioning_id
			FROM
				sensor_def	
			LEFT JOIN device_provisioning_link ON (
				device_provisioning_link.device_provisioning_id = " . $sanitisedInput['provisioning_id'] ."
				AND device_provisioning_link.sensor_def_sd_id = sensor_def.sd_id )
			WHERE sd_id IN ( '" . implode("', '", $sanitisedInput['sd_id']) . "' )";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			errorInvalid("sd_id", $API, $logParent);
		}
		//	Check to make sure all the input sd_id are valid sd_id values
		arrayExistCheck ($sanitisedInput['sd_id'], array_column($rows, 0), "sd_id", $API, $logParent);

		$errorArray = array();
		foreach ($rows as $row) {
			//	Push the bytelengths to the correct arrays
			$sanitisedInput['bytelength'][$row[0]] = $row[1];
			//	If we sd_id already exists for this provisioning ID, push the offending sd_id to an error array
			!isset($row[2])?: ($errorArray[] = "sd_id: " . $row[0]);
		}
			//	If we detected any sd_id that existed twice, then we need to die and error out
		if (count($errorArray) > 0) {
			$output = json_encode(
				array(
				'error' => "SD_ID_ALREADY_IN_USE",
				'error_detail' => $errorArray
				));
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}

		//	echo "\n*****************SD ID SECTION PASSED***************\n";


		//*************************/
		//*********CALIB_ID********/
		//*************************/

		//	If any of these exist that are NOT the defalt supplied value, we've got to search to make sure that they are allowed
		//	First check the values, find if they're NOT the default value. Any found push to an array
		foreach ($sanitisedInput['calib_id'] as $key => $val) {

			if ($val != 1) {
				//	This is 'catch' on the first non-default value, triggering the SQL search on all of them. In this manner we can avoid searching if necessary, otherwise search all of them at once
				//	If any are found do an SQL search on them, make sure they are what we expect them to be
				$sql = "SELECT 
						id
					FROM calibration_def 
					WHERE id IN ('" . implode("', '", $sanitisedInput['calib_id']) . "' )";

				$stm = $pdo->query($sql);
				$rows = $stm->fetchAll(PDO::FETCH_NUM);
				if (!isset($rows[0][0])) {
					errorInvalid("calib_id", $API, $logParent);
				}

				//	Check to make sure all the input calib_id are valid calib_id values
				arrayExistCheck ($sanitisedInput['calib_id'], array_column($rows, 0), "calib_id", $API, $logParent);
				//	Leave the loop once we've gone over it once.
				BREAK;
			}
		}	

		//	echo "\n*****************CALIB ID SECTION PASSED***************\n";

		
		//***********************/
		//*********MODULE********/
		//***********************/

		$sql = "SELECT 
				module_def_id
				, module_value
				, param_def_id
				, param_value
			FROM device_provisioning_link
			WHERE device_provisioning_link.device_provisioning_id = " . $sanitisedInput['provisioning_id'] . "
			ORDER BY module_def_id ASC";
		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		//	This seems odd but its necessary. 
		//	We want three things here:
		//		a) an array of the module and param IDs already associated with this provisioning ID
		//		b) an array of the module and param IDs that we are going to be inserting
		//		c) an array of the TOTAL module and param IDs used for this provisioning ID, ie. a merge of a) and b)
		//
		//	We need a) to compare new sensor type inserts against, ie. if they are inserting a GPS type sensor then we need to find if the provisioning ID already has a GPS type sensor, 
		//		and if so what module ID it is
		//	If we are inserting a sensor ID that does not have a pre-existing module ID for this sensor type, and we are also not provided with a specific module ID then we need c)
		//		Loop through sorted c) list to find the next lowest INT for module ID. 
		//		NOTE: TODO: Check if the module_ID is supposed to be specific for sensor types w/ Tim. This could cause issue if not performed properly

		//	Additionally it might seem odd to declare the array values as [$val] = $val, instead of [] = $val
		//	This API will require a decent chunk of array searching to find what values do or do not exist within arrays. 
		//	The searching function in_array is significantly slower than isset or array_key_exists, from here: https://gist.github.com/alcaeus/536156663fac96744eba77b3e133e50a
		//	As such we will declare the array variables in such a way as to utilise isset instead of in_array
		//		Please note that this means we also need to sort the arrays with asort() instead of sort() in order to preserve the keys
		
		//	Arrays ahoy~
		//	Abandon hope all ye who enter here

		$errorArray = array();

		if (isset($rows[0][0])) {
			$compareArray['module_id'] = array();
			$compareArray['param_id'] = array();
			$compareArray['module_value'] = array();
			$compareArray['module_value_flipped'] = array();
			$compareArray['param_value'] = array();

			foreach ($rows as $row) {
				$compareArray['module_id'][$row[0]] = $row[0];
				$compareArray['param_id'][$row[2]] = $row[2];
				$compareArray['module_value'][$row[0]] = $row[1];
				$compareArray['module_value_flipped'][$row[1]] = $row[0];
				$compareArray['param_value'][$row[0]][$row[3]] = $row[3];
			}

			//	$compareArray[a)]
			$compareArray['module_id'] = array_unique($compareArray['module_id']);
			$compareArray['param_id'] = array_unique($compareArray['param_id']);

			//	$compareArray[b)]
			$compareArray['full_module_id'] = $compareArray['module_id'] + $sanitisedInput['full_module_id'];
			$compareArray['full_param_id'] = $compareArray['param_id'] + $sanitisedInput['full_param_id'];
	
			//	TODO check this. I have a funny feeling this potentially could be an issue just declaring things like this
			$compareArray['full_param_value'] = $compareArray['param_value'] + $sanitisedInput['param_value'];

		}
		else {
			//	Case where this provisioning ID doesn't exist in database
			$compareArray['full_module_id'] = array_unique($sanitisedInput['full_module_id']);
			$compareArray['full_param_id'] = $sanitisedInput['full_param_id'];
			$compareArray['full_module_value'] = $sanitisedInput['full_module_value'];
			$compareArray['full_param_value'] = $sanitisedInput['param_value'];
			$compareArray['module_value'] = array();
		}

		//	Regardless of if this is a new or pre-existing provisioning ID we still need a list of all of the module values, both existing ones and ones we're inserting now
		//	While we could use the 'full_module_value' list we've created (as its of form $module_id => $module_value) this would require the use of a significantly slower search method
		//	Depending on the size of the query there could be a *lot* of searchng necessary. As such its faster to make a new array and push each 'full_module_values' value to a new array
		//		As we will be searching for both the module_values by module_id and by INT value we still need both of these arrays, hence ending up with two faster search arrays compared to one slow one.
		
		foreach ($sanitisedInput['full_module_value'] as $module_id => $throwaway) {
			foreach ($sanitisedInput['full_module_value'][$module_id] as $key => $module_value){
				if (isset($compareArray['module_value'][$module_id])) {
					if ($compareArray['module_value'][$module_id] == $module_value) {
						$compareArray['full_module_value'][$module_id] = $module_value;
					}
					else {
						$sanitisedInput['full_module_value'][$module_id] == $compareArray['module_value'][$module_id]?: $errorArray['db_request_module_value_difference'][$module_id] = $module_id;
					}
				}
				else {
					//	If the module ID is not already used in the database then we need to check if its module value is already used
					//	If yes then push to the error array
					//	If no then its fine
					isset($compareArray['module_value_flipped'][$module_value])? 
						(($errorArray['db_module_value_already_used'][$module_id]["Request"] = $module_value)
							&& ($errorArray['db_module_value_already_used'][$module_id]["db"] = $compareArray['module_value_flipped'][$module_value]))
						: $compareArray['full_module_value'][$module_id] = $module_value;				
				}
			}
		}
		
		//print_r($compareArray['full_module_value']);

		//	We need to make a list of ALL the used module values so we can check against it to determine free module values
		if (isset($compareArray['full_module_value'])) {
			//	Add the request values to the list
			foreach ($compareArray['full_module_value'] as $key => $value) {
				//	TODO HERE if not NOT SUPPLIED then go for it else do nothing
				$compareArray['list_module_value'][$value] = $value;
			}
			//	Add the values on the server to the list
			foreach ($compareArray['module_value'] as $key => $value) {
				$compareArray['list_module_value'][$value] = $value;
			}
		}

		//	NOTE: It is up to the user to ensure they are putting in the correct sensor type to the correct module ID
		//		There is no sensory type sanitisation check, you are absolutely free to cock it up with a temp sensor identified as GPS if you wish (for example)
		foreach ($sanitisedInput['module_id'] as $sd_id => $module_id) {
			$module_value = $sanitisedInput['module_value'][$sd_id];
			//echo "Now working on: $module_value\t module id: $module_id\n\n";

			//	What we do here depends on if there is any preexisting module_value for this module_id
			
			//	Check here to see if this value is already being used.
			//	If this module id already has these module values then throw error. Accumulate all errors and spit them out after we've processed them all

			//	Search through $module_id array to find if this already exists
			//	If it doesn't exist, or if val == "NOT_SUPPLIED" then find the next unusued value in module_id and set it to that. 
			//		If this is done then we must add the new value and its associated sensor type to the array for the next loop, otherwise could come up with compounding errors down the line
			
			//echo "LOOKING FOR MODULE VALUE: $module_value\tmodule_id: $module_id\tsd_id: $sd_id\n";

			if ($module_value != "NOT_SUPPLIED" ) {
				//	For most queries the 'full_module_value' array will be filled already so no need to add a check,
				//		however if ALL of the inserting sd_id modules have an incorrect module value when compared to the ones on the server we will hit an error
				//	As such, we expect to cascade to the error case below, so we need to avoid hitting the php runtime error here by adding the isset (which will fail), skipping the check
				
				if (isset($compareArray['full_module_value'])){
					!isset($compareArray['full_module_value'][$module_id])?
						: $sanitisedInput['module_value'][$sd_id] = $compareArray['full_module_value'][$module_id];
				}
			}
			else {
				//	If the user has not supplied a module value either we already know it (given in database OR given elsewhere in request for another sd_id with the same module_id)
				//		or we need to assign a free module_value to this module INT
				if (isset($compareArray['full_module_value'][$module_id])){
					$sanitisedInput['module_value'][$sd_id] = $compareArray['full_module_value'][$module_id];
				}
				else{
					//	This searches through the given INT 
					$searchINT = 1;
					while ($searchINT != 0) {
						//	echo "loop: $searchINT\tmodule_id: $module_id\tsd_id: $sd_id\n";
						if (isset($compareArray['list_module_value'][$searchINT])) {
							$searchINT++;
						}
						else {
							//	On success, three things are saved here
							//	a) list_module_value -> Add module_value/searchINT to the list of module_value/searchINT that are used
							//	b) full_module_value -> Saves the association between module_id and module_value/searchINT
							//	c) module_value -> Updates the actual output array to reflect the new module_value/searchINT
							$compareArray['list_module_value'][$searchINT] = $searchINT;
							$compareArray['full_module_value'][$module_id] = $searchINT;
							$sanitisedInput['module_value'][$sd_id] = $searchINT;
							BREAK;
						}
					}
				}
			}
		}


		//*******************ERROR HANDLING*********************** */

		$errorMsg == "";
		!isset($errorArray['db_module_value_already_used'])?: $errorMsg = "MODULE_VALUE_ALREADY_ASSOCIATED_WITH_EXISTING_MODULE_ID";
		!isset($errorArray['db_request_module_value_difference'])?: $errorMsg = "EXISTING_MODULE_ID_AND_INSERTING_MODULE_ID_HAVE_DIFFERENT_MODULE_VALUE";

		if ($errorMsg != "") {

			switch ($errorMsg) {
				CASE "EXISTING_MODULE_ID_AND_INSERTING_MODULE_ID_HAVE_DIFFERENT_MODULE_VALUE" :

					foreach ($errorArray['db_request_module_value_difference'] as $module_id => $throwaway) {
						$db_module_value = $compareArray['module_value'][$module_id];
						$errorArray['output']["Module ID: " . $module_id]["Given module value"] = $db_module_value;
						$errorArray['output']["Module ID: " . $module_id]["Expected module value"] = $db_module_value;
						foreach ($sanitisedInput['module_id_to_module_value'][$module_id] as $module_value => $throwaway){
							if ($module_value != "NOT_SUPPLIED"){
								$errorArray['output']["Module ID: " . $module_id]["Given module value"] = $module_value;
								foreach ($sanitisedInput['module_id_to_module_value'][$module_id][$module_value] as $sd_id) {
									$errorArray['output']["Module ID: " . $module_id]["Erroneous sd_id"][] = "sd_id: " . $sd_id;
								}
							}
						}
					}

					BREAK;

				CASE "MODULE_VALUE_ALREADY_ASSOCIATED_WITH_EXISTING_MODULE_ID":
				
					foreach ($errorArray['db_module_value_already_used'] as $module_id => $throwaway) {
						$db = $errorArray['db_module_value_already_used'][$module_id]['db'];
						$request = $errorArray['db_module_value_already_used'][$module_id]['Request'];
						$errorArray['output']['Module ID: ' . $module_id]["Request module value"] = $request;
						$errorArray['output']["Module ID: " . $module_id]["Module value " . $request . " belongs to"] = "Module ID " . $db;
						foreach ($sanitisedInput['module_id_to_module_value'][$module_id] as $module_value => $throwaway){
							foreach ($sanitisedInput['module_id_to_module_value'][$module_id][$module_value] as $sd_id) {
								$errorArray['output']["Module ID: " . $module_id]["Erroneous sd_id"][] = "sd_id: " . $sd_id;
							}
						}
					}

					BREAK;
				
			}
	
			$output = json_encode(
				array(
				'error' => $errorMsg,
				'error_detail' => $errorArray['output']
				));
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}
	
		//	We want to get all the existing param IDs and how they are associated with each module
		$sql = "SELECT 
				module_def.id
				, param_def.module_def_id
				, param_def.id
			FROM 
				module_def
			LEFT JOIN param_def ON param_def.module_def_id = module_def.id
			ORDER BY module_def.id ASC";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		if (!isset($rows[0][0])) {
			//	This error should never be reached by the user. 
			//	Its just a catch in the case that nothing is returned for some reason. Random code given so we can ctrl+f find it
			errorGeneric("General_error_contact_usm_CODE_1193", $API, $logParent);
		}

		//	Its possible for a module ID to exist without having its corrosponding value on the param_def table.
		//	As such we need to find all the values where this is a valid module_id and what its corrosponding param_id(s) are or if it doesn't have any
		
		//	'valid_module_id' 					== Module ID that occur on module_def database table
		//	'valid_module_id_with_param_id' 	== Module ID that occur on the the param_def table, ie. have an associated param_def
		//	'db_param_id_module'				== The param_def table ID that the module ID is associated with
		//	'db_param_id_module_by_module_id' 	== The param def table ID that the module ID is associated with, but seperated and sorted by module ID

		$compareArray['db_param_id_module'] = array();
		foreach ($rows as $row) {
			
			isset($compareArray['valid_module_id'][$row[0]])?: $compareArray['valid_module_id'][$row[0]] = $row[0];

			//	If we have a value in row[1], ie. this exists on the param_def table
			if (isset($row[1])){
				$compareArray['valid_module_id_with_param_id'][$row[1]] = $row[1];			
				$compareArray['db_param_id_module'][$row[2]] = $row[1];
				$compareArray['db_param_id_module_by_module_id'][$row[1]][$row[2]] = $row[2];
			}
		}

		//	Now we have an array of all the used param IDs on the database and what module they are connected to

		//	Check to make sure all the input param_id are valid param_id values

		//	Now I need to populate an array with the already existing params on db combined with the ones I am hoping to use in this query.
		//	This will be checked against in the following for each loop to ascertain what a 'safe' array int is to assign in case of "not_supplied"

		//	Sort through the parm_id and find what is already there for the db

		//	We need to remove all the param IDs that currently exist in the database and in the request from the 'db_param_id_module_by_module_id' array
		//	This leaves us with an array consisting SOLELY of the values that are free, sorted by module ID. This can then be used to give out / associate with any param ID that is "NOT_SUPPLIED"
		//	Note that this is only necessary if we have any param ID that is "NOT_SUPPLIED"

		if (isset($sanitisedInput['full_param_id']['NOT_SUPPLIED'])){
			foreach ($sanitisedInput['param_id'] as $sd_id => $param_id ) {
				if ($param_id != "NOT_SUPPLIED"){
					$module_id = $sanitisedInput['module_id'][$sd_id];
					if (isset($compareArray['db_param_id_module_by_module_id'][$module_id][$param_id])){
						unset($compareArray['db_param_id_module_by_module_id'][$module_id][$param_id]);
					}				
				}
			}
		}

		//	This will loop through the array of available param values in the 'db_param_id_module_by_module_id' array and 
		//		unset any that are already used in the database. 
		if (isset($compareArray['param_id'])){
			foreach ($sanitisedInput['full_module_id'] as $module_id) {
				foreach ($compareArray['param_id'] as $param_id => $throwaway){
					unset($compareArray['db_param_id_module_by_module_id'][$module_id][$param_id]);
				}
			}
		}

		$errorArray = array();

		//	This big loop does a couple things
		//	It checks the inputs the find if they have got a param value and a param id assigned to them
		//		It checks if the param value is used elsewhere in the database, erroring out if so
		//	If a param value is not given it will accumulate all the param values for this module ID, both in the database and elsewhere in the request
		//		It will then find the lowest possible INT and assign this value
		//	It finds the free param ID for the given module ID and assigns them to values that do not have a param ID
		//		If it runs out of free param ID it errors out, indicating the sd_id that have missed out on a param ID
		//	It made me feel sad to write :(
		//		I've dove into this section way too many times -Conor
		foreach ($sanitisedInput['param_id'] as $sd_id => $param_id){
			$module_id = $sanitisedInput['module_id'][$sd_id];
			$param_value = $sanitisedInput['param_value'][$sd_id];
			//echo "Now checking sd_id: $sd_id\tmodule_id: $module_id\tparam_id: $param_id\tparam_value: $param_value\n\n";
			
			isset($sanitisedInput['param_value_by_module_id'][$module_id])?: $sanitisedInput['param_value_by_module_id'][$module_id] = array();

			//	Check if this is a valid module ID
			isset($compareArray['valid_module_id'][$module_id])?: $errorArray['invalid_module_id']["Module ID: " . $module_id][] = "sd_id " . $sd_id;
			//	Check if this module ID has param ID(s) associated with it
			isset($compareArray['valid_module_id_with_param_id'][$module_id])?: $errorArray['invalid_module_no_param']["Module ID: " . $module_id][] = "sd_id " . $sd_id;
			
			//	Check if this param value is a valid param value
			if ($param_id != "NOT_SUPPLIED"){
				if (isset($compareArray['param_id'][$param_id])){
					!isset($compareArray['param_id'][$param_id])?: $errorArray['duplicate_db_request_param_id']["sd_id " . $sd_id] = "Param ID " . $param_id;
				}
				$expected_module_id = -1;
				isset($compareArray['db_param_id_module'][$param_id])?
					$expected_module_id = $compareArray['db_param_id_module'][$param_id]
					: $errorArray['invalid_param_id']["sd_id " . $sd_id] = "Param ID " . $param_id;

				//	If the an expected module value is given, check if the expected is the same as the given
				if ($expected_module_id != -1) {
					$module_id == $expected_module_id?: $errorArray['incorrect_module_id_param_id']["Module ID: " . $module_id][] = "sd_id: " . $sd_id;
				}
			}
			else {
				//	Need check to see if there are no associatable free numbers available.
				if (count($compareArray['db_param_id_module_by_module_id'][$module_id]) == 0) {
						//	If we have no more valid param_values to associate based on the module ID then we throw the extras to an error array.
						//		It is now up to the user to either:
						//			a) change the module ID of these sd_id
						//			b) change the module ID of other sd_id in database or request to free up some param ID for this module ID
						//			c) create new param ID for this module ID
					$errorArray['invalid_param_no_module']["module_id: " . $module_id][] = "sd_id: " . $sd_id;
				}
				else {
					//	If there is at least one free param ID not associated yet
					
					//	We only unset if the param value is NOT SUPPLIED to avoid running out of available values. 
					//		For further reason see else loop below for detail info
					//	Find the lowest / min value in this array
					//	Assign it to the sanitised input sd_id
					//	Unset / remove it from the list so it is not erroneously duplicate associated next loop

					$lowestValue = min($compareArray['db_param_id_module_by_module_id'][$module_id]);
					$sanitisedInput['param_id'][$sd_id] = $lowestValue;
					unset($compareArray['db_param_id_module_by_module_id'][$module_id][$lowestValue]);
					
					//echo "reached this location with param ID $param_id sd id $sd_id, setting param id to $lowestValue\n\n\n";
				}
				
			}
			//	If we have an isset 'param_values' we must have a provisioning ID already in the database
			//		If so, then we need to check each given param value against the already associated values to make sure we are not doubling up
			//		If a value is NOT given, then we need to find what value is NOT already associated (or intended association in the request query) and assign it the lowest free value possible
			//	If 'param_values' is NOT set then we do not have a provisioning ID already in database, as we
			//		already have a function in place to error out on duplicate param ID values in the request we can just take the values as given, and associate min values to values without param ID

			//	No need to differentiate between values that are or are not in the database ehre

			if ($param_value != "NOT_SUPPLIED"){
				!isset($compareArray['param_value'][$module_id][$param_value])?
					: $errorArray['param_value_already_associated']["Module ID: " . $module_id]["Param value: " . $param_value] = "sd_id: " . $sd_id;
			}
			else {
				$searchINT = 1;
				//echo "NEW SEARCH\tsd_id: $sd_id\tmodule ID: $module_id\n";

				//	Loop through the sanitised Input array per module ID to find the lowest unassociated value and assign it that
				while ($searchINT != 0) {

					//echo "\t\tlooping: $searchINT\n";
					//	Check if we have either a) already associated this value on this module ID
					//		or, b) if this association already exists on the database side
					if (isset($sanitisedInput['param_value_by_module_id'][$module_id][$searchINT])
						|| isset($compareArray['param_value'][$module_id][$searchINT])) {
						//	If yes to either of these conditions, we cannot use this int, so ++ it and try again
						$searchINT ++;
					}
					else {
						//	We assign to both the final 'param_value' array and the searching 'param_value_by_module_id' array
						//		The later is to ensure that next loop that needs to search this module ID doesn't repeat numbers
						//echo "assigning $searchINT to $sd_id\n\n\n";
						$sanitisedInput['param_value'][$sd_id] = $searchINT;
						$sanitisedInput['param_value_by_module_id'][$module_id][$searchINT] = $sd_id;
						//	Break to leave the while loop
						BREAK;
					}
				}
			}
		}

		//*******************ERROR HANDLING*********************** */

		$errorMsg = "";
		//	Reorder these to change the priority of the error message
		//		That said, please keep the order of 'MODULE_ID_HAS_NO_FREE_PARAM_ID_ASSOCIATIONS' and '', as its potentially an issue if this order is not upheld
		//	This differs from the other error sections because... it was coded on a different day and I didn't realise till later
		!isset($errorArray['invalid_param_no_module'])?: $errorMsg = "MODULE_ID_HAS_NO_FREE_PARAM_ID_ASSOCIATIONS";
		!isset($errorArray['param_value_already_associated'])?: $errorMsg = "PARAM_VALUE_ALREADY_ASSOCIATED";
		!isset($errorArray['invalid_param_id'])?: $errorMsg = "PARAM_ID_DOES_NOT_EXIST";
		!isset($errorArray['duplicate_db_request_param_id'])?: $errorMsg = "PARAM_ID_ALREADY_UTILISED";
		!isset($errorArray['incorrect_module_id_param_id'])?: $errorMsg = "MODULE_ID_IS_NOT_ASSOCIATED_WITH_GIVEN_PARAM_ID";
		!isset($errorArray['invalid_module_no_param'])?: $errorMsg = "MODULE_ID_HAS_NO_PARAM_ID_ASSOCIATIONS";
		!isset($errorArray['invalid_module_id'])?: $errorMsg = "MODULE_ID_DOES_NOT_EXIST";

		if ($errorMsg != ""){

			//print_r($errorArray);

			switch ($errorMsg) {
				CASE "MODULE_ID_HAS_NO_FREE_PARAM_ID_ASSOCIATIONS":
					$errorArray['output'] = $errorArray['invalid_param_no_module'];
					BREAK;

				CASE "PARAM_VALUE_ALREADY_ASSOCIATED":
					$errorArray['output'] = $errorArray['param_value_already_associated'];
					BREAK;
					
				CASE "PARAM_ID_DOES_NOT_EXIST":
					$errorArray['output'] = $errorArray['invalid_param_id'];
					BREAK;

				CASE "PARAM_ID_ALREADY_UTILISED":
					$errorArray['output'] = $errorArray['duplicate_db_request_param_id'];
					BREAK;

				CASE "MODULE_ID_IS_NOT_ASSOCIATED_WITH_GIVEN_PARAM_ID":
					$errorArray['output'] = $errorArray['incorrect_module_id_param_id'];
					BREAK;

				CASE "MODULE_ID_HAS_NO_PARAM_ID_ASSOCIATIONS":
					$errorArray['output'] = $errorArray['invalid_module_no_param'];
					BREAK;

				CASE "MODULE_ID_DOES_NOT_EXIST":
					$errorArray['output'] = $errorArray['invalid_module_id'];
					BREAK;
			}

			$output = json_encode(
				array(
				'error' => $errorMsg,
				'error_detail' => $errorArray['output']
				));
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}
	}

	//	echo "\n*****************MODULE AND PARAM SECTION PASSED***************\n";
	
	$data = array();

	//	Declaring this here so it orders correctly on output
	$insertArray['provisioning_id'] = $sanitisedInput['provisioning_id'];

	foreach ($sanitisedInput['sd_id'] as $sd_id) {
		//	Push to string for insert
		$data[] = "( '" . $sanitisedInput['provisioning_id'] . 
			"', '" . $sd_id . 
			"', '" . $sanitisedInput['bytelength'][$sd_id] . 
			"', '" . $sanitisedInput['module_id'][$sd_id] . 
			"', '" . $sanitisedInput['module_value'][$sd_id] . 
			"', '" . $sanitisedInput['param_id'][$sd_id] . 
			"', '" . $sanitisedInput['param_value'][$sd_id] . 
			"', '" . $sanitisedInput['calib_id'][$sd_id] . 
			"', '" . $sanitisedInput['sendvia'][$sd_id] . 
			"', '" . $sanitisedInput['active_status'][$sd_id] . 
			"', '" . $user_id . 
			"', '" . $timestamp . 
			"' )";

		//	Push to array for echo to user and log
		$insertArray['values'][] = array(
			"sd_id" => $sd_id
			, "module_id" => $sanitisedInput['module_id'][$sd_id]
			, "module_value" => $sanitisedInput['module_value'][$sd_id]
			, "param_id" => $sanitisedInput['param_id'][$sd_id]
			, "param_value" => $sanitisedInput['param_value'][$sd_id]
			, "active_status" => $sanitisedInput['active_status'][$sd_id]
			, "sendvia" => $sanitisedInput['sendvia'][$sd_id]
			, "calib_id" => $sanitisedInput['calib_id'][$sd_id]
		);

	}


	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try{
		$sql = "INSERT INTO device_provisioning_link(
			`device_provisioning_id`
			, `sensor_def_sd_id`
			, `sensor_def_data_type`
			, `module_def_id`
			, `module_value`
			, `param_def_id`
			, `param_value`
			, `calib_def_id`
			, `sendvia`
			, `active_status`
			, `last_modified_by`
			, `last_modified_datetime`)
		VALUES " . implode(", ", $data);

		$stm= $pdo->prepare($sql);
		if($stm->execute()){
			$insertArray ['error' ] = "NO_ERROR";
			echo json_encode($insertArray);
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
		}

	}catch(PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}

	//	Wipe $data just incase
	unset($data); 

	// Update device version
	$data = [   
		'provisioning_id' => $insertArray['provisioning_id']
		, 'last_modified_by' => $user_id
		, 'last_modified_datetime' => gmdate("Y-m-d H:i:s")
	];

	$sql = "UPDATE 
			devices 
		SET configuration_version = (configuration_version + 1)
			, last_modified_by = :last_modified_by
			, last_modified_datetime = :last_modified_datetime 
		WHERE device_provisioning_device_provisioning_id = :provisioning_id 
		AND active_status = 0";		

	$stmt= $pdo->prepare($sql);
	if ($stmt->execute($data)){
		logEvent($API . logText::response . str_replace('"', '\"', "{\"Success\":\"device configuration version updated +1\"}"), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"device configuration version update failed\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}
}


// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************


elseif ($sanitisedInput['action'] == 'update'){

	die("This action is not supported");

}


// *******************************************************************************
// *******************************************************************************
// *****************************DELETE********************************************
// *******************************************************************************
// *******************************************************************************


elseif ($sanitisedInput['action'] == 'delete'){

	// Provisioning ID

	//	Can delete by providing one of several options
	//	device provisioning link table ID
	//	sd_id
	//	param id
	//	(module id or module value) AND param value

	//	Regardless of chosen method we eventually want to get the device provisioning table ID and delete by that


	if (isset($inputarray['provisioning_link_id'])
		|| isset($inputarray['module_value'])
		|| isset($inputarray['module_id'])
		|| isset($inputarray['param_id'])
		|| isset($inputarray['sd_id'])
		){
		errorMissing("identification", $API, $logParent);
	}

	if (isset($inputarray['provisioning_id'])) {
		$sanitisedInput['provisioning_id'] = sanitise_input($inputarray['provisioning_id'], "provisioning_id", null, $API, $logParent);
	}
	else {
		errorMissing("provisioning_id", $API, $logParent);
	}

	if (isset($inputarray['provisioning_link_id'])) {
		$sanitisedInput['provisioning_link_id'] = sanitise_input_array($inputarray['provisioning_link_id'], "provisioning_link_id", null, $API, $logParent);
	}

	if (isset($inputarray['module_value'])) {
		$sanitisedInput['module_value'] = sanitise_input_array($inputarray['module_value'], "module_value", null, $API, $logParent);
	}

	if (isset($inputarray['module_id'])) {
		$sanitisedInput['module_id'] = sanitise_input_array($inputarray['module_id'], "module_id", null, $API, $logParent);
		$sql = "SELECT 
				id
			FROM
				module_def
			WHERE id IN ( " . implode(", ", $sanitisedInput['module_id']) . " )";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($rows[0][0])) {
			errorInvalid("module_id", $API, $logParent);
		}
		//	Check to make sure all the input module_id are valid module_id values
		arrayExistCheck ($sanitisedInput['module_id'], array_column($rows, 0), "module_id", $API, $logParent);
	
	}

	if (isset($inputarray['param_id'])) {
		$sanitisedInput['param_id'] = sanitise_input_array($inputarray['param_id'], "param_id", null, $API, $logParent);
		$sql = "SELECT 
				id
			FROM
				param_def
			WHERE id IN ( " . implode(", ", $sanitisedInput['param_id']) . " )";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($rows[0][0])) {
			errorInvalid("param_id", $API, $logParent);
		}
		//	Check to make sure all the input param_id are valid param_id values
		arrayExistCheck ($sanitisedInput['param_id'], array_column($rows, 0), "param_id", $API, $logParent);
	}

	if (isset($inputarray['sd_id'])) {
		$sanitisedInput['sd_id'] = sanitise_input_array($inputarray['sd_id'], "sd_id", null, $API, $logParent);
		$sql = "SELECT 
				sd_id
			FROM
				sensor_def
			WHERE sd_id IN ( " . implode(", ", $sanitisedInput['sd_id']) . " )";

		$stm = $pdo->query($sql);
		$rows = $stm->fetchAll(PDO::FETCH_NUM);

		if (!isset($rows[0][0])) {
			errorInvalid("sd_id", $API, $logParent);
		}
		//	Check to make sure all the input sd_id are valid sd_id values
		arrayExistCheck ($sanitisedInput['sd_id'], array_column($rows, 0), "sd_id", $API, $logParent);
	}

	$sql = "SELECT 
			device_provisioning.id
			, device_provisioning_link.id
			, device_provisioning_link.module_def_id
			, device_provisioning_link.module_value
			, device_provisioning_link.param_def_id
			, device_provisioning_link.sensor_def_sd_id
		FROM
			device_provisioning
		LEFT JOIN device_provisioning_link
			ON ( device_provisioning_link.device_provisioning_id = device_provisioning.id ";

	!isset($sanitisedInput['provisioning_link_id'])?
		: $sql .= "AND device_provisioning_link.id IN ( '" . implode ("', '", $sanitisedInput['provisioning_link_id']) . "' )";

	!isset($sanitisedInput['module_id'])?
		: $sql .= "AND device_provisioning_link.module_def_id IN ( '" . implode ("', '", $sanitisedInput['module_id']) . "' )";
	
	!isset($sanitisedInput['module_value'])?
		: $sql .= "AND device_provisioning_link.module_value IN ( '" . implode ("', '", $sanitisedInput['module_value']) . "' )";

	!isset($sanitisedInput['param_id'])?
		: $sql .= "AND device_provisioning_link.param_def_id IN ( '" . implode ("', '", $sanitisedInput['param_id']) . "' )";

	!isset($sanitisedInput['sd_id'])?
		: $sql .= "AND device_provisioning_link.sensor_def_sd_id IN ( '" . implode ("', '", $sanitisedInput['sd_id']) . "' )";

	$sql .= " ) WHERE device_provisioning.id = " . $sanitisedInput['provisioning_id'];

	$stm = $pdo->query($sql);
	$rows = $stm->fetchAll(PDO::FETCH_NUM);

	if (!isset($rows[0][0])) {
		errorInvalid("provisioning_id", $API, $logParent);
	}

	if (isset($rows[0][0])
		&& !isset($rows[0][1])) {
		errorInvalid("provisioning_link_provisioning_id", $API, $logParent);
	}

	//	Declare here to get the ordering right
	$deleteArray['action'] = "delete";
	$deleteArray['provisioning_id'] = $sanitisedInput['provisioning_id'];

	if (isset($sanitisedInput['provisioning_link_id'])){
		arrayExistCheck ($sanitisedInput['provisioning_link_id'], array_column($rows, 5), "provisioning_link_id", $API, $logParent);
		$deleteArray['provisioning_link_id'] = $sanitisedInput['provisioning_link_id'];
	}

	if (isset($sanitisedInput['module_id'])){
		arrayExistCheck ($sanitisedInput['module_id'], array_column($rows, 5), "provisioning_link_module_id", $API, $logParent);
		$deleteArray['module_id'] = $sanitisedInput['module_id'];
	}

	if (isset($sanitisedInput['module_value'])){
		arrayExistCheck ($sanitisedInput['module_value'], array_column($rows, 5), "provisioning_link_module_value", $API, $logParent);
		$deleteArray['module_value'] = $sanitisedInput['module_value'];
	}

	if (isset($sanitisedInput['param_id'])){
		arrayExistCheck ($sanitisedInput['param_id'], array_column($rows, 5), "provisioning_link_param_id", $API, $logParent);
		$deleteArray['param_id'] = $sanitisedInput['param_id'];
	}

	if (isset($sanitisedInput['sd_id'])){
		arrayExistCheck ($sanitisedInput['sd_id'], array_column($rows, 5), "provisioning_link_sd_id", $API, $logParent);
		$deleteArray['sd_id'] = $sanitisedInput['sd_id'];
	}

	$deleteArray['id'] = array_column($rows, 1);

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($deleteArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

	try
	{	
		$sql = "DELETE FROM 
				`device_provisioning_link` 
			WHERE `id` IN (" . implode (", ", $deleteArray['id']) . ")";

		$stmt= $pdo->prepare($sql);                   
		if($stmt->execute()){
			
			$deleteArray['error' ] = "NO_ERROR";
			logEvent($API . logText::response . str_replace('"', '\"', json_encode($deleteArray)), logLevel::response, logType::response, $token, $logParent);
			unset($deleteArray['id']);
			echo json_encode($deleteArray);
		
		}
		
	}catch(\PDOException $e){
		logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
		die("{\"error\":\"$e\"}");
	}	

	// Update device version
	$data = [   
		'provisioning_id' => $sanitisedInput['provisioning_id']
		, 'last_modified_by' => $user_id
		, 'last_modified_datetime' => gmdate("Y-m-d H:i:s")
	];

	$sql = "UPDATE 
			devices 
		SET configuration_version = (configuration_version + 1)
			, last_modified_by = :last_modified_by
			, last_modified_datetime = :last_modified_datetime 
		WHERE device_provisioning_device_provisioning_id = :provisioning_id 
		AND active_status = 0";		

	$stmt= $pdo->prepare($sql);
	if ($stmt->execute($data)){
		logEvent($API . logText::response . str_replace('"', '\"', "{\"Success\":\"device configuration version updated +1\"}"), logLevel::response, logType::response, $token, $logParent);
	}
	else {
		logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"device configuration version update failed\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
	}

}

else{
		logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
		errorInvalid("request", $API, $logParent);
}


		
$pdo = null;
$stm = null;

?>