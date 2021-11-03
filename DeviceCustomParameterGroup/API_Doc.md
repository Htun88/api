# API: DeviceCustomParameterGroup
 
### Parameters for an device custom parameter group
 

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

Standard request to select device custom parameter group(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all device custom prameter groups associated with the user.

```json
GET /v1/DeviceCustomParameterGroup/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all device custom prameter group(s) associated with the user will be returned.

```json
POST /v1/DeviceCustomParameterGroup/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "id" : INT
    , "name" : STRING
    , "tag_name" : STRING 
}
    //  Example
{
    "action" : "select"
    , "id" : 1
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| id | Optional | INT | [0.. ∞) | ID of parameter group |
| name | Optional | STRING | Length = 45 | name of parameter group|
| tag_name | Optional | STRING | Length = 45 | tag name of parameter group |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device custom prameter group(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "respone_0": {
              "id" : INT
            , "name" : STRING
            , "tag_name" : STRING
        },

            //  Example
        "response_0": {
             "id": 1
            , "name": "Bluetooth"
            , "tag_name": "ble"
        },
    }
}
```



<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| id | INT | Device Custom Parameter Group Id |
| name | STRING | Device Custom Parameter Group Name |
| tag_name | STRING | Device Custom Parameter Group Tag Name  |


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
| INVALID_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a device custom prameter group

## INSERT REQUEST

```json
POST /v1/DeviceCustomParameterGroup/ HTTP/1.1

    //  Request values
    {
        "action" : "insert"
        , "name" : STRING
        , "tag_name" : STRING
    }
    //  Example
    {
        "action" : "insert"
        , "name" : "Test"
        , "tag_name" : "testTag"
    }
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| name | **Required** | STRING | Length = 45 | Device Custom Parameter Group Name |
| tag_name | **Required** | STRING | Length = 45 | Device Custom Parameter Group Tag Name |


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device custom prameter groups information, as well as the generated *id* and an error: NO_ERROR code
```json
Content-Type: application/json

//  Response values
{
    "id": INT,
    "name": STRING,
    "tag_name": STRING,       //  Generated PARAMETER
    "error": "NO_ERROR"                       //  Error code
}
    //  Example response
{
    "id": "24",
    "name": "test",
    "tag_name": "testTagname",
    "error": "NO_ERROR"     //  Generated PARAMETER
                            //  Error code
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
| MISSING_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TAG_NAME_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# UPDATE

Request to UPDATE an existing device custom prameter groups

## UPDATE REQUEST
```json
POST /v1/DeviceCustomParameterGroup/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "id" : INT
    , "name" : STRING
    , "tag_name" : STRING
}
    //  Example
{
    "action" : "update"
    , "id": "169"
    , "name" : "test"
    , "tag_name" : "test1234"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| id | **Required** | INT | [0.. ∞) | Device Custom Parameter Group ID |
| name | **Required** | STRING | Length = 45 | Device Custom Parameter Group Name |
| tag_name | **Required** | STRING | Length = 45 | Device Custom Parameter Group Tag Name |


<br>

 
## UPDATE RESPONSE (SUCCESS)
Response success returns the update device custom prameter groups information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "id": INT,
    "name": STRING,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "id": "11",
    "name": "test",
    "error" : "NO_ERROR"        //  Error code
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
| INVALID_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 08-10-2021 by H. Htun