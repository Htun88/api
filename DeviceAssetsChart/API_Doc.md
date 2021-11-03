# API: DeviceAssetsChart
 
### Parameters for DeviceAssetsChart

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

Standard request to select deviceassetschart data, either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all deviceassetschart data associated with the user, up to the 1000 most recent data values.

```json
GET /v1/Deviceassetschart Data/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all deviceassetschart data associated with the user will be returned.

```json
POST /v1/Deviceassetschart Data/ HTTP/1.1

    //  Request values
{
    "action" : "select"
,   "deviceasset_id" : INT | ARRAY
,   "device_id" : INT | ARRAY
,   "asset_id" : INT | ARRAY
,   "sd_id" : INT | ARRAY   
,   "limit" : INT    
}

    //  Example
{
    "action" : "select"
,   "deviceasset_id": "59"
,   "device_id": "59"
,   "asset_id" : "29"
,   "limit" : 20   
,   "sd_id" : [ 9, 10 ]                               //  Return sd_id values

}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| deviceasset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset ID |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| sd_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor Definition ID |
| limit | Optional | INT | [0.. ∞) | Limit the number of responses. <br> If parameter not specified a default limit cap of 1000 responses is used |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected deviceassetschart data.

```json
Content-Type: application/json
{
    "responses": {
            //  Response values
        "response_0": {
            "deviceasset_id": INT,
            "device_id": INT,
            "asset_name": STRING,
            "asset_id": INT,
            "sd_id": INT,
            "gauge": INT,
            "trend": STRING,
            "uom_show_id": STRING
        },
            //  Example
        "response_1": {
            "deviceasset_id": 46,
            "device_id": 59,
            "asset_name": "Jess",
            "asset_id": 29,
            "sd_id": 9,
            "gauge": 0,
            "trend": 0,
            "uom_show_id": 3
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| deviceasset_id | INT | Deviceasset ID |
| device_id | INT | Device ID |
| asset name | STRING | Asset Name |
| asset_id | INT | Asset ID |
| sd_id | INT | Sensor definition ID |
| gauge | INT | Gauge |
| trend | INT | Trend |
| uom_show_id | INT | Unit of conversion Id |



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
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

# INSERT

Request to insert new deviceassetschart data

## INSERT REQUEST

```json
POST /v1/DeviceAssetsChart Data/ HTTP/1.1

    //  Request values
{
    "action": "insert",
    "deviceasset_id" : INT,
    "chart_options": {
           "sd_id": {
                    "uom_to_id": INT,
                    "gauge": INT,
                    "trend": INT
                },
            "sd_id": {
                    "uom_to_id": INT,
                    "gauge": INT,
                    "trend": INT
                }
        }
}

    //  Example{
{
    "action" : "insert"
,   "deviceasset_id" : 29
,   "chart_options": {
           "37": {
                    "uom_to_id": 7,
                    "gauge": 1,
                    "trend": 1
                },
            "38": {
                    "uom_to_id": 25,
                    "gauge": 1,
                    "trend": 1
                }
        }
}

```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | *Conditional* | INT \| ARRAY |[0.. ∞) | Asset id. <br> Optional if including *device_id* OR *deviceasset_id* parameter |
| device_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Device id <br> Optional if including *asset_id* OR *deviceasset_id* parameter|
| deviceasset_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Deviceasset id <br> Optional if including *device_id* OR *asset_id* parameter|
| chart_options | **Required** | ARRAY | N/A | Array of each sensor definition id which include uom_to_id, gauge, trend and it's associated with deviceasset id |
| sd_id | **Required** | INT | [0.. ∞) | sensor definition id is a reference id of sensor defintion table |
| uom_to_id | **Required** | INT | ( ∞,  ∞) | Unit of conversion id is a reference id of uom conversions table <br> See [notes](#uom-conversion) |
| gauge | **Required** | INT \| BOOLEAN | ( ∞,  ∞) | Gauge is either active (1) or inactive (0) <br> See [notes](#gauge) |
| trend | **Required** | INT \| BOOLEAN | ( ∞,  ∞) | Trend is either active (1) or inactive (0) <br> See [notes](#trend) | 

<br>

### **UOM Conversion**
- This uom id will check if there is a valid uom conversion id or not. If not, it will throw an error *invalid uom_to_id*.
- A vaild uom conversion id meaning sensor definition sd_uom_id must exist in the sensor_def table and must be associated with sensor_def sd_id and deviceasset_id. 

### **Gauge**
- If the sensor definition chart is disabled (0), deviceassetschart gauge has be to disabled (0).
- If the sensor_def chart is (1) enabled, either gague or trend should be enabled (1).
  
### **Trend** 
- If the sensor definition chart is disabled (0), deviceassetschart trend has be to disabled (0).
- If the sensor_def chart is (1) enabled, either gague or trend should be enabled (1).
  
<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted deviceassetschart data information, as well as the generated *deviceassetschart_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "deviceasset_id" : INT | ARRAY,
    "sd_id" : INT | ARRAY,
    "asset_id" : INT | ARRAY,
    "device_id" : INT | ARRAY,
    "gauge" : INT | ARRAY,
    "trend" : INT | ARRAY,
    "uom_to_id" : INT | ARRAY,   
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,               
    "error" : "NO_ERROR"                 //  Error code
}

    //  Example response
{
    "deviceasset_id": 29,
    "sd_id": [
        "37",
        "38"
    ],
    "gauge": [
        "1",
        "1"
    ],
    "trend": [
        "1",
        "1"
    ],
    "uom_to_id": [
        "7",
        "7"
    ],
    "last_modified_by": 1,
    "last_modified_datetime": "2021-11-01 03:55:33",
    "error": "NO_ERROR"       // Error Code
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
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_OR_DEVICE_ID_OR_ASSET_ID_PRAM | 1. Cannot insert deviceassetschart to *device_id*, or <br> *asset_id*  or <br> *deviceasset_id* not associated with user | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_UOM_TO_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GAUGE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TREND_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GAUGE_TREND_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MISSING_DEVICEASSET_ID_OR_DEVICE_ID_OR_ASSET_ID_PRAM | Mandatory parameter is missing | Include at least one missing parameter (*asset_id* or *device_id* or *deviceasset_id*) |
| MISSING_CHART_OPTIONS_PRAM | Mandatory parameter is missing | Include the parameter |

<br>

<br>

<br>

# UPDATE

Request to update an existing deviceasseschart parameter value

## UPDATE REQUEST
```json
POST /v1/DeviceCustomParameterValues/ HTTP/1.1

    //  Request values
{
    "action": "update",
    "deviceasset_id" : INT,
    "chart_options": {
           "sd_id": {
                    "uom_to_id": INT,
                    "gauge": INT,
                    "trend": INT
                },
            "sd_id": {
                    "uom_to_id": INT,
                    "gauge": INT,
                    "trend": INT
                }
        }
}

    //  Example 
{
    "action" : "update"
,   "deviceasset_id" : 29
,   "chart_options": {
           "37": {
                    "uom_to_id": 7,
                    "gauge": 1,
                    "trend": 1
                },
            "38": {
                    "uom_to_id": 25,
                    "gauge": 1,
                    "trend": 1
                }
        }
}
```

<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | *Conditional* | INT \| ARRAY |[0.. ∞) | Asset id. <br> Optional if including *device_id* OR *deviceasset_id* parameter |
| device_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Device id <br> Optional if including *asset_id* OR *deviceasset_id* parameter|
| deviceasset_id | *Conditional* | INT \| ARRAY | [0.. ∞) | Deviceasset id <br> Optional if including *device_id* OR *asset_id* parameter|
| chart_options | **Required** | ARRAY | N/A | Array of each sensor definition id which include uom_to_id, gauge, trend and it's associated with deviceasset id |
| sd_id | **Required** | INT | [0.. ∞) | sensor definition id is a reference id of sensor defintion table |
| uom_to_id | **Required** | INT | ( ∞,  ∞) | Unit of conversion id is a reference id of uom conversions table <br> See [notes](#uom-conversion) |
| gauge | **Required** | INT \| BOOLEAN | ( ∞,  ∞) | Gauge is either active (1) or inactive (0) <br> See [notes](#gauge) |
| trend | **Required** | INT \| BOOLEAN | ( ∞,  ∞) | Trend is either active (1) or inactive (0) <br> See [notes](#trend) | 

<br>

### **UOM Conversion**
- This uom id will check if there is a valid uom conversion id or not. If not, it will throw an error *invalid uom_to_id*.
- A vaild uom conversion id meaning sensor definition sd_uom_id must exist in the sensor_def table and must be associated with sensor_def sd_id and deviceasset_id. 

### **Gauge**
- If the sensor definition chart is disabled (0), deviceassetschart gauge has be to disabled (0).
- If the sensor_def chart is (1) enabled, either gague or trend should be enabled (1).
  
### **Trend** 
- If the sensor definition chart is disabled (0), deviceassetschart trend has be to disabled (0).
- If the sensor_def chart is (1) enabled, either gague or trend should be enabled (1).

<br>

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated device custom parameter value information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "deviceasset_id": INT,
    "sd_id": INT,
    "gauge": INT,
    "trend": INT,
    "uom_to_id": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "error": "NO_ERROR"                //  Error code
}

    //  Example response
{
    "deviceasset_id": 29,
    "sd_id": "37",
    "gauge": 1,
    "trend": 1,
    "uom_to_id": 7,
    "last_modified_by": 1,
    "last_modified_datetime": "2021-11-01 05:14:53",
    "error": "NO_ERROR"   // Error Code
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
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_OR_DEVICE_ID_OR_ASSET_ID_PRAM | 1. Cannot insert deviceassetschart to *device_id*, or <br> *asset_id*  or <br> *deviceasset_id* not associated with user | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_UOM_TO_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GAUGE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TREND_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GAUGE_TREND_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| MISSING_DEVICEASSET_ID_OR_DEVICE_ID_OR_ASSET_ID_PRAM | Mandatory parameter is missing | Include at least one missing parameter (*asset_id* or *device_id* or *deviceasset_id*) |
| MISSING_CHART_OPTIONS_PRAM | Mandatory parameter is missing | Include the parameter |

<br>

---
---

<br>

### Last modified 01-11-2021 by H. Htun