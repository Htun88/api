<?php   
$API = "DeviceAssetsTriggerDetail";
header('Content-Type: application/json');
include '../Includes/db.php';
include '../Includes/checktoken.php';
include '../Includes/sanitise.php';
include '../Includes/functions.php';
require_once '../Includes/utils.php';
use function UsmUtils\ {
    getUserAssetDetByUserId,
    getDeviceAssetDetByAssetId,
    getProvisioningIdByDeviceId,
    getProvisioningSensorDet,
    getTriggerSensorDefByTriggerId,
    filterTriggerIdBySource,
    getGeofenceIdByGeoTriggerId,
    upsertDeviceAssetTriggerDet,
    upsertDeviceAssetGeoDet,
    removeDeviceAssetTriggerDet,
    removeDeviceAssetGeoDet,
};

const API = "DeviceAssetsTriggerDetail";

$entitybody = file_get_contents('php://input');
$inputarray = json_decode($entitybody, true);

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $inputarray = null;
    $inputarray['action'] = "select";
}

$logParent = logEvent($API . logText::accessed, logLevel::accessed, logType::accessed, $token, null)['event_id'];
checkKeys($inputarray, $API, $logParent);

$sanitisedInput = [];

if (isset($inputarray['action'])) {
    $sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, $API, $logParent);
    if ($sanitisedInput['action'] === "update") errorInvalid("request", API, $logParent);
    $logParent = logEvent($API . logText::action . ucfirst($sanitisedInput['action']), logLevel::action, logType::action, $token, $logParent)['event_id'];
} else {
    errorInvalid("request", $API, $logParent);
}

/** error handler for functions live in utils.php */
$errorHandler = function($msg, $func) use (&$logParent) {
    $httpResp = ["error" => "DB_ERROR_IN_FUNCTION_" . $func];
    $log = [
        "error" => "DB_ERROR_IN_FUNCTION_" . $func,
        "message" => $msg,
    ];
    logEvent(
        API . logText::response . json_encode($log),
        logLevel::responseError, 
        logType::responseError, 
        TOKEN, 
        $logParent
    );
    die(json_encode($httpResp));
};

if ($sanitisedInput['action'] === "select") {

    $sql = "SELECT
            deviceassets_trigger_det.deviceassets_det
            , deviceassets_trigger_det.deviceassets_deviceasset_id
            , deviceassets_trigger_det.trigger_groups_trigger_id
            , deviceassets.devices_device_id
            , deviceassets.assets_asset_id
        FROM (
            users
            , user_assets
            , assets
            , deviceassets
            , deviceassets_trigger_det)
        LEFT JOIN userasset_details
        ON userasset_details.user_assets_user_asset_id = user_assets.user_asset_id
        WHERE deviceassets_trigger_det.deviceassets_deviceasset_id = deviceassets.deviceasset_id
        AND users.user_id = user_assets.users_user_id
        AND deviceassets.assets_asset_id = assets.asset_id
		AND deviceassets.date_to IS NULL
        AND user_assets.users_user_id = $user_id
        AND ((user_assets.asset_summary = 'some'
        AND assets.asset_id = userasset_details.assets_asset_id)
        OR (user_assets.asset_summary = 'all'))";

    if (isset($inputarray['deviceassets_det'])){
        $sanitisedInput['deviceassets_det'] = sanitise_input_array($inputarray['deviceassets_det'], "deviceassets_det", null, $API, $logParent);
        $sql .= " AND `deviceassets_trigger_det`.`deviceassets_det` IN (" . implode( ', ',$sanitisedInput['deviceassets_det'] ) . ")";
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
        $sql .= " AND `deviceassets_trigger_det`.`deviceassets_deviceasset_id` IN (" . implode( ', ',$sanitisedInput['deviceasset_id'] ) . ")";
    }

    if (isset($inputarray['trigger_id'])){
        $sanitisedInput['trigger_id'] = sanitise_input_array($inputarray['trigger_id'], "trigger_id", null, $API, $logParent);
        $sql .= " AND `deviceassets_trigger_det`.`trigger_groups_trigger_id` IN (" . implode( ', ',$sanitisedInput['trigger_id'] ) . ")";
    }

    $sql .= " ORDER BY `deviceassets_trigger_det`.`deviceassets_det` DESC";

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
        $json_deviceasset_trigger_det = array ();
        $outputid = 0;
        foreach($dbrows as $dbrow){
            $json_child = array(
            "deviceassets_det" => $dbrow[0]
            , "deviceasset_id" => $dbrow[1]
            , "trigger_id" => $dbrow[2]
            , "device_id" => $dbrow[3]
            , "asset_id" => $dbrow[4]);
            $json_deviceasset_trigger_det = array_merge($json_deviceasset_trigger_det, array("response_$outputid" => $json_child));
            $outputid++;
        }
        
        $json = array("responses" => $json_deviceasset_trigger_det);
        echo json_encode($json);
        logEvent($API . logText::response . str_replace('"', '\"', json_encode($json)), logLevel::response, logType::response, $token, $logParent);
    }
    else {
        logEvent($API . logText::response . str_replace('"', '\"', "{\"error\":\"NO_DATA\"}"), logLevel::responseError, logType::responseError, $token, $logParent);
        die ("{\"error\":\"NO_DATA\"}");
    }

    exit;
}

if (!isset($inputarray['asset_id']) || !isset($inputarray['trigger_id'])) {
    errorMissing("ASSET_ID_OR_TRIGGER_ID", API, $logParent);
}

$sanitisedInput['asset_id'] = sanitise_input_array(
    $inputarray['asset_id'], 
    "asset_id", 
    null, 
    API, 
    $logParent
);

$sanitisedInput['trigger_id'] = sanitise_input_array(
    $inputarray['trigger_id'], 
    "trigger_id", 
    null, 
    API, 
    $logParent
);

$logParent = logEvent(
    API . logText::request . json_encode($sanitisedInput), 
    logLevel::request, 
    logType::request,
    TOKEN, 
    $logParent
)['event_id'];

/** data authenticity checks for insert and delete */
if (USER_ROLE != 1) {
    $userDet = getUserAssetDetByUserId([USER_ID], $errorHandler);
    $userAssetDet = empty($userDet) ? [] : $userDet[1];
    $availableAssetId = empty(array_values($userAssetDet)) ? [] : array_values($userAssetDet)[0];
    $invalidIdArr = array_diff($sanitisedInput['asset_id'], $availableAssetId);
    if (!empty($invalidIdArr)) {
        errorGeneric("NO_PERMISSION_FOR_ASSET_ID_(" . implode('_', $invalidIdArr) . ")", API, $logParent);
    }
}

$deviceAssetDet = getDeviceAssetDetByAssetId($sanitisedInput['asset_id'], $errorHandler);
if (empty($deviceAssetDet)) {
    errorGeneric("NO_DEVICE_FOR_ASSET", API, $logParent);
}

$linkedAssetId = array_keys($deviceAssetDet);
$invalidIdArr = array_diff($sanitisedInput['asset_id'], $linkedAssetId);
if (!empty($invalidIdArr)) {
    errorGeneric("NO_DEVICE_FOR_ASSET_ID_(" . implode('_', $invalidIdArr) . ")", API, $logParent);
}

$deviceIdArr = array_column($deviceAssetDet, 'deviceId');
$deviceAssetIdArr = array_column($deviceAssetDet, 'deviceAssetId');
$sensorTriggerIdArr = filterTriggerIdBySource($sanitisedInput['trigger_id'], 'Sensor', $errorHandler);
$geofenceTriggerIdArr = filterTriggerIdBySource($sanitisedInput['trigger_id'], 'Geofence', $errorHandler);

// *******************************************************************************
// *******************************************************************************
// *****************************INSERT********************************************
// *******************************************************************************
// *******************************************************************************

if ($sanitisedInput['action'] === "insert") {
    if (count($sanitisedInput['trigger_id']) !== count(array_merge($sensorTriggerIdArr, $geofenceTriggerIdArr))) {
        errorGeneric("CONTAIN_NONE_EXIST_TRIGGER_ID", API, $logParent);
    }

    /** sensor trigger */
    if (!empty($sensorTriggerIdArr)) {
        $deviceProvisioningDet = getProvisioningIdByDeviceId($deviceIdArr, $errorHandler);
        if (count($deviceProvisioningDet) !== count($deviceIdArr)) {
            $invaidDeviceIdArr = array_diff($deviceIdArr, array_keys($deviceProvisioningDet));
            errorGeneric("INVALID_DEVICE_ID_(" . implode("_", $invaidDeviceIdArr) . ")", API, $logParent);
        }

        $provisioningIdArr = array_unique( array_values($deviceProvisioningDet) );
        $provisioningSensorDet = getProvisioningSensorDet($provisioningIdArr, $errorHandler);
        $triggerSensorDef = getTriggerSensorDefByTriggerId($sensorTriggerIdArr, $errorHandler);
        $triggerSdIdArr = array_values($triggerSensorDef);

        array_walk(
            $deviceProvisioningDet, 
            function($provisioningId, $deviceId) use ($triggerSdIdArr, $provisioningSensorDet, $triggerSensorDef, $API, $logParent) {
                $sensorIds = array_values($provisioningSensorDet[$provisioningId]);
                $availableSdIds = [];
                foreach($sensorIds as $sdIds) {
                    array_push($availableSdIds, ...$sdIds);
                }
                $invalidSdIdArr = array_diff($triggerSdIdArr, $availableSdIds);
                $invalidTriggerIdArr = [];
                foreach($triggerSensorDef as $tid => $sid) {
                    if (in_array($sid, $invalidSdIdArr)) array_push($invalidTriggerIdArr, $tid);
                }
                if (!empty($invalidTriggerIdArr)) {
                    errorGeneric("INVALID_TRIGGER_ID_(" . implode('_', $invalidTriggerIdArr) . ")_FOR_ASSET", $API, $logParent);
                }
            }
        );

        upsertDeviceAssetTriggerDet($deviceAssetIdArr, $sensorTriggerIdArr, $deviceIdArr, $errorHandler);
    }

    /** geo fence trigger */
    if (!empty($geofenceTriggerIdArr)) {
        $geofenceIdArr = array_unique( getGeofenceIdByGeoTriggerId($geofenceTriggerIdArr, $errorHandler) );
        upsertDeviceAssetGeoDet($deviceAssetIdArr, $geofenceIdArr, $deviceIdArr, $errorHandler);
        upsertDeviceAssetTriggerDet($deviceAssetIdArr, $geofenceTriggerIdArr, $deviceIdArr, $errorHandler);
    }
}

// *******************************************************************************
// *******************************************************************************
// *****************************DELETE********************************************
// *******************************************************************************
// *******************************************************************************

if ($sanitisedInput['action'] === "delete") {
    removeDeviceAssetTriggerDet($deviceAssetIdArr, $sanitisedInput['trigger_id'], $deviceIdArr, $errorHandler);
}

$httpResp = ["error" => "NO_ERROR"];
logEvent(
    API . logText::response . json_encode($httpResp),
    logLevel::response,
    logType::response,
    TOKEN,
    $logParent
);
exit(json_encode($httpResp));