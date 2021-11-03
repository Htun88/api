# API: AccessPoint
 
### Parameters for an access point

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

Standard request to select access point(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all access points associated with the user.

```json
GET /v1/AccessPoint/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all access points associated with the user will be returned.

```json
POST /v1/AccessPoint/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "ap_id" : INT
    , "active_status" : INT
    , "type" : INT
    , "mac_address" : STRING
}
    //  Example
{
    "action" : "select"
    , "active_status" : 1
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| ap_id | Optional | INT | [0.. ∞) | Access point ID |
| active_status | Optional | INT | [0.. 1] | Access point is either active (0) or inactive (1) |
| type | Optional | INT | [0.. ∞) | Access point type |
| mac_address | Optional | STRING | Length == 17 <br> Hexadecimal characters and ':' only | Access point MAC address <br> Format: Windows MAC address, eg. <br>12:34:56:78:90:AB |



<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected access point(s).

```json
Content-Type: application/json

{
    "aps": {
            //  Response values
        "ap 0": {
            "ap_id": INT,
            "lat": FLOAT,
            "long": FLOAT,
            "alt": INT,
            "mac_address": STRING,
            "type": INT,
            "description": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "ap 1": {
            "ap_id": 3,
            "lat": "-12.429448",
            "long": "130.888621",
            "alt": 0,
            "mac_address": "12:34:56:78:9A:BC",
            "type": 0,
            "description": "Description Dummy Data",
            "active_status": 1,
            "last_modified_by": 0,
            "last_modified_datetime": "2016-11-08 13:11:11"
        },
        ...
    }
}
```



<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| ap_id | INT | Access point ID  |
| lat | FLOAT | Access point latitude |
| long | FLOAT | Access point longitude |
| alt | INT | Access point altitude |
| mac_address | STRING | Access point MAC address |
| type | INT | Access point type |
| description | STRING | User set access point description string |
| active_status | INT | Access point is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the geofence |
| last_modified_datetime | DATETIME | Timestamp of last geofence modification |


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
| INVALID_AP_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MAC_ADDRESS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a new access point

## INSERT REQUEST

```json
POST /v1/AccessPoint/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "lat" : FLOAT
    , "long" : FLOAT
    , "alt" : INT
    , "mac_address" : STRING
    , "type" : INT
    , "description" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "insert"
    , "lat" : "48.858093"
    , "long" : "2.294694"
    , "alt" : "35"
    , "mac_address" : "01:23:45:67:89:AB" 
    , "type" : "1"
    , "description" : "Eiffel Tower Access Point"
    , "active_status" : "0"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| lat | **Required** | FLOAT | [-90, 90] <br> Values rounded to 6 decimal values | Access point latitude in decimal degrees |
| long | **Required** | FLOAT | [-180, 180] <br> Values rounded to 6 decimal values | Access point longitude in decimal degrees |
| alt | **Required** | INT | (-∞.. ∞) | Access point altitude in metres |
| mac_address | **Required** | STRING | Length == 17 <br> Hexadecimal characters and ':' only | Access point MAC address <br> Format: Windows MAC address, eg. <br>12:34:56:78:90:AB |
| type | **Required** | INT | [0.. ∞) | Access point type |
| description | Optional | STRING | Length <= 100 | User set access point description string |
| active_status | **Required** | INT | [0.. 1] | Access point active status, either active (0) or inactive (1) |

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted access point information, as well as the generated *ap_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "lat": FLOAT,
    "long": FLOAT,
    "alt": INT,
    "mac_address": STRING,
    "type": INT,
    "description": STRING,
    "active_status": INT,
    "ap_id": INT,                       //  Generated PARAMETER
    "error": "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "lat": 48.858093,
    "long": 2.294694,
    "alt": "35",
    "mac_address": "01:23:45:67:89:AB",
    "type": "1",
    "description": "Eiffel Tower Access Point",
    "active_status": "0",
    "ap_id": "31",                      //  Generated PARAMETER
    "error": "NO_ERROR"                 //  Error code
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
| MISSING_LAT_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_LONG_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ALT_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_MAC_ADDRESS_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TYPE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_LAT_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LONG_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MAC_ADDRESS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# UPDATE

Request to update an existing access point

## UPDATE REQUEST
```json
POST /v1/AccessPoint/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "ap_id" : INT
    , "active_status" : INT
    , "lat" : FLOAT
    , "long" : FLOAT
    , "alt" : INT
    , "mac_address" : STRING
    , "type" : INT
    , "description" : STRING   
}
    //  Example
{
    "action" : "update"
    , "ap_id" : "2"
    , "active_status" : "0"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| ap_id | **Required** | INT | [0.. ∞) | Access point ID | 
| active_status | Optional | INT | [0.. 1] | Access point is either active (0) or inactive (1) |
| lat | Optional | FLOAT | [-90, 90] <br> Values rounded to 6 decimal values | Access point latitude in decimal degrees | 
| long | Optional | FLOAT | [-180, 180] <br> Values rounded to 6 decimal values | Access point longitude in decimal degrees  | 
| alt | Optional | INT| (-∞.. ∞) | Access point altitude | 
| mac_address | Optional| STRING | Length == 17 <br> Hexadecimal characters and ':' only | Access point MAC address <br> Format: Windows MAC address, eg. <br>12:34:56:78:90:AB |
| type | Optional | INT | [0.. ∞) | Access point type | 
| description | Optional | STRING | Length <= 100 | User set access point description string | 


<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated access point information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "ap_id": INT,
    "active_status": INT,
    "lat": FLOAT,
    "long": FLOAT,
    "alt": INT,
    "mac_address": STRING,
    "type": INT,
    "description": STRING,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "ap_id": "2",
    "active_status": "0",
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
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "update" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_UPDATED_PRAM | No updatable parameter provided | Include minimum 1 updatable parameter in query |
| MISSING_AP_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_AP_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_LAT_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LONG_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ALT_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MAC_ADDRESS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 12-08-2021 by C. Rollinson