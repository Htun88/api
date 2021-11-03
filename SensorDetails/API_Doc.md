# API: SensorDetails
 
### Parameters for sensor details

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

Standard request to select sensor details, either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all sensor details.

```json
GET /v1/SensorDetails/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all sensor details will be returned.

```json
POST /v1/SensorDetails/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "sensor_det_id" : INT | ARRAY
    , "sensor_id" : INT | ARRAY
    , "sd_id" : INT | ARRAY
    , "active_status" : INT

}
    //  Example
{
    "action" : "select"
    , "sensor_id" : 
        [   
            10
            , 11
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| sensor_det_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor details ID |
| sensor_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor type ID |
| sd_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor definition ID |
| active_status | Optional | INT | [0.. 1] | Sensor detail is either active (0) or inactive (1) |

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected sensor detail(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "sensor_det_id": INT,
            "sensor_id": INT,
            "sd_id": INT,
            "sensor_name": STRING,
            "name": STRING,
            "active_status": INT
        },
            //  Example
        "response_1": {
            "sensor_det_id": 16,
            "sensor_id": 10,
            "sd_id": 13,
            "sensor_name": "GPS",
            "name": "GPS - Long",
            "active_status": 0
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| sensor_det_id | INT | Sensor detail ID |
| sensor_id | INT | Sensor type ID |
| sd_id | INT | Sensor definition ID |
| sensor_name | STRING | Sensor type name |
| name | STRING | Sensor definition name |
| active_status | INT | Sensor detail is either active (0) or inactive (1) |

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
| INVALID_SENSOR_DET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SENSOR_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert new sensor details. Multiple sensor details can be inserted at once by providing an array of *sd_id* values. If these values already exist then they will be updated as per the new insert request, otherwise the new data will be inserted.

## INSERT REQUEST

```json
POST /v1/SensorDetails/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "sensor_id" : INT
    , "sd_id" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action" : "insert"
    , "sensor_id" : 3
    , "sd_id" : 
        [
            4
            , 5
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| sensor_id |  **Required** | INT | [0.. ∞) | Sensor type ID |
| sd_id | **Required** | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor definition ID |
| active_status | Optional | INT | [0.. 1] | Sensor detail is either active (0) or inactive (1) <br> This defaults to (0) active if not included in request |

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted sensor detail information, as well as an array of the generated ID value *sensor_det_id* and an error: NO_ERROR code. Note that the return order of the *sd_id* array matches the order of the *sensor_det_id* array, ie. *sensor_det_id* [2] is the *sensor_det_id* of *sd_id* [2].

```json
Content-Type: application/json

    //  Response values
{
    "sensor_id": INT,
    "sd_id": ARRAY,
    "active_status" : INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "sensor_det_id": ARRAY,             //  Generated sensor_det_id
    "error": STRING                     //  Error code
}
    //  Example response
{
    "sensor_id": "1",
    "sd_id": 
        [
            "1",
            "3",
            "5",
            "8"
        ],
    "active_status": "0",
    "last_modified_by": 1,
    "last_modified_datetime": "2021-09-08 01:40:52",
    "sensor_det_id":                //  Array of generated sensor_det_id
        [
            1,
            52,
            54,
            81
        ],
    "error": "NO_ERROR"             //  Error code
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
| MISSING_SENSOR_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SD_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_SENSOR_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *sd_id* values are not valid *sd_id*. If *sd_id* input is an array of *sd_id*, this will be accompanied with an "error_detail" message indicating the invalid *sd_id*(s) | Alter parameter(s) to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# UPDATE

Request to update existing sensor details.

## UPDATE REQUEST
```json
POST /v1/SensorDetails/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "sensor_det_id" : INT
    , "sensor_id" : INT
    , "sd_id" : INT | ARRAY
    , "active_status" : INT 
}
    //  Example updating using sensor_det_id
{
    "action" : "update"
    , "sensor_det_id" : 42
    , "active_status" : 0
}
    //  Example updating using sensor_id and sd_id
{
    "action" : "update"
    , "sensor_id" : 1
    , "sd_id" :
        [
            1
            , 3
            , 5
        ]
    , "active_status" : 0
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| sensor_det_id | *Conditional* | INT | [0.. ∞) | Sensor detail ID <br> *sensor_det_id** is incompatable with *sensor_id* and *sd_id* and is mandatory if *sensor_id* and *sd_id* are not included parameters |
| sensor_id | *Conditional* | INT | [0.. ∞) | Sensor ID <br> *sensor_id** is incompatable with *sensor_det_id* and is mandatory if *sensor_det_id* is not an included parameter |
| sd_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor definition ID <br> *sd_id** is incompatable with *sensor_det_id* and is mandatory if *sensor_det_id* is not an included parameter |
| active_status | Optional | INT | [0.. 1] | Sensor detail is either active (0) or inactive (1) |

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated sensor detail information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "sensor_det_id": INT,
    "sensor_id": INT,
    "active_status": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "sd_id": ARRAY,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example updating with sensor_det_id
{
    "sensor_det_id": "42",
    "active_status": "0",
    "last_modified_by": 1,
    "last_modified_datetime": "2021-07-27 04:23:13",
    "error": "NO_ERROR"         //  Error code
}
    //  Example updating with sensor_id and sd_id
{
    "sensor_id": "1",
    "active_status": "0",
    "last_modified_by": 1,
    "last_modified_datetime": "2021-09-08 01:01:11",
    "sd_id": 
        [
            "1",
            "3",
            "5"
        ],
    "error": "NO_ERROR"           //  Error code
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
| MISSING_SENSOR_DET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SENSOR_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SD_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| INCOMPATABLE_IDENTIFICATION_PARAMS | *sensor_det_id* is incompatable with *sensor_id* and *sd_id* | Remove conflicting parameters(s) |
| INVALID_SENSOR_DET_ID_PRAM | 1. Parameter value is unsupported, or <br> One or more *sensor_det_id* values are not valid *sensor_det_id* values. If *sensor_det_id* input is an array of *sensor_det_id*, this will be accompanied with an "error_detail" message indicating the invalid *sensor_det_id*(s) | Alter parameter to valid value |
| INVALID_SENSOR_ID_PRAM | 1. Parameter value is unsupported, or <br> *sensor_id* is not a valid *sensor_id* value | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. One or more *sd_id* values are not valid *sd_id* values, or are not associated with *sensor_id*. If *sd_id* input is an array of *sd_id*, this will be accompanied with an "error_detail" message indicating the invalid *sd_id*(s) | Alter parameter to valid value |
| NO_SENSOR_ID_AND_SD_ID_ASSOCIATION | No updatable association exists with this combination of *sensor_id* and *sd_id* | Alter parameter(s) to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 08-09-2021 by C. Rollinson