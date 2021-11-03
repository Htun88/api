# API: Firmware
 
### Parameters for firmware

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

Standard request to select firmware, either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all firmware.

```json
GET /v1/Firmware/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all firmware will be returned.

```json
POST /v1/Firmware/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "device_provisioning_id" : INT | ARRAY
}
    //  Example
{
    "action" : "select"
    , "device_provisioning_id" : 
        [
            1
            , 2
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| device_provisioning_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Firmware provisioning ID |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected firmware(s).

```json
Content-Type: application/json

{
    "firmware": {
            //  Response values
        "firmware 0": {
            "name" : STRING,
        },
            //  Example
        "firmware 1": {
            "name" : "PSM_9999",
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| name | STRING | Firmware version name |

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
| INVALID_ACTION_PRAM | "action" parameter is not select | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_DEVICE_PROVISIONING_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 26-07-2021 by C. Rollinson