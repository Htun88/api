# API: Geofence
 
### Parameters for a geofenced area

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
Standard request to select geofence(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all geofences associated with the user.

```json
GET /v1/Geofence/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all geofences associated with the user will be returned.

```json
POST /v1/Geofence/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "device_id" : INT | ARRAY
    , "asset_id" : INT | ARRAY
    , "deviceasset_id" : INT | ARRAY
    , "geofence_id" : INT | ARRAY
    , "name" : STRING
    , "active_status" : INT
}
    //  Example
{
    "action" : "select"
    , "geofence_id" : "226"
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- | --------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| device_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Device ID associated with a geofence. <br> See [note](#note).
| asset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Asset ID associated with a geofence. <br> See [note](#note).
| deviceasset_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Deviceasset ID associated with a geofence. <br> See [note](#note).
| geofence_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | ID of a geofence. <br> See [note](#note).
| name | Optional | STRING | Length <= 45 | Name of a geofence
| active_status | Optional | INT | [0.. 1] | Geofence is either active (0) or inactive (1) |

<br>

### **Note**
Recommended to directly search for a particular geofence by its ID if known. If not, *device_id*, *asset_id* and *deviceasset_id* can be used to search by a known value, returning the geofence they are associated with. 

These values can be searched individually as INT, or as an ARRAY of INT values. 


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected geofence(s).

```json
Content-Type: application/json

{
    "geofences": {
            //  Response values
        "geofence 0": {
            "geofence_id": INT,
            "name": STRING,
            "points": STRING,
            "radius": INT | null,
            "safe_zone": INT,
            "color": STRING,
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": TIMESTAMP
        },
            //  Example
        "geofence 1": {
            "geofence_id": 226,
            "name": "test13",
            "points": "12.3456,67|12.12321,12.21321|11.223311,44.5566",
            "radius": null,
            "safe_zone": 1,
            "color": "#12A456",
            "active_status": 1,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-06-10 04:48:04"
        },
        ...
    }
}
```
<br> 

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| geofencing_id | INT | ID of the geofence |
| name | STRING | Name of the geofence |
| points | STRING | String of the geofence points, see [notes](#points) |
| radius | INT or NULL | Radius of the geofence around a single point, in metres |
| safe_zone | INT | Status of the geofenced area as either safe (1) or not safe (0) |
| color | STRING | User set geofence colour, 3 or 6 character html hex colour code |
| active_status | INT | Status of the geofenced area as either active (0) or not active (1) |
| last_modified_by | INT | User ID code that last modified the geofence |
| last_modified_datetime | DATETIME | Timestamp of last geofence modification |

<br>

## Notes

### **Points**
A series of GPS coordinates defining the geofenced area as a string. 
- Order is latitude then longitude, seperated by a comma. 
- Successive points are preceeded by a "|".
- Can have either exactly one, three or more than three points. Cannot have two points.
- Multiple points have a null radius. Single points have a non-null radius greater than 0.
- Values are rounded to 6 decimal place precision.

 ``` json
    //  Valid format
 {
    "points" : "lat,long"
 }
    //  Valid format 
{
    "points" : "lat,long|lat,long|lat,long|..."
}
 ```

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
| INVALID_DEVICE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_DEVICEASSET_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_GEOFENCING_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |

<br>

---
---

<br>

# INSERT


Request to insert a new geofence

## INSERT REQUEST
```json
POST /v1/Geofence/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "name" : STRING
    , "points" : STRING
    , "radius" : INT
    , "safe_zone" : INT
    , "color" : STRING
    , "active_status" : INT
}
    //  Example multiple point geofence
{
    "action" : "insert"
    , "name" : "Eiffel Tower Park"
    , "points" : "48.857692,2.291519|48.860198,2.295157|48.853916,2.304974|48.851706,2.301670"
    , "safe_zone" : "0"
    , "color" : "FF00EC"
    , "active_status" : "1"
}
    //  Example single point geofence
{
    "action" : "insert"
    , "name" : "Disputed Territory"
    , "points" : "21.896947,33.694221"
    , "radius" : "250"
    , "safe_zone" : "0"
    , "color" : "#ABC123"
    , "active_status" : "1"
}
```

<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| name | **Required** | STRING | Length <= 45 | Name of the geofence |
| points | **Required** | STRING | Length <= 200 | Geofence points string. See [notes](#points-1). |
| radius | *Conditional* see [notes](#radius) | INT | [0.. ∞) | Geofence radius, in metres, around single geofence point. See [notes](#radius).
| safe_zone | **Required** | INT | [0.. 1] | Status of the geofenced area as either safe (1) or not safe (0) |
| color | **Required** | STRING | Valid 3 or 6 character html hex colour code, preceeding '#' optional | Hex colour code of the geofence |
| active_status | **Required** | INT | [0.. 1] | Status of the geofenced area as either active (0) or not active (1) |

<br>

## Notes

### **Points**
A series of GPS coordinates to define the geofenced area as a string. 
- Order is latitude then longitude, seperated by a comma. 
- Successive points are preceeded by a "|".
- Can have either exactly one, three or more than three points. Cannot have two points.
- Multiple points require a null radius. Single points require a valid radius.
- Cannot set more than one point if geofence radius is not set as "0" in query, if defined.
- Cannot set one point if geofence radius is not also set in query.
- Accepted latitude values: valid float between -90 and +90, inclusive.
- Accepted longitude vales: valid float between -180 and +90, inclusive.
- Values are rounded to 6 decimal place precision.

 ``` json
    //  Valid format
 {
    "points" : "lat,long"
 }
    // Valid format 
{
    "points" : "lat,long|lat,long|lat,long|..."
}
 ```
### **Radius**

The radius of a geofenced area around a singular point.
- Int in metres >= 0.
- Decimal values are not supported.
- Is required as an int > 0 if inserting a geofence with only one geofence point.
- Radius must be either "0" or not defined when inserting a new geofence with multiple points.
- Setting the radius parameter to "0" defines the geofence as having null radius.
- Is null if not defined when inserting a new geofence.

 ``` json
    //  Valid format 
{
    "points" : "lat,long"
    , "radius" : "1"
}
    //  Valid format 
{
    "points" : "lat,long|lat,long|lat,long"
    , "radius" : "0"
}
    //  Invalid format 
{
    "points" : "lat,long"
    , "radius" : "0"
}
    //  Invalid format 
{
    "points" : "lat,long|lat,long|lat,long"
    , "radius" : "1"
}
 ```


<br> 

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted geofence information, as well as the generated geofence ID and an error code: NO_ERROR
```json
Content-Type: application/json

    //  Response values
{
    "name": STRING,
    "points": SRING,
    "radius": INT,
    "safe_zone": INT,
    "color": STRING,
    "active_status": INT, 
    "geofence_id": INT,       //  Generated geofence ID
    "error": STRING             //  Error code
}
    //  Example response
{
    "name": "Disputed Territory",
    "points": "21.896947,33.694221",
    "radius": 250,
    "safe_zone": "0",
    "color": "#ABC123",
    "active_status": "1",
    "geofence_id": "228",     //  Generated geofence ID
    "error": "NO_ERROR"         //  Error code
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
| MISSING_NAME_PRAM | Missing required parameter  | Include required parameter in query |
| MISSING_POINTS_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_RADIUS_PRAM | Missing required parameter  | Include required parameter in query |
| MISSING_SAFE_ZONE_PRAM | Missing required parameter | Include required parameter in query |
| MISSING_COLOR_PRAM | Missing required parameter  | Include required parameter in query |
| MISSING_ACTIVE_STATUS_PRAM | Missing required parameter  | Include required parameter in query |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_POINTS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_RADIUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SAFE_ZONE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COLOR_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_INCOMPATABLE_POINTS_AND_RADIUS_PRAM | 1. Radius > 0  with a multiple point geofence, or <br> 2. Radius = 0 with a single point geofence | 1. Remove radius parameter or set as = 0 <br> 2. Set radius parameter to > 0 |

<br>

---
---

<br>

# UPDATE

Request to update an existing geofence

## UPDATE REQUEST
```json
POST /v1/Geofence/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "geofence_id": STRING
    , "name": STRING
    , "points": STRING
    , "radius": INT
    , "safe_zone": INT
    , "color": STRING
    , "active_status": INT
}
    //  Example
{
    "action" : "update"
    , "geofence_id": "229"
    , "name" : "ChangedName"
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| geofence_id | **Required** | INT | [0.. ∞) | ID of the geofence |
| name | Optional | STRING | Length <= 45 | Name of the geofence |
| points | Optional | STRING | Length <= 1000 | Geofence points string. See [notes](#points-2). |
| radius | *Conditional* see [notes](#radius-1) | INT | [0.. ∞) | Geofence radius, in metres, around single geofence point. See [notes](#radius-1).
| safe_zone | Optional | INT | [0.. 1] | Status of the geofenced area as either safe (1) or not safe (0) |
| color | Optional | STRING | Valid 3 or 6 character html Hex colour code, preceeding '#' optional | Hex colour code of the geofence |
| active_status | Optional | INT | [0.. 1] | Status of the geofenced area as either active (0) or not active (1) |

<br>

## Notes

### **Points**

A series of GPS coordinates to define the geofenced area as a string. 
- Order is latitude then longitude, seperated by a comma. 
- Accepted latitude values: valid float between -90 and +90, inclusive.
- Accepted longitude vales: valid float between -180 and +90, inclusive.
- Values are rounded to 6 decimal place precision.
- Successive points are preceeded by a "|".
- Can have either exactly one, or three or more points. Cannot have two points.

Conditional useage
- Multiple points require a null radius. Single points require a valid radius.
- Cannot set more than one point if geofence already has a radius already or is not set as "0" in request query.
- Cannot set one point if geofence does not have a radius already or one is not set in request query.


 ``` json
    //  Valid format
{
    "points" : "lat,long"
}
    //  Invalid format
{
    "points" : "lat,long|lat,long"
}
    // Valid format 
{
    "points" : "lat,long|lat,long|lat,long|..."
}
 ```
### **Radius**
The radius of a geofenced area around a singular point.
- Int in metres >= 0.
- Decimal values are not supported.
- Is required as an int > 0 if updating a geofence to have only one geofence point.
- Is required as an int = 0 when updating a geofence to have more than one point.
- Setting the radius parameter to "0" defines the geofence as having null radius.
  
 ``` json
    //  Valid format 
{
    "points" : "lat,long"
    , "radius" : "1"
}
    //  Valid format 
{
    "points" : "lat,long|lat,long|lat,long"
    , "radius" : "0"
}
    //  Invalid format 
{
    "points" : "lat,long"
    , "radius" : "0"
}
    //  Invalid format 
{
    "points" : "lat,long|lat,long|lat,long"
    , "radius" : "1"
}
 ```


<br>

## UPDATE RESPONSE (SUCCESS)
Response success returns the updated geofence information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "action" : "update"
    , "geofence_id": STRING
    , "name": STRING
    , "points": STRING
    , "radius": INT
    , "safe_zone": INT
    , "color": STRING
    , "active_status": INT
    , "error" : "NO_ERROR"  //  Error code
}
    //  Example
{
    "geofence_id": "229",
    "name": "ChangedName",
    "error": "NO_ERROR"
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
| INVALID_GEOFENCE_ID_DOES_NOT_EXIST_PRAM | Geofence ID does not exist or is not selectable by current user | Change login or change request parameters |
| GEOFENCE_LOCKED | Cannot alter geofence after an alarm has been triggered for it | No resolution |
| NO_UPDATED_PRAM | No updatable parameter provided | Include minimum 1 updatable parameter in query |
| MISSING_GEOFENCE_ID_PRAM | Missing required geofence ID parameter | Include required parameter in query |
| MISSING_RADIUS_PRAM | Missing required radius parameter | Include required parameter in query |
| INVALID_GEOFENCE_ID_PRAM | 1. Parameter value is unsupported, or <br> 2. Geofence ID is not updatable for user | Alter parameter to valid value |
| INVALID_NAME_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_POINTS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_RADIUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_SAFE_ZONE_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_COLOR_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ACTIVE_STATUS_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_INCOMPATABLE_POINTS_AND_RADIUS_PRAM | 1. Radius > 0  with a multiple point geofence, or <br> 2. Radius = 0 with a single point geofence | 1. Remove radius parameter or set as = 0, or <br> 2. Set radius parameter to > 0, or <br> 3. Alter points parameters to single point, or <br> 4. Alter points parameters to multi point |

<br>

---
---

<br>

### Last modified 12-08-2021 by C. Rollinson