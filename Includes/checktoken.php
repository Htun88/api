<?php
	//	TODO Change this to a reasonable value
	$expiryminutes= 3600;
	$expirytime = new DateTime();
	$expirytime->setTimezone(new DateTimezone("gmt"));
	$expirytime->sub(new DateInterval('PT' . $expiryminutes . 'M'));
	$expirytime = $expirytime->format("Y/m/d H:i:s");
	$stm = $pdo->exec("DELETE FROM sessions WHERE sessions_timestamp < '$expirytime'");
	//$headers = apache_request_headers(); 
  	$token = "unset";
	//foreach ($headers as $header => $value) { 
	   // if ($header=="token"){
	    	//$token = $value;
			//echo "token";
			//echo $token;
	    	//$tokenHash = 
	    //}
	//} 
	
	if(isset($_SERVER['HTTP_TOKEN'])){
		$token = $_SERVER['HTTP_TOKEN'];
	}


	if ($token == "unset"){
		header('Content-Type: application/json');
		die("{\"error\":\"INVALID_TOKEN\"}");
	}
	//$stm = $pdo->query("SELECT users.user_id, users.user_roles, sessions.sessions_token FROM sessions, users WHERE sessions.users_user_id = users.user_id and sessions_token='$token' and users.active_status = 0");
	$stm = $pdo->query("SELECT users.user_id, users.user_roles, sessions.sessions_token, sessions.sessions_id FROM sessions, users WHERE sessions.users_user_id = users.user_id and users.active_status = 0 order by sessions_timestamp DESC");
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	$user_id = -1;
	if (isset($rows[0])) {
		foreach($rows as $row){
			//echo "$row[2]   <br>";
			if (password_verify($token,$row[2])){
				$user_id = $row[0];
				$user_roles = $row[1];
				$sessions_id = $row[3];
				$timestamp = gmdate("Y-m-d H:i:s");
				define("USER_ID", intval($user_id));
				define("USER_ROLE", intval($user_roles));
				define("TOKEN", $token);
				define("TIMESTAMP", $timestamp);
				//echo "sessions_id   $sessions_id   <br>";
				
				//$stm = $pdo->exec("Update sessions SET sessions_timestamp = '$timestamp' WHERE sessions.users_user_id = $user_id and sessions_token='$tokenHash'");
				$stm = $pdo->exec("Update sessions SET sessions_timestamp = '$timestamp' WHERE sessions.users_user_id = $user_id and sessions.sessions_id = $sessions_id");
				break;
			}
		}
		if ($user_id == -1){
			header('Content-Type: application/json');
			die("{\"error\":\"INVALID_TOKEN\"}");
		}
	}

	
	else{
		header('Content-Type: application/json');
		die("{\"error\":\"INVALID_TOKEN\"}");
	}
	$rows = null;
?>