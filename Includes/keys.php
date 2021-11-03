<?php
    // Keys
    //  A supplimentary file for the sanitise function. Contains arrays of valid keys for each API. 
    //  Each user input key is checked to ensure they are accurately inputting the key, and erroring out if incorrect

    /*

//*******************************************************
//**********************EXAMPLE**************************
//*******************************************************
,"exampleAPINAME" => [ 
    "action"
    , "Value_1"
    , "Value_2"
    , "Value_3"
    , "Value_4"
    , "Value_5"
    , "Value_6"
    , "Value_7"
    , "Value_8"
    , "Value_9"
    ]

*/


CONST mainArray = array(
//*******************************************************/
//**********************Message**************************/
//*******************************************************/
    "Message" => [ 
        "action"
		, "method"
        , "message_id"
		, "type"
		, "acknowledged"
        , "message_number"
		, "timestamp"
		, "timestamp_from"
		, "timestamp_to"
        , "last_received_id"
		, "number_index"
		, "iconpath"
		, "asset_id"
		, "device_id"
        , "limit"
        ]

//*******************************************************
//**********************MESSAGECODE*******************
//*******************************************************
, "MessageCode" => [ 
    "action"
    , "id"
    , "limit"
    ]


//*******************************************************
//**********************SYSLOGMESSAGE*******************
//*******************************************************
, "SyslogMessage" => [ 
    "action"
    , "id"
    , "message_id"
    , "limit"
    , "message_description"
    ]


//*******************************************************
//**********************SYSLOG**************************
//*******************************************************
, "Syslog" => [ 
    "action"
    , "syslog_id"
    , "priority"
    , "message_id"
    , "timestamp"
    , "device_id"
    , "timestamp_to"
    , "limit"
    , "timestamp_from"
    ]

//*******************************************************/
//**********************EVENT****************************/
//*******************************************************/
    , "Event" => [ 
        "action"
        , "event_id"
        , "timestamp_to"
        , "timestamp_from"
        , "ip_address"
        , "user_id"
        , "application"
        , "event_data"
        , "event_parent"
        , "limit"
        , "event_type"
        , "event_level"
		, "event_data"
        ]

//*******************************************************/
//**********************EventApplication*****************/
//*******************************************************/
    , "EventApplication" => [ 
        "action"
        , "timestamp_to"
        , "timestamp_from"
        ]
		
		
//*******************************************************/
//**********************GEOFENCE*************************/
//*******************************************************/
    , "Geofence" => [ 
        "action"
        , "active_status"
        , "asset_id"
        , "color"
        , "device_id"
        , "deviceasset_id"
        , "geofence_id"
        , "name"
        , "points"
        , "radius"
        , "limit"
        , "safe_zone"
        ]


//*******************************************************/
//**********************ALARM****************************/
//*******************************************************/
    , "Alarm" => [ 
        "action"
        , "acknowledge_state"
        , "acknowledge_trigger_state"
        , "active_status"
        , "alarm_id"  
        , "alarm_datetime"     
        , "asset_id"
        , "data_id"
        , "device_id"
        , "data_reset_id"
        , "data_reset_datetime"
        , "timestamp_from"
        , "timestamp_to"
        , "limit"
        , "trigger_id"
        , "trigger_state"
        , "user_agent"
        ]

//*******************************************************/
//**********************ALARMEVENTS****************************/
//*******************************************************/
, "AlarmEvents" => [ 
    "action"
    , "alarm_events_id"
    , "datetime"
    , "data_id"
    , "asset_id"
    , "device_id"
	, "deviceasset_id"
    , "timestamp_from"
    , "timestamp_to"
    , "trigger_id"
    , "action_id"
    , "limit"
    , "user_agent"
    ]   


//*******************************************************/
//**********************ASSETALARM****************************/
//*******************************************************/
, "AssetAlarm" => [ 
    "action"
    , "sd_id"
    , "asset_id"
    , "device_id"
    , "trigger_id"
    , "action_id"
	, "limit"
    ]   

//*******************************************************/
//**********************ALARMACTIONS****************************/
//*******************************************************/
, "AlarmActions" => [ 
    "action"
    , "action_id"
    , "alarm_action"
    , "active_status"
    , "limit"
    ]   

//*******************************************************
//**********************ACCESSPOINT**********************
//*******************************************************
    , "AccessPoint" => [ 
        "action"
        , "active_status"
        , "alt"
        , "ap_id"
        , "mac_address"
        , "type"
        , "description"
        , "lat"
        , "long"
        , "type"
        , "limit"
        ]    

//*******************************************************
//**********************ASSET****************************
//*******************************************************
    , "Asset" => [ 
        "action"
        , "active_status"
        , "asset_id"
        , "asset_marker_color"
        , "asset_marker_gif"
        , "asset_marker_gray"
        , "asset_marker_image"
        , "asset_name"
        , "asset_type"
        , "html_colorcode"
        , "trigger_id"
        , "user_id"
        , "limit"
        ]
    
//*******************************************************
//**********************DEVICES**************************
//*******************************************************
    ,"Device" => [ 
        "action"
        ,"device_id"
        , "device_name"
        , "device_sn"
        , "license_id"
        , "license_hash"
        , "provisioning_id"
        , "geofences_version"
        , "desired_version"
        , "desired_stored_versions"
        , "active_status"
        , "limit"
        ]


//*******************************************************
//**********************DEVICECUSTOMPARAMETER**************************
//*******************************************************
,"DeviceCustomParameter" => [ 
    "action"
    ,"id"
    ,"name"
    ,"tag_name"
    ,"default_value"
    , "limit"
    ]


//*******************************************************
//**********************DEVICECUSTOMPARAMETERGROUP**************************
//*******************************************************
,"DeviceCustomParameterGroup" => [ 
    "action"
    ,"id"
    ,"name"
    ,"tag_name"
    ,"limit"
    ]

//*******************************************************
//**********************DEVICECUSTOMPARAMETERGROUPCOMPONENTS**************************
//*******************************************************
,"DeviceCustomParameterGroupComponents" => [ 
    "action"
    , "id"
    ,"group_id"
    ,"param_id"
    ,"active_status"
    , "limit"
    ] 

//*******************************************************
//**********************DEVICECONNECTOR**************************
//*******************************************************
,"DeviceConnector" => [ 
    "action"
    ,"device_id"
    , "limit"
    ]


//*******************************************************
//**********************MODULEDEFINITION**************************
//*******************************************************
,"ParamDefinition" => [ 
    "action"
    ,"id"
    , "param_name"
    , "module_id"
    ]   

 
//*******************************************************
//**********************MODULEDEFINITION**************************
//*******************************************************
,"ModuleDefinition" => [ 
    "action"
    ,"id"
    , "module_name"
    ]   

//*******************************************************
//**********************CALIBRATIONDEFINITION**************************
//*******************************************************
,"CalibrationDefinition" => [ 
        "action"
        ,"id"
        ,"name"
        , "limit"
    ]
    
//*******************************************************
//**********************DEVICEASSET**********************
//*******************************************************
    ,"DeviceAsset" => [ 
        "action"
        , "active_status"
        , "asset_id"
        , "asset_task"
        , "date_from"
        , "deviceasset_id"
        , "device_id"
        , "test"
        , "linked"
        , "limit"
        , "trigger_id"
		, "provisioning_id"
        ]

//*******************************************************
//**********************DEVICEASSETSCHART****************
//*******************************************************
    ,"DeviceAssetsChart" => [ 
        "action"
        , "asset_id"
        , "active_status"
        , "deviceasset_id"
        , "device_id"
        , "gauge"
        , "sd_id"
        , "trend"
        , "uom_show_id"
        , "limit"
        , "chart_options"
        ]
        
//*******************************************************
//**********************SENSORDATA***********************
//*******************************************************
    ,"SensorData" => [ 
        "action"
        , "asset_id"
        , "conn_properties"
        , "data_id"
        , "device_id"
        , "filter"
        , "firmware_status"
        , "limit"
        , "order"
        , "packet_type"
        , "sd_id"
        , "sensor_id"
        , "sensor_data"
        , "sensor_value"
        , "timestamp"
        , "timestamp_from"
        , "timestamp_to"
        , "trigger_id"
        , "user_agent"
        ]    
//*******************************************************
//**********************LEVEL****************************
//*******************************************************
    ,"Level" => [ 
        "action"
        , "level_id"
        , "alt_from"
        , "alt_to"
        , "level_name"
        , "limit"
        ]
    
//*******************************************************
//**********************TRIGGER**************************
//*******************************************************
    ,"Trigger" => [ 
        "action"
        , "asset_id"
        , "trigger_id"
        , "deviceasset_id"
        , "device_sn"
        , "trigger_name"
        , "trigger_type"
        , "trigger_level"
        , "trigger_source"
        , "sd_id"
        , "span"
        , "geofence_id"
        , "value_operator"
        , "trigger_value"
        , "duration"
        , "device_id"
        , "device_alarm"
        , "site_alarm"
        , "trigger_email"
        , "trigger_sms"
        , "trigger_phone"
        , "trigger_fax"
        , "suggested_actions"
        , "reaction"
        , "active_status"
        , "limit"
        ]


//*******************************************************
//**********************ASSETCUSTOMPARAMETERVALUES*****************
//*******************************************************

,"AssetCustomParameterValues" => [ 
    "action"
    , "id"
    , "asset_id"
    , "value"
    , "custom_param_id"
    , "tag_name"
    , "limit"
    ]
       

//*******************************************************
//**********************ASSETCUSTOMPARAM*****************
//*******************************************************

,"AssetCustomParam" => [ 
    "action"
    , "name"
    , "tag_name"
    , "active_status"
    , "limit"
    ]


//*******************************************************
//**********************ASSETCUSTOMPARAMGROUP************
//*******************************************************

,"AssetCustomParameterGroup" => [ 
    "action"
    , "id"
    , "name"
    , "tag_name"
    , "limit"
    ]


//*******************************************************
//**********************ASSETCUSTOMPARAMGROUPCOMPONENTS************
//*******************************************************

,"AssetCustomParameterGroupComponents" => [ 
    "action"
    , "id"
    , "group_id"
    , "param_id"
    , "active_status"
    , "limit"
    ]
//*******************************************************
//**********************ASSETWHITELIST*******************
//*******************************************************
, "AssetWhitelist" => [ 
    "action"
    , "whitelist_id"
    , "active_status"
    , "asset_id"
    , "asset_number"
    , "limit"
    ]

//*******************************************************
//**********************DEVICEPROVISIONINGLINK***********
//*******************************************************
, "DeviceProvisioningLink" => [ 
    "action"
    , "active_status"
    , "limit"
    , "module_id"
    , "module_name"
    , "module_value"
    , "param_id"
    , "param_name"
    , "param_value"
    , "provisioning_id"
    , "provisioning_link_id"
    , "sd_id"
    , "values"
    ]

//************************************************************
//**********************DEVICEPROVISIONING***********
//************************************************************
, "DeviceProvisioning" => [ 
    "action"
    , "id"
    , "name"
    , "active_status"
    ]

//************************************************************
//**********************DEVICEPROVISIONINGCOMPONENTS***********
//************************************************************
, "DeviceProvisioningComponents" => [ 
    "action"
    , "provisioning_component_id"
    , "provisioning_id"
    , "component_id"
    , "component_type"
    , "active_status"
    , "limit"
    ]

//*******************************************************
//**********************DEVICECUSTOMPARAMETERVALUES******
//*******************************************************
, "DeviceCustomParameterValues" => [ 
    "action"
    , "id"
    , "device_id"
    , "asset_id"
    , "deviceasset_id"
    , "param_id"
    , "group_id"
    , "value"
    , "tag_name"
    , "group_id"
    , "limit"
    , "param_values"
    ]


//*******************************************************
//*****************DeviceAssetsTriggerDetail*************
//*******************************************************
, "DeviceAssetsTriggerDetail" => [ 
    "action"
    , "deviceassets_det"
    , "device_id"
    , "asset_id"
    , "deviceasset_id"
    , "trigger_id"
    , "limit"

    ]

//*******************************************************
//**********************ASSETCUSTOMPARAMETER*************
//*******************************************************
, "AssetCustomParameter" => [ 
    "action"
    , "active_status"
    , "default_value"
    , "id"
    , "name"
    , "tag_name"
    , "limit"
    ]

//*******************************************************
//**********************XBEE*****************************
//*******************************************************
, "Xbee" => [ 
    "action"
    , "id"
    , "mac_address"
    , "xbee_id"
    , "limit"
    ]

//*******************************************************
//**********************SENSORDATAID*********************
//*******************************************************
, "SensorDataID" => [ 
    "action"
    , "asset_id"
    , "data_id"
    , "timestamp_from"
    , "timestamp_to"    
    , "limit"
    ]
//*******************************************************
//**********************FIRMWARE*************************
//*******************************************************
, "Firmware" => [ 
    "action"
    , "device_provisioning_id"
    , "limit"
    ]

//*******************************************************
//**********************SENSOR***************************
//*******************************************************
, "Sensor" => [ 
    "action"
    , "sensor_id"
    , "active_status"
    , "sensor_name"
    , "limit"
    ]

//*******************************************************
//**********************SENSORDETAILS********************
//*******************************************************
, "SensorDetails" => [ 
    "action"
    , "sensor_id"
    , "active_status"
    , "sensor_det_id"
    , "sensor_name"
    , "sd_id"
    , "limit"
    ]

//*******************************************************
//**********************SENSORDEFINITION*****************
//*******************************************************
, "SensorDefinition" => [ 
    "action"
    , "asset_id"
    , "device_id"
    , "device_sn"
    , "sd_id"
    , "name"
    , "html_colorcode"
    , "iconpath"
    , "chartlabel"
    , "deactivated_data"
    , "data_min"
    , "data_max"
    , "graph_min"
    , "graph_max"
    , "uom_id"
    , "chart"
    , "bytelength"
    , "active_status"
    , "limit"
    ]

//*******************************************************
//**********************DEVICEASSETSGEOFENCEDETAIL*******
//*******************************************************
, "DeviceAssetsGeofenceDetail" => [ 
    "action"
    , "asset_id"
    , "device_id"
    , "deviceasset_id"
    , "geofence_id"
    , "deviceassets_det"
    ]


//*******************************************************
//**********************UNITOFMEASURECONVERSION*****************
//*******************************************************
, "UnitOfMeasureConversion" => [ 
    "action"
    , "id"
    , "uom_id_from"
    , "equation"
    , "uom_id_to"
    ]


//*******************************************************
//**********************UNITOFMEASURE*****************
//*******************************************************
, "UnitOfMeasure" => [ 
    "action"
    , "id"
    , "unit"
    , "chartlabel"
    ]


//*******************************************************
//**********************USERASSETDETAILS*****************
//*******************************************************
, "UserAssetDetails" => [ 
    "action"
    , "user_id"
    , "asset_id"
    ]


  

);
