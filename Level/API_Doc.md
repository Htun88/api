# API: Level
 
### Parameters for levels

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

Standard request to select level(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all levels.

```json
GET /v1/Level/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all levels will be returned.

```json
POST /v1/Level/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "level_id" : INT | ARRAY
    , "alt_from" : INT | ARRAY
    , "alt_to" : INT | ARRAY
    , "level_name" : STRING
    , "limit" : INT
}
    //  Example
{
    "action" : "select"
    , "level_id" : 
        [
            3
            , 4
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| level_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Altitude range level ID |
| alt_from | Optional | INT \| ARRAY | (∞.. ∞), or <br> ARRAY of (∞.. ∞) | Altitude lower bound |
| alt_to | Optional | INT \| ARRAY | (∞.. ∞), or <br> ARRAY of (∞.. ∞) | Altitude upper bound |
| level_name | Optional | STRING | Length <= 45 | Level name |
| limit | Optional | INT | [0.. ∞) | Maximum number of responses |


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected level(s).

```json
Content-Type: application/json

{
    "levels": {
            //  Response values
        "level_0": {
            "level_id": INT,
            "alt_from": INT,
            "alt_to": INT,
            "level_name": STRING,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "level_1": {
            "level_id": 4,
            "alt_from": 26,
            "alt_to": 30,
            "level_name": "test",
            "last_modified_by": 1,
            "last_modified_datetime": "2021-08-31 07:28:29"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| level_id | INT | Altitude range level ID  |
| alt_from | INT | Altitude lower bound |
| alt_to | INT | Altitude upper bound |
| level_name | STRING | Level name |
| last_modified_by | INT | User ID code that last modified the level |
| last_modified_datetime | DATETIME | Timestamp of last level modification |

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
| INVALID_LEVEL_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_TO_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LEVEL_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new level

Note that the range of *alt_to* -> *alt_from* must be > 0. *alt_to* and *alt_from* must also not be within the range of any other existing level

## INSERT REQUEST

```json
POST /v1/Level/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "alt_to" : INT
    , "alt_from" : INT
    , "level_name" : STRING
}
    //  Example
{
    "action": "insert"
    , "alt_from" : 40
    , "alt_to" : 49
    , "level_name" : "temperature range 40"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| alt_from | **Required**  | INT | (∞.. ∞) | Altitude lower bound <br> Must be lesser than *alt_to* |
| alt_to | **Required**  | INT | (∞.. ∞) | Altitude upper bound <br> Must be greater than *alt_from* |
| level_name | **Required**  | STRING | Length <= 45 | Level name |


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted level information, as well as the generated *level_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "alt_from": INT,
    "alt_to": INT,
    "level_name": STRING,
    "level_id": INT,                     //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "alt_from": "40",
    "alt_to": "49",
    "level_name": "temperature range 40",
    "level_id": "11",                    //  Generated PARAMETER
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
| MISSING_ALT_FROM_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ALT_TO_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_LEVEL_NAME_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ALT_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_TO_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LEVEL_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALTITUDE_RANGE | *alt_from* must not be equal or greater than *alt_to* | Alter parameter(s) to valid value |
| ALTITUDE_RANGE_CONFLICT | *alt_from* and/or *alt_to* falls within the range of an already existing level | Alter parameter(s) to valid value |

<br>

---
---

<br>

# UPDATE

Request to update an existing level

## UPDATE REQUEST
```json
POST /v1/Level/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "level_id" : INT
    , "alt_from" : INT
    , "alt_to" : INT
    , "level_name" : STRING
}
    //  Example
{
    "action": "update"
    , "level_id" : 11
    , "alt_from" : 45
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| level_id | **Required** | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Altitude range level ID |
| alt_from | Optional | INT | (∞.. ∞) | Altitude lower bound <br> Must be lesser than *alt_to*, either also included or current value<br> Must not be within the range of any other existing level |
| alt_to | Optional | INT | (∞.. ∞) | Altitude upper bound <br> Must be greater than *alt_from*, either also included or current value <br> Must not be within the range of any other existing level |
| level_name | Optional | STRING | Length <= 45 | Level name |

<br>

## UPDATE RESPONSE (SUCCESS)
Response success returns the updated level information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "level_id": INT,
    "alt_from": INT,
    "alt_to" : INT,
    "level_name" : STRING,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "level_id": "11",
    "alt_from": "45",
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
| MISSING_LEVEL_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_LEVEL_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_TO_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LEVEL_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALTITUDE_RANGE | *alt_from* must not be equal or greater than *alt_to* | Alter parameter(s) to valid value |
| ALTITUDE_RANGE_CONFLICT | *alt_from* and/or *alt_to* falls within the range of an already existing level | Alter parameter(s) to valid value |

<br>

---
---

<br>

# DELETE

Request to delete a level

## DELETE REQUEST

```json
POST /v1/Level/ HTTP/1.1

    //  Request values
{
    "action" : "delete"
    , "level_id" : INT | ARRAY
}
    //  Example
{
    "action" : "delete"
    , "level_id" : 
        [
            1
            , 2
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "delete" | Request action ||
| level_id | **Required** | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Level ID |


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the deleted *level_id* information, as well as an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "level_id" : INT | ARRAY,             
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "level_id" : 
        [
            1
            , 2
        ],                   
    "error" : "NO_ERROR"                 //  Error code
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
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "delete" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_LEVEL_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_LEVEL_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 01-09-2021 by C. Rollinson