# API: AssetWhitelist
 
### Parameters for an asset whitelist number

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

Standard request to select asset whitelist(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all asset whitelist numbers associated with the user.

```json
GET /v1/AssetWhitelist/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all asset whitelists associated with the user will be returned.

```json
POST /v1/AssetWhitelist/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "whitelist_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "asset_number" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action" : "select"
    , "asset_number" : 
        [
            123456
            , "+54321"
            , 987123
        ]
    , "active_status" : "1"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| whitelist_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset whitelist number ID |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID associated with whitelist numbers |
| asset_number | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset whitelist number(s). <br> If including '+' character that number must be STRING type with Length <= 45  |
| active_status | Optional | INT | [0.. 1] | Asset whitelist number is either active (0) or inactive (1) |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected asset whitelist number(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "whitelist_id": INT,
            "asset_id": INT,
            "asset_number": INT | STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "response_1": {
            "whitelist_id": 105,
            "asset_id": 6,
            "asset_number": "+54321",
            "active_status": 1,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-10-11 07:12:57"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| whitelist_id | INT | Asset whitelist number ID |
| asset_id | INT | Asset ID associated with whitelist numbers |
| asset_number | INT \| STRING | Asset whitelist number |
| active_status | INT | Asset whitelist number is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the asset whitelist number |
| last_modified_datetime | DATETIME | Timestamp of last asset whitelist number modification |


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
| INVALID_WHITELIST_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_NUMBER_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new asset whitelist number

## INSERT REQUEST

```json
POST /v1/AssetWhitelist/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "asset_id" : INT
    , "asset_number" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action": "insert"
    , "asset_id" : 8
    , "asset_number" : 
        [ 
            123
            , 12345
            , "+978"
            , 789
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | **Required** | INT | [0.. ∞) | Asset ID to associate whitelist numbers with |
| asset_number | **Required** | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset whitelist number(s). <br> If including '+' character that number must be STRING type with Length <= 45 |
| active_status | Optional | INT | [0.. 1] | Asset whitelist number(s) are either active (0) or inactive (1) <br> This value will default to active (0) if not included in query |

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted asset whitelist information, as well as an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "asset_number": ARRAY,
    "asset_id": INT, 
    "active_status": INT, 
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "error": "NO_ERROR"                  //  Error code
}
    //  Example response
{
    "asset_number": [
        "123",
        "+1345",
        "789"
    ],
    "asset_id": "8",
    "active_status": 0,
    "last_modified_by": 1,
    "last_modified_datetime": "2021-10-11 06:50:17",
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
| INVALID_ASSET_NUMBER_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MISSING_ASSET_NUMBER_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |

<br>

---
---

<br>

# UPDATE

Request to update an existing asset whitelist number

## UPDATE REQUEST
```json
POST /v1/AssetWhitelist/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "whitelist_id" : INT | ARRAY
    , "asset_id" : INT
    , "asset_number" : INT | ARRAY
    , "active_status" : INT
}
    //  Example
{
    "action": "update"
    , "asset_id" :6
    , "asset_number" : 
        [
            123456
            , "+54321"
            , 987123
        ]
    , "active_status" : 0
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| whitelist_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset whitelist numbers ID <br> Not compatible with *asset_id* or *asset_number* parameter. Mandatory if not including *asset_id* or *asset_number* parameter |
| asset_id | *Conditional* | INT | [0.. ∞) | Asset ID associated with the asset whitelist number(s) <br> Not compatible with *whitelist_id* parameter. Mandatory if not including *whitelist_id* parameter |
| asset_number | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset whitelist number(s). <br> If including '+' character that number must be STRING type with Length <= 45 <br> Not compatible with *whitelist_id* parameter. Mandatory if not including *whitelist_id* parameter |
| active_status | Optional | INT | [0.. 1] | Asset whitelist number is either active (0) or inactive (1) |

<br>

## UPDATE RESPONSE (SUCCESS)
Response success returns the updated asset whitelist information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "active_status": INT,
    "whitelist_id": ARRAY,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
  "active_status": "0",
  "whitelist_id": [
    106,
    105,
    107
  ],
  "error": "NO_ERROR"
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
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_NUMBER_PRAM | Missing required parameter | Include required parameter in query |
| INCOMPATABLE_IDENTIFICATION_PARAMS | *whitelist_id* is incompatable with *asset_id* and *asset_number* parameters | Remove incompatable parameters |
| INVALID_WHITELIST_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *whitelist_id* values are not valid *whitelist_id* associated with user. If *whitelist_id* input is an array of *whitelist_id*, this will be accompanied with an "error_detail" message indicating the invalid *whitelist_id*(s) | Alter parameter(s) to valid value |
| INVALID_ASSET_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_ASSET_NUMBER_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *asset_number* values are not valid *asset_number* associated with user. If *asset_number* input is an array of *asset_number*, this will be accompanied with an "error_detail" message indicating the invalid *asset_number*(s) | Alter parameter(s) to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 11-10-2021 by C. Rollinson