<?php 
$API = "ModuleDefinition";
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

    $schemainfoArray = getMaxString ("module_def", $pdo);

    $sql = "SELECT 
            id, 
            module_name
            FROM module_def
            WHERE 1=1";

    if (isset($inputarray['id'])) {		
        $sanitisedInput['id'] = sanitise_input_array($inputarray['id'], "id", null, $API, $logParent);
        $sql .= " AND `id` IN ( '" . implode("', '", $sanitisedInput['id']) . "' )";
    }

    if (isset($inputarray['module_name'])) {		
        $sanitisedInput['module_name'] = sanitise_input_array($inputarray['module_name'], "module_name",  $schemainfoArray["module_name"], $API, $logParent);
        $sql .= " AND `module_name` IN ( '" . implode("', '", $sanitisedInput['module_name']) . "' )";
    }

    $sql .= " ORDER BY id ASC";

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($sanitisedInput)), logLevel::request, logType::request, $token, $logParent)['event_id'];

    $stm = $pdo->query($sql);
    $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
    if(isset($dbrows[0][0])){
        $json_parent = array ();
        $outputid = 0;
        foreach($dbrows as $dbrow){
            $json_child = array(
            "id"=>$dbrow[0],
            "module_name"=>$dbrow[1]
            );

            $json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
            $outputid++;
        }

        $json = array("responses" => $json_parent);
        echo json_encode($json);
        logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
    }
    else{
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

    $schemainfoArray = getMaxString ("module_def", $pdo);
    $insertArray = [];

    if (isset($inputarray['module_name'])) {
        $insertArray['module_name'] = sanitise_input_array($inputarray['module_name'], "module_name", $schemainfoArray["module_name"], $API, $logParent);
        $sql = "SELECT module_name 
                FROM module_def 
                WHERE module_name IN ( '" . implode("', '", $insertArray['module_name']) ."' )";
        $stm = $pdo->query($sql);
        $rows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($rows[0][0])) {
            $count = count($rows);
            foreach ($insertArray['module_name'] as $key => $module_name){
                foreach ($rows as $key => $check) {
                    if (strcasecmp($check[0], $module_name) == 0) {
                        $errorArray['module_name'][] = $module_name;
                        $count --;  
                        BREAK;  
                    }
                }

                if ($count == 0) {
                    //  If we've found them all then no need to keep looping, break
                    BREAK;
                }
            }

        $output = json_encode(
            array(
            'error' => "MODULE_NAME_ALREADY_EXISTS",
            'error_detail' => $errorArray['module_name']
            ));
        logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
        die ($output);
        }
    }
    else {
        errorMissing("module_name", $API, $logParent);
    }

    $logParent = logEvent($API . logText::request . str_replace('"', '\"', json_encode($insertArray)), logLevel::request, logType::request, $token, $logParent)['event_id'];
    
    try{
                
        $sql = "INSERT INTO module_def (
                `module_name` )
            VALUES ( '" . implode("' ), ( '", $insertArray['module_name']) . "' )";

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
}


// *******************************************************************************
// *******************************************************************************
// *****************************UPDATE********************************************
// *******************************************************************************
// *******************************************************************************


else if ($sanitisedInput['action'] == "update"){

    $schemainfoArray = getMaxString ("module_def", $pdo);
    $updateArray = [];

    if (isset($inputarray['id'])){
        $updateArray['id'] = sanitise_input($inputarray['id'], "id", null, $API, $logParent);
        $sql = "SELECT id 
                FROM module_def 
                WHERE id = " . $updateArray['id'];
        $stm = $pdo->query($sql);
        $rows = $stm->fetchAll(PDO::FETCH_NUM);
        if (!isset($rows[0][0])) {
            errorInvalid("id", $API, $logParent);
        }
    }
    else{
        errorMissing("id", $API, $logParent);           
    }
    

    if (isset($inputarray['module_name'])) {
        $updateArray['module_name'] = sanitise_input($inputarray['module_name'], "module_name", $schemainfoArray["module_name"], $API, $logParent);
        $sql = "SELECT module_name 
                FROM module_def 
                WHERE module_name = '". $updateArray['module_name'] ."'";
        $stm = $pdo->query($sql);
        $rows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($rows[0][0])) {
            errorInvalid("MODULE_NAME_ALREADY_EXISTS", $API, $logParent);
        }
    }
    else{
        errorMissing("module_name", $API, $logParent);           
    }
    
    try {          
        $sql = "UPDATE 
                module_def 
                SET module_name = :module_name 
                WHERE id = :id";

        $stm= $pdo->prepare($sql);	
        if($stm->execute($updateArray)){
            $updateArray ['error'] = "NO_ERROR";
            echo json_encode($updateArray);
            logEvent($API . logText::response . str_replace('"', '\"', json_encode($updateArray)), logLevel::response, logType::response, $token, $logParent);
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