# API: DeviceCustomParameterGroupComponents
 
### Parameters for an device custom parameter group components
 

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

Standard request to select device custom parameter group component(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all device custom prameter group component associated with the user.

```json
GET /v1/DeviceCustomParameter/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all device custom prameter group component(s) associated with the user will be returned.

```json
POST /v1/DeviceCustomParameterGroupComponents/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "group_id" : INT
    , "param_id" : INT
    , "active_status" : INT
    , "limit" : INT
}
    //  Example
{
    "action" : "select"
    , "group_id" : 2
    , "param_id": 11
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| group_id | Optional | INT | [0.. ∞) | Group Id is a reference id of device custom param group table |
| param_id | Optional | INT | [0.. ∞) | Parameter Id a reference id of device custom parameter table |
| active_status | Optional | INT | [0.. 1] | Active status is either active (0) or inactive (1) |
| limit | Optional | INT | [0.. ∞) | Maximum number of responses |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device custom parameter group component(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "respone_0": {
            "parameter_id": INT,
            "parameter_name": STRING,
            "parameter_tag_name": STRING,
            "group_id": INT,
            "group_name": STRING,
            "group_tag_name": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },

            //  Example
        "response_0": {
            "parameter_id": 1,
            "parameter_name": "BLE Scan Time",
            "parameter_tag_name": "bstime",
            "group_id": 1,
            "group_name": "Bluetooth",
            "group_tag_name": "ble",
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "0000-00-00 00:00:00"
        },
    }
}
```



<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| parameter_id | INT | Device custom parameter Id |
| parameter_name | STRING | Device custom parameter name |
| parameter_tag_name | STRING | Device custom parameter tag name  |
| group_id | INT | Device custom parameter group id  |
| group_name | STRING | Device custom parameter group name |
| group_tag_name | STRING | Device custom parameter group tag name |
| active_status | INT | Device custom parameter active status  |
| last_modified_by | INT | User ID code that last modified the device custom parameter group components |
| last_modified_datetime | DATETIME | Timestamp of device custom parameter group components  |



<br>

## SELECT RESPONSE (FAILURE)

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
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_GROUP_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a device custom prameter group component

## INSERT REQUEST

```json
POST /v1/DeviceCustomParameterGroupComponents/ HTTP/1.1

    //  Request values
    {
        "action" : "insert"
        , "group_id" : INT
        , "param_id" : INT
        , "active_status" : INT
    }
    //  Example
    {
        "action" : "insert"
        , "group_id" : "24"
        , "param_id" : "46"
        , "active_status" : "1"
    }
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| group_id | **Required** |  INT | [0.. ∞) | Group Id is a reference id of device custom param group table |
| param_id | **Required** |  INT | [0.. ∞) | Parameter Id is a reference id of device custom parameter table |
| active_status | Optional | INT | [0.. 1] | Active status is either active (0) or inactive (1) |


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device custom prameter group component information, as well as the generated *id* and an error: NO_ERROR code
```json
Content-Type: application/json

//  Response values
{
    "id": INT,
    "group_id": INT,
    "parameter_id": INT,
    "active_status": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,  
    "error": "NO_ERROR"                   //  Error code
}
    
//  Example response
{
    "id": "70",                 //  Generated PARAMETER
    "group_id": "24",
    "parameter_id": "48",
    "active_status": 0,
    "last_modified_by": 1,
    "last_modified_datetime": "2021-10-12 01:46:38",
    "error": "NO_ERROR"     //  Error code
}

```

<br>


## INSERT RESPONSE (FAILURE)

```json
Content-Type: application/json

    //  Error value
{
    "error": ERROR_CODE
}
    //  Example error
{
    "error": "INVALID_TOKEN"
}
```

<br>

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_GROUP_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_PARAM_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_GROUP_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
<br>

---
---

<br>

# UPDATE

Request to UPDATE an existing device custom prameter group component
## UPDATE REQUEST
```json
POST /v1/DeviceCustomParameterGroupComponents/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "group_id" : INT
    , "param_id" : INT
    , "active_status" : INT
}
    //  Example
{
    "action" : "update"
    , "group_id": "24"
    , "param_id" : "48"
    , "active_status" : "1"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| id | *Conditional* | INT | [0.. ∞) | Id of device custom parameter group component <br> If *group_id* and *param_id* parameters  are not included, Id parameter is mandatory | 
| group_id | *Conditional* | INT | [0.. ∞) | Group Id is a reference id of device custom param group table <br> If *id* parameter is not included, *group_id* and *param_id* are mandatory |
| param_id | *Conditional* | INT | [0.. ∞) | Parameter Id is a reference id of device custom parameter table <br> If *id* parameter is not included, *group_id* and *param_id* are mandatory |
| active_status | Optional | INT | [0.. 1] | Active status is either active (0) or inactive (1) |


<br>

 
## UPDATE RESPONSE (SUCCESS)
Response success returns the update device custom prameter information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "active_status": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME, 
    "group_id": INT,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "active_status": "1",
    "last_modified_by": 1,
    "last_modified_datetime": "2021-10-12 04:34:00",
    "group_id": "24",
    "error": "NO_ERROR" //  Error code
}
```
<br>

## UPDATE RESPONSE (FAILURE)

```json
Content-Type: application/json

    //  Error value
{
    "error": ERROR_CODE
}
    //  Example error
{
    "error": "INVALID_TOKEN"
}

```
<br>

| Error Code | Explanation | Resolution |
| ---------- | ------------| ---------- |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_GROUP_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_PARAM_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| INCOMPATABLE_IDENTIFICATION_PARAMS | Two parameters are incompatable | Include compatable parameters in query |
| INVALID_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GROUP_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value | 
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 12-10-2021 by H. Htun