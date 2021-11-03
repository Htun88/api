# API: Device
 
### Parameters for Devices

### Ver 1.0
---

## Table of Contents
- [SELECT](#select)
  - [REQUEST](#select-request)
  - [REQUEST (FILTERED)](#select-request-filtered)
  - [RESPONSE (SUCCESS)](#select-response-success)
  - [RESPONSE (FAILURE)](#select-response-failure)
- [INSERT](#insert)
  - [REQUEST](#insert-request)
  - [RESPONSE (SUCCESS)](#insert-response-success)
  - [RESPONSE (FAILURE)](#insert-response-failure)
- [UPDATE](#update)
  - [REQUEST](#update-request)
  - [RESPONSE (SUCCESS)](#update-response-success)
  - [RESPONSE (FAILURE)](#update-response-failure)

<br>

---
---

<br>

# SELECT

Standard request to select device(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all devices.

```json
GET /v1/Device/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all devices will be returned. Responses are limited by default to 1000 responses.

```json
POST /v1/Device/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "device_id" : INT | ARRAY
    , "device_name" : INT | ARRAY
    , "device_sn" : INT | ARRAY
    , "license_id" : INT | ARRAY
    , "license_hash" : INT | ARRAY
    , "configuration_version" : INT | ARRAY
    , "geofences_version" : INT | ARRAY
    , "triggers_version" : INT | ARRAY
    , "desired_version" : INT | ARRAY
    , "desired_stored_versions" : INT | ARRAY
    , "provisioning_id" : INT | ARRAY
    , "active_status" : INT 
    , "limit" : INT

}
    //  Example
{
    "action" : "select"
    , "desired_stored_versions" : 
        [
            "2248"
            , "GAM"
        ]
    , "active_status" : 0
    , "provisioning_id" : 2
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID |
| device_name | Optional | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Device name, searched by 'like %*device_name*%' |
| device_sn | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device serial number |
| license_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | License ID |
| license_hash | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | License hash |
| configuration_version | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Configuration version |
| geofences_version | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Geofences version |
| triggers_version | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Triggers version |
| desired_version | Optional | STRING \| ARRAY | Length <= 10, or <br> ARRAY of Length <= 10 | Desired firmware version, searched by 'like %*desired_version*%' |
| desired_stored_versions | Optional | STRING \| ARRAY | Length <= 250, or <br> ARRAY of Length <= 250 | Stored firmware versions, searched by 'like %*desired_stored_versions*%' |
| provisioning_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Provisioning ID |
| active_status | Optional | INT | [0.. 1] | Device is either active (0) or inactive (1) |
| limit | Optional | INT | [0.. ∞) | Maximum number of responses |

<br>


## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device(s).

```json
Content-Type: application/json

{
    "devices": {
            //  Response values
        "device_1": {
            "device_id": INT,
            "device_name": STRING,
            "device_sn": INT | null,
            "license_id": INT | null,
            "license_hash": STRING,
            "license_expiry": DATETIME,
            "configuration_version": INT,
            "geofences_version": INT,
            "triggers_version": INT,
            "desired_version": STRING,
            "desired_stored_versions": STRING,
            "provisioning_id": INT,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "device_1": {
            "device_id": 2,
            "device_name": "Test Device",
            "device_sn": "19210182",
            "license_id": 5,
            "license_hash": "3affdfbc",
            "license_expiry": "2022-06-28 00:00:00",
            "configuration_version": 17,
            "geofences_version": 45,
            "triggers_version": 24,
            "desired_version": "PSM_2248",
            "desired_stored_versions": "PSM_2248,PSM_2247,PSM_2245",
            "provisioning_id": 2,
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-08-23 05:04:46"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| device_id | INT | Device ID |
| device_name | STRING | Device name |
| device_sn | INT | Device serial number |
| license_id | INT | License ID |
| license_hash | INT | License hash |
| license_expiry | DATETIME | License expiry |
| configuration_version | INT | Configuration version |
| geofences_version | INT | Geofences version |
| triggers_version | INT | Triggers version |
| desired_version | STRING | Desired firmware version |
| desired_stored_versions | STRING | Stored firmware versions |
| provisioning_id | INT | Provisioning ID |
| active_status | INT | Device is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the device |
| last_modified_datetime | DATETIME | Timestamp of last device modification |


<br>

## SELECT RESPONSE (FAILURE)

```json
Content-Type: application/json

    //  Error value
{
    "error" : ERROR_CODE
}
    //  Example error
{
    "error" : "NO_DATA"
}

```
<br> 

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_SN_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LICENSE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LICENSE_HASH_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_CONFIGURATION_VERSION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GEOFENCES_VERSION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGERS_VERSION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DESIRED_VERSION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DESIRED_STORED_VERSIONS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PROVISIONING_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a new device

Inserting a new device will also associate the new device ID with device custom parameter values as appropriate based on the *provisioning_id*

A newly inserted device will have *configuration version*, *geofences_version* and *triggers_version* set to 1

## INSERT REQUEST

```json
POST /v1/Device/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "device_name" : STRING
    , "device_sn" : INT
    , "license_id" : INT
    , "license_hash" : STRING
    , "provisioning_id" : INT
    , "desired_version" : STRING
    , "desired_stored_versions" : STRING | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action" : "insert"
    , "device_name" : "Device 7"
    , "license_id" : 16
    , "desired_stored_versions" : 
        [
            "PSM_2248"
            , "PSM_2247"
        ]
    , "provisioning_id" : 2
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| device_name | **Required** | STRING | Length <= 45 | Device name |
| device_sn | Optional | INT | [0.. ∞) | Device serial number |
| license_id | Optional | INT | [0.. ∞) | License ID <br> Must be associated with *license_hash* if also including this parameter |
| license_hash | Optional | STRING | Length <= 256 | License hash <br> Must be associated with *license_id* if also including this parameter |
| provisioning_id | **Required** | INT | [0.. ∞) | Provisioning ID |
| desired_version | Optional | STRING | Length <= 250 | Desired firmware version <br> Must be a firmware version associated with *provisioning_id* <br> Will default to the most recent *provisioning_id* firmware version if not specified |
| desired_stored_versions | Optional | STRING \| ARRAY | Length <= 250, or <br> ARRAY of Length <= 10 | Desired stored firmware versions, see [notes](#desired-stored-versions) <br> Must be a firmware version associated with *provisioning_id* <br> Will default to the most recent *provisioning_id* firmware version if not specified |
| active_status | Optional | INT | [0.. 1] | Device is either active (0) or inactive (1) <br> Will default to active (0) if not specified |

<br>

## Notes

### **Desired stored versions**
String of firmware versions to be stored on the device, seperated by a comma ",", eg "PSM_0001,PSM_0002,PSM_0003".

This can be input directly as a string of multiple versions already in this format, or as an array of individual firmware versions. Regardless of format each individual firmware version must be a valid firmware file associated with the provided *provisioning_id*. 

If *desired_version* firmware version is not already included in *desired_stored_versions* then it will be automatically added. 

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device information, as well as the generated *device_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "device_name": STRING,
    "license_id": INT,
    "device_sn" : INT,
    "provisioning_id": INT,
    "desired_version": STRING,
    "desired_stored_versions": STRING,
    "active_status": INT,
    "configuration_version": INT,
    "geofences_version": INT,
    "triggers_version": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "device_id": INT,                    //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "device_name": "Device 7",
    "license_id": "16",
    "provisioning_id": "2",
    "desired_version": "PSM_2248",
    "desired_stored_versions": "PSM_2247,PSM_2248",
    "active_status": 0,
    "configuration_version": 1,
    "geofences_version": 1,
    "triggers_version": 1,
    "last_modified_by": 1,
    "last_modified_datetime": "2021-08-31 04:16:49",
    "device_id": "21",                   //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
```

<br>


## INSERT RESPONSE (FAILURE)

```json
Content-Type: application/json

    //  Error value
{
    "error" : ERROR_CODE
}
    //  Example error
{
    "error" : "INVALID_TOKEN"
}
```

<br>

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_DEVICE_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_PROVISIONING_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_DEVICE_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_SN_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LICENSE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *license_id* is not a valid license ID | Alter parameter to valid value |
| LICENSE_ID_ALREADY_IN_USE | *license_id* is already associated with another device | Alter parameter to valid value |
| INVALID_LICENSE_HASH_PRAM | 1. Parameter value is unsupported, or <br> 2. *license_hash* is not associated with a valid license ID | Alter parameter to valid value |
| LICENSE_HASH_ALREADY_IN_USE | *license_hash* is associated with a license ID that is already associated with another device | Alter parameter to valid value |
| INVALID_LICENSE_ID_AND_LICENSE_HASH_PRAM | *license_id* and *license_hash* are not associated with each other | Alter parameter to valid value |
| LICENSE_EXPIRED | *license_id* and/or *license_hash* are associated with a license ID that has expired and cannot be used | Alter parameter to valid value |
| INVALID_PROVISIONING_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *provisioning_id* is not a valid active provisioning ID | Alter parameter to valid value |
| MISSING_FIRMWARE_FILES | *provisioning_id* is not associated with any valid files | Contact USM |
| MISSING_LATEST_FIRMWARE_VERSION | *provisioning_id* is not associated with any valid firmware files | Contact USM |
| INVALID_DESIRED_VERSION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MISSING_DESIRED_FIRMWARE_VERSION | *desired_version* is not a valid firmware version associated with *provisioning_id* | Alter parameter to valid value |
| INVALID_DESIRED_STORED_VERSIONS_PRAM | 1. Parameter value is unsupported, or <br> 2. One or more *desired_stored_versions* are not associated with *provisioning_id*. If so this error is accompanied with subsequent message "error_detail: X", where X is an array of the invalid *desired_stored_versions* | Alter parameter(s) to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# UPDATE

Request to update an existing device.

## UPDATE REQUEST
```json
POST /v1/Device/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "device_id" : INT
    , "device_name" : STRING
    , "device_sn" : INT 
    , "license_id" : INT 
    , "license_hash" : STRING
    , "provisioning_id" : INT
    , "desired_version" : STRING
    , "desired_stored_versions" : STRING | ARRAY
    , "active_status" : INT
 
}
    //  Example
{
    "action": "update"
    , "device_id": 22
    , "device_name" : "device 8 prov 1"
    , "desired_version" : "PSM_2247"
    , "desired_stored_versions" : 
    [
        "PSM_2248"
        , "PSM_2245"
    ]
    , "provisioning_id" : 1
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| device_id | **Required** | INT | [0.. ∞) | Device ID |
| device_name | Optional | STRING | Length <= 45 | Device name |
| device_sn | Optional | INT | (-1.. ∞) | Device serial number <br> Setting *device_sn* to -1 will remove the device and device serial number association |
| license_id | Optional | INT | (-1.. ∞) | License ID <br> Setting *license_id* to -1 will remove the device and license ID association <br> Must be associated with *license_hash* if also including this parameter and not removing license ID |
| license_hash | Optional | STRING | Length <= 256 | License hash <br> Setting *license_hash* to -1 will remove the device and license ID association <br> Must be associated with *license_id* if also including this parameter |
| provisioning_id | Optional | INT | [0.. ∞) | Provisioning ID <br> Updating provisioning ID is not supported if devices is currently associated with an asset |
| desired_version | Optional | STRING | Length <= 250 | Desired firmware version <br> Must be a firmware version associated with the current device *provisioning_id* if *provisioning_id* is not also being updated, or the new *provisioning_id* if it is. <br> Will default to the most recent *provisioning_id* firmware version if *provisioning_id* is being updated and *desired_version* is not specified |
| desired_stored_versions | Optional | STRING \| ARRAY | Length <= 250, or <br> ARRAY of Length <= 10 | Desired stored firmware versions, see [notes](#desired-stored-versions-1) <br> Setting *desired_stored_versions* to -1 will remove the current stored firmware versions and automatically set *desired_stored_versions* to *desired_version* <br> Must be a firmware version associated with the current device *provisioning_id* if *provisioning_id* is not also being updated, or the new *provisioning_id* if it is. <br> Will automatically include *desired_version* if not already included |
| active_status | Optional | INT | [0.. 1] | Device is either active (0) or inactive (1) |

<br>

## Notes

### **Desired stored versions**
String of firmware versions to be stored on the device, seperated by a comma ",", eg "PSM_0001,PSM_0002,PSM_0003".

This can be input directly as a string of multiple versions already in this format, or as an array of individual firmware versions. Regardless of format each individual firmware version must be a valid firmware file associated with the provided *provisioning_id*. 

If *desired_version* firmware version is not already included in *desired_stored_versions* then it will be automatically added. 


<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated device information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "device_id": INT,
    "configuration_version": INT,
    "device_name": STRING,
    "device_sn": INT | "NULL",  //  Returns "NULL" if removing device - device_sn association
    "license_id": INT | "NULL", //  Returns "NULL" if removing device - license_id association
    "provisioning_id": INT,
    "desired_version": STRING,
    "desired_stored_versions": STRING,
    "active_status": INT,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "device_id": "22",
    "configuration_version": 2,
    "device_name": "device 8 prov 1",
    "provisioning_id": "1",
    "desired_version": "PSM_2247",
    "desired_stored_versions": "PSM_2245,PSM_2247,PSM_2248",
    "error": "NO_ERROR"        //  Error code
}
```
<br>

## UPDATE RESPONSE (FAILURE)

```json
Content-Type: application/json

    //  Error value
{
    "error" : ERROR_CODE
}
    //  Example error
{
    "error" : "INVALID_TOKEN"
}

```
<br>

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "update" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_UPDATED_PRAM | No updatable parameter provided | Include minimum 1 updatable parameter in query |
| MISSING_DEVICE_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_DEVICE_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_SN_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LICENSE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *license_id* is not a valid license ID | Alter parameter to valid value |
| LICENSE_ID_ALREADY_IN_USE | *license_id* is already associated with another device | Alter parameter to valid value |
| INVALID_LICENSE_HASH_PRAM | 1. Parameter value is unsupported, or <br> 2. *license_hash* is not associated with a valid license ID | Alter parameter to valid value |
| LICENSE_HASH_ALREADY_IN_USE | *license_hash* is associated with a license ID that is already associated with another device | Alter parameter to valid value |
| INVALID_LICENSE_ID_AND_LICENSE_HASH_PRAM | 1. *license_id* and *license_hash* are not associated with each other, or <br> 2. One parameter is set to valid value while the other is set to "-1" | Alter parameter(s) to valid value |
| LICENSE_EXPIRED | *license_id* and/or *license_hash* are associated with a license ID that has expired and cannot be used | Alter parameter to valid value |
| INVALID_PROVISIONING_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *provisioning_id* is not a valid active provisioning ID | Alter parameter to valid value |
| PROVISIONING_ID_LOCKED | Cannot update the provisioning ID of a device currently associated with an asset | Remove the asset - device association and try again |
| MISSING_FIRMWARE_FILES | *provisioning_id* is not associated with any valid files | Contact USM |
| MISSING_LATEST_FIRMWARE_VERSION | *provisioning_id* is not associated with any valid firmware files | Contact USM |
| INVALID_DESIRED_VERSION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MISSING_DESIRED_FIRMWARE_VERSION | *desired_version* is not a valid firmware version associated with *provisioning_id* | Alter parameter to valid value |
| INVALID_DESIRED_STORED_VERSIONS_PRAM | 1. Parameter value is unsupported, or <br> 2. One or more *desired_stored_versions* are not associated with *provisioning_id*. If so this error is accompanied with subsequent message "error_detail: X", where X is an array of the invalid *desired_stored_versions*. Note that if *provisioning_id* is updated without also updating *desired_stored_versions* the conflict will be with the existing *desired_stored_versions* | Alter parameter(s) to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 01-09-2021 by C. Rollinson