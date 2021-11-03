<?php
	function getClientIP(){       
		 if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
				return  $_SERVER["HTTP_X_FORWARDED_FOR"];  
		 }else if (array_key_exists('REMOTE_ADDR', $_SERVER)) { 
				return $_SERVER["REMOTE_ADDR"]; 
		 }else if (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
				return $_SERVER["HTTP_CLIENT_IP"]; 
		 } 

		 return null;
	}
	
	function logEvent($event, $level, $type, $token, $logParent){
		global $pdo;
		global $timestamp;
		global $user_id;
		$directConnect = 1;
		$eventLevel = 4;
		if ($level <= $eventLevel){
			if (strlen($event) >= 4050 ) {
				//	Explode the event data around the "-" to retrieve the $API and request type
				$API = explode("-", $event );
				$event = $API[0] . "-" . $API[1] . logText::responseLarge . strlen($event) . " Characters";
			}
			if ($directConnect == 1 ) {
				try {
					$insertArray['application'] = $_SERVER['HTTP_HOST'];
		
					$insertArray['event_data'] = $event;
					$insertArray['event_type'] = $type;
					$insertArray['ip_address'] = getClientIP();
					$insertArray['event_level'] = $level;
					$insertArray['event_parent'] = $logParent;
					$sql = "INSERT INTO events(
						`datetime`
						, `user_user_id`
						, `application`
						, `event_data`
						, `event_type`
						, `ip_address`
						, `event_level`
						, `event_parent`) 
					VALUES (
						'$timestamp'
						, $user_id
						, :application
						, :event_data
						, :event_type
						, :ip_address
						, :event_level
						, :event_parent)";

					$stm= $pdo->prepare($sql);
					if($stm->execute($insertArray)){
						$insertArray['event_id'] = $pdo->lastInsertId();
						$insertArray ['error' ] = "NO_ERROR";
					}
				}
				catch (PDOException $e){
					die("{\"error\":\"$e\"}");
				}
				$insertArray['action'] = "insert";
				return $insertArray;

			}
			else {
				
				$apiHost = "https://" .  $_SERVER['HTTP_HOST'];
				$apiVer =  "v1";
				//	$apiEventData = "{\"action\" : \"insert\",\"application\" : \"" . $_SERVER['HTTP_HOST'] . "\",\"event_data\" : \"" . $event . "\",\"parent\" : \"" . $parent . "\"}";
				$apiEventData['action'] = "insert";
				$apiEventData['application'] = $_SERVER['HTTP_HOST'];
				$apiEventData['event_data'] = $event;
				$apiEventData['event_type'] = $type;
				$apiEventData['event_level'] = $level;
				if (isset($logParent)) {
					$apiEventData['event_parent'] = $logParent;
				}
				//echo "$apiEventData";
				return apiCall(json_encode($apiEventData), $apiHost, $apiVer, "Event", $token);
			}
		}
		else {
			return array("error" => "EVENT_LEVEL");
		}
		
	}
	
	
	function apiCall($Data, $apiHost, $apiVer, $apiEndpoint, $token) {
		$context_options = array(
			'http' => array(
				'header'  => "Content-type: application/json\r\ntoken: $token",
				'method'  => 'POST',
				'content' => $Data
			)
		);
		$context = stream_context_create($context_options);
		$fp = fopen("$apiHost/$apiVer/$apiEndpoint/", 'r', false, $context);
		$buffer = '';
		if ($fp) {
			while (!feof($fp)) {
			  $buffer .= fgets($fp, 5000);
			}
			fclose($fp);
		}
		
		return json_decode($buffer, true);
	}

	/**
	 * Loop through two arrays error out any values that do not appear in both arrays
	 * 
	 * output: die( {error: INVALID_ strtoupper($arg) _PRAM} )
	 * @param array $arg_1 - The haystack array (input array)
	 * @param array $arg_2 - The needle array (returned from database)
	 * @param string $arg_3 - String of the $API parameter being searched
	 * 
	 */
	function arrayExistCheck ($arg_1, $arg_2, $arg_3, $API, $logParent) {
		//	Case where less values returned than sent, ie. at least one value is !trigger_id
		$arg_1 = array_unique($arg_1);
		$dif = count($arg_1) - count($arg_2);
		if ($dif != 0) {
			//	If so, return unique values between the arrays. 
			foreach ($arg_1 as $arg_1_value) {
				if (!in_array($arg_1_value, $arg_2)) {
					$errorArray[] = $arg_1_value;
					$dif --;
				}
				if ($dif == 0) {
					BREAK;
				}			
			}
			$output = json_encode(
				array(
				'error' => "INVALID_" . strtoupper($arg_3) . "_PRAM",
				'error_detail' => $errorArray
				));
			global $token;
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}
	}

	/**
	 * Loop through two arrays and error out any values that do appear in both arrays
	 * Opposite of arrayExistCheck
	 * 
	 * output: die( {error: INVALID_ strtoupper($arg) _PRAM} )
	 * @param array $arg_1 - The haystack array (input array)
	 * @param array $arg_2 - The needle array (returned from database)
	 * @param string $arg_3 - String of the $API parameter being searched
	 * 
	 */
	function arrayDoesntExistCheck ($arg_1, $arg_2, $arg_3, $API, $logParent) {
		$arg_1 = array_unique($arg_1);
		$count = array_intersect($arg_2, $arg_1);
		if (count($count) != 0) {
			$output = json_encode(
				array(
				'error' => "INVALID_" . strtoupper($arg_3) . "_PRAM",
				'error_detail' => $count
				));
			global $token;
			logEvent($API . logText::genericError . str_replace('"', '\"', $output), logLevel::responseError, logType::responseError, $token, $logParent);
			die ($output);
		}
	}

?> 