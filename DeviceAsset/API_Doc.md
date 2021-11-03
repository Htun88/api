<!-- title: Document Title -->

# API: Device Asset
 
### Parameters for a device asset

### Ver 1.0
---

## Table of Contents
- [SELECT](#select)
  - [REQUEST](#select-request)
  - [REQUEST (FILTERED)](#select-request-filtered)
  - [RESPONSE (SUCCESS)](#select-response-success)
  - [RESPONSE (ERROR)](#select-response-error)
- [INSERT](#insert)
  - [REQUEST](#insert-request)
  - [RESPONSE (SUCCESS)](#insert-response-success)
  - [RESPONSE (ERROR)](#insert-response-error)
- [UPDATE](#update)
  - [REQUEST](#update-request)
  - [RESPONSE (SUCCESS)](#update-response-success)
  - [RESPONSE (ERROR)](#update-response-error)

<br>

---
---

<br>

# SELECT

Standard request to select access device asset(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all device assets associated with the user.

```json
GET /v1/DeviceAsset/ HTTP/1.1
```

## SELECT REQUEST
```json
POST /v1/DeviceAsset/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "deviceasset_id": STRING
    , "device_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "active_status": INT
}
    //  Example
{
    "action" : "select"
    , "device_id" : 
        [
            1
            , 3
        ]
    , "active_status": "1"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| deviceasset_id | Optional | INT | [0.. ∞) | Device asset ID |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| active_status | Optional | INT | [0.. 1] | Device asset is either active (0) or inactive (1) |


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device asset(s).

```json
Content-Type: application/json

{
    "deviceassets": {
            //  Response values
        "deviceasset 0": {
            "deviceasset_id": INT,
            "devices_device_id": INT,
            "assets_asset_id": INT,
            "asset_name": STRING,
            "asset_task": STRING,
            "active_status": INT,
            "date_from": DATETIME,
            "date_to": DATETIME | NULL,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "deviceasset 1": {
            "deviceasset_id": 2,
            "devices_device_id": 1,
            "assets_asset_id": 1,
            "asset_name": "Boaty McBoatface",
            "asset_task": "TestInsert",
            "active_status": 1,
            "date_from": "2021-06-25 04:09:17",
            "date_to": "2021-06-25 07:26:26",
            "last_modified_by": 1,
            "last_modified_datetime": "2021-06-25 07:26:26"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| deviceasset_id | INT | Device asset ID |
| devices_device_id | INT | Device ID |
| assets_asset_id | INT | Asset ID |
| asset_name | STRING | Asset name |
| asset_task | STRING | Asset task decription string |
| active_status | INT | Device asset is either active (0) or inactive (1) |
| date_from | DATETIME | Datetime the device asset task began |
| date_to | DATETIME \| NULL | Null if the device asset is still active <br> Datetime if the device asset is inactive |
| last_modified_by | INT | User ID code that last modified the device asset |
| last_modified_datetime | DATETIME | Timestamp of last device asset modification |


<br>

## SELECT RESPONSE (ERROR)

```json
Content-Type: application/json

    //  Error value
{
    "error": ERROR_CODE
}
    //  Example error
{
    "error": "NO_DATA"
}

```
<br> 

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data |
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a new device asset.

## INSERT REQUEST

```json
POST /v1/DeviceAsset/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "device_id" : INT
    , "asset_id" : INT
    , "asset_task" : STRING
    , "trigger_id" : INT | ARRAY
}
    //  Example
{
    "action" : "insert"
    , "device_id" : "3"
    , "asset_id" : "1"
    , "asset_task" : "Patrol location B"
    , "trigger_id" : 
        [
            1
            , 3
            , 5
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| device_id | **Required** | INT | [0.. ∞) | Device ID |
| asset_id | **Required** | INT | [0.. ∞) | Asset ID |
| asset_task | **Required** | STRING | Length <= 100 | Asset task decription string |
| trigger_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Trigger ID(s)  to associate with this deviceasset |

### Note
When inserting a new device asset two additional parameters are automatically generated: 
<li> date_from <br> DATETIME | Datetime that the device asset becomes active, generated as  datetime GMT when the insert request is sent.
<li> active_status <br> INT | Device asset active status, set to (0) active.

<br>
<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device asset information, as well as the generated device asset ID and an error: NO_ERROR code. 

If *trigger_id* parameter is also included, the error_t: NO_ERROR and/or the error_gt: NO_ERROR codes will also be included, depending on the trigger source type ("Sensor" and "Geofence" respectively). If the *trigger_id* parameter does not include at least one trigger ID of a trigger_source type then its error code will not be present. 

Inserting a new deviceasset will iterate the configuration version, geofence triggers and sensor triggers for the associated device.


```json
Content-Type: application/json

    //  Response values
{
    "device_id": INT,
    "asset_id": INT,
    "asset_task": STRING,
    "active_status": INT,
    "date_from": DATETIME,
    "deviceasset_id": INT,              //  Generated PARAMETER
    "error": STRING,                    //  Error code
    "error_t": STRING,                  //  Error code for "sensor" triggers
    "error_gt": STRING                  //  Error code for "geofence" triggers

}
    //  Example response
{
    "device_id": "3",
    "asset_id": "1",
    "asset_task": "Patrol location B",
    "active_status": 0,
    "date_from": "2021-06-28 01:27:20",
    "deviceasset_id": 4,                //  Generated PARAMETER
    "error": "NO_ERROR",                //  Error code
    "error_t": "NO_ERROR",              //  Error code for "sensor" triggers
    "error_gt": "NO_ERROR"              //  Error code for "geofence" triggers
}
```

<br>


## INSERT RESPONSE (ERROR)

```json
Content-Type: application/json

    //  Error value
{
    "error": ERROR_CODE
}
    //  Example error
{
    "error": "NO_ERROR"
}
```

<br>

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| NO_ERROR | No error returned, query succesful | No action required |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| ASSET_ID_INACTIVE | Asset ID is inactive, asset must be active to insert new deviceasset | Alter parameter value, or <br> update asset ID to active |
| ASSET_ID_IN_USE | Asset ID already associated with an active deviceasset, assets can only be associated with one active deviceasset at one time | Alter parameter value, or <br> update current associated deviceasset to inactive |
| DEVICE_ID_IN_USE |  Device ID already associated with an active deviceasset, device can only be associated with one active deviceasset at one time | Alter parameter value, or <br> update current associated deviceasset to inactive |
| INVALID_DEVICE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. device ID does not exist | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. user does not have permission to utilise this asset ID | Alter parameter to valid value |
| INVALID_ASSET_TASK_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *trigger_id* values are not valid *trigger_id*. If *trigger_id* input is an array of *trigger_id*, this will be accompanied with an "error_detail" message indicating the invalid *trigger_id*(s) | Alter parameter(s) to valid value |
| MISSING_DEVICEASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_TASK_PRAM | Missing required parameter | Include required parameter in query |


<br>

---
---

<br>

# UPDATE
---
Request to update an existing device asset

Updating a deviceasset will iterate the configuration version, geofence triggers and sensor triggers for the associated device.

## UPDATE REQUEST
```json
POST /v1/DeviceAsset/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "deviceasset_id": INT
    , "active_status": INT
}
    //  Example
{
    "action" : "update"
    , "deviceasset_id": "4"
    , "active_status": "1"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| deviceasset_id | **Required** | INT | INT >= 0 | Deviceasset ID | 
| active_status | **Required** | INT | 1 | Device asset is either active (0) or inactive (1) <br> Can only update the status to be inactive (1) |


### Note
When updating a device asset an additional parameter is automatically generated: 
<li> date_to <br> DATETIME | Datetime that the device asset becomes inactive, generated as  datetime GMT when the update request is sent.

<br>
<br>

## UPDATE RESPONSE (SUCCESS)
Response success returns the updated device asset information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "action" : "update"
    , "deviceasset_id": INT
    , "active_status": INT
    , "date_to": DATETIME
    , "error" : "NO_ERROR"  //  Error code
}
    //  Example
{
    "action" : "update"
    , "deviceasset_id": "4"
    , "active_status": "1"
    , "date_to": "2021-06-28 02:44:18"
    , "error" : "NO_ERROR"  //  Error code
}
```
<br>

## UPDATE RESPONSE (ERROR)

```json
Content-Type: application/json

    //  Error value
{
    "error": ERROR_CODE
}
    //  Example error
{
    "error": "NO_ERROR"
}

```
<br>

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| NO_ERROR | No error returned, query succesful | No action required |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "update" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_UPDATED_PRAM | No updatable parameter provided | Include minimum 1 updatable parameter in query |
| DEVICEASSET_ID_INACTIVE | Deviceasset ID is already inactive, cannot update deviceasset once inactive | No resolution possible |
| INVALID_DEVICEASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. user does not have permission to update this deviceasset ID |  Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | 1. Parameter value is unsupported, or <br> 2. parameter value is not "1" |  Alter parameter to valid value |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_DEVICEASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |

<br>

---
---

<br>

### Last modified 18-08-2021 by C. Rollinson