<?php 
    //  asset definition
    $API = "AssetCustomParameter";
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

    if ($user_roles != '1'){
        logEvent($API . logText::requestInsufficientPermission, logLevel::requestError, logType::requestError, $token, $logParent);
        die ("{\"error\":\"ACCESS_DENIED\"}");
    }

	checkKeys($inputarray, $API, $logParent);

	if (isset($inputarray['action'])){
        $sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, $API, $logParent);
		$logParent = logEvent($API . logText::action . ucfirst($sanitisedInput['action']), logLevel::action, logType::action, $token, $logParent)['event_id'];
	}
	else{
		errorInvalid("request", $API, $logParent);
	}

if ($sanitisedInput['action'] == "select"){

	$schemainfoArray = getMaxString("asset_custom_param", $pdo);

    $sql = "SELECT `id`
        , `name`
        , `tag_name`
        , `default_value`   
        , `active_status`
        , `last_modified_by`
        , `last_modified_datetime`
        FROM asset_custom_param
        WHERE 1 =1";

    if (isset($inputarray['id'])){
		$sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
		$sql .= " AND `id` IN (" . implode( ', ',$sanitisedInput['id'] ) . ")";
	}

    if (isset($inputarray['name'])) {
		$sanitisedInput['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
		$sql .= " AND `name` = '". $sanitisedInput['name'] ."'";
	}

    if (isset($inputarray['tag_name'])) {
        $sanitisedInput['tag_name'] = sanitise_input($inputarray['tag_name'], "tag_name", $schemainfoArray['tag_name'], $API, $logParent);
        $sql .= " AND `tag_name` = '". $sanitisedInput['tag_name'] ."'";
    }

    if (isset($inputarray['active_status'])) {
		$sanitisedInput['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
		$sql .= " AND `active_status` = '". $sanitisedInput['active_status'] ."'";
	}

	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    $stm = $pdo->query($sql);
    $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
    if (isset($dbrows[0][0])){
        $json_parent = array ();
        $outputid = 0;
        foreach($dbrows as $dbrow){
            $json_child = array(
            "id" => $dbrow[0]
            , "name" => $dbrow[1]
            , "tag_name" => $dbrow[2]
            , "default_value" => $dbrow[3]
            , "active_status" => $dbrow[4]
            , "last_modified_by" => $dbrow[5]
            , "last_modified_datetime" => $dbrow[6]);
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

else if ($sanitisedInput['action'] == "insert"){

	$insertArray = [];
	$schemainfoArray = getMaxString ("asset_custom_param", $pdo);

	if (isset($inputarray['name'])) {
		$insertArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['name'], $API, $logParent);
	}
	else {
		errorMissing("name", $API, $logParent);
	}

	if (isset($inputarray['tag_name'])) {
		$insertArray['tag_name'] = sanitise_input($inputarray['tag_name'], "tag_name", $schemainfoArray['tag_name'], $API, $logParent);
	}
	else {
		errorMissing("tag_name", $API, $logParent);
	}

    if (isset($inputarray['default_value'])){
		$insertArray['default_value'] = sanitise_input($inputarray['default_value'], "default_value", $schemainfoArray['default_value'], $API, $logParent);
	}

	if (isset($inputarray['active_status'])) {
		$insertArray['active_status'] = sanitise_input($inputarray['active_status'], "active_status", null, $API, $logParent);
	}
	else {
		errorMissing("active_status", $API, $logParent);
	}
    
	$logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];

     try{
            $stm = $pdo->query("SELECT id FROM asset_custom_param where name = '" . $insertArray["name"] . "' OR tag_name = '". $insertArray['tag_name'] ."'");
            $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
            if (!isset($dbrows[0][0])){               
                $sql = "INSERT INTO asset_custom_param(
                    `name`
                    , `tag_name`
                    , `default_value`
                    , `active_status`
                    , `last_modified_by`
                    , `last_modified_datetime`)
                VALUES (
                    :name
                    , :tag_name";
                    if (isset($insertArray['default_value'])){
                        $sql .= ", :default_value";
                    }
                    else {
                        $sql .= ", NULL";
                    }
                $sql .= "
                    , :active_status
                    , $user_id
                    , '$timestamp')";

                $stm= $pdo->prepare($sql);
                if($stm->execute($insertArray)){
                    $insertArray['id'] = $pdo->lastInsertId();
                   // $insertArray ['error' ] = "NO_ERROR";
                   // echo json_encode($insertArray);
                   // logEvent($API . logText::response . str_replace('"', '\"', json_encode($insertArray)), logLevel::response, logType::response, $token, $logParent);
                }
            }
            else{

                $insertArray['id'] = $dbrows[0][0];
                $insertArray['last_modified_by'] = $user_id;
                $insertArray['last_modified_datetime'] = $timestamp;

                $sql = "UPDATE asset_custom_param
                        SET name = :name,
                        tag_name = :tag_name, active_status = :active_status";

                if(isset($insertArray['default_value'])){
                    $sql .= " ,`default_value` = :default_value";  
                }
                            
                $sql .= " ,last_modified_by = :last_modified_by, last_modified_datetime = :last_modified_datetime  WHERE id = :id";

                //print_r($insertArray);
                //echo $sql;ss
                $stmt= $pdo->prepare($sql);
                if($stmt->execute($insertArray)){
                 }
            }

            if(isset($insertArray['id'])){
              $outputArray['id'] = $insertArray['id'];
            }

              $outputArray['name'] = $insertArray['name'];
              $outputArray['tag_name'] = $insertArray['tag_name'];
              $outputArray['active_status' ] =  $insertArray['active_status'];

            if(isset($insertArray['default_value'])){
                $outputArray['default_value'] = $insertArray['default_value'];
            }

            $outputArray ['error' ] = "NO_ERROR";
            echo json_encode($outputArray);
            logEvent($API . logText::response . str_replace('"', '\"', json_encode($outputArray)), logLevel::response, logType::response, $token, $logParent);      
        }
        catch(PDOException $e){
            logEvent($API . logText::responseError . str_replace('"', '\"', "{\"error\":\"$e\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
            die ("{\"error\":\"" . $e . "\"}");
        }
}


// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************

else if ($sanitisedInput['action'] == "update"){

	$updateArray = [];
	$updateString = "";

	$schemainfoArray = getMaxString("asset_custom_param", $pdo);

    if (isset($inputarray['id'])){
		$updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
    }
    else {
        errorMissing("id", $API, $logParent);
	}

    if (isset($inputarray['name'])){
		$updateArray['name'] = sanitise_input($inputarray['name'], "name", $schemainfoArray['asset_type'], $API, $logParent);
		$updateString .= " `name` = :name,";
    }

    if (isset($inputarray['tag_name'])){
		$updateArray['tag_name'] = sanitise_input($inputarray['tag_name'], "tag_name", $schemainfoArray['asset_type'], $API, $logParent);
		$updateString .= " `tag_name` = :tag_name,";
    }

    if (isset($inputarray['default_value'])){
		$updateArray['default_value'] = sanitise_input($inputarray['default_value'], "default_value", $schemainfoArray['default_value'], $API, $logParent);
		$updateString .= " `default_value` = :default_value,";
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
            $sql = "UPDATE 
                asset_custom_param 
                SET ". $updateString . " `last_modified_by` = $user_id 
				, `last_modified_datetime` = '$timestamp' 
                WHERE `id` = :id";
     
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
}

else{
    logEvent($API . logText::invalidValue . strtoupper($sanitisedInput['action']), logLevel::invalid, logType::error, $token, $logParent);
    errorInvalid("request", $API, $logParent);
} 

$pdo = null;
$stm = null;

?>