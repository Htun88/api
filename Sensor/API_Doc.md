# API: Sensor
 
### Parameters for a sensor

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

Standard request to select sensor(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all sensors.

```json
GET /v1/Sensor/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all sensors will be returned.

```json
POST /v1/Sensor/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "sensor_id" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action" : "select"
    , "sensor_id" : 
        [
            1
            , 2
            , 3
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| sensor_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor ID |
| active_status | Optional | INT | [0.. 1] | Sensor is either active (0) or inactive (1) |

<br>


## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected sensor(s).

```json
Content-Type: application/json

{
    "sensors": {
            //  Response values
        "sensor 0": {
            "id": INT,
            "name": STRING,
            "active_status": INT
        },
            //  Example
        "sensor 1": {
            "id": 2,
            "name": "Carbon Monoxide",
            "active_status": 0
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| id | INT | Sensor ID  |
| name | STRING | Sensor name |
| active_status | INT | Sensor is either active (0) or inactive (1) |


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
| INVALID_SENSOR_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new sensor.

## INSERT REQUEST

```json
POST /v1/Sensor/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "sensor_name" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "insert"
    , "sensor_name" : "Altitude sensor"
    , "active_status" : "0" 
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| sensor_name | Optional | STRING | Length <= 45 | Sensor name |
| active_status | Optional | INT | [0.. 1] | Sensor is either active (0) or inactive (1) |

<br>

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted sensor information, as well as the generated *sensor_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "sensor_name": STRING,
    "active_status": INT,
    "sensor_id": INT,                    //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "sensor_name": "Altitude sensor",
    "active_status": "0",
    "sensor_id" : 23,                    //  Generated PARAMETER
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
| MISSING_SENSOR_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_SENSOR_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# UPDATE

Request to update an existing sensor

## UPDATE REQUEST
```json
POST /v1/Sensor/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "sensor_id" : INT
    , "sensor_name" : STRING
    , "active_status" : INT
 
}
    //  Example
{
    "action" : "update"
    , "sensor_id" : 23
    , "sensor_name" : "Altitude and Pressure sensor"
    , "active_status" : "0" 
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| sensor_id | Optional | INT | [0.. ∞) | Sensor ID |
| sensor_name | Optional | STRING | Length <= 45 | Sensor name |
| active_status | Optional | INT | [0.. 1] | Sensor is either active (0) or inactive (1) |

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated sensor information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "sensor_id": INT,
    "sensor_name": STRING,
    "active_status": INT,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "sensor_id": "23",
    "sensor_name": "Altitude and Pressure sensor",
    "active_status": "0",
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
| MISSING_SENSOR_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SENSOR_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 26-07-2021 by C. Rollinson