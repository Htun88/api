# API: Xbee
 
### Parameters for an Xbee

### Ver 1.0
---

## Table of Contents
- [SELECT](#select)
  - [REQUEST](#select-request)
  - [REQUEST (FILTERED)](#select-request-filtered)
  - [RESPONSE (SUCCESS)](#select-response-success)
  - [RESPONSE (FAILURE)](#select-response-failure)

<br>

---
---

<br>

# SELECT

Standard request to select Xbee(s), either all available or a filtered selection.

## SELECT REQUEST
A GET request to the server will return all Xbees.

```json
GET /v1/Xbee/ HTTP/1.1
```

## SELECT REQUEST (FILTERED)
Include optional fields to filter results. If no optional field is supplied, ie. the only query field is "action" : "select", then all Xbee will be returned.

```json
POST /v1/Xbee/ HTTP/1.1

    //  Request values
{
    "action" : "select"
    , "xbee_id" : INT | ARRAY
    , "mac_address" : STRING 
}
    //  Example
{
    "action" : "select"
    , "xbee_id" : 
        [
            1
            , 3
        ]
}
```

<br>

| PARAMETER | REQ/OPT | TYPE | ACCEPTED VALUES | DESCRIPTION |
| --------- | --------| ---- |--------------- | ----------- |
| action | **Required** | STRING | "select" | Request action |
| xbee_id | Optional | INT \| ARRAY | [0.. ∞), or <br> ARRAY of [0.. ∞) | Xbee ID |
| mac_address | Optional | STRING | Length == 17 <br> Hexadecimal characters and ':' only | Xbee MAC address <br> Format: Windows MAC address, eg. <br>12:34:56:78:90:AB |


<br>

## SELECT RESPONSE (SUCCESS)
Response success returns all information for the selected Xbee(s).

```json
Content-Type: application/json

{
    "devices": {
            //  Response values
        "device 0": {
            "xbee_id": INT,
            "lat": FLOAT,
            "long": FLOAT,
            "alt": INT,
            "mac_address": STRING,
            "type": INT,
            "description": STRING | null,
            "paired_mac_address": STRING | null,    //  TODO
            "ptt_server_host": null,                //  TODO
            "ptt_server_port": null,                //  TODO
            "ntp_time_server": null,                //  TODO
            "xbee_pan_id": INT | null,              //  TODO
            "active_status": INT,
            "last_modified_by": INT,
            "last_modified_datetime": DATETIME
        },
            //  Example
        "device 1": {
            "xbee_id": 3,
            "lat": "89.123",
            "long": "123.990334",
            "alt": 100,
            "mac_address": "ab:cd:EF:12:56:12",
            "type": 2,
            "description": "descriptive string",
            "paired_mac_address": null,
            "ptt_server_host": null,
            "ptt_server_port": null,
            "ntp_time_server": null,
            "xbee_pan_id": null,
            "active_status": 0,
            "last_modified_by": 1,
            "last_modified_datetime": "2021-07-14 01:38:05"
        },
        ...
    }
}
```

<br>

| PARAMETER | TYPE | DESCRIPTION |
| --------- | ---- | ----------- |
| xbee_id | INT | Xbee ID |
| lat | FLOAT | Latitude in decimal degrees |
| long | FLOAT | Longitude in decimal degrees |
| alt | INT | Altitude in metres |
| mac_address | STRING | Xbee MAC address |
| type | INT | TODO |
| description | STRING \| NULL | Xbee description |
| paired_mac_address | STRING \| NULL | TODO |
| ptt_server_host | TODO \| NULL | TODO |
| ptt_server_port | TODO \| NULL | TODO |
| ntp_time_server | TODO \| NULL | TODO |
| xbee_pan_id | TODO \| NULL | TODO |
| active_status | INT | Xbee is either active (0) or inactive (1) |
| last_modified_by | INT | User ID code that last modified the Xbee |
| last_modified_datetime | DATETIME | Timestamp of last Xbee modification |


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
| INVALID_XBEE_ID_PRAM | Parameter value is unsupported | Alter parameter to valid value |
| INVALID_MAC_ADDRESS_PRAM | Parameter value is unsupported | Alter parameter to valid value |


<br>

---
---

<br>

### Last modified 21-07-2021 by C. Rollinson