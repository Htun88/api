# API: DeviceProvisioningLink
 
### Parameters for establishing device provisioning links

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
- [DELETE](#delete)
  - [REQUEST](#delete-request)
  - [RESPONSE (SUCCESS)](#delete-response-success)
  - [RESPONSE (FAILURE)](#delete-response-failure)

<br>

---
---

<br>

# SELECT

Standard request to select device provisioning link(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all device provisioning links.

```json
GET /v1/DeviceProvisioningLink/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all device provisioning links will be returned.

```json
POST /v1/DeviceProvisioningLink/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "provisioning_link_id" : INT | ARRAY
    , "provisioning_id" : INT | ARRAY
    , "module_value" : INT | ARRAY
    , "module_id" : INT | ARRAY
    , "module_name" : STRING | ARRAY
    , "param_value" : INT | ARRAY
    , "param_id" : INT | ARRAY
    , "param_name" : STRING | ARRAY
    , "sd_id" : INT | ARRAY
    , "active_status" : INT | ARRAY
    , "limit" : INT | ARRAY
}
    //  Example
{
    "action": "select"
    , "provisioning_id" : 4
    , "module_id" : 
        [
            1
            , 12
        ]
    , "param_value" : 
        [
            0
            , 1
            , 2
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| provisioning_link_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device provisioning link unique ID |
| provisioning_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device provisioning ID |
| module_value | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor module ID, device side  |
| module_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor module ID, database side |
| module_name | Optional | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Sensor module name / type |
| param_value | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Unique ID param number within a sensor module, device side |
| param_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Unique ID param number, database side |
| param_name | Optional | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Param name / type |
| sd_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor definition ID |
| active_status | Optional | INT | [0.. 1] | Device provisioning link is either active (0) or inactive (1) |
| limit | Optional | INT | [0, ∞) | Limit maximum number of responses <br> Default is 1000 responses maximum |
<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected device provisioning link(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "provisioning_link_id": INT,
            "provisioning_id": INT,
            "module_value": INT,
            "module_id": INT,
            "module_name": STRING,
            "param_value": INT,
            "param_id": INT,
            "param_name": STRING,
            "sd_id": INT,
            "sd_name": STRING,
            "sd_data_type": STRING,
            "sendvia": STRING,
            "calib_id": INT,
            "calib_name": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "response_1": {
            "provisioning_link_id": 140,
            "provisioning_id": 4,
            "module_value": 4,
            "module_id": 12,
            "module_name": "GNSS",
            "param_value": 1,
            "param_id": 10,
            "param_name": "GNSS Longitude",
            "sd_id": 13,
            "sd_name": "GPS - Long",
            "sd_data_type": "Int32",
            "sendvia": "AUTO",
            "calib_id": 1,
            "calib_name": "NONE",
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-09-01 16:22:34"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| provisioning_link_id | INT | Device provisioning link unique ID |
| provisioning_id | INT | Device provisioning ID |
| module_value | INT | Sensor module ID, device side |
| module_id | INT | Sensor module ID, database side |
| module_name | STRING | Sensor module name / type |
| param_value | INT | Unique ID param number within a sensor module, device side |
| param_id | INT | Unique ID param number, database side |
| param_name | STRING | Param name / type |
| sd_id | INT | Sensor definition ID |
| sd_name | STRING | Sensor definition name |
| sd_data_type | STRING | Sensor data type |
| sendvia | STRING | Sensor sending method |
| calib_id | INT | Calibration ID |
| calib_name | STRING | Calibration name / type |
| active_status | INT | Device provisioning link is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the device provisioning link |
| last_modified_datetime | DATETIME | Timestamp of last device provisioning link modification |


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
| INVALID_ACTION_PRAM | "action" parameter is not select, delete or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_PROVISIONING_LINK_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PROVISIONING_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MODULE_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MODULE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MODULE_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new device provisioning link.

This will also increment the configuration version of any device using the associated provisioning ID.

## INSERT REQUEST

```json
POST /v1/DeviceProvisioningLink/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "provisioning_id" : INT
    , "values" : 
        [
            {
                "sd_id" : INT
                , "module_id" : INT
                , "module_value" : INT
                , "param_id" : INT
                , "param_value" : INT
                , "active_status" : INT
                , "sendvia" : STRING
                , "calib_id" : INT
            },
            ...
        ]
}
    //  Example
{
    "action" : "insert"
    , "provisioning_id" : 11
    , "values" : 
        [
            {
                "sd_id" : 18
                , "module_id" : 3
                , "param_value" : 8
                , "module_value" : 2
            },
            {
                "sd_id" : 19
                , "module_id" : 3
            }
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| provisioning_id | **Required** | INT | [0.. ∞) | Provisioning ID |
| values | **Required** | ARRAY | N/A | Array of seperate objects to insert |
| sd_id | **Required** | INT | [0.. ∞) | Sensor definition ID <br> Unique *sd_id* required for each insert object |
| module_id | **Required** | INT | [0.. ∞) | Developer defined module ID for grouping individual *sd_id* as a module on a database level. While any valid *module_id* can be utilised, it is recommended to use a *module_id* with suitable description for the *sd_id* or to create a new *module_id* with an apt description (see the *module_id* API for more information) <br> *module_id* is uniquely paired with *module_value* per *provisioning_id* |
| module_value | Optional | INT | [0.. ∞) | Developer defined module ID for grouping individual *sd_id* as a module on a device level <br> *module_value* is uniquely paired with *module_id* per *provisioning_id* <br> If *module_value* is not included in the request but the objects *module_id* is already associated with a *module_value* (either elsewhere in the request or in the database) then that *module_value* will be automatically used <br> If *module_value* is not included in the request and the objects *module_id* is NOT already associated with a *module_value* (either elsewhere in the request or in the database) then the lowest unused *module_value* will be automatically used |
| param_id | Optional | INT | [0.. ∞) | Developer defined unique parameter ID for identifying *sd_id* <br> Each unique *param_id* must be a *param_id* that is associated with the objects *module_id* |
| param_value | Optional | INT | [0.. ∞) | Developer defined unique identifying value for an *sd_id* within each *module_value*/*module_id* on a device level <br> If *param_value* is not included in the request then the lowest unused *param_value* for the *module_value*/*module_id* will be automatically used |
| active_status | Optional | INT | [0.. 1] | Provisioning link is either active (0) or inactive (1) <br> If *active_status* is not included in the request then the *active_status* will default to (0) active |
| sendvia | Optional | STRING | Length <= 4 | Sending method for particular sensor data <br> Accepted values: "AUTO", "GSM", "SAT", "XBEE", "WIFI" <br> If *sendvia* is not included in the request then the *sendvia* will default to "AUTO" |
| calib_id | Optional | INT | [0.. ∞) | <br> If *calib_id* is not included in the request then the *calib_id* will default to 1 |

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted device provisioning information, as well as an error: NO_ERROR code.

```json
Content-Type: application/json

    //  Response values
{
    "provisioning_id" : INT,
    "values": [
        {
            "sd_id": INT,
            "module_id": INT,
            "module_value": INT,
            "param_id": INT,
            "param_value": INT,
            "active_status": INT,
            "sendvia": STRING,
            "calib_id": INT
        },
        ...
    ],
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "provisioning_id": "11",
    "values": [
        {
            "sd_id": "18",
            "module_id": "3",
            "module_value": "2",
            "param_id": 48,
            "param_value": "8",
            "active_status": 0,
            "sendvia": "AUTO",
            "calib_id": 1
        },
        {
            "sd_id": "19",
            "module_id": "3",
            "module_value": "2",
            "param_id": 50,
            "param_value": 1,
            "active_status": 0,
            "sendvia": "AUTO",
            "calib_id": 1
        }
    ],        
    "error": "NO_ERROR"
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
| INVALID_ACTION_PRAM | "action" parameter is not select, delete or insert | Alter parameter to "insert" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_PROVISIONING_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_VALUES_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SD_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_MODULE_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_SD_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. One or more *sd_id* is not a valid *sd_id*. If more than one *sd_id* input is invalid this will be accompanied with an "error_detail" message indicating the invalid *sd_id*(s) | Alter parameter(s) to valid value |
| INVALID_MODULE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MODULE_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_PARAM_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SENDVIA_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_CALIB_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. One or more *calib_id* is not a valid *calib_id*. If more than one *calib_id* input is invalid this will be accompanied with an "error_detail" message indicating the invalid *calib_id*(s) | Alter parameter(s) to valid value |
| MODULE_ID_HAS_NO_FREE_PARAM_ID_ASSOCIATIONS | One or more *module_id* values do not have any free *param_id* associations to satisfy the request. This may be because there are none to begin with or the quantity of automatic *param_id* assignments required is greater than the available free *param_id*. <br> This error message will be accompanied with an "error_detail" message indicating the invalid *module_id*(s) and the affected *sd_id* | 1. Alter request *module_id* as necessary, or <br> 2. create more *param_id* - *module_id* associations as necessary (see *param_id* API) |
| MODULE_ID_HAS_DUPLICATE_PARAM_VALUES | One or more *module_id* containst duplicate *param_value*(s). *param_value* must be unique within each *module_id* association. This error message will be accompanied with an "error_detail" message indicating each erroneous *module_id*, each duplicate *param_value* within the *module_id* and each *sd_id* associated with the *param_value* | Alter parameter(s) to valid value(s) |
| DUPLICATE_PARAM_ID | One or more *param_id* values in the request are duplicate values. *param_id* must be unique values. <br> This error message will be accompanied with an "error_detail" message indicating the invalid *param_id*(s) and the *sd_id* associated with the duplicate *param_id* | Alter parameter(s) to valid value(s) |
| MULTIPLE_MODULE_ID_HAVE_THE_SAME_MODULE_VALUE | One or more *module_id* values in the request are associated with the same *module_value*. *module_id* and *module_value* associations must be unique. <br> This error message will be accompanied with an "error_detail" message indicating the erroneous *module_value*(s), its *module_id* associations and the *sd_id* associated with each of the erroneous *module_value* and *module_id* associations | Alter request to have a unique *module_value* for each unique *module_id* |
| MODULE_ID_HAS_DUPLICATE_MODULE_VALUES | One or more unique *module_id* values in the request have multiple non-unique *module_value* associations. *module_id* and *module_value* associations must be unique. <br> This error message will be accompanied with an "error_detail" message indicating the erroneous *module_id*(s), its *module_value* associations and the *sd_id* associated with each of the *module_value*s | Alter request to have a unique *module_value* for each unique *module_id* |
| MODULE_ID_IS_NOT_ASSOCIATED_WITH_GIVEN_PARAM_ID| One or more *param_id* values are not associated with the correct *module_id* value. Each *module_id* can only be paired with specific *param_id* values. <br> This error message will be accompanied with an "error_detail" message indicating the incorrectly paired *module_id*(s) and which *sd_id* they are associated with | Alter parameter(s) to valid value(s) |
| DUPLICATE_REQUEST_SD_ID_PRAM | One or more *sd_id* is not unique. *sd_id* values must be unique. <br> This error message will be accompanied with an "error_detail" message indicating the duplicate *sd_id* value(s) | Alter parameter(s) to valid value |
| SD_ID_ALREADY_IN_USE | One or more *sd_id* values are already associated with this *provisioning_id*. *sd_id* - *provisioning_id* associations must be unique. <br> This error message will be accompanied with an "error_detail" message indicating the erroneous *sd_id*(s) | Alter parameter(s) to valid value |
| MODULE_VALUE_ALREADY_ASSOCIATED_WITH_EXISTING_MODULE_ID | One or more *module_id* values are associated with a *module_value* that is already associated with a different *module_id*. Each unique *module_id* is uniquely associated with one *module_value*. <br> This error message will be accompanied with an "error_detail" message indicating the erroneous *module_id*, its request *module_value*, which *module_id* the *module_value* belongs to and all erroneous *sd_id*| Alter request to have the correct unique *module_value* for each unique *module_id* |
| EXISTING_MODULE_ID_AND_INSERTING_MODULE_ID_HAVE_DIFFERENT_MODULE_VALUE | One or more *module_id* values have *module_value* value that is different to the *module_value* stored in the database for the *module_id*. Each unique *module_id* is uniquely associated with one *module_value*. <br> This error message will be accompanied with an "error_detail" message indicating the invalid *module_id*(s), its associated *module_value* from the request, the *module_value* stored in the database and the erroneous *sd_id*(s) associated with the *module_id* | Alter request to have the correct unique *module_value* for each unique *module_id* |
| PARAM_ID_DOES_NOT_EXIST | One or more *param_id* values are not valid *param_id*(s). <br> This error message will be accompanied with an "error_detail" message indicating the invalid *param_id*(s) and the associated *sd_id*(s) | Alter parameter(s) to valid value |
| PARAM_ID_ALREADY_UTILISED | One or more *param_id* are already associated with another *sd_id*. *param_id* and *sd_id* associations must be unique. <br> This error message will be accompanied with an "error_detail" message indicating the invalid *param_id*(s) and the associated *sd_id*(s) | Alter parameter(s) to valid value |
| MODULE_ID_HAS_NO_PARAM_ID_ASSOCIATIONS | One or more *module_id* are valid *module_id* that are not associated with a *param_id*. Each device provisioning link requires that each *module_id* has at least one valid *param_id* association. <br> This error message will be accompanied with an "error_detail" message indicating the invalid *module_id*(s) and the associated *sd_id*(s) | Alter *module_id*(s) to a *module_id* with *param_id* association or create new *param_id* association(s) for the affected *module_id*(s) (see *param* API) |
| MODULE_ID_DOES_NOT_EXIST | One or more *module_id* values are not valid *module_id*(s). <br> This error message will be accompanied with an "error_detail" message indicating the invalid *module_id*(s) and the associated *sd_id*(s) | Alter parameter(s) to valid value |
| PARAM_VALUE_ALREADY_ASSOCIATED | One or more *module_id* contains one more *param_value* that is already associated with this *module_id*. Each *param_value* within each *module_id* must be unique <br> This error message will be accompanied with an "error_detail" message indicating the invalid *module_id*(s), the erroneous *param_values* and their associated *sd_id*(s) | Alter parameter(s) to valid value |

<br>

---
---

<br>

# DELETE

Request to delete an existing device provisioning link.

This will also increment the configuration version of any device using the associated provisioning ID.

## DELETE REQUEST
```json
POST /v1/DeviceProvisioningLink/ HTTP/1.1

    //  Request values
{
    "action" : "delete"
    , "provisioning_link_id" : INT
    , "provisioning_id" : INT | ARRAY
    , "module_value" : INT | ARRAY
    , "module_id" : INT | ARRAY
    , "param_id" : INT | ARRAY
    , "sd_id" : INT | ARRAY 
}
    //  Example
{
    "action" : "delete"
    , "provisioning_id" : 11
    , "sd_id" : 
        [
            17
            , 18
            , 19
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "delete" | Request action |
| provisioning_id | **Required** | INT | [0.. ∞) | Device provisioning ID |
| provisioning_link_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Unique device provisioning link ID <br> At least one of *provisioning_link_id*, *module_value*, *module_id*, *param_id* or *sd_id* is required | |
| module_value | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor module ID, device side <br> At least one of *provisioning_link_id*, *module_value*, *module_id*, *param_id* or *sd_id* is required |
| module_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor module ID, database side <br> At least one of *provisioning_link_id*, *module_value*, *module_id*, *param_id* or *sd_id* is required |
| param_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Unique param ID <br> At least one of *provisioning_link_id*, *module_value*, *module_id*, *param_id* or *sd_id* is required |
| sd_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Unique sensor ID <br> At least one of *provisioning_link_id*, *module_value*, *module_id*, *param_id* or *sd_id* is required |

<br>

<br>

## DELETE RESPONSE (SUCCESS)
Response success returns the deleted provisioning link information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "action": INT,
    "provisioning_id": INT,
    "provisioning_id" : ARRAY,
    "module_value" : ARRAY,
    "module_id" : ARRAY,
    "param_id" : ARRAY,
    "sd_id" : ARRAY,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "action": "delete",
    "provisioning_id": "11",
    "sd_id": [
        "17",
        "18",
        "19"
    ],
    "error" : "NO_ERROR"        //  Error code
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
| INVALID_ACTION_PRAM | "action" parameter is not select, delete or insert | Alter parameter to "delete" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| MISSING_PROVISIONING_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_IDENTIFICATION_PRAM | Missing required parameter. At least one of *provisioning_id*, *module_value*, *module_id*, *param_id* or *sd_id* is also required | Include at least one required parameter in query |
| INVALID_PROVISIONING_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. One or more values are not valid *provisioning_id* | Alter parameter to valid value |
| INVALID_PROVISIONING_LINK_PROVISIONING_ID_PRAM | 1. *provisioning_id* is not associated with any device provisioning link values, or <br> 2. No device provisioning link values have been returned for the given search value(s) | Alter parameter to valid value(s) |
| INVALID_PROVISIONING_LINK_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MODULE_VALUE_PRAM | Parameter value is unsupported  | Alter parameter to valid value |
| INVALID_MODULE_ID_PRAM | Parameter value is unsupported <br> 2. One or more *module_id* values are not valid *module_id* <br> This error will be accompanied with an error detail indicating the erroneous *module_id*(s) | Alter parameter(s) to valid value |
| INVALID_PARAM_ID_PRAM | Parameter value is unsupported <br> 2. One or more *param_id* values are not valid *param_id* <br> This error will be accompanied with an error detail indicating the erroneous *param_id*(s) | Alter parameter(s) to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported <br> 2. One or more *sd_id* values are not valid *sd_id* <br> This error will be accompanied with an error detail indicating the erroneous *sd_id*(s) | Alter parameter(s) to valid value |
| INVALID_PROVISIONING_LINK_MODULE_VALUE_PRAM | One or more*module_value* values does not exist for this *provisioning_id*. <br> This error will be accompanied with an error detail indicating the erroneous *module_value*(s) | Alter parameter(s) to valid value |
| INVALID_PROVISIONING_LINK_MODULE_ID_PRAM | One or more*module_id* values does not exist for this *provisioning_id*. <br> This error will be accompanied with an error detail indicating the erroneous *module_id*(s) | Alter parameter(s) to valid value |
| INVALID_PROVISIONING_LINK_PARAM_ID_PRAM | One or more *param_id* values does not exist for this *provisioning_id*. <br> This error will be accompanied with an error detail indicating the erroneous *param_id*(s) | Alter parameter(s) to valid value |
| INVALID_PROVISIONING_LINK_SD_ID_PRAM | One or more *sd_id* values does not exist for this *provisioning_id*. <br> This error will be accompanied with an error detail indicating the erroneous *sd_id*(s) | Alter parameter(s) to valid value |

<br>

---
---

<br>

### Last modified 02-11-2021 by C. Rollinson