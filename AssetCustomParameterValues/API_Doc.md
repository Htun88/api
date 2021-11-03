# API: AssetCustomParameterValues
 
### Parameters for asset custom paramater values

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
- [DELETE](#delete)
  - [REQUEST](#delete-request)
  - [RESPONSE (SUCCESS)](#delete-response-success)
  - [RESPONSE (FAILURE)](#delete-response-failure)

<br>

---
---

<br>

# SELECT

Standard request to select asset custom parameter value(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all asset custom parameter value associated with the user.

```json
GET /v1/AssetCustomParameterValues/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all asset custom parameter values associated with the user will be returned.

```json
POST /v1/AssetCustomParameterValues/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "custom_param_id" : INT | ARRAY
    , "tag_name" : STRING | ARRAY
    , "value" : STRING | ARRAY
    , "limit" : INT
}
    //  Example
{
    "action": "select"
,   "tag_name" : 
    [
        "btime"
        , "xbch"
    ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset custom param value ID |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID |
| custom_param_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset custom param ID |
| tag_name | Optional | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Asset custom param tag name |
| value | Optional | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Asset custom parameter value |
| limit | Optional | INT | [0.. ∞) | Maximum responses. Default 1000 responses |


## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected asset custom parameter value(s).

```json
Content-Type: application/json

{
    "responses": {
        "response_0": {
            "id": INT,
            "asset_id": INT,
            "value": STRING,
            "name": STRING,
            "tag": STRING,
            "group_id": INT | NULL,
            "group_name": STRING | NULL,
            "group_tag": STRING | NULL
        },
        "response_1": {
            "id": 2,
            "asset_id": 1,
            "value": "+61417638836",
            "name": "Emergency Call Number",
            "tag": "enum",
            "group_id": null,
            "group_name": null,
            "group_tag": null
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| id | INT | Asset custom param value ID |
| asset_id | INT | Asset ID |
| value | STRING | Asset custom param value |
| name | STRING | Description string |
| tag | STRING | Asset custom param type tag name |
| group_id | INT \| NULL| Asset custom param group ID |
| group_name | STRING  \| NULL| Asset custom param group description string |
| group_tag | STRING  \| NULL| Asset custom param group tag name |

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
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_CUSTOM_PARAM_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_TAG_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_VALUE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_LIMIT_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new asset custom parameter value.

If you insert information that already exists, it will update that information with the new values.

An insert request will add +1 to the configuration version of any device currently associated with the asset ID

## INSERT REQUEST

```json
POST /v1/AssetCustomParameterValues/ HTTP/1.1

    //  Request values
    //  Value is an object of every asset custom param to insert as a key / value pair where:
    //      key: tag name string OR custom param ID
    //      value: user defined string OR "Default" for the default value for that key
{
    "action" : "insert"
    , "asset_id" : INT
    , "value" : 
        {
            STRING : STRING
            , ...
        }
}
    //  Example using tag_name strings
{
    "action" : "insert"
    , "asset_id" : "11"
    , "value" : 
        {
            "btime" : "1"
            , "xbch" : "Default"
        }
}
    //  Example using custom param IDs
{
    "action" : "insert"
    , "asset_id" : "11"
    , "value" : 
        {
            "1" : "1"
            , "3" : "Default"
        }
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | asset_id | INT | [0.. ∞) | Asset_id |
| value | **Required** | OBJECT | Object | Object of key value pairs <br> See [here](#value-object)

<br>


## Notes

### **Value object**
Object of key value pairs to insert.
Both keys and values are type STRING with length <= 45.

Keys are either asset custom param IDs or tag_names. Only **one** type can be used for all keys, mixing types in one query is not supported. 

Values are the user inputted value for this key. This can be any string the user wishes, or if they desire the default value for this asset custom param type the keyword "Default" can be used instead.

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted asset custom parameter value information, as well as an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "asset_id": INT,
    "value": [
        {
            "id": INT,
            "tag_name": STRING,
            "value": STRING
        },
        ...
    ],
    "error" : "NO_ERROR"                //  Error code
}
    //  Example response
{
    "asset_id": "11",
    "value": [
        {
            "id": 1,
            "tag_name": "btime",
            "value": "1"
        },
        {
            "id": 3,
            "tag_name": "xbch",
            "value": "1"
        }
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
| INVALID_ASSET_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| ASSET_ID_INACTIVE | Cannot form new associations with an inactive asset ID | Set asset ID to active and re-attempt |
| CONFLICTING_VALUE_TYPES | Cannot use both tag name and custom param IDs in value object | Include only one type in value object and retry |
| INVALID_VALUE_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *tag names* or *custom param IDs* values are not valid *tag names* or *custom param IDs*. If *value* input is multiple *tag names* or *custom param IDs*, this will be accompanied with an "error_detail" message indicating the invalid *tag name*(s) or *custom param ID*(s) | Alter parameter(s) to valid value |
| INVALID_CUSTOM_PARAM_VALUE_PRAM | One or more *tag names* or *custom param IDs* values are unsupported | Alter parameter(s) to valid value |
| MISSING_VALUE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |

<br>

---
---

<br>

# UPDATE

Request to update an existing asset custom parameter value

An update request will add +1 to the configuration version of any device currently associated with the asset ID

## UPDATE REQUEST
```json
POST /v1/AssetCustomParameterValues/ HTTP/1.1

    //  Request values
    //  Value is an object of every asset custom param to insert as a key / value pair where:
    //      key: tag name string OR custom param ID
    //      value: user defined string OR "Default" for the default value for that key
{
    "action" : "update"
    , "asset_id" : INT
    , "value" : 
        {
            STRING : STRING
            , ...
        }
}
    //  Example using tag_name strings
{
    "action" : "update"
    , "asset_id" : "11"
    , "value" : 
        {
            "btime" : "1"
            , "xbch" : "Default"
        }
}
    //  Example using custom param IDs
{
    "action" : "update"
    , "asset_id" : "11"
    , "value" : 
        {
            "1" : "1"
            , "3" : "Default"
        }
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| asset_id | asset_id | INT | [0.. ∞) | Asset_id |
| value | **Required** | OBJECT | Object | Object of key value pairs <br> See [here](#value-object-1)

<br>


## Notes

### **Value object**
Object of key value pairs to insert.
Both keys and values are type STRING with length <= 45.

Keys are either asset custom param IDs or tag_names. Only **one** type can be used for all keys, mixing types in one query is not supported. 

Values are the user inputted value for this key. This can be any string the user wishes, or if they desire the default value for this asset custom param type the keyword "Default" can be used instead.


<br>


<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated asset custom parameter value information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "asset_id": INT,
    "value": [
        {
            "id": INT,
            "tag_name": STRING,
            "value": STRING
        },
        ...
    ],
    "error" : "NO_ERROR"                //  Error code
}
    //  Example response
{
    "asset_id": "11",
    "value": [
        {
            "id": 1,
            "tag_name": "btime",
            "value": "1"
        },
        {
            "id": 3,
            "tag_name": "xbch",
            "value": "1"
        }
    ],
    "error": "NO_ERROR"                 //  Error code
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
| INVALID_ASSET_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| CONFLICTING_VALUE_TYPES | Cannot use both tag name and custom param IDs in value object | Include only one type in value object and retry |
| INVALID_VALUE_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *tag names* or *custom param IDs* values are not valid *tag names* or *custom param IDs* associated with this user. If *value* input is multiple *tag names* or *custom param IDs*, this will be accompanied with an "error_detail" message indicating the invalid *tag name*(s) or *custom param ID*(s) | Alter parameter(s) to valid value |
| INVALID_CUSTOM_PARAM_VALUE_PRAM | One or more *tag names* or *custom param IDs* values are unsupported | Alter parameter(s) to valid value |
| MISSING_VALUE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |

<br>

---
---

<br>

# DELETE

Request to delete an existing asset custom parameter ID

A delete request will add +1 to the configuration version of any device currently associated with the asset ID

## DELETE REQUEST
```json
POST /v1/AssetCustomParameterValues/ HTTP/1.1

    //  Request values
{
    "action" : "delete"
    , "id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "value" : STRING | ARRAY
}
    //  Example
{
    "action" : "delete"
    , "asset_id" : "11"
    , "value" : 
        [
            "btime"
            , "xbch"
        ]
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "delete" | Request action |
| id | *Conditional* | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset custom param value ID <br> Incompatible with *asset_id* and *value* parameters |
| asset_id | *Conditional* | INT | [0.. ∞) | Asset ID <br> Incompatible with *id*  parameter |
| value | *Conditional* | STRING \| ARRAY | Length <= 45, or <br> ARRAY of Length <= 45 | Array of either *tag name* or *custom param ID* values to delete. Note that this array must consist of only **one** type of value <br> Incompatible with *id* parameter |


## DELETE RESPONSE (SUCCESS)
Response success returns the deleted asset custom parameter value id, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "id": [
    INT,
    ...
    ],
    "error": "NO_ERROR"         //  Error code
}
    //  Example
{
    "id": [
    139,
    140
    ],
    "error": "NO_ERROR"         //  Error code
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
| INVALID_ACTION_PRAM | "action" parameter is not select, update,  insert or delete | Alter parameter to "delete" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| NO_UPDATED_PRAM | No updatable parameter provided | Include minimum 1 updatable parameter in query |
| INCOMPATABLE_IDENTIFICATION_PARAMS | *id* is incompatable with *asset_id* and *value* parameters | Remove incompatable parameters |
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_VALUE_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ID_PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *id* values are not valid *id* associated with user. If *id* input is an array of *id*, this will be accompanied with an "error_detail" message indicating the invalid *id*(s) | Alter parameter(s) to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| CONFLICTING_VALUE_TYPES | Cannot use both tag name and custom param IDs in value array | Include only one type in value object and retry |
| INVALID_VALUE_PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *tag names* or *custom param IDs* values are not valid *tag names* or *custom param IDs* associated with this user. If *value* input is multiple *tag names* or *custom param IDs*, this will be accompanied with an "error_detail" message indicating the invalid *tag name*(s) or *custom param ID*(s) | Alter parameter(s) to valid value |

<br>

---
---

<br>

### Last modified 22-09-2021 by C. Rollinson