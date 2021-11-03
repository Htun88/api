<?php 
    header('Content-Type: application/json');
	include '../Includes/db.php';
	include '../Includes/checktoken.php';

    $entitybody = file_get_contents('php://input');
    $inputarray = json_decode($entitybody, true);
    
	$action = "select";
    if (isset($inputarray['action'])){
         $action = $inputarray['action'];
    }
    if (isset($inputarray['device_id'])){
        $device_id = $inputarray['device_id'];
    }
    else{
        die ("{\"error\":\"INVALID_DEVICE_ID\"}");
    }
    
        $stm = $pdo->query("SELECT 
        device_custom_param_group_id as group_id, 
        device_custom_param_group.name as group_name,
        device_custom_param.id as param_id,  
        device_custom_param.name as param_name,
        device_custom_param.tag_name as param_tag,
        device_custom_param_values.value 
        FROM (devices, device_provisioning, device_provisioning_components, device_custom_param_group)        
        left join device_custom_param_values on device_custom_param_values.device_custom_param_group_id = device_custom_param_group.id 
        and
        device_custom_param_values.devices_device_id = devices.device_id
        left join device_custom_param on device_custom_param_values.device_custom_param_id = device_custom_param.id       
        where 
        devices.device_id = $device_id
        and devices.device_provisioning_device_provisioning_id = device_provisioning.id 
        and device_provisioning_components.device_provisioning_device_provisioning_id = device_provisioning.id
        and device_provisioning_components.device_component_type = 'Group Parameter'
        and device_custom_param_group.id = device_provisioning_components.device_component_id

        union

        SELECT 
        Null as group_id, Null as group_name, 
        device_custom_param.id as param_id, 
        device_custom_param.name as param_name, 
        device_custom_param.tag_name as param_tag,
        device_custom_param_values.value 
        FROM (devices, device_provisioning, device_provisioning_components, device_custom_param)
        left join device_custom_param_values on device_custom_param_values.device_custom_param_id = device_provisioning_components.device_component_id 
        and device_custom_param_values.devices_device_id = devices.device_id
        where 
        devices.device_id = $device_id
        and devices.device_provisioning_device_provisioning_id = device_provisioning.id 
        and device_provisioning_components.device_provisioning_device_provisioning_id = device_provisioning.id
        and device_provisioning_components.device_component_type = 'Parameter'
        and device_custom_param.id = device_provisioning_components.device_component_id
        ");
        $dbrows = $stm->fetchAll(PDO::FETCH_NUM);
        if (isset($dbrows[0][0])){
            $json_parent = array();
            $outputid = 0;
            foreach($dbrows as $dbrow){
                $json_child = array(
                "group_id"=>$dbrow[0],
                "group_name"=>$dbrow[1],
                "param_id"=>$dbrow[2],
                "param_name"=>$dbrow[3],
                "param_tag"=>$dbrow[4],
                "value"=>$dbrow[5]
                );

                $json_parent = array_merge($json_parent,array("response_$outputid" => $json_child));
                $outputid++;
           }
            $json = array("responses" => $json_parent);
            echo json_encode($json);
        }
        else{
            die("{\"error\":\"NO_DATA\"}");
        }

    $pdo = null;
	$stm = null;

?>