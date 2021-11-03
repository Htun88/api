# API: SensorDataID
 
### Parameters for sensor data ID

### Ver 1.0
---

## Table of Contents
- [SELECT](#select)
  - [REQUEST](#select-request)
  - [REQUEST (FILTERED)](#select-request-filtered)
  - [RESPONSE (SUCCESS)](#select-response-success)
  - [RESPONSE (FAILURE)](#select-response-failure)


<br>

---
---

<br>

# SELECT

Standard request to select sensor data ID(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all sensor data ID associated with the user.

```json
GET /v1/SensorDataID/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all sensor data ID's associated with the user will be returned.

```json
POST /v1/SensorDataID/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "data_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "timestamp_from" : DATETIME
    , "timestamp_to" : DATETIME

}
    //  Example
{
    "action" : "select"
    , "asset_id" : 
        [
            1
            , 2
        ]
    , "timestamp_from" : "2021-04-01"
    , "timestamp_to" : "2021-08-01"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| data_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Data ID |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-from) | Datetime: return sensor data IDs that have occured after this datetime. Must be before timestamp_to, if included
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-to) | Datetime: return sensor data IDs that have occured before this datetime. Must be after timestamp_from, if included
<br>

## Notes

### **Timestamp from**
DATETIME. Select all sensor data that occurred after this datetime.

If timestamp_to parameter is also used, timestamp_from must be a datetime that occurs prior to timestamp_to.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.
  
### **Timestamp to**
DATETIME. Select all sensor data that occurred before this datetime.

If timestamp_from parameter is also used, timestamp_to must be a datetime that occurs after to timestamp_from.

- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected sensor data ID(s).

```json
Content-Type: application/json

{
    "sensordata": {
            //  Response values
        "sensordata 0": {
            "data_id": INT,
            "asset_id": INT,
            "asset_name": STRING,
            "data_datetime": DATETIME
        },
            //  Example
        "sensordata 1": {
            "data_id": 601,
            "asset_id": 1,
            "asset_name": "Boaty McBoatface",
            "data_datetime": "2021-07-21 07:49:06"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| data_id | INT | Data ID |
| asset_id | INT | Asset ID |
| asset_name | STRING | Asset name |
| data_datetime | DATETIME | Sensor data date time |

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
| INVALID_DATA_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TIMESTAMP_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TIMESTAMP_TO_PRAM | 1. Parameter value is unsupported, or <br> 2. timestamp_to is a datetime before timestamp_from | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 26-07-2021 by C. Rollinson