# API: Message
 
### Parameters for a message

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

Standard request to select message(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all messages associated with the user.

```json
GET /v1/Message/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all messages associated with the user will be returned.

```json
POST /v1/Message/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "message_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "acknowledged" : INT
    , "method" : INT
    , "timestamp_to" : DATETIME
    , "timestamp_from" : DATETIME
    , "last_received_id" : INT
}
    //  Example
{
    "action" : "select"
    , "asset_id" : 
        [
            1
            , 2
            , 9
        ]
    , "acknowledged" : "1"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| message_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Unique message ID <br> See [notes](#message-id) |
| asset_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID associated with message <br> See [notes](#asset-id) |
| acknowledged | Optional | INT | [0.. 1] | Message is either (0) not acknowledge or (1) acknowledged |
| method | Optional | INT | [0.. 1] | Message method <br> Message is either (0) Sent or (1) Received |
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-to) | Datetime: return messages that have occured after this datetime. Must be before timestamp_from, if included
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-from) | Datetime: return messages that have occured before this datetime. Must be after timestamp_to, if included
| last_received_id | *Conditional* | INT | [0.. ∞) | Filtering term. <br>Set value = 0 to return last received message, or <br> Set value > 0 to return all messages recieved after value <br> See [notes](#last-received-id)|

<br>

## Notes

### **Message ID**
Identifying ID parameter, unique to each message. 
- Can filter for a singular message with a single INT value.
- Can filter for multiple messages with an array of INT values.
- Cannot be used if also using *last_received_ID* parameter.

### **Asset ID**
Asset ID associated with a message.
- Can filter for a singular asset ID with a single INT value.
- Can filter for multiple asset IDs with an array of INT values.
- Must filter for a singular asset ID if also using *last_received_ID* parameter.

### **Method**
Message method. Message is either (0) Sent or (1) Received.
- Not a mandatory parameter if also using *last_received_ID* parameter, but if included *method* must be set to 1.

### **Timestamp to**
Datetime parameter: Return all messages that occurred BEFORE this datetime. Must be greater than timestamp_from (if included).
- To return all messages that have occurred before this date time, do not include the timestamp_from parameter.
- To return all messages that have occurred within a certain range, include the timestamp_from parameter.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

### **Timestamp from**
Datetime parameter: Return all messages that occurred AFTER this datetime. Must be smaller than timestamp_to (if included).
- To return all messages that have occurred after this date time, do not include the timestamp_to parameter.
- To return all messages that have occurred within a certain range, include the timestamp_to parameter.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

### **Last Received ID**
Filter messages either by last message received, or every message following a particular message.
- Cannot be used in conjunction with *message_id*, *asset_id* when filtering by an ARRAY, or *method* when method != 1.
- Recommended to also include *asset_id* parameter (as singular INT, not ARRAY of INT) to filter by messages for a particular asset.
- If value is 0 then last message received will be returned.
- If value is > 0 then all messages with IDs greater than value will be returned. 


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected message(s).

```json
Content-Type: application/json

{
    "messages": {
            //  Response values
        "message 0": {
            "message_id": INT,
            "asset_id": INT,
            "method": INT,
            "type": INT,
            "message_number": INT,
            "message": STRING,
            "number_index": INT | NULL,
            "acknowledged": INT,
            "iconpath": STRING,
            "timestamp": DATETIME
        },
            //  Example
        "message 1": {
            "message_id": 4,
            "asset_id": 1,
            "method": 1,
            "type": 1,
            "message_number": 24,
            "message": "Entering",
            "number_index": null,
            "acknowledged": 0,
            "iconpath": "speech-bubble.png",
            "timestamp": "2021-07-20 05:31:03"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| message_id | INT | Message ID |
| asset_id | INT | Asset ID associated with the message |
| method | INT | Method is either (0) sent or (1) recieved |
| type | INT | Message type <br> <li> 0 - Invalid <li> 1 - Code <li> 2 - Freetext <li> 3 - Command <br> This functionality is not supported at this time. Default is type 1. |
| message_number | INT | Message code number |
| message | STRING | Message code number string |
| number_index | INT \| NULL | Whitelist index <br> This functionality is not supported at this time. Default is NULL. |
| acknowledged | INT | Message is either (0) not acknowledged, or (1) acknowledged |
| iconpath | STRING | Image associated with this message |
| timestamp | DATETIME | Timestamp of the message |

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
| INVALID_MESSAGE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACKNOWLEDGED_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_METHOD_PRAM | 1. Parameter value is unsupported, or <br> 2. Parameter is not set to "1" while also including *last_received_id* parameter | Alter parameter to valid value |
| INVALID_TIMESTAMP_TO_PRAM | 1. Parameter value is unsupported, or <br> 2. *timestamp_to* value is set to a time before *timestamp_from* value | Alter parameter to valid value |
| INVALID_TIMESTAMP_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LAST_RECEIVED_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INCOMPATIBLE_PRAMS | Request includes an incompatable combination, either: <br> <li> Both *message_id* and *last_received_id*, or <li> Both *asset_id* and *last_received_id*, where *asset_id* is an ARRAY  | Alter or remove parameters to avoid conflict |

<br>

---
---

<br>

# INSERT

Request to insert a new message.

## INSERT REQUEST

```json
POST /v1/Message/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "method" : INT
    , "message_number" : INT
    , "asset_id" : INT
    , "acknowledged" : INT
    , "iconpath" : STRING
    , "type" : INT
    , "number_index" : INT
}
    //  Example
{
    "action" : "insert"
    , "method" : 1
    , "message_number" : 14
    , "asset_id" : 1
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| method | **Required** | INT | [0.. 1] | Message method <br> Message is either (0) Sent or (1) Received |
| message_number | **Required** | INT | [0.. 25] | Message string number. |
| asset_id | **Required** | INT | [0.. ∞) | Asset ID associated with message |
| acknowledged | Optional | INT | [0.. 1] | Message is either (0) not acknowledge or (1) acknowledged <br> If not included, acknowledge will default to (0) not acknowledged |
| iconpath | Optional | STRING | Length <= 100 | Image associated with this message <br> If not included will default to "speech-bubble.png" |
| type | Optional | INT | [0.. 3] | Message type. This functionality is not supported at this time <br> If not included will default to "1" <br> See [notes](#type)|
| number_index | Optional | INT | [0.. ∞) | Whitelist index. This functionality is not supported at this time. <br> If not included will default to NULL. |

<br>

## Notes

### **Type**
Message type <li> 0 - Invalid <li> 1 - Code <li> 2 - Freetext <li> 3 - Command 

This functionality is not supported at this time. Default is type 1.


<br>

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted message information, as well as the generated *message_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "method": INT,
    "message_number": INT,
    "asset_id": INT,
    "acknowledged": INT,
    "iconpath": INT,
    "type": INT,
    "number_index": INT | null,
    "timestamp": DATETIME,
    "message_id": INT,                  //  Generated PARAMETER
    "error": "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "method": "1",
    "message_number": "14",
    "asset_id": "1",
    "acknowledged": 0,
    "iconpath": "speech-bubble.png",
    "type": 1,
    "number_index": null,
    "timestamp": "2021-07-21 01:10:10",
    "message_id": "10",                  //  Generated PARAMETER
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
| MISSING_METHOD_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_MESSAGE_NUMBER_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_METHOD_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MESSAGE_NUMBER_PRAM | 1. Parameter value is unsupported, or <br> 2. Parameter value is not a valid message number | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *asset_id* is not associated with user | Alter parameter to valid value |
| INVALID_ACKNOWLEDGED_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ICONPATH_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_NUMBER_INDEX_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# UPDATE

Request to update an existing message

## UPDATE REQUEST
```json
POST /v1/Message/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "message_id" : INT
    , "acknowledged" : INT
}
    //  Example
{
    "action" : "update"
    , "message_id" : 10
    , "acknowledged" : 1
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| message_id | **Required** | INT | [0.. ∞) | Message ID |
| acknowledged | **Required** | INT | [1] | Message is either (0) not acknowledged, or (1) acknowledged. <br> It is only possible to update a message from (0) not acknowledged to (1) acknowledged |

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated message information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "message_id": INT,
    "acknowledged": INT,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "message_id": "10",
    "acknowledged": "1",
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
| MISSING_MESSAGE_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACKNOWLEDGED_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_MESSAGE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *message_id* is associated with an asset that is not associated with user | Alter parameter to valid value |
| INVALID_ACKNOWLEDGED_PRAM | 1. Parameter value is unsupported, or <br>2. *acknowledged* value is not "1" | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 21-07-2021 by C. Rollinson