# API: DeviceProvisioningComponents
 
### Parameters for device provisioning components

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

Standard request to select device provisioning component(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all device provisioning components.

```json
GET /v1/DeviceProvisioningComponents/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all device provisioning components will be returned.

```json
POST /v1/DeviceProvisioningComponents/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "provisioning_component_id" : INT | ARRAY
    , "provisioning_id" : INT 
    , "component_type" : INT | STRING
    , "component_id" : INT | ARRAY
    , "active_status" : INT
    , "limit" : INT
}
    //  Example
{
    "action": "select"
    , "provisioning_id" : 11
    , "component_type" : "Sensor"
    , "component_id" : 
        [
            1
            , 2
            , 4
            , 6
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| provisioning_component_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device provisioning components ID |
| provisioning_id | Optional | INT | [0.. ∞) | Device provisioning ID |
| component_type | Optional | INT \| STRING | [0.. ∞), or <br> Length <= 15 | Component type. Parameter can be either STRING or INT input <br> Accepted values: (0) Sensor, (1) Parameter or (2) Group Parameter |
| component_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Component ID |
| active_status | Optional | INT | [0.. 1] | Device provisioning component is either active (0) or inactive (1) |
| limit | Optional | INT | [0.. ∞) | Maximum limit of returned responses. Default 1000 response limit |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device provisioning component(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "provisioning_component_id" : INT,
            "provisioning_id": INT,
            "component_type": SENSOR,
            "component_id": INT,
            "name": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "response_1": {
            "provisioning_component_id" : 12,
            "provisioning_id": 11,
            "component_type": "Sensor",
            "component_id": 2,
            "name": "Carbon Monoxide",
            "active_status": 1,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-10-12 06:35:44"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| provisioning_component_id | INT | Device provisioning component ID |
| provisioning_id | INT | Provisioning ID |
| component_type | STRING | Component type |
| component_id | INT | Component ID |
| name | STRING | Component name |
| active_status | INT | Device provisioning component is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the device provisioning component |
| last_modified_datetime | DATETIME | Timestamp of last device provisioning component modification |


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
| INVALID_PROVISIONING_COMPONENT_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PROVISIONING_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COMPONENT_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COMPONENT_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert new device provisioning components

## INSERT REQUEST

```json
POST /v1/DeviceProvisioningComponents/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "provisioning_id" : INT
    , "component_type" : INT | STRING
    , "component_id" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action": "insert"
    , "provisioning_id": 11
    , "component_type": "Parameter"
    , "component_id" : 
        [
            2
            , 3
            , 4
        ]
}
```
<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "insert" | Request action |
| provisioning_id | **Required** | INT | [0.. ∞) | Device provisioning ID |
| component_type | **Required** | INT \| STRING | [0.. ∞), or <br> Length <= 15 | Component type. Parameter can be either STRING or INT input <br> Accepted values: (0) Sensor, (1) Parameter or (2) Group Parameter |
| component_id | **Required** | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Component ID |
| active_status | Optional | INT | [0.. 1] | Device provisioning component is either active (0) or inactive (1) <br> This will default to (0) active if not specified |

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device provisioning component(s) information, as well as an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "provisioning_id": INT,
    "component_type": STRING,
    "component_id": ARRAY,
    "active_status": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "error": "NO_ERROR"                  //  Error code
}
    //  Example response
{
    "provisioning_id": "11",
    "component_type": "Parameter",
    "component_id": [
        "2",
        "3",
        "4"
    ],
    "active_status": 0,
    "last_modified_by": 1,
    "last_modified_datetime": "2021-10-12 05:19:35",
    "error": "NO_ERROR"                  //  Error code
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
| MISSING_PROVISIONING_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_COMPONENT_TYPE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_COMPONENT_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_PROVISIONING_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *provisioning_id* is not a valid provisioning ID | Alter parameter to valid value |
| INVALID_COMPONENT_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COMPONENT_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COMPONENT_SENSOR_ID_PRAM | One or more *component_id* values are not valid sensor IDs. If *component_id* input is an array of *component_id*, this will be accompanied with an "error_detail" message indicating the invalid *component_id*(s) | Alter parameter(s) to valid value |
| INVALID_COMPONENT_PARAMETER_ID_PRAM | One or more *component_id* values are not valid parameter IDs. If *component_id* input is an array of *component_id*, this will be accompanied with an "error_detail" message indicating the invalid *component_id*(s) | Alter parameter(s) to valid value |
| INVALID_COMPONENT_GROUP_PARAMETER_ID_PRAM | One or more *component_id* values are not valid group parameter IDs. If *component_id* input is an array of *component_id*, this will be accompanied with an "error_detail" message indicating the invalid *component_id*(s) | Alter parameter(s) to valid value |

<br>

---
---

<br>

# UPDATE

Request to update existing device provisioning components

## UPDATE REQUEST
```json
POST /v1/DeviceProvisioningComponents/ HTTP/1.1

    //  Request values
{
    "action": "update"
    , "provisioning_component_id" : INT | ARRAY
    , "provisioning_id" : INT
    , "component_type" : INT | STRING
    , "component_id" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action": "update"
    , "provisioning_id" : 11
    , "component_type" : 0
    , "component_id" : 
        [
            1
            , 2
            , 4
            , 5
        ]
    , "active_status" : 1
}
```
<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "update" | Request action |
| provisioning_component_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device provisioning components ID <br> Incompatible with *provisioning_id*, *component_type* and *component_id* paramaters. Mandatory if not including *provisioning_id*, *component_type* and *component_id* parameters |
| provisioning_id | *Conditional* | INT | [0.. ∞) | Device provisioning ID <br> Incompatible with *id* paramater. Mandatory if not including *id* parameter |
| component_type | *Conditional* | INT \| STRING | [0.. ∞), or <br> Length <= 15 | Component type. Parameter can be either STRING or INT input <br> Accepted values: (0) Sensor, (1) Parameter or (2) Group Parameter <br> Incompatible with *id* paramater. Mandatory if not including *id* parameter |
| component_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Component ID <br> Incompatible with *id* paramater. Mandatory if not including *id* parameter |
| active_status | **Required** | INT | [0.. 1] | Device provisioning component is either active (0) or inactive (1) |

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated TODO information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "provisioning_id": INT,
    "component_type": INT,
    "component_id": ARRAY,
    "active_status": INT,
    "error": "NO_ERROR"         //  Error code  
}
    //  Example
{
    "provisioning_id": "11",
    "component_type": "Sensor",
    "component_id": [
        "1",
        "2",
        "4",
        "5"
    ],
    "active_status": "1",
    "error": "NO_ERROR"         //  Error code  
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
| NO_UPDATED_PRAMS | No updatable parameter provided | Include minimum 1 updatable parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| PROVISIONING_COMPONENT_ID | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PROVISIONING_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COMPONENT_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COMPONENT_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *id* values are not valid device provisioning components IDs. If *id* input is an array of *id*, this will be accompanied with an "error_detail" message indicating the invalid *id*(s) | Alter parameter(s) to valid value |

<br>

---
---

<br>

### Last modified 12-10-2021 by C. Rollinson