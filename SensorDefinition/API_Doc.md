# API: SensorDefinition
 
### Parameters for sensor definitions

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

Standard request to select sensor definition(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all sensor definitions.

```json
GET /v1/SensorDefinition/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all sensor definitions will be returned.

```json
POST /v1/SensorDefinition/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "sd_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "device_id" : INT | ARRAY
    , "device_sn" : STRING
}
    //  Example
{
    "action" : "select"
    , "sd_id" : 
        [
            12
            , 13
            , 14
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| sd_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor definition ID |
| asset-id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID |
| device_sn | Optional | STRING | Length <= 45 | Device serial number string |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected sensor definition(s).

```json
Content-Type: application/json

{
    "sensor_definitions": {
            //  Response values
        "sensor_definition 0": {
            "sd_id": INT,
            "sd_name": STRING,
            "html_colorcode": STRING | null,
            "iconpath": STRING | null,
            "chartlabel": STRING | null,
            "sd_deactivated_data": INT,
            "sd_data_min": INT,
            "sd_data_max": INT,
            "sd_graph_min": INT | null,
            "sd_graph_max": INT | null,
            "sd_uom_id": INT,
            "chart": INT,
            "bytelength": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "sensor_definition 1": {
            "sd_id": 13,
            "sd_name": "GPS - Long",
            "html_colorcode": "#000000",
            "iconpath": null,
            "chartlabel": "GPS Longitude",
            "sd_deactivated_data": -181,
            "sd_data_min": "-180",
            "sd_data_max": "180",
            "sd_graph_min": null,
            "sd_graph_max": null,
            "sd_uom_id": 1,
            "chart": 0,
            "bytelength": "Int24",
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "0000-00-00 00:00:00"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| sd_id | INT | Sensor definition ID |
| sd_name | STRING | Sensor definition name |
| html_colorcode | STRING | Sensor definition html colorcode |
| iconpath | STRING \| NULL | Sensor definition iconpath string |
| chartlabel | STRING | Sensor definition chart label |
| sd_deactivated_data | INT \| NULL| Deactivated sensor value |
| sd_data_min | INT \| NULL | Sensor data min value |
| sd_data_max | INT \| NULL | Sensor data max value |
| sd_graph_min | INT \| NULL | Sensor data graph min value |
| sd_graph_max | INT \| NULL | Sensor data graph max value |
| sd_uom_id | INT | Unit of measure conversion ID |
| chart | INT | Sensor data is not chartable (0) or chartable (1) |
| bytelength | STRING | Unit data length <br> Values: "UInt32", "UInt24", "UInt16", "UInt8", "Int32", "Int24", "Int16", "Int8"|
| active_status | INT | Sensor definition is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the sensor definition |
| last_modified_datetime | DATETIME | Timestamp of last sensor definition modification |

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
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_SN_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new sensor definition.

## INSERT REQUEST

```json
POST /v1/SensorDefinition/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "name" : STRING
    , "html_colorcode" : STRING
    , "iconpath" : STRING
    , "chartlabel" : STRING
    , "deactivated_data" : INT
    , "data_min" : VARCHAR
    , "data_max" : VARCHAR
    , "graph_min" : VARCHAR
    , "graph_max" : VARCHAR
    , "uom_id" : INT
    , "chart" : INT
    , "bytelength" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "insert"
    , "name" : "Tank Capacity"
    , "data_min" : 1
    , "data_max" : 2000
    , "deactivated_data" : -1
    , "uom_id" : 13
    , "chart" : 1
    , "bytelength" : "uint32"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| name | **Required** | STRING | Length <= 100 | Sensor definition name |
| html_colorcode | Optional | STRING | Length <= 7 <br> Valid 3 or 6 character html hex color code, preceeding '#' optional | Sensor data color code <br> Defaults to #000000 if not included |
| iconpath | Optional | STRING | Length <= 100 | Sensor icon path <br> Defaults to null if not included |
| chartlabel | Optional | STRING | Length <= 20 | Sensor chart label <br> Defaults to null if not included |
| deactivated_data | Optional | INT | (∞.. ∞) | Deactivated data value <br> Defaults to null if not included <br> See [notes](#data-and-graph-min-max-values) |
| data_min | **Required** | INT | (∞, ∞) | Sensor data min value <br> See [notes](#data-and-graph-min-max-values) |
| data_max | **Required** | INT | (∞, ∞) | Sensor data max value <br> See [notes](#data-and-graph-min-max-values)|
| graph_min | *Conditional* | INT | (∞, ∞) | Sensor data graph min value <br> Mandatory if also including *graph_max* parameter <br> Defaults to null if not included <br> See [notes](#data-and-graph-min-max-values)|
| graph_max | *Conditional* | INT | (∞, ∞) | Sensor data graph max value <br> Mandatory if also including *graph_min* parameter <br> Defaults to null if not included <br> See [notes](#data-and-graph-min-max-values)|
| uom_id |  **Required** | INT | [0.. ∞) | Unit of measure conversion ID |
| chart | **Required** | INT | [0.. ∞) | Sensor data is either not chartable (0) or chartable (1) |
| bytelength | **Required** | STRING | Length <= 6 | Sensor data byte length <br> Accepted values: "UInt32", "UInt24", "UInt16", "UInt8", "Int32", "Int24", "Int16", "Int8" |
| active_status | Optional | INT | [0.. 1] | Sensor definition is either active (0) or inactive (1) <br> Defaults to inactive (1) if not included |

<br>

## Notes

### **Data and Graph Min Max Values**
Values that specify the acceptable sensor data and graph minimum and maximum values. Essentially data and graph min values cannot be greater than their respective max values, and the data min/max values must fall inside the range of the graph min/max. The deactivated value must also fall outside the range of the data and graph min/max (higher or lower). Ideally the deactivated value should be set to some value the data is impossible / improbable to reach (eg. negative value, upper/lower int bounds, etc)

Acceptable values:
- *data_min* < *data_max*
- *data_min* >= *graph_min* 
- *data_max* <= *graph_max* 
- *graph_min* < *graph_max* 
- *deactivated_data* < data graph range < *deactivated_data*

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted sensor definition information, as well as the generated *sd_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "name": STRING,
    "html_colorcode": STRING,
    "chartlabel": STRING,
    "deactivated_data": INT,
    "data_min": INT,
    "data_max": INT,
    "uom_id": INT,
    "chart": INT,
    "bytelength": STRING,
    "active_status": INT,
    "last_modified_by": INT,
    "last_modified_datetime": DATETIME,
    "sd_id" : INT,                       //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "name": "Tank Capacity",
    "html_colorcode": "#000000",
    "data_min": "1",
    "data_max": "2000",
    "deactivated_data": "-1",
    "uom_id": "13",
    "chart": "1",
    "bytelength": "UInt32",
    "active_status": "1",
    "last_modified_by": 1,
    "last_modified_datetime": "2021-08-03 07:26:06",
    "sd_id": "101",                      //  Generated PARAMETER
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
| MISSING_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_DATA_MIN_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_DATA_MAX_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_UOM_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_CHART_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_BYTELENGTH_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_HTML_COLORCODE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ICONPATH_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_CHARTLABEL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEACTIVATED_DATA_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEACTIVATED_DATA_DATA_RANGE_PRAM | *deactivated_data* value is within data min-max range | Alter parameter to valid value  |
| INVALID_DEACTIVATED_DATA_GRAPH_RANGE_PRAM | *deactivated_data* value is within graph min-max range | Alter parameter to valid value |
| INVALID_DATA_MIN_PRAM | 1. Parameter value is unsupported, or <br> 2. *data_min* >= *data_max*, or <br> 3. *data_min* < *graph_min* | Alter parameter to valid value |
| INVALID_DATA_MAX_PRAM | 1. Parameter value is unsupported, or <br> 2.  *data_max* <= *data_min*, or <br> 3. *data_max* > *graph_max* | Alter parameter to valid value |
| INVALID_GRAPH_MIN_PRAM | 1. Parameter value is unsupported, or <br> 2. *graph_min* >= *graph_max* | Alter parameter to valid value |
| INVALID_GRAPH_MAX_PRAM | 1. Parameter value is unsupported, or <br> 2. *graph_max* <= *graph_min* | Alter parameter to valid value |
| INVALID_CHART_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_UOM_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *uom_id* value is not a valid or active *uom_id* | Alter parameter to valid value |
| INVALID_BYTELENGTH_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# UPDATE

Request to update an existing sensor definition.

## UPDATE REQUEST
```json
POST /v1/SensorDefinition/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "sd_id" : INT
    , "name" : STRING
    , "html_colorcode" : STRING
    , "iconpath" : STRING
    , "chartlabel" : STRING
    , "data_min" : INT
    , "data_max" : INT
    , "graph_min" : INT
    , "graph_max" : INT
    , "deactivated_data" : INT
    , "uom_id" : INT
    , "chart" : INT
    , "bytelength" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "update"
    , "sd_id" : 17
    , "graph_min" : 0
    , "graph_max" : 3000
    , "deactivated_data" : -1
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| sd_id | Optional | INT | [0.. ∞) | Sensor definition ID |
| name | Optional | STRING | Length <= 100 | Sensor definition name |
| html_colorcode | Optional | STRING | Length <= 7 <br> Valid 3 or 6 character html hex color code, preceeding '#' optional | Sensor data color code |
| iconpath | Optional | STRING | Length <= 100 | Sensor icon path |
| chartlabel | Optional | STRING | Length <= 20 | Sensor chart label |
| data_min | Optional | INT | (∞, ∞) | Sensor data min value <br> See [notes](#data-and-graph-min-max-values-1) |
| data_max | Optional | INT | (∞, ∞) | Sensor data max value <br> See [notes](#data-and-graph-min-max-values-1)|
| graph_min | *Conditional* | INT | (∞, ∞) | Sensor data graph min value <br> Mandatory if including *graph_max* parameter for an *sd_id* without existing graph min/max values <br> See [notes](#data-and-graph-min-max-values-1)|
| graph_max | *Conditional* | INT | (∞, ∞) | Sensor data graph max value <br> Mandatory if including *graph_min* parameter for an *sd_id* without existing graph min/max values <br> See [notes](#data-and-graph-min-max-values-1)|
| deactivated_data | Optional | INT | (∞, ∞) | Deactivated data value |
| uom_id | Optional | INT | [0.. ∞) | Unit of measure conversion ID |
| chart | Optional | INT | [0.. 1]  | Sensor data is either not chartable (0) or chartable (1) |
| bytelength | Optional | STRING | Length <= 6 | Sensor data byte length <br> Accepted values: "UInt32", "UInt24", "UInt16", "UInt8", "Int32", "Int24", "Int16", "Int8" |
| active_status | Optional | INT | [0.. 1] | Sensor definition is either active (0) or inactive (1) |

<br>

## Notes

### **Data and Graph Min Max Values**
Values that specify the acceptable sensor data and graph minimum and maximum values. Essentially data and graph min values cannot be greater than their respective max values, and the data min/max values must fall inside the range of the graph min/max. The deactivated value must also fall outside the range of the data and graph min/max (higher or lower). Ideally the deactivated value should be set to some value the data is impossible / improbable to reach (eg. negative value, upper/lower int bounds, etc)

Acceptable values:
- *data_min* < *data_max*
- *data_min* >= *graph_min* 
- *data_max* <= *graph_max* 
- *graph_min* < *graph_max* 
- *deactivated_data* < data graph range < *deactivated_data*


<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated sensor definition information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "sd_id" : INT,
    "name" : STRING,
    "html_colorcode" : STRING,
    "iconpath" : STRING,
    "chartlabel" : STRING,
    "data_min" : INT,
    "data_max" : INT,
    "graph_min" : INT,
    "graph_max" : INT,
    "deactivated_data" : INT,
    "uom_id" : INT,
    "chart" : INT,
    "bytelength" : STRING,
    "active_status" : INT,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "sd_id": "17",
    "graph_min": "0",
    "graph_max": "3000",
    "deactivated_data": "-1",
    "last_modified_by": 1,
    "last_modified_datetime": "2021-08-03 07:00:27",
    "error" : "NO_ERROR"        //  Error code
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
| MISSING_SD_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_GRAPH_MIN_PRAM | Missing required parameter <br> *graph_min* is mandatory if including *graph_max* parameter for an *sd_id* without existing graph min/max values | Include required parameter in query |
| MISSING_GRAPH_MAX_PRAM | Missing required parameter <br> *graph_max* is mandatory if including *graph_min* parameter for an *sd_id* without existing graph min/max values  | Include required parameter in query |
| INVALID_SD_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *sd_id* is not a valid *sd_id* | Alter parameter to valid value |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_HTML_COLORCODE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ICONPATH_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_CHARTLABEL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DATA_MIN_PRAM | 1. Parameter value is unsupported, or <br> 2. *data_min* >= *data_max*, or <br> 3. *data_min* < *graph_min*, or <br> 4. *data_min* <= *deactivated_data* ( where *deactivated_data* < *data_max* ) | Alter parameter to valid value |
| INVALID_DATA_MAX_PRAM | 1. Parameter value is unsupported, or <br> 2. *data_max* <= *data_min*, or <br> 3. *data_max* > *graph_max*, or <br> 4. *data_max* >= *deactivated_data* ( where *deactivated_data* > *data_min* )  | Alter parameter to valid value |
| INVALID_GRAPH_MIN_PRAM | 1. Parameter value is unsupported, or <br> 2. *graph_min* >= *graph_max*, or <br> 3. *graph_min* > *data_min*, or <br> 4. *graph_min* > *data_max*, or <br> 5. *graph_min* <= *deactivated_data* ( where *deactivated_data* < *graph_max* ) | Alter parameter to valid value |
| INVALID_GRAPH_MAX_PRAM | 1. Parameter value is unsupported, or <br> 2. *graph_max* <= *graph_min* <br> 3. *graph_max* < *data_min* <br> 4. *graph_max* < *data_max* <br> 5. *graph_max* >= *deactivated_data* ( where *deactivated_data* > *graph_min* ) | Alter parameter to valid value |
| INVALID_DEACTIVATED_DATA_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEACTIVATED_DATA_DATA_RANGE_PRAM | *deactivated_data* value is within data min-max range | *deactivated_data* must not be a value within data min-max range <br> Alter parameter to valid value |
| INVALID_DEACTIVATED_DATA_GRAPH_RANGE_PRAM | *deactivated_data* value is within graph min-max range | *deactivated_data* must not be a value within graph min-max range <br> Alter parameter to valid value |
| INVALID_UOM_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *uom_id* value is not a valid or active *uom_id* | Alter parameter to valid value |
| INVALID_CHART_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_BYTELENGTH_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

### Last modified 04-08-2021 by C. Rollinson