<?php
    // Sanitise
    // A file of sanitisation for the USM API.
    // Please don't break this :(
    // Please be careful placing echos and remove when done
    //      Any left over echos / print_r / var_dump / etc likely will break logs
include 'keys.php';
//include 'checktoken.php';

//  Small class for active and inactive values. 
class status {
    CONST active = 0;
    CONST inactive = 1;
}

class allFile {
    CONST limit = 1000;
    CONST smalllimit = 50;
}

//  Class to change the log level of various log messages. 
//  Done once here instead of in each file
class logLevel {
    //  Change maximumlevel for the maximum allowed level. This is only used within sanitise file as an upper bounds "$value <= MAXIMUMLEVEL" check
    CONST MAXIMUMLEVEL = 4;
    CONST accessed = 3;
    CONST action = 3;
    CONST request = 3;
    CONST requestError = 1;
    CONST response = 1;
    CONST responseError = 1;
    CONST invalid = 1;
    CONST missing = 2;
}

//  Class to change the log text of various log messages. 
//  Done once here instead of in each file
class logText {
    CONST accessed = " - Accessed";
    CONST action = " - Action Type - ";
    CONST request = " - Request - ";
    CONST genericError = " - ERROR - ";
    CONST requestInsufficientPermission = " - ERROR Insufficient Access Permissions";
    CONST requestIncompatable = " - ERROR Incompatable Parameters - ";
    CONST response = " - Response - ";
    CONST responseError = " - ERROR Response Error - ";
    CONST invalidKey = " - ERROR Invalid Key";
    CONST invalidValue = " - ERROR Invalid Value in Key - ";
    CONST missingUpdate = " - ERROR Insufficient Update Args - ";
    CONST missingDelete = " - ERROR Insufficient Delete Args - ";
    CONST missing = " - ERROR Missing Arg - ";
    CONST responseLarge = " - LARGE DATA - ";
    CONST emailSuccess = " - Email Sent - ";
    CONST emailError = " - ERROR Email Failed to Send ";
}

//  Class to change the log type of various log messages. 
//  Done once here instead of in each file
class logType {
    //  Change maximumtype for the maximum allowed type. This is only used within sanitise file as an upper bounds "$value <= MAXIMUMTYPE" check
    CONST MAXIMUMTYPE = 2;
    CONST error = 0;
    CONST response = 1;
    CONST responseError = 0;
    CONST success = 1;
    CONST accessed = 2;
    CONST request = 2;
    CONST requestError = 1;
    CONST action = 2;
}

//  Const arrays for matching
//  Replace array used for string sanitisation. Removes a chunk of escape characters.
//  Taken from: https://www.php.net/manual/en/language.types.string.php
CONST replaceArray = ["\"", "'", "`", "<", ">", "\\", "\n", "\r", "\t", "\v", "\e", "\f", "$", "\$"];
CONST eventDateArray = ["`", "<", ">", "\n", "\r", "\t", "\v", "\e", "\f", "$", "\$"];
CONST assetTypeArray = ["person", "vessel", "truck", "trap", "aircraft", "access point"];
CONST componentTypeArray = ["Module", "Parameter", "Group Parameter", "Sensor", "Connector Def"];
CONST sendViaArray = ["auto", "gsm", "sat", "sat", "xbee", 'wifi'];
CONST colourTypeArray = ["white", "silver", "gray", "black", "red", "maroon", "yellow", "olive", "lime", "green", "aqua", "teal", "blue", "navy", "fuchsia", "purple"];
CONST actionTypeArray = ["select", "insert", "update", "delete"];
CONST acknowledgeTriggerStateArray = ["00", "01", "10", "11"];
CONST methodTypeArray = ["sent", "recieved"];
CONST sensorDataType = ["Numeric", "String"];
CONST triggerSpanArray = ["Current", "Previous", "Available"];
CONST byteLength = ["UInt32", "Int32", "UInt24", "Int24", "UInt16", "Int16", "UInt8", "Int8"];

//  Regex match functions
class regex {
    CONST timedate = "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/";
    CONST noPeriod = "/^\d*\.\d*$/";
    CONST noDecimal = "/^\d*\.|\,\d*$/";    
    CONST colourCode = "/^#?[0-9a-f]{3}([0-9a-f]{3})?$/i";
    CONST intlTelephoneCallCode = "/^\+(\d{1,})$/";  
    CONST intlTelephone = "/^[+]?(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d|2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]|4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$/";
    CONST mobTelephone = "/^[\+{1}]?[\d]{1,15}$/";
    CONST dateFormat = "/^([2][0|1]\d{2}-(0[\d]|1[0-2])-(0[\d]|[12]\d|3[01]))$/";
    CONST timeFormat = "/^(00|0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9])?(:[0-5][0-9])?$/";
    //  CONST stringSpaceDashDot = "/^[\w]+([\s\-._]?\w)+$/";       //  Old version
    CONST stringSpaceDashDot = "/^[\w\-\.]+( +[\w\-\.]+)*$/";       //  New Version. Note you cannot start nor end on whitespace
    CONST custom_param_value = "/^[\w\-\+\.]+( +[\w\-\+\.]+)*$/";
    CONST stringOneWord = "/^[\w]*$/";
    CONST userAgent = "/^[\w*[\|_#]*]*$/";
    CONST twoValueBinary = "/^[01]{2}$/";
    CONST zeroToThree = "/^[0123]{1}$/";
    CONST validMathEquation = "/^[\(\)\+\*\.\-\/\d ]{1,}$/";
    CONST validMathEquationInput = "/^([\(\)\+\*\.\-\%\/ \d]|(\\$\bvalue\b)){1,}$/";
    CONST validMac = "/^([[:xdigit:]]{2}[:\-.]?){5}[[:xdigit:]]{2}$/";
    CONST validMathComparitor = "/^[<>=]$|^[!<>]=$/";
    CONST validTriggerComparitor = "/^[<>]=?$|^=$|^\bExit\b$|^\bEntry\b$/";
}

/**
 * Generate error message for a missing parameter
 * 
 * output: die( {error: MISSING_ strtoupper($arg) _PRAM} )
 * @param string $arg - the missing parameter
 */
function errorMissing ($arg, $API, $logParent) {
    global $token;
    logEvent($API . logText::missing . strtoupper($arg), logLevel::missing, logType::error, $token=$token, $logParent);
    die("{\"error\":\"MISSING_" . strtoupper($arg)  . "_PRAM\"}");
}

/**
 * Generate error message for an invalid parameter
 * 
 * output: die( {error: INVALID_ strtoupper($arg) _PRAM} )
 * @param string $arg - the invalid parameter
 */
function errorInvalid ($arg, $API, $logParent) {
    global $token;
    logEvent($API . logText::invalidValue . strtoupper($arg), logLevel::invalid, logType::error, $token=$token, $logParent);
    die("{\"error\":\"INVALID_" . strtoupper($arg)  . "_PRAM\"}");
}

/**
 * Generate error message for a generic error
 * 
 * output: die( {error: strtoupper($arg)} )
 * @param string $arg - the error message
 */
function errorGeneric ($arg, $API, $logParent) {
    global $token;
    logEvent($API . logText::genericError . strtoupper($arg), logLevel::invalid, logType::error, $token=$token, $logParent);
    die("{\"error\":\"" . strtoupper($arg)  . "\"}");
}

/**
 * Function to turn a single or array of inputs into a sanitised array. 
 * 
 * @param array|int|string $arg input values
 * @param string $argtype String of the sanitisation method
 * @param int $strlength Max string length, if applicable
 * 
 * @return array $arg  return sanitised array
 */
function sanitise_input_array ($arg, $argtype, $strlength, $API, $logParent) {
    if(!is_array($arg)) {
        $arg = [$arg];
    }

    $argArray = [];
    foreach ($arg as $value){
        $value = sanitise_input($value, $argtype, $strlength, $API, $logParent);
        if (!in_array($value, $argArray)) {
            array_push($argArray, $value);
        }
    }

    if (empty($argArray)) {
        errorInvalid ($argtype, $API, $logParent);
    }

    return $argArray;    
}

/**
 * @param string $arg Input to be filtered
 * @param string $argtype Input name / type
 * @param int|null $strlength length of input name string. null if number
 * @return $arg if arg passes respective checks, return a sanitised version of arg
 */
function sanitise_input ($arg, $argtype, $strlength, $API, $logParent) {
        
    switch ($argtype) {
        CASE "trigger_state":
        CASE "acknowledge_state":
        CASE "operator":
        CASE "active_status":
        CASE "safe_zone":
        CASE "firmware_status":
        CASE "chart":
        CASE "stel_report":
        CASE "linked":
        CASE "trend":
        CASE "gauge":
        CASE "message.type":
        CASE "method":
        CASE "acknowledged":
            //  Accepted values: 0 or 1, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && ($arg == status::active
                || $arg == status::inactive)
                ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
            return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "user_id":
        CASE "radius":
        CASE "number_index":
        CASE "message_id":
        CASE "data_id":
        CASE "imei":
        CASE "deviceassets_deviceasset_id":
        CASE "sensor_data_data_id":
        CASE "provisioning_component_id":
        CASE "provisioning_link_id":
        CASE "param_id":
        CASE "module_id":
        CASE "id":
        CASE "custom_param_id":
        CASE "associated_id":
        CASE "message_number":
        CASE "assets_asset_id":
        CASE "whitelist_id":
        CASE "asset_id":
        CASE "type":
        CASE "device_provisioning_id": 
        CASE "asset_custom_param_id":
        CASE "trigger_id":
        CASE "action_id":
        CASE "update_authorized":
        CASE "device_license_id":
        CASE "trigger_id":
        CASE "level_id":
        CASE "trigger_level":
        CASE "sensor_def_sd_id":
        CASE "sd_id":
        CASE "geofencing_geofencing_id":
        CASE "duration":
        CASE "AP_type":
        CASE "alarm_id":
        CASE "uom_to_id":
        CASE "xbee_id":
        CASE "ap_id":
        CASE "geofencing_id":
        CASE "geofence_id":
        CASE "sensor_data_value":
        CASE "deviceasset_id":
        CASE "device_id":
        CASE "provisioning_id":
        CASE "alarm_events_id":
        CASE "calib_id":
        CASE "param_value":
        CASE "module_value":
        CASE "component_id":
        CASE "sensor_id":
        CASE "sensor_det_id":
        CASE "event_parent":
        CASE "asset_custom_param_group_id":
        CASE "group_id":
        CASE "parameter_id":
        CASE "geofences_version":
        CASE "limit":
        CASE "deviceassets_det":
        CASE "sd_uom_id":
        CASE "uom_id_from":
        CASE "uom_id_to":
        CASE "priority":
        CASE "last_received_id":
        CASE "packet_type": 
        CASE "sensor_def": 
            //  Accepted values: valid int greater than 0 (inclusive), without decimal indication
            if (is_numeric($arg) == true 
			    && intval($arg) >= 0 
			    && preg_match(regex::noDecimal, $arg) === 0
			    ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                return $arg;
		    }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "response_limit":
        CASE "data_reset_id":
        CASE "sd_deactivated_data":
        CASE "sd_data_min":
        CASE "sd_data_max":
        CASE "sd_graph_min":
        CASE "sd_graph_max":
        CASE "alt_range_id":
        CASE "alt_from":
        CASE "sensor_value" :
        CASE "alt_to":
        CASE "alt":
        CASE "trigger_value":
        CASE "license_id":
            //  Accepted value: valid int, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "event_level":
            //  Accepted value: valid int, 0-MAXIMUMLEVEL, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && intval($arg) >= 0
                && intval($arg) <= logLevel::MAXIMUMLEVEL  
                ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "event_type":
            //  Accepted value: valid int, 0-MAXIMUM_TYPE, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && intval($arg) >= 0
                && intval($arg) <= logType::MAXIMUMTYPE  
                ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "mac_address" :
            //  Accepted values: valid MAC addresses
            //  MAC addresses can be returned in several formats. We just want the windows format: 12 hexadecimal values as 6 sets of 2 character chunks seperated by ':'
            $arg = str_replace([":","-","."], "", $arg);        //  Remove all delimeters
            $arg = implode(":", str_split($arg, 2));            //  Split the string into groups of 2, then join them together around ':' character
            $arg = str_replace(replaceArray, "", $arg );        //  Search through the replace array and remove any escape characters
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);    //  Filter the resultant string
            if (strlen($arg) != 17                              //  String should be exactly 17 characters in length
                || preg_match(regex::validMac, $arg) == 0       //      and must pass the validMac regex. If either condition fails then error out.
                ) {
                    errorInvalid($argtype, $API, $logParent);
                }
            else {
                return $arg;
            }
            BREAK;
        CASE "math_Value":
            //  Accepted values: valid INT
            if (is_numeric($arg) == true ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "sensor_data_sensor_id":
            //  Accepted values: valid INT
            //  Pass lat / long to their respective GPS sanitisations
            if (is_numeric($arg) == true ){
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                if ($arg == 12) {
                    $arg = sanitise_input($arg, "lat", null, $API, $logParent);
                }
                else if ($arg == 13) {
                   $arg = sanitise_input($arg, "long", null, $API, $logParent);
                }
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "sensor_value_12":
		    //  Accepted values: valid Latitude

			$arg = filter_var(floatval($arg), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$arg = round ($arg, 6);
			return $arg;
			
            errorInvalid("sensor_value", $API, $logParent);

            BREAK;
        CASE "lat" :
            //  Accepted values: valid Latitude
            if (is_numeric($arg) == true 
			    && floatval($arg) <= 90 
			    && floatval($arg) >= -90
			    ){
                $arg = filter_var(floatval($arg), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $arg = round ($arg, 6);
                return $arg;
            }
            //if ($argtype == "sensor_value_12") {
                //errorInvalid("sensor_value", $API, $logParent);
           // }
            //else {
               errorInvalid($argtype, $API, $logParent);
            //}
            BREAK;
        CASE "sensor_value_13":
            //  Accepted values: valid Longitude

			$arg = filter_var(floatval($arg), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
			$arg = round ($arg, 6);
			return $arg;
            
            errorInvalid("sensor_value", $API, $logParent);
            

            BREAK;
        CASE "long":
            //  Accepted values: valid Longitude
            if (is_numeric($arg) == true  
                && floatval($arg) <= 180 
                && floatval($arg) >= -180
                ){
                $arg = filter_var(floatval($arg), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $arg = round ($arg, 6);
                return $arg;
            }
            //if ($argtype == "sensor_value_13") {
               // errorInvalid("sensor_value", $API, $logParent);
            //}
           // else {
                errorInvalid($argtype, $API, $logParent);
            //}
            BREAK;
        CASE "asset_marker_image":
        CASE "asset_marker_gray":
        CASE "asset_marker_gif":
            $arg = strtolower($arg);
        CASE "name":
        CASE "module_name": 
        CASE "param_name":    
        CASE "base":
        CASE "license_hash":
        CASE "syslog_message_id":
        CASE "asset_name":
        CASE "trigger_name":
        CASE "description":
        CASE "message_description":
        CASE "iconpath":
        CASE "device_name":
        CASE "default_value":
        CASE "device_conn_properties":
        CASE "suggested_actions":
        CASE "device_sn":
        CASE "sd_name":
        CASE "unit":
        CASE "chartlabel":
        CASE "sensor_name":
        CASE "level_name":
        CASE "level_foldername":
        CASE "asset_task":
        CASE "value":
        CASE "tag_name": 
        CASE "sensor_def_data_type": 
           
            $arg = str_replace(replaceArray, "", $arg );
                 $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringSpaceDashDot,$arg) === 1
                ) {          
                return $arg;
		    }
		    else {
                errorInvalid($argtype, $API, $logParent);
		    }
            BREAK;
		CASE "desired_stored_versions":
        CASE "desired_version":	
		CASE "application":
        CASE "alarm_action":
		    $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength
                ) {
                return $arg;
		    }
		    else {
                errorInvalid($argtype, $API, $logParent);
		    }
		BREAK;
		CASE "event_data": //todo connor
			$arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            //if (strlen($arg) <= $strlength
                //) {
                return $arg;
		    //}
		    //else {
               // errorInvalid($argtype, $API, $logParent);
		    //}
		BREAK;
        CASE "sd_data_type": 
            $arg = ucfirst(strtolower($arg));
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringOneWord,$arg) === 1
                && in_array($arg, sensorDataType)
                ) {
                return $arg;
            }
            errorInvalid($argtype, $API, $logParent);
        CASE "span": 
        $arg = ucfirst(strtolower($arg));
        $arg = str_replace(replaceArray, "", $arg );
        $arg = filter_var($arg, FILTER_SANITIZE_STRING);
        if (strlen($arg) <= $strlength 
            && preg_match(regex::stringOneWord,$arg) === 1
            && in_array($arg, triggerSpanArray)
            ) {
            return $arg;
        }
        errorInvalid($argtype, $API, $logParent);
        CASE "bytelength": 
            //  Need to selectively capitialise the first two characters if its UInt, vs 1 if Int
            $arg = strtolower($arg);
            if (substr($arg, 0, 1) == "u") {
                $arg = "U" . ucfirst(substr($arg, 1));
            }
            else {
                $arg = ucfirst($arg);
            }
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringOneWord,$arg) === 1
                && in_array($arg, byteLength)
                ) {
                return $arg;
            }
            errorInvalid($argtype, $API, $logParent);
		CASE "event_data":
            //  Accepted value: strings and string length
            $arg = htmlspecialchars($arg, ENT_NOQUOTES);
            if (strlen($arg) <= $strlength) {
                return $arg;
		    }
		    else {
                errorInvalid($argtype, $API, $logParent);
		    }
			BREAK;
        CASE "trigger_type":
            //  Accepted values: 0 or 1, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && ($arg == status::active
                || $arg == status::inactive)
                ){
                if ($arg == 0) {
                    $arg = "Critical";
                    return $arg;
                }
                else if ($arg == 1) {
                    $arg = "Warning";
                    return $arg;
                }
            }
            else {
                $arg = ucfirst(strtolower($arg));
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    ) {
                    if ($arg == "Warning"
                        || $arg == "Critical"
                        ) {
                    return $arg;
                    }
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "order":
            //  Accepted values: 0 or 1, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && ($arg == status::active
                || $arg == status::inactive)
                ){
                if ($arg == 0) {
                    $arg = "DESC";
                    return $arg;
                }
                else if ($arg == 1) {
                    $arg = "ASC";
                    return $arg;
                }
            }
            else {
                $arg = strtoupper($arg);
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    ) {
                    if ($arg == "DESC"
                        || $arg == "ASC"
                        ) {
                    return $arg;
                    }
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "site_alarm":
        CASE "device_alarm":
            //  Accepted values: 0 or 1, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && ($arg == status::active
                || $arg == status::inactive)
                ){
                if ($arg == 0) {
                    $arg = "Off";
                    return $arg;
                }
                else if ($arg == 1) {
                    $arg = "On";
                    return $arg;
                }
            }
            else {
                $arg = ucfirst(strtolower($arg));
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    ) {
                    if ($arg == "Off"
                        || $arg == "On"
                        ) {
                    return $arg;
                    }
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "trigger_source":
            //  Accepted values: 0 or 1, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && ($arg == status::active
                || $arg == status::inactive)
                ){
                if ($arg == 0) {
                    $arg = "Geofence";
                    return $arg;
                }
                else if ($arg == 1) {
                    $arg = "Sensor";
                    return $arg;
                }
            }
            else {
                $arg = ucfirst(strtolower($arg));
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    ) {
                    if ($arg == "Geofence"
                        || $arg == "Sensor"
                        ) {
                    return $arg;
                    }
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "reaction":
            //  Accepted values: 0 or 1, without decimal indication
            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && ($arg == status::active
                || $arg == status::inactive)
                ){
                if ($arg == 0) {
                    $arg = "Acknowledge";
                    return $arg;
                }
                else if ($arg == 1) {
                    $arg = "Action";
                    return $arg;
                }
            }
            else {
                $arg = ucfirst(strtolower($arg));
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    ) {
                    if ($arg == "Acknowledge"
                        || $arg == "Action"
                        ) {
                    return $arg;
                    }
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "component_type":
            //  Accepted values: 0 or 1, without decimal indication
            //echo $arg;

            if (is_numeric($arg) == true 
                && preg_match(regex::noDecimal, $arg) === 0
                && $arg >= 0
                ){
                if ($arg == 0) {
                    $arg = "Sensor";
                }
                else if ($arg == 1) {
                    $arg = "Parameter";
                }
                else if ($arg == 2) {
                    $arg = "Group Parameter";
                }
                return $arg;
            }
            else {
                $arg = ucwords(strtolower($arg));
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    ) {
                    if ($arg == "Sensor"
                        || $arg == "Parameter"
                        || $arg == "Group Parameter"
                        ) {
                    return $arg;
                    }
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "sendvia":
        $arg = strtolower($arg);
        if (strlen($arg) <= $strlength 
            && in_array($arg, sendViaArray)
            ) {
            return strtoupper($arg);
        }
        errorInvalid($argtype, $API, $logParent);
        BREAK;
        CASE "trigger_email":
            //  Accepted value: string
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_EMAIL);
            if (strlen($arg) <= $strlength 
                && filter_var($arg, FILTER_VALIDATE_EMAIL)
                ) {
                return $arg;
		    }
		    else {
                errorInvalid($argtype, $API, $logParent);
		    }
            BREAK;
        CASE "asset_type":
            //  Accepted value: strings and string length
            $arg = strtolower($arg);
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringSpaceDashDot,$arg) === 1
                ) {
                $arg = checkValidAssetTypeInput($arg, $API, $logParent);
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "asset_marker_color":
            //  Accepted value: strings and string length
            $arg = strtolower($arg);
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringSpaceDashDot,$arg) === 1
                ) {
                $arg = checkValidColourInput($arg, $API, $logParent);
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "user_agent":
            //  Accepted value: strings and string length
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::userAgent,$arg) === 1
                ) {
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "device_type":
            //  Accepted value: strings and string length
            $arg = strtoupper($arg);
            $arg = str_replace(replaceArray, "", $arg );
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringOneWord,$arg) === 1
                && in_array($arg, assetTypeArray)
                ) {
                return $arg;
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "action":
            $arg = strtolower($arg);
            $arg = str_replace(replaceArray, "", $arg);
            $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::stringOneWord,$arg) === 1
                && in_array($arg, actionTypeArray)
                ) {
                return $arg;
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;    
        CASE "html_colorcode":
        CASE "colors":
        CASE "color":
            //  Accepted value: Colour code in form of 3 or 6 characters 0-9 || A-F. Preceeding # optional
            //  TODO filter sanitisation
            if (preg_match(regex::colourCode, $arg) === 1) {
                if ($arg[0] != '#'){
                    $arg = ('#' . strtoupper($arg));
                }
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
		    }
            BREAK;
        CASE "timestamp_from":
        CASE "timestamp_to":
        CASE "timestamp":
        CASE "date_from":
        CASE "date_to":
        CASE "alarm_datetime":
        CASE "data_reset_datetime":
        CASE "sensor_data_reset_time":
            //  Accepted value: Valid date input from user.
            //  Returns: Valid date
            //  TODO: Further sanitisation
            $arg = checkValidDateTime($arg, $argtype, $API, $logParent);
            return $arg;
            BREAK;
        CASE "points":
            //  Accepted value: String of valid lat / longs, seperated by commas and |
            //  TODO: Sanitisation filter
            $arg = checkValidPointsInput($arg, $API, $logParent);
            return $arg;            
            BREAK;
        CASE "acknowledge_trigger_state_value":
            //  acknowledge_trigger_state array, max 4 cells of binary, any combination of:  00, 01, 10, 11
            if (preg_match(regex::zeroToThree, $arg) === 1) {
                $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                return $arg;
            }
            else {
                $arg = strtolower($arg);
                $arg = str_replace(replaceArray, "", $arg );
                $arg = filter_var($arg, FILTER_SANITIZE_STRING);
                if (strlen($arg) <= $strlength 
                    && preg_match(regex::stringSpaceDashDot,$arg) === 1
                    && in_array($arg, acknowledgeTriggerStateArray)
                    ){
                    return $arg;
                }
            }
            errorInvalid($argtype, $API, $logParent);
            BREAK;
        CASE "math_Equation":
            if (preg_match(regex::validMathEquation, $arg) === 1
                || $arg == null
                ) {
                  return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "equation":
            if (preg_match(regex::validMathEquationInput, $arg) === 1
                || $arg == null
                ) {
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "math_Comparison":
            if (preg_match(regex::validMathComparitor, $arg) === 1) {
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "value_operator":
            if (preg_match(regex::validTriggerComparitor, $arg) === 1) {
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "custom_param_value":
            //  Accepted value: valid international phone number            
            $arg = str_replace(replaceArray, "", $arg );
                 $arg = filter_var($arg, FILTER_SANITIZE_STRING);
            if (strlen($arg) <= $strlength 
                && preg_match(regex::custom_param_value,$arg) === 1
                ) {          
                return $arg;
		    }
		    else {
                errorInvalid($argtype, $API, $logParent);
		    }
            BREAK;
        CASE "whitelist_asset_number":
            if (preg_match(regex::intlTelephone, $arg) === 1
                && strlen($arg) <= $strlength 
                ) {
                if (preg_match(regex::intlTelephoneCallCode, $arg) === 1) {
                    $arg = ltrim($arg, "+");
                    $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                    $arg = "+" . $arg;
                }
                else {
                    $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                }
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        CASE "asset_number":
        CASE "phone_number":
        CASE "trigger_phone":
        CASE "trigger_sms":
        CASE "trigger_fax":
            //  Accepted value: valid international phone number            
            if (preg_match(regex::intlTelephone, $arg) === 1) {
                if (preg_match(regex::intlTelephoneCallCode, $arg) === 1) {
                    $arg = ltrim($arg, "+");
                    $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                    $arg = "+" . $arg;
                }
                else {
                    $arg = filter_var(intval($arg), FILTER_SANITIZE_NUMBER_INT);
                }
                return $arg;
            }
            else {
                errorInvalid($argtype, $API, $logParent);
            }
            BREAK;
        
        DEFAULT :
            errorInvalid("unknown_sanitise", "SANITISE_ARRAY", $logParent);
            BREAK;
    }
}

// *******************************************************************************
// *******************************************************************************
// **********************PHP FUNCTIONS FOR VERSION <8.0***************************
// *******************************************************************************
// *******************************************************************************

//  str_contains is introduced in php v8.0. At time of writing, server runs on v7.0. 
//  This is a workaround function in the meantime
//  Source: https://stackoverflow.com/questions/66519169/call-to-undefined-function-str-contains-php

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
// *******************************************************************************
// *******************************************************************************
// **************************OTHER FUNCTIONS**************************************
// *******************************************************************************
// ******************************************************************************* 

//  TODO fold this into normal sanitise file
//  Function to check valid asset type is input
function checkValidAssetTypeInput($arg, $API, $logParent) {
    if (in_array($arg, assetTypeArray)){
        return $arg;
    }
    else { 
        errorInvalid("asset_type", $API, $logParent);
    }
}

//  TODO fold this into normal sanitise file
//  Function to check valid colour type is input
//	Colours taken from table "Basic colors" @ https://en.wikipedia.org/wiki/Web_colors
function checkValidColourInput($arg, $API, $logParent) {
    if (in_array($arg, colourTypeArray)){
        $arg = ucwords($arg);
        return $arg;
    }
    else { 
        errorInvalid("asset_marker_colour", $API, $logParent);
    }
}

function checkValidDateTime ($arg, $argtype, $API, $logParent) {
    //  Function to check if user input date time is valid. 
    //  Adds time values if the user has not or partially input time. Defaults to 00:00:00
    //  User input: yyyy-mm-dd hh:mm:ss
    //  24hr time. 
    if (str_contains ($arg, " ") !== true) {                                    //  Adds a space to the end of the string if the user did not add a time to the date. Loop breaks without a space
        $arg = $arg . " ";
    }
    $arg = preg_replace ("!\s+!", "~", $arg);                                   //  Replace all whitespace with "~". Necessary for sanitisation
    $arg = filter_var($arg, FILTER_SANITIZE_URL);                               //  Sanitise input
    $dateTimeArray = explode("~", $arg);                                        //  Explode the string into two array values around the "~". array[0] == date, array [1] == time
    $dateTimeArray[0] = str_replace(["/",",","."], "-", $dateTimeArray[0] );    //  Replace common date seperator characters with the expected "-"
    if (preg_match(regex::dateFormat, $dateTimeArray[0]) === 1) {               //  Switch through months, dies on incorrect amount of days per month
        $dateArray = explode("-", $dateTimeArray[0]);
        switch ($dateArray[1]) {
            CASE "04":
            CASE "06":
            CASE "09":
            CASE "11":
                if ($dateArray[2] > 30) {
                    errorInvalid($argtype, $API, $logParent);               
                }
            BREAK;
            CASE "01":
            CASE "03":
            CASE "05":
            CASE "07":
            CASE "08":
            CASE "10":
            CASE "12":
                if ($dateArray[2] > 31) {
                    errorInvalid($argtype, $API, $logParent);
                }
            BREAK;
            CASE "02":
                //  Case for leap year
                if ($dateArray[0] % 4 == 0) {
                    if ($dateArray[2] > 29) {
                        errorInvalid($argtype, $API, $logParent);               
                    }    
                }
                else {
                    if ($dateArray[2] > 28) {
                        errorInvalid($argtype, $API, $logParent);
                    }
                }
            BREAK;
            DEFAULT: 
            errorInvalid($argtype, $API, $logParent);    
        }
    }
    else {
        errorInvalid($argtype, $API, $logParent);
    }

    if ($dateTimeArray[1] == null) {                                    //  Case where user has not input time
        $dateTimeArray[1] = '00:00:00';
    }
    
    if (preg_match(regex::timeFormat, $dateTimeArray[1]) === 1) {       
        if (strlen($dateTimeArray[1]) == 2) {                           //  Case where user has only input hours
            $dateTimeArray[1] = $dateTimeArray[1] . ":00:00";
        }
        else if (strlen($dateTimeArray[1]) == 5) {                      //  Case where user has only input hours and minutes
            $dateTimeArray[1] = $dateTimeArray[1] . ":00";
        }
    }
    else {
        errorInvalid($argtype, $API, $logParent);
    }

    $fixedString = $dateTimeArray[0] . " " . $dateTimeArray[1];         //  Re-connect the date and time strings
    return $fixedString;
}      

function checkValidPointsInput($arg, $API, $logParent) {
    $latLongArray = explode("|", $arg);
    $length = count($latLongArray);
    $returnString = "";
    $numPoints = 0;
    foreach ($latLongArray as $value) {
        //  Each lat/long pair must have EXACTLY one comma "," or fail.
        if (substr_count($value, ",") == 1) {
            $latLongArray2 = explode(",", $value);
            $latLongArray2[0] = sanitise_input($latLongArray2[0], "lat", null, $API, $logParent);
            $latLongArray2[1] = sanitise_input($latLongArray2[1], "long", null, $API, $logParent);
            $numPoints++; 
            $returnString .= $latLongArray2[0] . "," . $latLongArray2[1] . "|";
        } else {
            errorInvalid("points", $API, $logParent);
        }
    }

    //  Catch error cases where number of lats / longs differs. Shouldn't occur, but catch case for it regardless.
    //  Error on length == 2. Can have length == 1 (with a radius) or length > 2 (area). Length == 2 is just a line, which is not allowed. 
    if ($length !== $numPoints 
        || $length == 2) {
            errorInvalid("points", $API, $logParent);
        }
    else {
        $returnString = substr($returnString, 0, -1);
        return $returnString;
    }
}

//  A function called by most APIs. This queries the server for the maximum character length for each column and returns it in an array. 
//  Note do not use this to get max character length if the expected type != STRING. 
function getMaxString ($arg, $pdo) {
    $sql = "SELECT 
        COLUMN_NAME
        , CHARACTER_MAXIMUM_LENGTH
        FROM information_schema.columns
        WHERE table_schema = DATABASE() 
        AND table_name = '";
    $sql .= $arg . "'";
    $stm = $pdo->query($sql);
    $dbinformation_schema = $stm->fetchAll(PDO::FETCH_NUM);
    $schemainfoArray = [];
    foreach($dbinformation_schema as $dbrows){
        if (isset($dbrows[0][0])) {
            $schemainfoArray[$dbrows[0]] = $dbrows[1];
        }
    }
    
    return $schemainfoArray;
}

//  A function that checks that the users input keyes are the expected keys for that API
//  Checks against the API keys array in the 'keys.php' file.

function checkKeys ($arg, $API, $logParent) {
    if (is_array($arg) || is_object($arg)){ 
        foreach ($arg as $key => $value) {
            if (!in_array($key, mainArray[$API])){
                $key = strtoupper($key) ;
                global $token;
                logEvent($API . logText::invalidKey, logLevel::invalid, logType::error, $token, $logParent);
                die ( "{\"error\":\"INVALID_INPUT_PRAM\", \"error_detail\": \"INVALID_PRAM_'$key'\"}");
            }
        }
    }

}
 

?>