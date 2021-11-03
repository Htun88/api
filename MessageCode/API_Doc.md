# API: MessageCode
 
### Parameters for Message Codes

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

Standard request to select message code(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all message codes.

```json
GET /v1/MessageCode/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all message codes will be returned.

```json
POST /v1/MessageCode/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "id" : INT | ARRAY

}
    //  Example
{
    "action" : "select"
    , "id" : "3"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| id | Optional | INT \| ARRAY | (0.. ∞), or <br> ARRAY of (0.. ∞) | Message code ID <br> Filter by singular ID or an array of IDs |


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected message code(s).

```json
Content-Type: application/json

{
    "message_codes": {
            //  Response values
        "message_code 0": {
            "message_code_id": INT,
            "message": STRING,
            "device_type": STRING
        },
            //  Example
        "message_code 1": {
            "message_code_id": 3,
            "message": "SUCCESS",
            "device_type": "PSM"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| message_code_id | INT | Unique message code ID |
| message | STRING | Message string |
| device_type | STRING | Device type associated with message code |

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
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. "action" parameter value is not "select", or <br> 3. incorrect JSON format | 1. Include required parameter, or <br> 2. alter parameter to "select", or <br> 3. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data exists that could be returned | Change request parameters |
| INVALID_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---


<br>

### Last modified 21-07-2021 by C. Rollinson