# API: Syslog
 
### Parameters for syslogs

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

<br>

---
---

<br>

# SELECT

Standard request to select syslogs(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all syslogs.

```json
GET /v1/Syslog/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all syslogs will be returned.

```json
POST /v1/Syslog/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "syslog_id" : INT | ARRAY
    , "device_id" : INT | ARRAY
    , "priority" : INT | ARRAY
    , "message_id" : STRING | ARRAY
    , "timestamp_from" : DATETIME
    , "timestamp_to" : DATETIME

}
    //  Example
{
    "action" : "select"
    , "message_id" : 
        [
        "0B090000"
        , "0B080000"
        , "0B070000"
        ]
}

```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| syslog_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Syslog message ID |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID syslog originated from |
| priority | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Message priority |
| message_id | Optional | STRING \| ARRAY |Length <= 8, or <br> ARRAY of Length <= 8 | Message ID string, device side |
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return syslog that have occured after this datetime. Must be before timestamp_to, if included
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return syslog that have occured before this datetime. Must be after timestamp_from, if included
<br>

## Notes

### **datetime-notes**
Datetime parameter. Brief explanation.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected syslogs(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "syslog_id": INT,
            "device_id": INT,
            "priority": INT,
            "message_id": STRING,
            "timestamp": DATETIME
        },
            //  Example
        "response_1": {
            "syslog_id": 47,
            "device_id": 2,
            "priority": 3,
            "message_id": "0B080000",
            "timestamp": "2021-09-08 07:27:42"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| syslog_id | INT | Syslog ID |
| device_id | INT | Device ID |
| priority | INT | Message priority |
| message_id | STRING | Message ID string, device side |
| timestamp | DATETIME | Message timestamp |

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
| INVALID_ACTION_PRAM | "action" parameter is not select or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_SYSLOG_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PRIORITY_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MESSAGE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TIMESTAMP_TO_PRAM | 1. Parameter value is unsupported, or <br> 2. *timestamp_to* is a date earlier than *timestamp_from* | Alter parameter to valid value |
| INVALID_TIMESTAMP_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a new syslog

## INSERT REQUEST

```json
POST /v1/Syslog/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "device_id" : INT
    , "message_id" : STRING
    , "priority" : INT
    , "timestamp" : DATETIME
}
    //  Example
{
    "action" : "insert"
    , "device_id" : 12
    , "priority" : 6
    , "message_id" : 1000201
}

```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| device_id | **Required** | INT | [0.. ∞) | Device ID |
| message_id | **Required** | STRING | Length <= 8 | Message ID string, device side |
| priority | **Required** | INT | [0.. ∞) | Message priority |
| timestamp | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Message timestamp <br> If not supplied then timestamp will be autofilled to current gmdate |
<br>

## Notes

### **datetime-notes**
Datetime parameter. Brief explanation.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

<br>

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted syslog information, as well as the generated *syslog_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "priority": INT,
    "message_id": STRING,
    "device_id": INT,
    "timestamp": DATETIME,
    "syslog_id": INT,                   //  Generated syslog_id
    "error": "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "priority": "6",
    "message_id": "1000201",
    "device_id": "12",
    "timestamp": "2021-11-02 06:56:29",
    "syslog_id": "54",                  //  Generated syslog_id
    "error": "NO_ERROR"                 //  Error code
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
| INVALID_ACTION_PRAM | "action" parameter is not select or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_DEVICE_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_MESSAGE_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_PRIORITY_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TIMESTAMP_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_DEVICE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *device_id* is not a valid *device_id* | Alter parameter to valid value |
| INVALID_MESSAGE_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. *message_id* is not a valid syslog message *message_id*. *message_id* must be a value from the syslog message table, (see syslog message API) | Alter parameter to valid value |
| INVALID_PRIORITY_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TIMESTAMP_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| SYSLOG_ALREADY_EXIST | A syslog already exists for this unique combination of *message_id* and *priority* that has a more recent *timestamp* <br> A syslog will only be inserted if it has a more recent *timestamp* than an already existing conflicting unique paired syslog | Alter parameter to valid value

<br>

---
---

<br>

### Last modified 02-11-2021 by C. Rollinson