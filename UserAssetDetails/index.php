<?php
header('Content-Type: application/json');
require_once '../Includes/db.php';
require_once '../Includes/checktoken.php';
require_once '../Includes/sanitise.php';
require_once '../Includes/functions.php';

require_once '../Includes/utils.php';
use function UsmUtils\ {
    getUserAssetDetByUserId,
    getDeviceAssetDetByAssetId,
    getDeviceNameByDeviceId,
    getAssetsByAssetId,
    fetchAllAssets,
    upsertUserAssetsDet,
};
use const UsmUtils\ {
    ACTIVE,
    INACTIVE,
};

const API = "UserAssetDetails";

$entitybody = file_get_contents('php://input');
$inputarray = json_decode($entitybody, true);

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $inputarray = null;
    $inputarray['action'] = "select";
}

$logParent = logEvent(
    API . logText::accessed, 
    logLevel::accessed, 
    logType::accessed, 
    TOKEN, 
    null
)['event_id'];

checkKeys($inputarray, API, $logParent);

$sanitisedInput = [];

if (isset($inputarray['action'])) {
    $sanitisedInput['action'] = sanitise_input($inputarray['action'], "action", 7, API, $logParent);
    if ($sanitisedInput['action'] === "update") errorInvalid("request", API, $logParent);
    $logParent = logEvent(
      API . logText::action . ucfirst($sanitisedInput['action']), 
      logLevel::action, 
      logType::action, 
      TOKEN, 
      $logParent
    )['event_id'];
} else {
    errorInvalid("request", API, $logParent);
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

if (!isset($inputarray['user_id'])) errorMissing("USER_ID", API, $logParent);
$sanitisedInput['user_id'] = sanitise_input($inputarray['user_id'], "user_id", 7, API, $logParent);

if (USER_ROLE != 1 && $sanitisedInput['user_id'] != USER_ID) {
    errorInvalid("PERMISSION_DENIED", API, $logParent);
}

if ($sanitisedInput['user_id'] != 1) {
    $detail = getUserAssetDetByUserId([$sanitisedInput['user_id']], $errorHandler);
    $userUserAssetDet = empty($detail) ? [] : $detail[0];
    $userAssetDet = empty($detail) ? [] : $detail[1];
    $availableAssetIdArr = empty($userAssetDet) ? [] : $userAssetDet[$sanitisedInput['user_id']];
    $availableAssetArr = empty($availableAssetIdArr) ? [] : getAssetsByAssetId($availableAssetIdArr, $errorHandler);
} else {
    $availableAssetArr = fetchAllAssets($errorHandler);
}

if ($sanitisedInput['action'] === "select") {
    $logParent = logEvent(
        API . logText::request . json_encode($sanitisedInput), 
        logLevel::request, 
        logType::request,
        TOKEN, 
        $logParent
    )['event_id'];

    if (empty($availableAssetArr)) {
        $httpResp = ["error" => "NO_DATA"];
        logEvent(
            API . logText::response . json_encode($httpResp), 
            logLevel::responseError, 
            logType::responseError, 
            TOKEN, 
            $logParent
        );
        exit(json_encode($httpResp));
    } else {
        $deviceAssetDet = getDeviceAssetDetByAssetId(
            array_column($availableAssetArr, 'asset_id'), 
            $errorHandler
        );

        if (empty($deviceAssetDet)) {
            $httpResp = [
                "error" => "NO_ERROR",
                "responses" => $availableAssetArr,
            ];
            logEvent(
                API . logText::response . json_encode($httpResp),
                logLevel::response,
                logType::response,
                TOKEN,
                $logParent
            );
            exit(json_encode($httpResp));
        }

        $deviceNameArr = getDeviceNameByDeviceId(
            array_column(array_values($deviceAssetDet), 'deviceId'),
            $errorHandler
        );

        array_walk($availableAssetArr, function(&$asset) use ($deviceAssetDet, $deviceNameArr) {
            if (isset($deviceAssetDet[$asset['asset_id']])) {
                $asset['deviceasset_id'] = $deviceAssetDet[$asset['asset_id']]['deviceAssetId'];
                $asset['device_id'] = $deviceAssetDet[$asset['asset_id']]['deviceId'];
                $asset['device_name'] = $deviceNameArr[$asset['device_id']];
            }
        });
    }

    $httpResp = [
        "error" => "NO_ERROR",
        "responses" => $availableAssetArr,
    ];
    logEvent(
        API . logText::response . json_encode($httpResp),
        logLevel::response,
        logType::response,
        TOKEN,
        $logParent
    );
    exit(json_encode($httpResp));
}

if (!isset($inputarray['asset_id'])) errorMissing("ASSET_ID", API, $logParent);
$sanitisedInput['asset_id'] = sanitise_input_array(
    $inputarray['asset_id'], 
    "asset_id", 
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


if (empty($availableAssetArr) && USER_ROLE != 1) {
    errorInvalid("PERMISSION_DENIED", API, $logParent);
}

if (USER_ROLE != 1) {
    $invalidIdArr = array_diff($sanitisedInput['asset_id'], $availableAssetIdArr);
    if (!empty($invalidIdArr)) {
        errorInvalid("PERMISSION_DENIED_FOR_ASSET_ID_(" . implode('_', $invalidIdArr) . ")", API, $logParent);
    }
}

$requestAssetArr = getAssetsByAssetId($sanitisedInput['asset_id'], $errorHandler);
if (count($requestAssetArr) !== count($sanitisedInput['asset_id'])) {
    errorInvalid("CONTAIN_NONE_EXIST_OR_INACTIVE_ASSET_ID", API, $logParent);
}

$userAssetId = array_keys(
    array_filter($userUserAssetDet, function($val) use ($sanitisedInput) {
        return $val == $sanitisedInput['user_id'];
    })
)[0];

if ($sanitisedInput['action'] === "insert") {
    if (!empty($userAssetId)) {
        upsertUserAssetsDet(
            [$userAssetId], 
            $sanitisedInput['asset_id'], 
            USER_ID,
            TIMESTAMP,
            ACTIVE,
            $errorHandler
        );
    } // else SuperAdmin, no need to update
}

if ($sanitisedInput['action'] === "delete") {
    if ($sanitisedInput['user_id'] === 1) {
        errorInvalid("ATTEMPT_REMOVE_ASSET_FROM_SUPERADMIN", API, $logParent);
    }

    upsertUserAssetsDet(
        [$userAssetId], 
        $sanitisedInput['asset_id'], 
        USER_ID,
        TIMESTAMP,
        INACTIVE,
        $errorHandler
    );
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