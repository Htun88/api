# API: ModuleDefinition
 
### Parameters for a module definition

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

Standard request to select module definitions(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all module definitions.

```json
GET /v1/ModuleDefinition/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all module definitions will be returned.

Request values are ordered by *id* ascending.

```json
POST /v1/ModuleDefinition/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "id" : INT | ARRAY
    , "module_name" : STRING | ARRAY
}
    //  Example
{
    "action" : "select"
    , "id" : 
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
| id | Optional | INT \| ARRAY | [0.. ∞) or <br> ARRAY of [0.. ∞) | Module ID |
| module_name | Optional | STRING \| ARRAY | Length <= 45 or <br> ARRAY of Length <= 45) | Module definition name |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected module definition(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "id": 1,
            "module_name": "Accelerometer"
        },
            //  Example
        "response_1": {
            "id": 2,
            "module_name": "Audio"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| id | INT | Module definition ID |
| module_name | STRING | Module definition name |

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
| INVALID_MODULE_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a new module definition

## INSERT REQUEST

```json
POST /v1/ModuleDefinition/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "module_name" : STRING | ARRAY
}
    //  Example
{
    "action" : "insert"
    , "module_name" : 
        [
            "String1"
            , "String2"
            , "String3"
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| module_name | **Required** | STRING \| ARRAY | Length <= 45 or <br> ARRAY of Length <= 45) | Module definition name |


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted module definition information, as well as an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "module_name" : STRING | ARRAY,
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "module_name": [
        "String1",
        "String2",
        "String3"
    ],
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
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_MODULE_NAME_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_MODULE_NAME_PRAM | One or more *module_name* values are invalid | Alter parameters to valid values |
| MODULE_NAME_ALREADY_EXISTS | One or more *module_name* already exists. *module_name* must be unique, case insensitive. This error message will be accompanied with an error detail identifying the erroneous *module_name*(s) | Alter parameters to valid unique values |

<br>

---
---

<br>

# UPDATE

Request to update an existing module definition

## UPDATE REQUEST
```json
POST /v1/ModuleDefinition/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "id" : INT
    , "module_name" : STRING 
}
    //  Example
{
    "action" : "update"
    , "id" : 30
    , "module_name" : "NewNameString"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| PARAMETER | **Required** | INT | [0.. ∞) | Module definition ID |
| PARAMETER | **Required** | STRING | Length <= 45 | Module definition name |

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated module definition information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "id": INT,
    "module_name" : STRING,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "id": "30",
    "module_name": "NewNameString",
    "error": "NO_ERROR"         //  Error code
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
| MISSING_MODULE_NAME_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *id* is not a valid module definition *id* | Alter parameter to valid value |
| INVALID_MODULE_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MODULE_NAME_ALREADY_EXISTS | *module_name* is already associated with another module definition. *module_name* values must be unique, case insensitive. | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 02-11-2021 by C. Rollinson