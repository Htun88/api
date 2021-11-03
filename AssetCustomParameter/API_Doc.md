# API: AssetCustomParameter
 
### Parameters for an asset custom parameter

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

Standard request to select asset custom parameter(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all asset custom parameter(s) associated with the user.

```json
GET /v1/AssetCustomParameter/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all asset custom parameters associated with the user will be returned.

```json
POST /v1/AssetCustomParameter/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "id" : INT | ARRAY
    , "name" : STRING
    , "tag_name" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "select"
    , "id" : 
        [
            1
            , 3
            , 5
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset custom parameter ID |
| name | Optional | STRING | Length <= 45 | Asset custom parameter name |
| tag_name | Optional | STRING | Length <= 45 | Asset custom parameter tag name |
| active_status | Optional | INT | [0.. 1] | Asset is either active (0) or inactive (1) |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected asset custom parameter(s).

```json
Content-Type: application/json

{
    "assetcustom": {
            //  Response values
        "assetcustom 0": {
            "id" : INT,
            "name" : STRING,
            "tag_name" : STRING,
            "default_value" : STRING | NULL,
            "active_status" : INT,
            "last_modified_by" : INT,
            "last_modified_datetime" : DATETIME
        },
            //  Example
        "assetcustom 1": {
            "id": 3,
            "name": "Push To talk Channel ID",
            "tag_name": "xbch",
            "default_value": "1",
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-07-20 02:19:21"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| id | INT | Asset custom parameter ID |
| name | STRING | Asset custom parameter name |
| tag_name | STRING | Asset custom parameter tag name |
| default_value | STRING | Asset custom parameter default value |
| active_status | INT | Asset custom parameter is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the asset custom parameter |
| last_modified_datetime | DATETIME | Timestamp of last asset custom parameter modification |


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
| INVALID_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new asset custom paramter

## INSERT REQUEST

```json
POST /v1/AssetCustomParameter/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "name" : STRING
    , "tag_name" : STRING
    , "default_value" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "insert"
    , "name" : "On Call Phone Number"
    , "tag_name" : "enum"
    , "active_status" : "1"
}
    //  Example with default value
{
    "action" : "insert"
    , "name" : "On Call Phone Number"
    , "tag_name" : "enum"
    , "default_value" : "+12 3456 7890"
    , "active_status" : "1"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| name | **Required** | STRING | Length <= 45 | Asset custom parameter name |
| tag_name | **Required** | STRING | Length <= 45 | Asset custom parameter tag name |
| default_value | Optional | STRING | Length <= 50 | Asset custom parameter default value <br> Defaults to NULL if not included |
| active_status | **Required** | INT | [0.. 1] | Asset custom parameter is either active (0) or inactive (1) |


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted asset custom parameter information, as well as the generated *id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "name" : STRING,
    "tag_name" : STRING,
    "default_value" : STRING,
    "active_status" : INT,
    "id" : INT,                          //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "name": "On Call Phone Number",
    "tag_name": "enum",
    "active_status": "1",
    "id" : 10,                           //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response with default value
{
    "name": "On Call Phone Number",
    "tag_name": "enum",
    "default_value": "+12 3456 7890",
    "active_status": "1",
    "id" : 10,                           //  Generated PARAMETER
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
| MISSING_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TAG_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEFAULT_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# UPDATE
Request to update an existing asset custom parameter

## UPDATE REQUEST
```json
POST /v1/AssetCustomParameter/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "id" : INT
    , "name" : STRING
    , "tag_name" : STRING
    , "default_value" : STRING
    , "active_status" : INT
 
}
    //  Example
{
    "action" : "update"
    , "id" : "10"
    , "active_status" : "0"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| id | **Required** | INT | [0.. ∞) | Asset custom parameter ID |
| name | Optional | STRING | Length <= 45 | Asset custom parameter name |
| tag_name | Optional | STRING | Length <= 45 | Asset custom parameter tag name |
| default_value | Optional | STRING | Length <= 50 | Asset custom parameter default value |
| active_status | Optional | INT | [0.. 1] | Asset custom parameter is either active (0) or inactive (1) |


<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated asset custom parameter information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "id": INT,
    "name": STRING,
    "tag_name": STRING,
    "default_value": STRING,
    "active_status": INT,
    "error": "NO_ERROR"        //  Error code   
}
    //  Example
{
    "id": "10",
    "active_status": "0",
    "error": "NO_ERROR"        //  Error code
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
| MISSING_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEFAULT_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 20-07-2021 by C. Rollinson