# API: DeviceCustomParameterValues
 
### Parameters for device custom paramater values

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

Standard request to select device custom parameter value(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all device custom parameter value associated with the user.

```json
GET /v1/DeviceCustomParameterValues/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all device custom parameter values associated with the user will be returned.

```json
POST /v1/DeviceCustomParameterValues/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "id" : INT 
    , "device_id" : INT
    , "param_id" : INT

    //  Example
{
    "action": "select"
    , "id": "2"
    , "device_id": "1"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| id | Optional | INT | [0.. ∞) | Device custom param values id |
| device_id | Optional | INT | [0.. ∞) | Device id |
| param_id | Optional | INT | [0.. ∞) | Device custom param id |

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device custom parameter value(s).

```json
Content-Type: application/json

{
    "responses": {
        "response_0": {
            "id": INT,          
            "value": STRING | NULL,
            "device_id": INT | NULL,
            "param_id": INT,
            "param_name": STRING | NULL,
            "param_tag_name": STRING | NULL
        },
        
        "response_1": {
            "id": 2,
            "value": "10",           
            "device_id": 1,
            "param_id": 2,
            "param_name": "BLE Retry Count",
            "param_tag_name": "brcnt"
        },
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| id | INT | Device custom param value id |
| value | STRING | Device custom param value |
| device_id | INT | Device id  |
| param_id | INT | Device custom param id |
| param_name | STRING | Device custom param name |
| param_tag_name | STRING | Device custom param tag name |

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
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT

Request to insert a new device custom parameter value.

If you insert information that already exists, it will update that information with the new values.

An insert request will add +1 to the configuration version of any device currently associated with the device ID

## INSERT REQUEST

```json
POST /v1/DeviceCustomParameterValues/ HTTP/1.1

    //  Request values

{
   "action": "insert",
   "asset_id": INT,
   "param_values": [
        {
                "param_id": INT,
                "value": STRING
        },
        {
                "param_id": INT,
                "value": STRING 
        }
   ]
}

    //  Example 
{
   "action": "insert",
   "asset_id": "1",
   "param_values": [
        {
                "param_id": "47",
                "value": "2"
        },
        {
                "param_id": "37",
                "value": "11" 
        }
   ]
}

```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | *Conditional* | INT \| ARRAY |[0.. ∞) | Asset id. <br> Optional if including *device_id* OR *deviceasset_id* parameter |
| device_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Device id <br> Optional if including *asset_id* OR *deviceasset_id* parameter|
| deviceasset_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Deviceasset id <br> Optional if including *device_id* OR *asset_id* parameter|
| param_values | **Required** | ARRAY | N/A | Array of each param id including values and it's associated with device id |
| value | **Required** | STRING | length = 45 | Device custom param values |
| param_id | **Required** | INT |  [0.. ∞) | Param Id is a reference id of device custom param table |

<br>
<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device custom parameter values information, as well as an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "value": ARRAY | STRING,
    "param_id": ARRAY | INT,
    "device_id": ARRAY | INT,
    "id": ARRAY | INT,         //Autogenerate Ids
    "error": "NO_ERROR"     //Error code
}

    //  Example response
{
    "value": [
        "2",
        "11"
    ],
    "param_id": [
        "47",
        "37"
    ],
    "device_id": 2,
    "id": [
        1825,
        1826
    ],
    "error": "NO_ERROR"  //Error code
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
| INVALID_DEVICE_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_ASSEST_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_DEVICEASSEST_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_PARAM_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_VALUE_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| MISSING_PARAM_VALUES_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_PARAM_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_VALUE_PRAM | Missing required parameter | Include required parameter in query |

<br>

---
---

<br>

# UPDATE

Request to update an existing device custom parameter value

An update request will add +1 to the configuration version of any device currently associated with the device ID

## UPDATE REQUEST
```json
POST /v1/DeviceCustomParameterValues/ HTTP/1.1

    //  Request values
{
   "action": "update",
   "asset_id": INT,
   "param_values": [
        {
                "param_id": INT,
                "value": STRING
        },
        {
                "param_id": INT,
                "value": STRING 
        }
   ]
}

    //  Example 
{
   "action": "update",
   "asset_id": INT,
   "param_values": [
        {
                "param_id": INT,
                "value": STRING
        },
        {
                "param_id": INT,
                "value": STRING
        }
   ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| id | *Conditional* | INT \| ARRAY |[0.. ∞) | Id. <br> is mandatory when *device_d* OR *deviceasset_id* OR *asset_id* is not included |
| asset_id | *Conditional* | INT \| ARRAY |[0.. ∞) | Asset id. <br> Optional if including *device_id* OR *deviceasset_id* OR *id* parameter |
| device_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Device id <br> Optional if including *asset_id* OR *deviceasset_id* OR *id* parameter|
| deviceasset_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Deviceasset id <br> Optional if including *device_id* OR *asset_id* OR *id* parameter| 
| param_values | **Required** | ARRAY | N/A | Array of each param id including values and it's associated with device id |
| value | **Required** | STRING | length = 45 | Device custom param values |
| param_id | **Required** | INT |  [0.. ∞) | Param Id is a reference id of device custom param table |

<br>

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated device custom parameter value information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "device_id": INT,
    "param_id": INT | ARRAY,
    "value": STRING | ARRAY,
    "error": "NO_ERROR"
}

    //  Example response
{
    "device_id": 2,
    "param_id": [
        "47",
        "37"
    ],
    "value": [
        "2",
        "11"
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
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| INVALID_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_OR_DEVICE_ID_OR_ASSET_ID_PRAM | 1. Cannot insert deviceassetschart to *device_id*, or <br> *asset_id*  or <br> *deviceasset_id* not associated with user | Alter parameter to valid value |
| INCOMPATABLE_IDENTIFICATION_PARAMS | One or more parameters are incompatable |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MISSING_PARAM_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_VALUE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_PARAM_VALUES_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_DEVICEASSET_ID_OR_ASSET_ID_OR_DEVICE_ID_PRAM | Missing required parameter | Include required parameter in query |

<br>

---
---

<br>

### Last modified 02-11-2021 by H. Htun