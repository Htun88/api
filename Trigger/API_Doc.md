# API: Trigger
 
### Parameters for a trigger

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

Standard request to select trigger(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all triggers associated with the user. Responses are limited to a maximum 1000 responses by default.

```json
GET /v1/Trigger/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all triggers associated with the user will be returned.

```json
POST /v1/Trigger/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "device_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "deviceasset_id" : INT | ARRAY
    , "device_sn" : STRING | ARRAY
    , "span" : STRING
    , "trigger_id" : INT | ARRAY
    , "trigger_type" : INT | STRING
    , "trigger_level" : INT | ARRAY
    , "sd_id" : INT | ARRAY
    , "geofence_id" : INT | ARRAY
    , "trigger_source" : INT | STRING
    , "duration" : INT | ARRAY
    , "device_alarm" : INT | STRING
    , "site_alarm" : INT | STRING
    , "reaction" : INT | STRING
    , "active_status" : INT 
    , "limit" : INT 
}
    //  Example
{
    "action" : "select"
    , "deviceasset_id" : 
        [
            1
            , 2
        ]
    , "duration" : 0
    , "trigger_type" : 1
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| device_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Triggers associated with this device ID <br> If *span* parameter included mandatory to include at least one of *device_id*, *device_sn*, *asset_id* or *deviceasset_id* |
| asset_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Triggers associated with this asset ID <br> If *span* parameter included mandatory to include at least one of *device_id*, *device_sn*, *asset_id* or *deviceasset_id* |
| deviceasset_id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Triggers associated with this deviceasset ID <br> If *span* parameter included mandatory to include at least one of *device_id*, *device_sn*, *asset_id* or *deviceasset_id* <br>|
| device_sn | *Conditional* | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Triggers associated with this device serial number <br> If *span* parameter included mandatory to include at least one of *device_id*, *device_sn*, *asset_id* or *deviceasset_id* <br>|
| span | *Conditional* | STRING | Length <= 9 | Select current triggers, previously used or all available for selected *device_id*, *asset_id* and/or *deviceasset_id* <br> Accepted values: "Current", "Previous" or "Available" <br> Defaults to "Current" if not specified and at least one of *device_id*, *asset_id* or *deviceasset_id* included |
| trigger_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Trigger ID |
| trigger_type | Optional | INT \| STRING | [0.. 1], or <br> Length <= 8  | Trigger type <br> Either (0) "Critical" or (1) "Warning" |
| trigger_level | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Trigger level severity value |
| sd_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Sensor definition ID |
| geofence_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Geofence ID |
| trigger_source | Optional | INT \| STRING | [0.. 1], or <br> Length <= 8 | Trigger source <br> Either (0) "Geofence" or (1) "Sensor" |
| duration | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Duration (seconds) the sensor value must satisfy the trigger condition for the trigger to occur |
| device_alarm | Optional | INT \| STRING | [0.. 1], or <br> Length <= 3  | Device alarm status on trigger occur <br> Either (0) "Off" or (1) "On" |
| site_alarm | Optional | INT \| STRING | [0.. 1], or <br> Length <= 3  | Site alarm status on trigger occur <br> Either (0) "Off" or (1) "On" |
| reaction | Optional | INT \| STRING | [0.. 1], or <br> Length <= 11 | Reaction required on trigger occur <br> Either (0) "Acknowledge" or (1) "Action" |
| active_status | Optional | INT | [0.. 1] | Trigger is either active (0) or inactive (1) |
| limit | Optional | INT | [0.. ∞) | Response limit <br> Default 1000 responses |

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected trigger(s).

```json
Content-Type: application/json

{
    "triggers": {
            //  Response values
        "triggers 1": {
            "trigger_id": INT,
            "trigger_name": STRING,
            "trigger_type": STRING,
            "trigger_level": INT,
            "trigger_source": STRING,
            "sd_id": INT | null,
            "geofence_id": INT | null,
            "value_operator": STRING,
            "trigger_value": INT | null,
            "duration": INT,
            "device_alarm": STRING,
            "site_alarm": STRING,
            "trigger_email": STRING | null,
            "trigger_sms": STRING | null,
            "trigger_phone": STRING | null,
            "trigger_fax": STRING | null,
            "suggested_actions": STRING | null,
            "reaction": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "triggers 1": {
            "trigger_id": 3,
            "trigger_name": "accelerometer g",
            "trigger_type": "Warning",
            "trigger_level": 0,
            "trigger_source": "Sensor",
            "sd_id": 8,
            "geofence_id": null,
            "value_operator": ">=",
            "trigger_value": 7,
            "duration": 0,
            "device_alarm": "On",
            "site_alarm": "Off",
            "trigger_email": "admin@usm.net.au",
            "trigger_sms": null,
            "trigger_phone": null,
            "trigger_fax": null,
            "suggested_actions": null,
            "reaction": "Acknowledge",
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-08-11 00:11:52"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| trigger_id | INT | Trigger ID |
| trigger_name | STRING | Trigger name |
| trigger_type | STRING | Trigger type <br> Either "Warning" or "Critical" |
| trigger_level | INT | Trigger level |
| trigger_source | STRING | Trigger source <br> Either "Sensor" or "Geofence" |
| sd_id | INT \| NULL | Sensor ID associataed with trigger <br> NULL if *geofence_id* is not null |
| geofence_id | INT \| NULL | Geofence ID associated with trigger <br> NULL if *sd_id* or *trigger_value* are not null |
| value_operator | STRING | Comparison value used to compare a value against *trigger_value* <br> Either ">", "<", ">=", "<=", "=", "Exit" or "Entry"|
| trigger_value | INT \| NULL | Value to compare sensor data against using *value_operator* <br> NULL if *geofence_id* is not null |
| duration | INT | Duration (seconds) that a sensor value must satisfy the trigger condition for a trigger to occur <br> Duration of 0 indicates an immediate trigger occurance <br> If *trigger_source* is "Geofence" the duration is always 0 |
| device_alarm | STRING | Device alarm state if the trigger occurs <br> Either "Off" or "On" |
| site_alarm | STRING | Site alarm state if the trigger occurs <br> Either "Off" or "On" |
| trigger_email | STRING \| NULL | Email address associated with this trigger |
| trigger_sms | STRING \| NULL | SMS number associated with this trigger |
| trigger_phone | STRING \| NULL | Phone number associated with this trigger |
| trigger_fax | STRING \| NULL | Fax number associated with this trigger |
| suggested_actions | STRING \| NULL | Suggested action if this trigger occurs |
| reaction | STRING | Reaction required if this trigger occurs <br> Either "Acknowledge" or "Action" |
| active_status | INT | Trigger is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the trigger |
| last_modified_datetime | DATETIME | Timestamp of last trigger modification |


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
| MISSING_IDENTIFICATION_PRAM | Missing mandatory parameter <br> If *span* parameter is used it is mandatory to include at least one of *device_id*, *device_sn*, *asset_id* or *deviceasset_id* | Include at least one mandatory parameter |
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_SN_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SPAN_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_LEVEL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GEOFENCE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_SOURCE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DURATION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICE_ALARM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SITE_ALARM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_REACTION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new trigger

## INSERT REQUEST

```json
POST /v1/Trigger/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "trigger_name" : STRING
    , "trigger_type" : INT | STRING
    , "trigger_level" : INT
    , "sd_id" : INT
    , "trigger_value" : INT
    , "geofence_id" : INT
    , "value_operator" : STRING
    , "duration" : INT
    , "device_alarm" : INT | STRING
    , "site_alarm" : INT | STRING
    , "trigger_email" : STRING
    , "trigger_sms" : STRING
    , "trigger_phone" : STRING
    , "trigger_fax" : STRING
    , "suggested_actions" : STRING
    , "reaction" : INT | STRING
    , "active_status" : INT   
}
    //  Example
{
    "action" : "insert"
    , "trigger_name" : "Temperature above 5C"
    , "trigger_type" : 1
    , "sd_id" : 9
    , "trigger_value" : 5
    , "value_operator" : ">"
    , "duration" : 30
    , "device_alarm" : 1
    , "site_alarm" : 0
    , "trigger_email": "alerts@fridgeCompany.com"
    , "reaction" : 0
    , "suggested_actions" : "Log. Check fridge in person for issues"
    , "active_status" : 0
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| trigger_name | **Required** | STRING | Length <= 45 | Trigger name |
| trigger_type | **Required** | INT \| STRING | [0.. 1], or <br> Length <= 8  | Trigger type <br> Either (0) "Critical" or (1) "Warning" |
| trigger_level | **Required** | INT | [0.. ∞) | Trigger level severity value |
| sd_id | *Conditional* | INT | [0.. ∞) | Sensor definition ID <br> Mandatory to have either *sd_id* or *geofence_id* <br> Incompatable with *geofence_id* <br> Mandatory if *trigger_value* also included |
| trigger_value | *Conditional* | INT | (-∞, ∞) | Trigger value to compare values against with *value_operator* <br> Mandatory if *sd_id* also included <br> If also including *sd_id* parameter then *trigger_value* must be a value inclusively within the *sd_id* data min - data max range  <br> Incompatable with *geofence_id* |
| geofence_id | *Conditional* | INT | [0.. ∞) |  Geofence ID <br> Mandatory to have either *sd_id* or *geofence_id* <br> Incompatable with *sd_id* and *trigger_value*  |
| value_operator | **Required** | STRING | Length <= 5 | Value comparison operator <br> See [notes](#value-operator) <br> If also including *sd_id*, accepted values: ">", "<", ">=", "<=" or "=" <br> If also including *geofence_id*, accepted values: "Exit" or "Entry" |
| duration | *Conditional*  | INT | [0.. ∞) | Duration (seconds) a value must satisfy the trigger condition before the trigger occurs <br> Mandatory if also including *sd_id* and *trigger_value* parameters <br> If also including *geofence_id* parameter *duration* defaults to "0", and must be "0" if explicitly included |
| device_alarm | **Required** | INT \| STRING | [0.. 1], or <br> Length <= 3  | Device alarm status on trigger occur <br> Either (0) "Off" or (1) "On" |
| site_alarm | **Required** | INT \| STRING | [0.. 1], or <br> Length <= 3  | Site alarm status on trigger occur <br> Either (0) "Off" or (1) "On" |
| trigger_email | Optional | STRING | Length <= 45 | Email associated with this trigger |
| trigger_sms | Optional | STRING | Length <= 45 | SMS number associated with this trigger |
| trigger_phone | Optional | STRING | Length <= 45 | Phone number associated with this trigger |
| trigger_fax | Optional | STRING | Length <= 45 | Fax number associated with this trigger |
| suggested_actions | Optional | STRING | Length <= 1000 | Suggested action <br> Defaults to NULL if not included |
| reaction | Optional | INT \| STRING | [0.. 1], or <br> Length <= 11  | Reaction required on trigger occur <br> Either (0) "Acknowledge" or (1) "Action" <br> Defaults to NULL if not included |
| active_status | **Required** | INT | [0.. 1] | Trigger is either active (0) or inactive (1) |

## Notes

### **Value Operator**
Value comparison operator. Used with sensor values to compare a value against *trigger_value* to determine if a trigger occurs. Used with geofence triggers to define trigger on "Entry" or "Exit"

Format for "Sensor" triggers is:
    sensor value *value_operator* *trigger_value*

```json
//  Example values
    sensor value : 10    
    value_operator : ">"
    trigger_value : 15

//  Resulting check
    10 > 15 

//  Sensor value 10 is not greater than trigger value 15, so no trigger occurs    
```

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted trigger information, as well as the generated *trigger_id* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "trigger_name": STRING,
    "trigger_type": STRING,
    "trigger_level": INT,
    "trigger_source": STRING,
    "sd_id": INT,
    "trigger_value": INT,
    "geofence_id" : INT,
    "value_operator": STRING,
    "duration": INT,
    "device_alarm": STRING,
    "site_alarm": STRING,
    "trigger_email": STRING,
    "trigger_sms": STRING,
    "trigger_phone": STRING,
    "trigger_fax": STRING,
    "suggested_actions": STRING,
    "reaction": STRING,
    "active_status": INT,
    "trigger_id" : INT,                  //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "trigger_name": "Temperature above 5C",
    "trigger_type": "Warning",
    "trigger_level": 0,
    "trigger_source": "Sensor",
    "sd_id": "9",
    "trigger_value": "5",
    "value_operator": ">",
    "duration": "30",
    "device_alarm": "On",
    "site_alarm": "Off",
    "trigger_email": "alerts@fridgeCompany.com",
    "suggested_actions": "Log. Check fridge in person for issues",
    "reaction": "Acknowledge",
    "active_status": "0",
    "trigger_id": "17",                  //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
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
| MISSING_SD_ID_OR_GEOFENCE_ID_PRAM | Insert request requires either *sd_id* or *geofence_id* parameters | Include either required parameter in query |
| INVALID_INCOMPATABLE_SD_ID_AND_GEOFENCE_ID_PRAM | Insert request must have only one of *sd_id* or *geofence_id* parameters | Include only one required parameter in query |
| INVALID_INCOMPATABLE_SD_ID_AND_VALUE_OPERATOR_PRAM | *sd_id* is not compatable with value operators "Exit" and "Entry" | Alter parameters to a valid value |
| INVALID_INCOMPATABLE_GEOFENCE_AND_VALUE_OPERATOR_PRAM | *geofence_id* is not compatable with value operators that are NOT "Exit" and "Entry" | Alter parameters to a valid value |
| INVALID_INCOMPATABLE_GEOFENCE_AND_TRIGGER_VALUE_PRAM | *geofence_id* is not compatable with *trigger_value* | Remove *trigger_value* parameter |
| INVALID_TRIGGER_VALUE_RANGE_PRAM | Trigger value is not within the *sd_id* data min - data max range | Alter parameter to valid value |
| GEOFENCE_LOCKED | Geofence is locked. New triggers cannot be attributed to this geofence | No action necessary |
| MISSING_TRIGGER_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TRIGGER_TYPE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TRIGGER_VALUE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SD_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_VALUE_OPERATOR_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_DURATION_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_DEVICE_ALARM_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SITE_ALARM_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_REACTION_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_TRIGGER_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_LEVEL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *sd_id* is not a valid or active *sd_id* | Alter parameter to valid value |
| INVALID_TRIGGER_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GEOFENCE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *geofence_id* is not a valid or active *geofence_id* | Alter parameter to valid value |
| INVALID_VALUE_OPERATOR_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DURATION_PRAM | 1. Parameter value is unsupported, or <br> 2. *duration* is not 0 while also using *geofence_id* parameter | Alter parameter to valid value |
| INVALID_DEVICE_ALARM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SITE_ALARM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_EMAIL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_SMS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_PHONE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_FAX_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SUGGESTED_ACTIONS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_REACTION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# UPDATE

Request to update an existing Trigger.

If a trigger has been associated with an alarm event, then only *trigger_name* can be updated.

Updating a trigger will also iterate associated devices trigger version by 1.

## UPDATE REQUEST
```json
POST /v1/Trigger/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "trigger_id" : INT
    , "trigger_name": STRING
    , "trigger_type": INT | STRING
    , "trigger_level": INT
    , "sd_id": INT
    , "trigger_value": INT
    , "geofence_id" : INT
    , "value_operator": STRING
    , "duration": INT
    , "device_alarm": INT | STRING
    , "site_alarm": INT | STRING 
    , "trigger_email": STRING
    , "trigger_sms": STRING
    , "trigger_phone": STRING
    , "trigger_fax": STRING
    , "suggested_actions": STRING
    , "reaction": INT | STRING
    , "active_status": INT
}
    //  Example
{
    "action" : "update"
    , "trigger_id" : 17
    , "trigger_name" : "Temperature above 10C"
    , "trigger_type" : 0
    , "trigger_value" : 10
    , "value_operator" : ">"
    , "suggested_actions" : "Call supervisor immediately. Check fridges immediately"
    , "reaction" : 1
    , "trigger_phone" : "12345678"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| trigger_id | **Required** | INT | [0.. ∞) | Trigger ID |
| trigger_name | Optional | STRING | Length <= 45 | Trigger name
| trigger_type | Optional | INT \| STRING | [0.. 1], or <br> Length <= 8  | Trigger type <br> Either (0) "Critical" or (1) "Warning" |
| trigger_level | Optional | INT | [0.. ∞)| Trigger level severity value |
| sd_id | *Conditional* | INT | [0.. ∞)| Sensor definition ID <br> If not also updating *trigger_value* then the new *sd_id* data min-max range must encompass the already listed *trigger_value* <br> Mandatory if changing trigger source from "Geofence" to "Sensor" <br> Incompatable with *geofence_id* |
| trigger_value | *Conditional* | INT | (-∞, ∞) | Trigger value to compare values against with *value_operator* <br> Must be a value inclusively within the data min - data max range of either the already listed *sd_id* (if not also updating), or the new *sd_id* (if also updating) <br> Mandatory if changing trigger source from "Geofence" to "Sensor" <br> Incompatable with *geofence_id* |
| geofence_id | *Conditional* | INT | [0.. ∞) | Geofence ID <br> Geofence must be valid and not locked <br> Mandatory if changing trigger source from "Sensor" to "Geofence" <br> Incompatable with *sd_id* and *trigger_value*  |
| value_operator | *Conditional*  | STRING | Length <= 5 | Value comparison operator <br> Mandatory if changing trigger source <br> If also including *sd_id* or if "Sensor" source trigger, accepted values: ">", "<", ">=", "<=" or "=" <br> If also including *geofence_id* or if "Geofence" source trigger, accepted values: "Exit" or "Entry" <br> See [notes](#value-operator-1) |
| duration | *Conditional* | INT | [0.. ∞) | Duration (seconds) a value must satisfy the trigger condition before the trigger occurs <br> Mandatory if changing trigger source from "Geofence" to "Sensor" <br> If also including *geofence_id* parameter *duration* defaults to "0", and must be "0" if explicitly included |
| device_alarm | Optional | INT \| STRING | [0.. 1], or <br> Length <= 3  | Device alarm status on trigger occur <br> Either (0) "Off" or (1) "On" |
| site_alarm | Optional | INT \| STRING | [0.. 1], or <br> Length <= 3  | Site alarm status on trigger occur <br> Either (0) "Off" or (1) "On" |
| trigger_email | Optional | STRING | Length <= 45 | Trigger email |
| trigger_sms | Optional | STRING | Length <= 45 | Trigger sms number |
| trigger_phone | Optional | STRING | Length <= 45 | Trigger phone number |
| trigger_fax | Optional | STRING | Length <= 45 | Trigger fax number |
| suggested_actions | Optional | STRING | Length <= 1000 | Suggested action string |
| reaction | Optional | INT \| STRING | [0.. 1], or <br> Length <= 11  | Reaction required on trigger occur <br> Either (0) "Acknowledge" or (1) "Action" |
| active_status | Optional | INT | [0.. 1] | Trigger is either active (0) or inactive (1) |

<br>

## Notes

### **Value Operator**
Value comparison operator. Used with sensor values to compare a value against *trigger_value* to determine if a trigger occurs. Used with geofence triggers to define trigger on "Entry" or "Exit"

Format for "Sensor" triggers is:
    sensor value *value_operator* *trigger_value*

```json
//  Example values
    sensor value : 10    
    value_operator : ">"
    trigger_value : 15

//  Resulting check
    10 > 15 

//  Sensor value 10 is not greater than trigger value 15, so no trigger occurs    
```

<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated trigger information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "trigger_name": STRING,
    "trigger_type": STRING,
    "trigger_level": INT,
    "trigger_source": STRING,
    "sd_id": INT,
    "trigger_value": INT,
    "geofence_id" : INT,
    "value_operator": STRING,
    "duration": INT,
    "device_alarm": STRING,
    "site_alarm": STRING,
    "trigger_email": STRING,
    "trigger_sms": STRING,
    "trigger_phone": STRING,
    "trigger_fax": STRING,
    "suggested_actions": STRING,
    "reaction": STRING,
    "active_status": INT,
    "error": "NO_ERROR"        //  Error code   
}
    //  Example
{
    "trigger_id": "17",
    "trigger_value": "10",
    "value_operator": ">",
    "trigger_name": "Temperature above 10C",
    "trigger_type": "Critical",
    "trigger_phone": "+12345678",
    "suggested_actions": "Call supervisor immediately. Check fridges immediately",
    "reaction" : "Action",
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
| INVALID_INCOMPATABLE_SENSOR_AND_GEOFENCE_PRAM | Incompatable paramaters included in query <br> *geofence_id* is incompatable with *sd_id* and *trigger_value* | Remove parameters as appropriate |
| MISSING_TRIGGER_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_SD_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_TRIGGER_VALUE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_VALUE_OPERATOR_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_TRIGGER_VALUE_RANGE_PRAM | 1. Update *sd_id* parameter data min-max values do not encompass the already listed *trigger_value*, or <br> 2. Update *trigger_value* is not a value encompassed by already listed *sd_id* data min-max values, or <br> 3. Update *sd_id* parameter data min-max values do not encompass update *trigger_value* | 1. Alter parameter to a valid value, or <br> 2. Include a valid *trigger_value* as an update parameter |
| GEOFENCE_LOCKED | Geofence is locked. This geofence cannot be associated with any trigger updates | No action necessary |
| TRIGGER_LOCKED | Trigger is locked. Only *trigger_name* parameter can be updated | No action necessary |
| INVALID_TRIGGER_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_LEVEL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SD_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *sd_id* is not a valid active *sd_id* | Alter parameter to valid value |
| INVALID_TRIGGER_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GEOFENCE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. *geofence_id* is not a valid active *geofence_id* | Alter parameter to valid value |
| INVALID_VALUE_OPERATOR_PRAM | 1. Parameter value is unsupported, or <br> 2. Incompatable *value_operator* parameter used for trigger source | Alter parameter to valid value |
| INVALID_DURATION_PRAM | 1. Parameter value is unsupported, or <br> 2. Duration is not 0 when trigger_source "Geofence" | Alter parameter to valid value |
| INVALID_DEVICE_ALARM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SITE_ALARM_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_EMAIL_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_SMS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_PHONE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TRIGGER_FAX_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SUGGESTED_ACTIONS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_REACTION_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 07-09-2021 by C. Rollinson