<?php declare(strict_types = 1);
namespace UsmUtils;

require_once '../Includes/db.php';
use PDO;
use Exception;

const ACTIVE = 0;
const INACTIVE = 1;


function getAssetsByAssetId(array $id, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `asset_id`, `asset_name` FROM `assets`
            WHERE `asset_id` IN (" . implode(',', $id) . ") "
            . "AND `active_status` = " . ACTIVE;

    try {
        $assets = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    return $assets;
}

function fetchAllAssets(callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `asset_id`, `asset_name` FROM `assets` 
            WHERE `active_status` = " . ACTIVE;

    try {
        $assets = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    return $assets;
}

function getUserAssetDetByUserId(array $uid, callable $errorHandler = null): array {
    global $pdo;

    // ignore super admin
    $uid = array_filter($uid, function($id) {return $id != 1;});

    $sql = "SELECT `user_asset_id` FROM `user_assets` 
            WHERE `users_user_id` IN (" . implode(',', $uid) . ")"
            . " AND `active_status` = " . ACTIVE;
    
    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
    
    if (empty($result)) {
        return [];
    }

    $userAssetIds = array_column($result, 0);
    $userUserAssetDet = array_combine($userAssetIds, $uid);

    $sql = "SELECT `user_assets_user_asset_id`, `assets_asset_id` FROM `userasset_details`
            WHERE `user_assets_user_asset_id` IN (" . implode(',', $userAssetIds) . ")"
            . " AND `active_status` = " . ACTIVE;
    
    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    $userAssetDet = [];
    foreach ($result as $row) {
        if (!isset($userAssetDet[ $userUserAssetDet[ $row['user_assets_user_asset_id'] ]])) {
            $userAssetDet[ $userUserAssetDet[ $row['user_assets_user_asset_id'] ]] = [ $row['assets_asset_id'] ];
        } else {
            array_push($userAssetDet[ $userUserAssetDet[ $row['user_assets_user_asset_id'] ]], $row['assets_asset_id']);   
        }
    }

    return [$userUserAssetDet, $userAssetDet];
}

function getDeviceNameByDeviceId(array $id, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `device_id`, `device_name` FROM `devices`
            WHERE `device_id` IN (" . implode(',', $id) . ") ";

    try {
        $devices = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    return $devices;
}

function getDeviceAssetDetByAssetId(array $id, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `assets_asset_id`, `deviceasset_id`, `devices_device_id` FROM `deviceassets`
            WHERE `assets_asset_id` IN (" . implode(',', $id) . ")
            AND `date_to` IS NULL
            AND `active_status` = " . ACTIVE;
    
    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
    
    $deviceAssetDet = [];
    foreach ($result as $row) {
        $deviceAssetDet[ $row['assets_asset_id'] ] = [
            "deviceAssetId" => $row['deviceasset_id'],
            "deviceId" => $row['devices_device_id']
        ];
    }
    
    return $deviceAssetDet;
}

function getDeviceAssetDetByDeviceId(array $id, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `assets_asset_id`, `deviceasset_id`, `devices_device_id` FROM `deviceassets`
            WHERE `devices_device_id` IN (" . implode(',', $id) . ")
            AND `date_to` IS NULL
            AND `active_status` = " . ACTIVE;
    
    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    $deviceAssetDet = [];
    foreach ($result as $row) {
        $deviceAssetDet[ $row['assets_asset_id'] ] = [
            "deviceAssetId" => $row['deviceasset_id'],
            "deviceId" => $row['devices_device_id']
        ];
    }
    
    return $deviceAssetDet;
}

function getProvisioningIdByDeviceId(array $id, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `device_id`, `device_provisioning_device_provisioning_id` FROM `devices`
            WHERE `device_id` IN (" . implode(',', $id) . ")
            AND `active_status` = " . ACTIVE;
    
    try {
        $deviceProvisioningDet = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    return $deviceProvisioningDet;
}

function getProvisioningSensorDet(array $provisioningId, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT dpc.device_provisioning_device_provisioning_id, sdt.sensors_sensor_id, sdt.sensor_def_sd_id
            FROM `device_provisioning_components` AS dpc
            JOIN `sensors_det` AS sdt
            WHERE dpc.device_provisioning_device_provisioning_id IN (". implode(',', $provisioningId) .")
            AND dpc.device_component_type = 'Sensor'
            AND dpc.active_status = ". ACTIVE . " " . "
            AND sdt.sensors_sensor_id = dpc.device_component_id";
    
    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    $provisioningSensorDet = [];
    foreach ($result as $row) {
        if (!isset($provisioningSensorDet[ $row['device_provisioning_device_provisioning_id'] ])) {
            $provisioningSensorDet[ $row['device_provisioning_device_provisioning_id'] ] = [
                $row['sensors_sensor_id'] => [
                    $row['sensor_def_sd_id']
                ]
            ];
        } else {
            if (!isset($provisioningSensorDet[ $row['device_provisioning_device_provisioning_id'] ][ $row['sensors_sensor_id'] ])) {
                $provisioningSensorDet[ $row['device_provisioning_device_provisioning_id'] ][ $row['sensors_sensor_id'] ] = [ $row['sensor_def_sd_id'] ];
            } else {
                array_push($provisioningSensorDet[ $row['device_provisioning_device_provisioning_id'] ][ $row['sensors_sensor_id'] ], $row['sensor_def_sd_id']);
            }
        }
    }

    return $provisioningSensorDet;
}

function getTriggerSensorDefByTriggerId(array $triggerId, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `trigger_id`, `sensor_def_sd_id` FROM `trigger_groups` 
            WHERE `trigger_id` IN (" . implode(',', $triggerId) . ") 
            AND `active_status` = " . ACTIVE;
    
    try {
        $triggerSensorDefDet = $pdo->query($sql)->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    return $triggerSensorDefDet;
}

function filterTriggerIdBySource(array $triggerId, string $source, callable $errorHandler = null): array {
    global $pdo;

    switch ($source) {
        case 'Sensor':
            $sql = "SELECT `trigger_id` FROM `trigger_groups` 
                    WHERE `trigger_id` in (" . implode(',', $triggerId) . ") 
                    AND `trigger_source` = 'Sensor' 
                    AND `active_status` = " . ACTIVE;
            break;
        case 'Geofence':
            $sql = "SELECT `trigger_id` FROM `trigger_groups` 
                    WHERE `trigger_id` in (" . implode(',', $triggerId) . ") 
                    AND `trigger_source` = 'Geofence' 
                    AND `active_status` = " . ACTIVE;
            break;
    }

    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    $filtered = array_column($result, 0);
    return $filtered;
}

function getGeofenceIdByGeoTriggerId(array $geoTriggerId, callable $errorHandler = null): array {
    global $pdo;

    $sql = "SELECT `geofencing_geofencing_id` FROM `trigger_groups` 
            WHERE `trigger_id` in (" . implode(',', $geoTriggerId) . ") 
            AND `trigger_source` = 'Geofence' 
            AND `active_status` = " . ACTIVE;

    try {
        $result = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }

    $geofenceId = array_column($result, 0);
    return $geofenceId;
}

function upsertDeviceAssetTriggerDet(
    array $deviceAssetId, 
    array $triggerId, 
    array $devicId, 
    callable $errorHandler = null
): void {
    global $pdo;
    $upsertTriggerDetDataArr = [];
    foreach($deviceAssetId as $d) {
        foreach($triggerId as $t) {
            $upsertTriggerDetDataArr[] = "($d, $t)";
        }
    }
    $sql = "INSERT INTO `deviceassets_trigger_det` (`deviceassets_deviceasset_id`, `trigger_groups_trigger_id`) "
            . " VALUES " . implode(",", $upsertTriggerDetDataArr) 
            . " ON DUPLICATE KEY UPDATE "
            . "`deviceassets_deviceasset_id` = VALUES(`deviceassets_deviceasset_id`), "
            . "`trigger_groups_trigger_id` = VALUES(`trigger_groups_trigger_id`)";
    
    try {
        $pdo->beginTransaction();
        $pdo->prepare($sql)->execute();
        increaseDeviceTriggerVersionByOne($devicId);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

function upsertDeviceAssetGeoDet(
    array $deviceAssetId, 
    array $geofenceId, 
    array $deviceId, 
    callable $errorHandler = null
): void {
    global $pdo;

    $upsertDeviceAssetGeoDetDataArr = [];
    foreach($deviceAssetId as $d) {
        foreach($geofenceId as $g) {
            $upsertDeviceAssetGeoDetDataArr[] = "($d, $g)";
        }
    }

    $sql = "INSERT INTO `deviceassets_geo_det` (`deviceassets_deviceasset_id`, `geofencing_geofencing_id`) "
            . " VALUES " . implode(",", $upsertDeviceAssetGeoDetDataArr)
            . " ON DUPLICATE KEY UPDATE "
            . "`deviceassets_deviceasset_id` = VALUES(`deviceassets_deviceasset_id`), "
            . "`geofencing_geofencing_id` = VALUES(`geofencing_geofencing_id`)";
    
    try {
        $pdo->beginTransaction();
        $pdo->prepare($sql)->execute();
        increaseDeviceGeofenceVersionByOne($deviceId);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

function upsertUserAssetsDet(
    array $userAssetId, 
    array $assetIdArr, 
    int $userId,
    string $dateTime,
    int $activeStatus,
    callable $errorHandler = null
): void {

    global $pdo;
    $upsertUserAssetsDetDataArr = [];
    foreach($userAssetId as $ua) {
        foreach($assetIdArr as $a) {
            $upsertUserAssetsDetDataArr[] = "($ua, $a, " 
                                             . $activeStatus . ", "
                                             . $userId . ", "
                                             . "'" . $dateTime . "')";
        }
    }

    $sql = "INSERT INTO `userasset_details` (
                `user_assets_user_asset_id`, 
                `assets_asset_id`, 
                `active_status`,
                `last_modified_by`,
                `last_modified_datetime`
            ) VALUES " . implode(",", $upsertUserAssetsDetDataArr)
            . " ON DUPLICATE KEY UPDATE "
            . "`user_assets_user_asset_id` = VALUES(`user_assets_user_asset_id`), "
            . "`assets_asset_id` = VALUES(`assets_asset_id`), "
            . "`active_status` = " . $activeStatus . ", "
            . "`last_modified_by` = " . $userId . ", "
            . "`last_modified_datetime` = '" . $dateTime . "'";
    
    try {
        $pdo->prepare($sql)->execute();
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

function removeDeviceAssetTriggerDet(
    array $deviceAssetId, 
    array $triggerId, 
    array $deviceId, 
    callable $errorHandler
): void {
    global $pdo;

    $delDeviceAssetTriggerDetDataArr = [];
    foreach($deviceAssetId as $d) {
        foreach($triggerId as $t) {
            $delDeviceAssetTriggerDetDataArr[] = "($d, $t)";
        }
    }

    $sql = "DELETE FROM `deviceassets_trigger_det` 
            WHERE (`deviceassets_deviceasset_id`, `trigger_groups_trigger_id`) "
            . " IN (" . implode(',', $delDeviceAssetTriggerDetDataArr) . ")";
      //echo $sql;
    try {
        $pdo->beginTransaction();
        $pdo->prepare($sql)->execute();
        increaseDeviceTriggerVersionByOne($deviceId);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

function removeDeviceAssetGeoDet(
    array $deviceAssetId, 
    array $geofenceId, 
    array $deviceId, 
    callable $errorHandler
): void {
    global $pdo;

    $delDeviceAssetGeoDetDataArr = [];
    foreach($deviceAssetId as $d) {
        foreach($geofenceId as $g) {
            $delDeviceAssetTriggerDetDataArr[] = "($d, $g)";
        }
    }

    $sql = "DELETE FROM `deviceassets_geo_det` 
            WHERE (`deviceassets_deviceasset_id`, `geofencing_geofencing_id`) "
            . " IN (" . implode(',', $delDeviceAssetGeoDetDataArr) . ")";
       
    try {
        $pdo->beginTransaction();
        $pdo->prepare($sql)->execute();
        increaseDeviceGeofenceVersionByOne($deviceId);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollback();
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

function increaseDeviceTriggerVersionByOne(array $deviceId, callable $errorHandler = null): void {
    global $pdo;

    $sql = "UPDATE `devices` SET `triggers_version` = `triggers_version` + 1
            WHERE `device_id` IN (" . implode(',', $deviceId) . ") 
            AND `active_status` = " . ACTIVE;
    
    try {
        $pdo->prepare($sql)->execute();
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

function increaseDeviceGeofenceVersionByOne(array $deviceId, callable $errorHandler = null): void {
    global $pdo;

    $sql = "UPDATE `devices` SET `geofences_version` = `geofences_version` + 1
            WHERE `device_id` IN (" . implode(',', $deviceId) . ") 
            AND `active_status` = " . ACTIVE;
    
    try {
        $pdo->prepare($sql)->execute();
    } catch (Exception $e) {
        if ($errorHandler != null) {
            $errorHandler($e->getMessage(), $func = __FUNCTION__);
        } else {
            throw $e;
        }
    }
}

// TODO
function increaseDeviceConfigVersionByOne(array $provisioningId, callable $errorHandler = null): void {
    global $pdo;
}