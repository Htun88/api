# API: DeviceAssetsGeofenceDetail
 
### Parameters for associating a deviceasset and a geofence 

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
- [DELETE](#delete)
  - [REQUEST](#delete-request)
  - [RESPONSE (SUCCESS)](#delete-response-success)
  - [RESPONSE (FAILURE)](#delete-response-failure)

<br>

---
---

<br>

# SELECT

Standard request to select deviceasset and geofence association(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all deviceasset and geofence associations associated with the user.

```json
GET /v1/DeviceAssetsGeofenceDetail/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all deviceasset and geofence associations associated with the user will be returned.

```json
POST /v1/DeviceAssetsGeofenceDetail/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "deviceassets_det" : INT | ARRAY
    , "device_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "deviceasset_id" : INT | ARRAY
    , "geofence_id" : INT | ARRAY
    , "limit" : INT
}
    //  Example
{
    "action" : "select"
    , "asset_id" : 
        [
            1
            , 2
            , 3
            , 4
            , 5
        ]
    , "geofence_id" : 
        [
            1
            , 4
            , 6
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| deviceassets_det | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset geofence detail ID |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| deviceasset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset ID |
| geofence_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Geofence ID |
| limit | Optional | INT | [0.. ∞) | Maximum number of responses |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected deviceasset and geofence association(s).

```json
Content-Type: application/json

{
    "deviceasset_geofence_details": {
            //  Response values
        "deviceasset_geo 0": {
            "deviceassets_det": INT,
            "deviceasset_id": INT,
            "geofence_id": INT
        },
            //  Example
        "deviceasset_geo 1": {
            "deviceassets_det": 13,
            "deviceasset_id": 4,
            "geofence_id": 6
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| deviceassets_det | INT | Deviceasset geofence detail ID |
| deviceasset_id | INT | Deviceasset ID |
| geofence_id | INT | Geofence ID |

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
| INVALID_DEVICEASSETS_DET_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GEOFENCE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new deviceasset and geofence association

## INSERT REQUEST

```json
POST /v1/DeviceAssetsGeofenceDetail/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "deviceasset_id" : INT | ARRAY
    , "device_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "geofence_id" : INT | ARRAY
}
    //  Example
{
    "action": "insert"
    , "asset_id" : 
        [
             3
            , 8
            , 7
        ]
    , "geofence_id": 
        [
            1
            , 2
            , 3 
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| deviceasset_id | *Conditional*  | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset ID <br> Incompatable with *asset_id* and *device_id* |
| asset_id | *Conditional*  | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID <br> Incompatable with *deviceasset_id* and *device_id* |
| device_id | *Conditional*  | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID <br> Incompatable with *deviceasset_id* and *asset_id* |
| geofence_id | **Required** | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Geofence ID |

<br>

### **Note**
Insert requests require one or more valid *geofence_id* values AND one or more valid values from ONE of either *deviceasset_id*, *asset_id* or *device_id*


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted deviceasset and geofence association information, any associations that already existed and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "deviceasset_id": {
        INT : {
            "geofence_id": INT | ARRAY,
            "pre-existing_geofences": INT | ARRAY       //  Only present if pre-existing geofences associations included in insert request
        },
        ...
    },
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
  "deviceasset_id": {
    "5": {
      "geofence_id": [
        "2",
        "3",
        "6"
      ],
      "Pre-existing_geofences": [
        1,
        4,
        5
      ]
    },
    "6": {
      "geofence_id": [
        "2",
        "3",
        "4",
        "5"
      ],
      "Pre-existing_geofences": [
        6,
        1
      ]
    },
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
| INVALID_INCOMPATABLE_IDENTIFIER_PRAM | Invalid combination of parameters | Remove conflicting parameters |
| MISSING_INSERT_IDENTIFICATION_PRAM | Missing one of required *deviceassets_id*, *asset_id* or *device_id* parameter | Include required parameters |
| MISSING_DEVICEASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_GEOFENCE_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_DEVICEASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *deviceasset_id* values are not valid *deviceasset_id* associated with user. If *deviceasset_id* input is an array of *deviceasset_id*, this will be accompanied with an "error_detail" message indicating the invalid *deviceasset_id*(s) | Alter parameter(s) to valid value |
| INVALID_DEVICE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *device_id* values are not valid *device_id* associated with user. If *device_id* input is an array of *device_id*, this will be accompanied with an "error_detail" message indicating the invalid *device_id*(s) | Alter parameter(s) to valid value |
| INVALID_ASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *asset_id* values are not valid *asset_id* associated with user. If *asset_id* input is an array of *asset_id*, this will be accompanied with an "error_detail" message indicating the invalid *asset_id*(s) | Alter parameter(s) to valid value |
| INVALID_GEOFENCE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *geofence_id* values are not valid *geofence_id*. If *geofence_id* input is an array of *geofence_id*, this will be accompanied with an "error_detail" message indicating the invalid *geofence_id*(s) | Alter parameter(s) to valid value |
| INVALID_DUPLICATE_EXISTS_PRAM | An association of this deviceasset ID and geofence ID already exists | No action necessary |


<br>

---
---

<br>

# UPDATE

Request to update an existing deviceasset and geofence association

## UPDATE REQUEST
```json
POST /v1/DeviceAssetsGeofenceDetail/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "deviceassets_det" : INT 
    , "deviceasset_id" : INT
    , "geofence_id" : INT
}
    //  Example
{
    "action" : "update"
    , "deviceassets_det" : 12
    , "geofence_id" : 5
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| deviceassets_det | **Required**  | INT | [0.. ∞) | Deviceasset geofence detail ID |
| deviceasset_id | Optional | INT | [0.. ∞) | Deviceasset ID |
| geofence_id | Optional | INT | [0.. ∞) | Geofence ID |

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated deviceasset and geofence association information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "deviceassets_det": INT,
    "deviceasset_id": INT,
    "geofence_id": INT,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "deviceassets_det": "12",
    "geofence_id": "5",
    "error" : "NO_ERROR"        //  Error code
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
| INVALID_DEVICEASSETS_DET_PRAM | 1. Parameter value is unsupported, or <br> 2. paramater value is not a valid *deviceassets_det* | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *deviceasset_id* value is not a valid *deviceasset_id* | Alter parameter to valid value |
| INVALID_GEOFENCE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *geofence_id* value is not a valid *geofence_id* | Alter parameter to valid value |
| MISSING_DEVICEASSETS_DET_PRAM | Missing required parameter | Include missing require parameter |

<br>

---
---

<br>

# DELETE

Request to remove an existing deviceasset and geofence association

## DELETE REQUEST
```json
POST /v1/DeviceAssetsGeofenceDetail/ HTTP/1.1

    //  Request values
{
    "action" : "delete"
    , "deviceassets_det" : INT | ARRAY
    , "deviceasset_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "device_id" : INT | ARRAY
    , "geofence_id" : INT
}
    //  Example deleting with deviceassets_det
{
    "action" : "delete"
    , "deviceassets_det" : 
        [
            1
            , 2
            , 15
        ]
}
    //  Example deleting with a geofence_id and asset_id
{
    "action" : "delete"
    , "asset_id" : 
        [
            5
            , 6
            , 8
        ]
    , "geofence_id" : 9
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "delete" | Request action |
| deviceassets_det | *Conditional*  | INT | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset geofence detail ID <br> Incompatable with any parameter other than *action* |
| deviceasset_id | *Conditional*  | INT | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset ID <br> Incompatable with *asset_id* and *device_id* |
| asset_id | *Conditional*  | INT | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID <br> Incompatable with *deviceasset_id* and *device_id* |
| device_id | *Conditional*  | INT | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID <br> Incompatable with *deviceasset_id* and *asset_id* |
| geofence_id | *Conditional*  | INT | [0.. ∞) | Geofence ID <br> Mandatory if not including *deviceassets_det* parameter |


<br>

### **Note**
Delete requests require EITHER of: 
1. one or more valid *deviceassets_det*, or
2. one valid *geofence_id* AND one or more valid values from ONE of either *deviceasset_id*, *asset_id* or *device_id*

<br>

## DELETE RESPONSE (SUCCESS)
Response success returns an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "error" : "NO_ERROR"        //  Error code   
}

```
<br>

## DELETE RESPONSE (FAILURE)

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
| INVALID_ACTION_PRAM | "action" parameter is not select, update, insert or delete | Alter parameter to "delete" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| INVALID_INCOMPATABLE_IDENTIFIER_PRAM | Invalid combination of parameters | Remove conflicting parameters |
| MISSING_DELETE_IDENTIFICATION_PRAM | MMissing one of required *deviceassets_id*, *asset_id* or *device_id* parameter | Include required parameters |
| MISSING_GEOFENCE_PRAM | Missing required *geofence_id* parameter | Include required parameters |
| INVALID_DEVICEASSETS_DET_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *deviceassets_det* values are not valid *deviceassets_det* associated with user. If *deviceassets_det* input is an array of *deviceassets_det*, this will be accompanied with an "error_detail" message indicating the invalid *deviceassets_det*(s) | Alter parameter(s) to valid value |
| INVALID_DEVICEASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *deviceasset_id* values are not valid *deviceasset_id* associated with user. If *deviceasset_id* input is an array of *deviceasset_id*, this will be accompanied with an "error_detail" message indicating the invalid *deviceasset_id*(s) | Alter parameter(s) to valid value |
| INVALID_DEVICE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *device_id* values are not valid *device_id* associated with user. If *device_id* input is an array of *device_id*, this will be accompanied with an "error_detail" message indicating the invalid *device_id*(s) | Alter parameter(s) to valid value |
| INVALID_ASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *asset_id* values are not valid *asset_id* associated with user. If *asset_id* input is an array of *asset_id*, this will be accompanied with an "error_detail" message indicating the invalid *asset_id*(s) | Alter parameter(s) to valid value |
| INVALID_GEOFENCE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *geofence_id* is not a valid *geofence_id* | Alter parameter to valid value |
| INCOMPATABLE_PARAMETERS | No geofence / device association exists with the combination of supplied parameters | Alter parameter values to valid values |


<br>

---
---

<br>

### Last modified 25-08-2021 by C. Rollinson