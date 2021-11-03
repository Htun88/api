<!-- title: Document Title -->

# API: Asset
 
### Parameters for an asset

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

Standard request to select asset(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all assets associated with the user.

```json
GET /v1/Asset/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all assets associated with the user will be returned.

```json
POST /v1/Asset/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "trigger_id" : INT
    , "user_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "asset_name" : STRING
    , "asset_type" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "select"
    , "asset_type" : "truck"
    , "active_status" : "1"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| trigger_ID | Optional | INT | [0.. ∞)| Trigger ID. Use to select all assets associated with this trigger(s) |
| user_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | User ID(s). Use to select all assets associated with this ID(s) |
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID(s)|
| asset_name | Optional | STRING | Length <= 45 | Asset name |
| asset_type | Optional | STRING | Length <= 12 <br>See [notes](#asset-type)| Asset type |
| active_status | Optional | INT | [0.. 1] | Asset is either active (0) or inactive (1) |

<br>

## Notes

### **Asset type**
Type of asset from a limited list, case insensitive.
Valid assets: 
- Aircraft
- Access Point
- Person
- Trap
- Truck
- Vessel


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected asset(s).

```json
Content-Type: application/json

{
    "responses": {
        //  Response values
        "response_0": {
            "asset_id": INT,
            "asset_type": STRING,
            "asset_name": STRING,
            "asset_marker_image": STRING,
            "asset_marker_gray": STRING,
            "asset_marker_gif": STRING,
            "asset_marker_color": STRING,
            "html_colorcode": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
        //  Example response
        "response_1": {
            "asset_id": 28,
            "asset_type": "Truck",
            "asset_name": "Test GAM1",
            "asset_marker_image": "truck3.png",
            "asset_marker_gray": "truck3_inactive.png",
            "asset_marker_gif": "truck3.png",
            "asset_marker_color": "green",
            "html_colorcode": "#008000",
            "active_status": 1,
            "last_modified_by": 1,
            "last_modified_datetime": "2017-03-17 17:34:00"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| asset_id | INT | Asset ID |
| asset_type | STRING | Asset type |
| asset_name | STRING | Asset name |
| asset_marker_image | STRING | Asset marker image |
| asset_marker_gray | STRING | Asset marker gray |
| asset_marker_gif | STRING | Asset marker gif |
| asset_marker_color | STRING | Asset marker colour |
| html_colorcode | STRING | html colourcode |
| active_status | INT| Asset active state, either active (0) or inactive (1) |
| last_modified_by | INT| User ID code that last modified the asset |
| last_modified_datetime | DATETIME | Timestamp of last asset modification |

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
| NO_DATA | No data for user exists that could be returned | Change login or change request parameters |
| INVALID_TOKEN | 1. Token has timed out, or <br> 2. Token is invalid | Provide a new token |
| INVALID_REQUEST_PRAM | 1. "action" parameter is missing, or <br> 2. incorrect JSON format | 1. Include required parameter, or <br> 2. check request JSON for incorrect data
| INVALID_ACTION_PRAM | "action" parameter is not select, update or insert | Alter parameter to "select" |
| INVALID_INPUT_PRAM | Input parameter does not exist or is spelt incorrectly | Remove parameter or alter parameter to correct spelling. <br> This error is accompanied with subsequent message "error_detail: INVALID_PRAM_X", where X is the first occuring invalid parameter |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new asset

## INSERT REQUEST

```json
POST /v1/Asset/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "asset_type" : INT
    , "asset_name" : STRING
    , "asset_marker_image" : STRING
    , "asset_marker_gray" : STRING
    , "asset_marker_gif" : STRING
    , "asset_marker_color" : STRING
    , "html_colorcode" : STRING
    , "active_status" : INT
}
    //  Example without optional fields
{
    "action" : "insert"
    , "asset_type" : "Vessel"
    , "asset_name" : "Boaty McBoatface"
    , "asset_marker_color" : "purple"
    , "html_colorcode" : "#00F0FF"
    , "active_status" : "0"
}
    //  Example with optional fields
{
    "action" : "insert"
    , "asset_type" : "Vessel"
    , "asset_name" : "Boaty McBoatface"
    , "asset_marker_image" : "niceboat.png"
    , "asset_marker_gray" : "niceboat_inactive.png"
    , "asset_marker_gif" : "niceboat.png"
    , "asset_marker_color" : "purple"
    , "html_colorcode" : "#00F0FF"
    , "active_status" : "0"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_type | **Required**  | STRING | Length <= 12 <br> See [notes](#asset-type-1)| Asset type |
| asset_name | **Required**  | STRING | Length <= 45 | Asset name  |
| asset_marker_image | Optional | STRING | Length <= 100 | Asset marker, standard image. <br> Defaults to null if not included |
| asset_marker_gray | Optional | STRING | Length <= 100  | Asset marker, grayed image. <br> Defaults to null if not included |
| asset_marker_gif | Optional | STRING | Length <= 100  | Asset marker gif.<br> Defaults to null if not included |
| asset_marker_color | **Required**  | STRING | Length <= 7 <br> See [notes](#asset-marker-color) | Asset marker colour |
| html_colorcode | **Required** | STRING | Valid 3 or 6 character html hex colour code, preceeding '#' optional | Asset marker HTML colour |
| active_status | **Required**  | INT | [0.. 1] | Asset is either active (0) or inactive (1) |

<br>

## Notes

### **Asset type**
Type of asset from a limited list, case insensitive.
Valid assets: 
- Aircraft
- Access Point
- Person
- Trap
- Truck
- Vessel


### **Asset marker color**
Asset marker colour from a limited list, case insensitive.
Valid colours: 
- White
- Silver
- Gray
- Black
- Red
- Maroon
- Yellow
- Olive
- Lime
- Green
- Aqua
- Teal
- Blue
- Navy
- Fuchsia
- Purple


<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted asset information, as well as the generated asset and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "asset_type": INT,
    "asset_name": STRING,
    "asset_marker_image": STRING,
    "asset_marker_gray": STRING,
    "asset_marker_gif": STRING,
    "html_colorcode": STRING,
    "active_status": INT,
    "asset_id": INT,                    //  Generated PARAMETER
    "error": STRING                     //  Error code
}
    //  Example response when not using optional fields
{
    "asset_type": "Vessel",
    "asset_name": "Boaty McBoatface",
    "asset_marker_color": "purple",
    "html_colorcode": "#00F0FF",
    "active_status": "0",
    "asset_id": "299",                  //  Generated PARAMETER
    "error": "NO_ERROR"                 //  Error code
}
    //  Example response when using optional fields
{
    "asset_type": "Vessel",
    "asset_name": "Boaty McBoatface",
    "asset_marker_image": "niceboat.png",
    "asset_marker_gray": "niceboat_inactive.png",
    "asset_marker_gif": "niceboat.png",
    "asset_marker_color": "purple",
    "html_colorcode": "#00F0FF",
    "active_status": "0",
    "asset_id": "298",                  //  Generated PARAMETER
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
| MISSING_ASSET_TYPE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_NAME_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ASSET_MARKER_COLOR_PRAM| Missing required parameter | Include required parameter in query |
| MISSING_HTML_COLORCODE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ASSET_TYPE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_MARKER_IMAGE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_MARKER_GRAY_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASET_MARKER_GIF_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASET_MARKER_COLOR_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_HTML_COLORCODE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# UPDATE
---
Request to update an existing asset

## UPDATE REQUEST
```json
POST /v1/Asset/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "asset_id" : INT
    , "asset_type" : STRING
    , "asset_name" : STRING
    , "asset_marker_image" : STRING
    , "asset_marker_gray" : STRING
    , "asset_marker_gif" : STRING
    , "asset_marker_color" : STRING
    , "html_colorcode" : STRING
    , "active_status" : STRING
}
    //  Example
{
    "action" : "update"
    , "asset_id" : "299"
    , "asset_name" : "Sir Boaty McBoatface"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| asset_id | **Required**  | INT | [0.. ∞)| Asset ID |
| asset_type | Optional  | STRING | Length <= 12 <br> See [notes](#asset-type-2)| Asset type |
| asset_name | Optional | STRING | Length <= 45 | Asset name  |
| asset_marker_image | Optional | STRING | Length <= 100 | Asset marker, standard image. <br> Defaults to null if not included |
| asset_marker_gray | Optional | STRING | Length <= 100  | Asset marker, grayed image. <br> Defaults to null if not included |
| asset_marker_gif | Optional | STRING | Length <= 100  | Asset marker gif. <br> Defaults to null if not included |
| asset_marker_color | Optional  | STRING | Length <= 7 <br> See [notes](#asset-marker-color-1) | Asset marker colour |
| html_colorcode | Optional | STRING | Valid 3 or 6 character html hex colour code, preceeding '#' optional | Asset marker HTML colour |
| active_status | Optional  | INT | [0.. 1] | Asset is either active (0) or inactive (1) |

<br> 

## Notes

### **Asset type**
Type of asset from a limited list, case insensitive.
Valid assets: 
- Aircraft
- Access Point
- Person
- Trap
- Truck
- Vessel


### **Asset marker color**
Asset marker colour from a limited list, case insensitive.
Valid colours: 
- White
- Silver
- Gray
- Black
- Red
- Maroon
- Yellow
- Olive
- Lime
- Green
- Aqua
- Teal
- Blue
- Navy
- Fuchsia
- Purple

<br>

## UPDATE RESPONSE (SUCCESS)
Response success returns the updated asset information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "asset_id": INT,
    "asset_type": STRING,
    "asset_name": STRING,
    "asset_marker_image": STRING,
    "asset_marker_gray": STRING,
    "asset_marker_gif": STRING,
    "asset_marker_color": STRING,
    "html_colorcode": STRING,
    "active_status": STRING,
    "error" : "NO_ERROR"        //  Error code
}
    //  Example
{
    "asset_id": "299",
    "asset_name": "Sir Boaty McBoatface",
    "error": "NO_ERROR"         //  Error code
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
| MISSING_ASSET_ID_PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ASSET_ID_PRAM| 1. Parameter value is unsupported, or <br> 2. Asset ID is not accessible by user | Parameter value is unsupported |
| INVALID_ASSET_TYPE_PRAM | Parameter value is unsupported | Parameter value is unsupported |
| INVALID_ASSET_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_MARKER_IMAGE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_MARKER_GRAY_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASET_MARKER_GIF_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASET_MARKER_COLOR_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_HTML_COLORCODE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 19-07-2021 by C. Rollinson