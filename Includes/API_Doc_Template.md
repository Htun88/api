# API: TODO
 
### Parameters for a/an TODO

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

Standard request to select TODO(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all TODO associated with the user.

```json
GET /v1/TODO/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all TODO associated with the user will be returned.

```json
POST /v1/TODO/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "PARAMETER" : INT
    , "PARAMETER" : INT | ARRAY
    , "PARAMETER" : FLOAT
    , "PARAMETER" : STRING
    , "PARAMETER" : DATETIME
}
    //  Example
{
    "action" : "select"
    , "PARAMETER" : "1"
    , "PARAMETER" : 
        [
            1
            , 2
        ]
    , "PARAMETER" : "string"
    , "PARAMETER" : "1.2" 
    , "PARAMETER" : "2021-01-01 01:01:01" 
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| PARAMETER | *Conditional* | INT | [0.. ∞) | TODO |
| PARAMETER | Optional | INT | [0.. ∞) | TODO |
| PARAMETER | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | TODO |
| PARAMETER | Optional | STRING | Length <= TODO | TODO |
| PARAMETER | Optional | FLOAT | [0, ∞) | TODO |
| PARAMETER | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | TODO |
*COMMONLY USED*
| active_status | Optional | INT | [0.. 1] | TODO is either active (0) or inactive (1) |
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return TODO that have occured after this datetime. Must be before timestamp_to, if included
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return TODO that have occured before this datetime. Must be after timestamp_from, if included
<br>

## Notes

### **datetime-notes**
Datetime parameter. Brief explanation.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected TODO(s).

```json
Content-Type: application/json

{
    "responses": {
            //  Response values
        "response_0": {
            "PARAMETER" : INT,
            "PARAMETER" : INT | ARRAY,
            "PARAMETER" : FLOAT,
            "PARAMETER" : STRING,
            "PARAMETER" : DATETIME,
            "last_modified_by" : INT,
            "last_modified_datetime" : DATETIME
        },
            //  Example
        "response_1": {
            "PARAMETER" : "1",
            "PARAMETER" : [1, 2],
            "PARAMETER" : "1.1",
            "PARAMETER" : "string",
            "PARAMETER" : "2021-01-01 01:01:01",
            "last_modified_by" : 1,
            "last_modified_datetime" : "2021-01-01 01:01:01"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| PARAMETER | INT | TODO |
| PARAMETER | INT \| ARRAY | TODO |
| PARAMETER | FLOAT | TODO |
| PARAMETER | STRING | TODO |
| PARAMETER | DATETIME | TODO |

*COMMONLY USED*
| active_status | INT | TODO is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the TODO |
| last_modified_datetime | DATETIME | Timestamp of last TODO modification |


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

*TODO THIS SECTION BELOW*
| INVALID_ TODO _PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

# INSERT

Request to insert a new TODO

## INSERT REQUEST

```json
POST /v1/TODO/ HTTP/1.1

    //  Request values
{
    "action" : "insert"
    , "PARAMETER" : INT
    , "PARAMETER" : INT | ARRAY
    , "PARAMETER" : STRING
    , "PARAMETER" : FLOAT
    , "PARAMETER" : DATETIME
}
    //  Example
{
    "action" : "insert"
    , "PARAMETER" : "1"
    , "PARAMETER" : 
        [
            1
            , 2
        ]
    , "PARAMETER" : "string"
    , "PARAMETER" : "1.2" 
    , "PARAMETER" : "2021-01-01 01:01:01" 
}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "insert" | Request action |
| PARAMETER | *Conditional* | INT | [0.. ∞) | TODO |
| PARAMETER | Optional | INT | [0.. ∞) | TODO |
| PARAMETER | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | TODO |
| PARAMETER | Optional | STRING | Length <= TODO | TODO |
| PARAMETER | Optional | FLOAT | [0, ∞) | TODO |
| PARAMETER | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | TODO |
*COMMONLY USED*
| active_status | Optional | INT | [0.. 1] | TODO is either active (0) or inactive (1) |
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return TODO that have occured after this datetime. Must be before timestamp_to, if included
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return TODO that have occured before this datetime. Must be after timestamp_from, if included
<br>

## Notes

### **datetime-notes**
Datetime parameter. Brief explanation.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

<br>

<br>

## INSERT RESPONSE (SUCCESS)

Response success returns the inserted TODO information, as well as the generated *TODO* and an error: NO_ERROR code
```json
Content-Type: application/json

    //  Response values
{
    "PARAMETER" : INT,
    "PARAMETER" : INT | ARRAY,
    "PARAMETER" : STRING,
    "PARAMETER" : FLOAT,
    "PARAMETER" : DATETIME,
    "PARAMETER" : INT,                   //  Generated PARAMETER
    "error" : "NO_ERROR"                 //  Error code
}
    //  Example response
{
    "PARAMETER" : 1,
    "PARAMETER" : [1, 2],
    "PARAMETER" : "string",
    "PARAMETER" : 1.1,
    "PARAMETER" : "2021-01-01 01:01:01",
    "PARAMETER" : 1,                     //  Generated PARAMETER
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

*TODO THIS SECTION BELOW*
| MISSING_ TODO _PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ TODO _PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ TODO _PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_ TODO _PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *todo* values are not valid *todo* associated with user. If *todo* input is an array of *todo*, this will be accompanied with an "error_detail" message indicating the invalid *todo*(s) | Alter parameter(s) to valid value |
| TODO | *todo* is incompatable with *todo* and *todo* parameters | Remove incompatable parameters |

<br>

---
---

<br>

# UPDATE

Request to update an existing TODO

## UPDATE REQUEST
```json
POST /v1/TODO/ HTTP/1.1

    //  Request values
{
    "action" : "update"
    , "PARAMETER" : INT
    , "PARAMETER" : INT | ARRAY
    , "PARAMETER" : FLOAT
    , "PARAMETER" : STRING
    , "PARAMETER" : DATETIME
 
}
    //  Example
{
    "action" : "update"
    , "PARAMETER" : "1"
    , "PARAMETER" : 
        [
            1
            , 2
        ]
    , "PARAMETER" : "1.1"
    , "PARAMETER" : "string"
    , "PARAMETER" : "2021-01-01 01:01:01"

}
```
<br>

| PARAMETER    | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| ---------    | ------- | ---- | ----------------| ----------- |
| action | **Required** | STRING | "update" | Request action |
| PARAMETER | *Conditional* | INT | [0.. ∞) | TODO |
| PARAMETER | Optional | INT | [0.. ∞) | TODO |
| PARAMETER | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | TODO |
| PARAMETER | Optional | STRING | Length <= TODO | TODO |
| PARAMETER | Optional | FLOAT | [0, ∞) | TODO |
| PARAMETER | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | TODO |
*COMMONLY USED*
| active_status | Optional | INT | [0.. 1] | TODO is either active (0) or inactive (1) |
| timestamp_from | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return TODO that have occured after this datetime. Must be before timestamp_to, if included
| timestamp_to | Optional | DATETIME | yyyy-mm-dd HH:mm:ss <br> See [notes](#datetime-notes) | Datetime: return TODO that have occured before this datetime. Must be after timestamp_from, if included
<br>

## Notes

### **datetime-notes**
Datetime parameter. Brief explanation.
- Time portion optional, defaults to 00:00:00. 24hr time. 
- Partial time inputs accepted, any missing data is autofilled to :00. ie. 19:12 -> 19:12:00.
- Single digit values must be preceeded with a 0. eg. 2021-04-12 02:08:05.

<br>


<br>


## UPDATE RESPONSE (SUCCESS)
Response success returns the updated TODO information, as well as an error: NO_ERROR code

```json
Content-Type: application/json

    //  Response values
{
    "PARAMETER": INT,
    "PARAMETER": INT | ARRAY,
    "PARAMETER": FLOAT,
    "PARAMETER": STRING,
    "PARAMETER": DATETIME,
    "error" : "NO_ERROR"        //  Error code   
}
    //  Example
{
    "PARAMETER" : "1",
    "PARAMETER" : [1, 2],
    "PARAMETER" : "1.1",
    "PARAMETER" : "string",
    "PARAMETER" : "2021-01-01 01:01:01",
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
*TODO THIS SECTION BELOW*
| MISSING_ TODO _PRAM | Missing required parameter | Include required parameter in query |
| INVALID_ TODO _PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_ TODO _PRAM | 1. Parameter value is unsupported , or <br> 2. Parameter value is not accessible for this user | Alter parameter to valid value |
| INVALID_ TODO _PRAM | 1. Parameter value is unsupported, or <br> 2. one or more *todo* values are not valid *todo* associated with user. If *todo* input is an array of *todo*, this will be accompanied with an "error_detail" message indicating the invalid *todo*(s) | Alter parameter(s) to valid value |
| TODO | *todo* is incompatable with *todo* and *todo* parameters | Remove incompatable parameters |

<br>

---
---

<br>

### Last modified dd-mm-yyyy by Initial. Lastname TODO