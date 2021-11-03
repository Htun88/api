# API: SensorData
 
### Parameters for SensorData

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

<br>

---
---

<br>

# SELECT

Standard request to select sensor data, either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all sensor data associated with the user, up to the 1000 most recent data values.

```json
GET /v1/SensorData/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all sensor data associated with the user will be returned.

```json
POST /v1/SensorData/ HTTP/1.1

    //  Request values
{
    "action" : "select"
,   "asset_id" : INT | ARRAY
,   "device_id" : INT | ARRAY
,   "data_id" : INT | ARRAY
,   "timestamp_from" : DATETIME
,   "timestamp_to" : DATETIME  
,   "limit" : INT  
,   "sd_id" : INT | ARRAY                   //  Return sd_id values
,   "filter" :                              //  Return filtered sd_id values
    [  
        {
            "sd_id" : INT
            ,"value" : 
            [
                {
                    "comp" : STRING
                    , "val" : INT
                    , "op" : INT
                },
                ...
            ]
        },
        ...
    ]
}

    //  Example
{
    "action" : "select"
,   "asset_id" : "1"
,   "timestamp_from" : "2021-07-06 00:00:00"
,   "timestamp_to" : "2021-07-06 10:30:00" 
,   "limit" : 20   
,   "sd_id" :                               //  Return sd_id values
    [
        1
        , 2
        , 3
    ]
,   "filter" :                              //  Return filtered sd_id values
    [  
        {
            "sd_id" : "4"
            ,"value" : 
            [
                {
                    "comp" : ">"
                    , "val" : "200"
                    , "op" : "1"
                }
                , {
                    "comp" : "<="
                    , "val" : "300"
                    , "op" : "0"
                }
            ]
        },
        {
            "sd_id" : "5"
            ,"value" : 
            [
                {
                    "comp" : ">"
                    , "val" : "10"
                    , "op" : "1"
                }
                , {
                    "comp" : "<="
                    , "val" : "50"
                    , "op" : "0"
                }
            ]
        }
    ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID |
| data_id | Optional |INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Data ID |
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-from) | Select sensor data that occurred after timestamp_from |
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-to) | Select sensor data that occurred before timestamp_from  |
| limit | Optional | INT | [0.. ∞) | Limit the number of responses. <br> If parameter not specified a default limit cap of 1000 responses is used
| sd_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY: See [notes](#sensor-definition-id-sd-id) | Sensor type ID |
| filter | Optional | ARRAY | N/A <br> See [notes](#filter) | Array of filter conditions |
| value | *Conditional* | ARRAY | N/A <br> See [notes](#filter) | Array of individual filter conditions per sd_id |
| comp | *Conditional* | STRING | Length <= 2 <br> See [notes](#comparison-comp) | Math comparison used to compare values with for filtering |
| val | *Conditional* | INT | ( ∞,  ∞) <br> See [notes](#value-val) | Value used to compare values against for filtering |
| op | *Conditional* | INT | [0.. 1] <br> See [notes](#operator-op) |  OR (0), or AND (1) <br> Condition used to string multiple filters together |

<br>

### **Timestamp from**
DATETIME. Select all sensor data that occurred after this datetime.

If timestamp_to parameter is also used, timestamp_from must be a datetime that occurs prior to timestamp_to.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.
  
### **Timestamp to**
DATETIME. Select all sensor data that occurred before this datetime.

If timestamp_from parameter is also used, timestamp_to must be a datetime that occurs after to timestamp_from.

- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

### **Sensor Definition ID (sd id)**
INT. Defines the type of sensor (GPS, O2, temperature, etc).

Required if using *filter* parameter. ID of the sensor definition. Accepted values: INT >= 0

Can be searched as its own parameter as either a single or array of INT values, or used in the filter parameter array to return sensor data that satisifies the filter condition(s).

### **Filter**
ARRAY. Filter for sensor data from specific IDs that satisfy some expected value.
Array requires several subparameters, if any are missing it will cause error: INVALID_FILTER_PRAM

``` json
    //  Array form
"filter" : 
    [  
        {
            //  part A 
            "sd_id" : INT
            ,"value" : 
            [
                {
                    //  part B
                    "comp" : STRING
                    , "val" : INT
                    , "op" : INT
                },
                ...
            ]
        },
        ...
    ]
```
There must be at least 1 part A object in the array. Each part A "value" array must contain at least 1 part B object. 

There can be multiple part A objects in the filter array, each containing multiple part B objects. Each part B object is a mathematical equation, returning values that satisfy it. 

This takes the form of: 

    returnValue COMP VAL OP




``` json
    //  Example code
{
    "sd_id" : 2
    , "value" : 
    [
        {
            "comp" : ">"
            , "val" : "10"
            , "op" : "1"
        }
        , {
            "comp" : "<="
            , "val" : "50"
            , "op" : "0"        //  Note: not used, see Operator(op)
        } 
    ]
}
    
    //  Example result
    sensor definition ID = 2, AND
    returnvalue > 10 AND returnvalue <= 50

    //  This will return all sensor data that satisfies the given conditions
    //      ie. sensor data for sensor definition ID no. 2 with a value greater than 10 and less than or equal to 50.
```

### **Value (val)**
INT. Required if using *filter* parameter. Value to compare against when filtering sensor data. Accepted values: valid INT, including negative values.

Used with *comp* and *op* parameters to filter a search. 

### **Comparison (comp)**
STRING. Required if using *filter* parameter. Math comparison value. Accepted values: =, !=, <, >, <=, >=

Used with *val* and *op* parameters to filter a search. 

### **Operator (op)**
INT. Required if using *filter* parameter. Used to string several comparison filters together. Accepted values: 0, 1

Used with *val* and *comp* parameters to filter a search. 

- 0: OR
- 1: AND

If there are no subsequent comparison objects the final op is not utilised but is still a required field. Either 0 or 1 can be used. 

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected sensor data.

```json
Content-Type: application/json

{
    "sensordata": {
            //  Response values
        "sensordata 0": {
            "data_id": INT,
            "data_datetime": DATETIME,
            "asset_id": INT,
            "sd_id": INT,
            "user_agent": STRING,
            "sensor_value": INT,
            "sd_name": STRING,
            "uom_chartlabel": STRING,
            "equation": STRING,
            "data_min": INT,
            "data_max": INT,
            "graph_min": INT,
            "graph_max": INT,
            "chart": STRING,
            "data_type": STRING,
            "colorcode": STRING,
            "sd_chartlabel": STRING
        },
            //  Example
        "sensordata 1": {
            "data_id": 138,
            "data_datetime": "2021-07-06 01:19:21",
            "asset_id": 1,
            "sd_id": 1,
            "user_agent": "PSM_2081|PSM_2103|PSM_2204X#23#0#2",
            "sensor_value": "TEST",
            "sd_name": "4OXV CiTiceL O2",
            "uom_chartlabel": "%",
            "equation": TODO,
            "data_min": "1",
            "data_max": "25",
            "graph_min": "",
            "graph_max": "",
            "chart": "Yes",
            "data_type": "Numeric",
            "colorcode": "#FF8000",
            "sd_chartlabel": "O2"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| data_id | INT | Data ID |
| data_datetime | DATETIME | Datetime of data |
| asset_id | INT | Asset ID |
| sd_id | INT | Sensor definition ID |
| user_agent | STRING | User agent string, firmware identification |
| sensor_value | INT | Sensor value |
| sd_name | STRING | Sensor definition name |
| uom_chartlabel | STRING | Unit of measure chart label |
| equation | STRING | Mathematical equation applied to raw data ($value) |
| data_min | INT | Minimum sensor value |
| data_max | INT | Maximum sensor value |
| graph_min | INT | Minimum graph value |
| graph_max | INT | Maximum graph value |
| chart | STRING | "Yes" / "No". Is the data chartable <br> Example: Altitude is "YES", GPS is "NO" |
| data_type | STRING | Data type |
| colorcode | STRING | Chart colour |
| sd_chartlabel | STRING | Sensor definition chart label |


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
| INVALID_DATA_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TIMESTAMP_FROM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TIMESTAMP_TO_PRAM | 1. Parameter value is unsupported, or <br> 2. timestamp_to is a datetime before timestamp_from | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_FILTER_PRAM | Missing array parameter(s) <br> See [notes](#filter-1)  | Include missing parameter(s) |
| INVALID_MATH_COMPARISON_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SENSOR_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_OPERATOR_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

### **Filter**
ARRAY. Filter for sensor data from specific IDs that satisfy some expected value.
Array requires several subparameters, if any are missing it will cause error: INVALID_FILTER_PRAM

For further information see [here](#filter)

Valid array example:
``` json
"filter" : 
    [  
        {
            //  part A 
            "sd_id" : INT
            ,"value" : 
            [
                {
                    //  part B
                    "comp" : STRING
                    , "val" : INT
                    , "op" : INT
                },
                ...
            ]
        },
        ...
    ]
```

<br>

---
---

<br>

# INSERT

Request to insert new sensor data

## INSERT REQUEST

```json
POST /v1/SensorData/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
,   "asset_id" : INT
,   "device_id" : INT
,   "timestamp" : DATETIME
,   "user_agent" : STRING
,   "sensor_data": 
    [
        {
            "sd_id" : INT
            , "value" : INT 
        },
        ...
    ]
}
    //  Example
{
    "action" : "insert"
,   "asset_id" : 1
,   "user_agent" : "PSM_2081|PSM_2103|PSM_2204X#23#0#2"
,   "sensor_data": 
    [
        {
            "sd_id" : "14",
            "value" : "122"
        },
        {
            "sd_id" : "12",
            "value" : "12.34"
        },
                {
            "sd_id" : "13",
            "value" : "16.9876"
        }

    ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | *Conditional* | INT |[0.. ∞) | Asset ID. <br> Optional if including *device_id* parameter |
| device_id | *Conditional* | INT | [0.. ∞) | Device ID <br> Optional if including *asset_id* parameter|
| timestamp | *Conditional* | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#timestamp-1) | Timestamp of the data insert |
| user_agent | **Required** | STRING | Length <= 50 | User agent string. |
| sensor_data | **Required** | ARRAY | N/A | Array of each sensor ID and associated value data |
| sensor_id | **Required** | INT | [0.. ∞) | ID of the sensor |
| value | **Required** | INT | ( ∞,  ∞) | Sensor value  |

<br>

## Notes

### **Timestamp**
DATETIME. Timestamp of the sensor data.
- Not including this parameter defaults the timestamp to GMT date ime of when the insert request is made.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted sensor data information, as well as the generated *data_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "sd_id" : INT | STRING,
    "sensor_id" : INT | STRING,
    "sensor_value" : INT | FLOAT | STRING,
    "asset_id" : INT,
    "device_id" : INT,
    "deviceasset_id" : INT,
    "timestamp" : DATETIME,
    "user_agent" : STRING,
    "data_id" : INT,                   //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "sd_id": "14, 12, 13",
    "sensor_id": "1, 1, 1",
    "sensor_value": "122, 12.34, 16.9876",
    "asset_id": 1,
    "device_id": 13,
    "deviceasset_id": 9,
    "timestamp": "2021-07-21 08:03:22",
    "user_agent": "PSM_2081|PSM_2103|PSM_2204X#23#0#2",
    "data_id": "602",                  //  Generated PARAMETER
    "error": "NO_ERROR"                //  Error code
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
| INVALID_ASSET_ID_OR_DEVICE_ID_PRAM | 1. Cannot insert sensor data to *device_id*, or <br> 2. *asset_id* not associated with user | Alter parameter to valid value |
| INVALID_TIMESTAMP_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_USER_AGENT_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SENSOR_DATA_PRAM | Sensor data array object(s) is missing one or more mandatory values | Alter parameter array object(s) to include mandatory value(s) |
| INVALID_DUPLICATE_SD_ID_PRAM | Sensor data contains duplicate *sd_id* values | Alter parameter or remove duplicate values |
| INVALID_SENSOR_DATA_SENSOR_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. sensor data sensor ID is not a sensor definition associated with this device ID | Alter parameter to valid value |
| INVALID_SENSOR_DATA_VALUE_PRAM | 1. Parameter value is unsupported, or <br> 2. parameter value is outside the value minimum and maximum range | Alter parameter to valid value |
| MISSING_ASSET_ID_OR_DEVICE_ID_PRAM | Mandatory parameter is missing | Include at least one missing parameter (*asset_id* or *device_id*) |
| MISSING_USER_AGENT_PRAM | Mandatory parameter is missing | Include the parameter |
| MISSING_SENSOR_DATA_PRAM | Mandatory parameter is missing | Include the parameter |

<br>

---
---

<br>

### Last modified 09-08-2021 by C. Rollinson