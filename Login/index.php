<?php
	header('Content-Type: application/json');
	//Login
	include '../Includes/db.php';
	$tokenlen = 171;
	//$tokenlen = 10;
	global $pdo;
	//echo "login";
	function gentoken($n) { 
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/'; 
	    $randomString = ''; 
	    for ($i = 0; $i < $n; $i++) { 
	        $index = rand(0, strlen($characters) - 1); 
	        $randomString .= $characters[$index]; 
	    } 
	    return $randomString; 
	} 
	
	function comptoken($newtoken,$currenttokenarray){
		if (isset($currenttokenarray[0])) {
			foreach($currenttokenarray as $currenttoken){
				if ($newtoken == $currenttoken){
					return 1;
				}
				
			}
		}
		return 0;
	}

	$password = "";
	$username =	"";
	$errmsg = "NO_ERROR";
	$statusmsg = "FAILURE";
	$token ="ERROR";
	$contenttype = "";
	if (isset($_SERVER["CONTENT_TYPE"])){
		$contenttype = $_SERVER["CONTENT_TYPE"];
		if ($_SERVER["CONTENT_TYPE"] != "application/x-www-form-urlencoded"){
			$errmsg = "INVALID_CONTENT_TYPE_HEADER";
		}
	} else {
		$errmsg = "MISSING_CONTENT_TYPE_HEADER";
	}
	
	
	if (!isset($_POST['token'])){
			
		if (isset($_POST['username'])){
			$username = $_POST['username'];
			//echo $username;
			if (strlen(trim($username, " ")) != strlen($username)){
				$username = "";
			}
		}
		
		
		if (isset($_POST['password'])){
			$password = $_POST['password'];
		}
		
		
		$stm = $pdo->prepare('SELECT 
	users.user_id, 
	users.user_name, 
	users.user_login_id, 
	users.user_roles, 
	users.user_password, 
	user_roles.userrole_name 
	FROM `users`, 
	`user_roles` 
	WHERE 
	users.user_roles = user_roles.userrole_def_id 
	and users.active_status =0 
	AND users.user_login_id = :username');
		$stm->bindParam(':username', $username);
		$stm->execute();
		
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
			$dbuser_id = "";
			$dbuser_role_name = "";
		
		if (isset($rows[0])) {
			$dbuser_id = $rows[0][0];
			
			$dbuser_name = $rows[0][1];
			$dbuser_login_id = $rows[0][2];
			$dbuser_role = $rows[0][3];
			$dbuser_role_name = $rows[0][5];
			//echo $dbuser_role;
			$dbuser_password = $rows[0][4];
			if (password_verify($password, $dbuser_password)) {
				$statusmsg = "SUCCESS";
				
				$stm = $pdo->query("SELECT * FROM information_schema.tables WHERE table_schema = '$databasename' AND table_name = 'sessions' LIMIT 1");
				$rows = $stm->fetchAll(PDO::FETCH_NUM);
				if (isset($rows[0])) {

				}
				else{
					$stm = $pdo->exec("CREATE TABLE `sessions` (
	  `sessions_id` int(11) NOT NULL AUTO_INCREMENT,
	  `users_user_id` int(11) DEFAULT NULL,
	  `sessions_token` varchar(256) DEFAULT NULL,
	  `sessions_timestamp` datetime DEFAULT NULL,
	  PRIMARY KEY (`sessions_id`)
	) ENGINE=InnoDB AUTO_INCREMENT=4031 DEFAULT CHARSET=utf8");
				}
				
				$stm = $pdo->query("SELECT sessions_token FROM sessions");
				$rows = $stm->fetchAll(PDO::FETCH_NUM);
				
				//$token = gentoken(171);
				//while (comptoken($token,$rows)){
					//$token = gentoken(171);
				//}
			   
				//$stm = $pdo->query("SELECT userrole_name FROM `user_roles` WHERE `userrole_def_id`= '" . $dbuser_role . "'");
				//$stm->bindParam(':userrole', $dbuser_role);
				//$row = $stm->fetchAll(PDO::FETCH_NUM);

				//$user_role = $row[0][0];

				$options = ['cost' => 5];
				$token = gentoken($tokenlen);
				
				$tokenHash = password_hash($token, PASSWORD_DEFAULT, $options);
				while (comptoken($tokenHash,$rows)){
					$token = gentoken($tokenlen);
					$tokenHash = password_hash($token, PASSWORD_DEFAULT, $options);
				}

				//$stm = $pdo->exec("DELETE FROM sessions WHERE users_user_id = '$dbuser_id'"); // this line will only allow one session per user. Remove if multi-session is required.
				$timestamp = date("Y-m-d H:i:s");
				//$stm = $pdo->exec("INSERT INTO sessions (users_user_id, sessions_token, sessions_timestamp) VALUES ($dbuser_id, '$token','$timestamp')");
				$stm = $pdo->exec("INSERT INTO sessions (users_user_id, sessions_token, sessions_timestamp) VALUES ($dbuser_id, '$tokenHash','$timestamp')");
			}
			else {
				$errmsg = "ACCOUNT_PASSWORD_INCORRECT";
			}
		}
		else {
			$errmsg = "ACCOUNT_DOES_NOT_EXIST";
		}
		
		$conn = null;

		$json = array("token"=>$token, "userid" => $dbuser_id, "userrole"=>$dbuser_role_name, "status"=>$statusmsg, "error"=>$errmsg, "username"=>$username, "CONTENT_TYPE"=>$contenttype);
		echo json_encode($json);
	
	
	}
	else{//logout
		$stm = $pdo->query("SELECT users.user_id, users.user_roles, sessions.sessions_token, sessions.sessions_id FROM sessions, users WHERE sessions.users_user_id = users.user_id and users.active_status = 0 order by sessions_timestamp DESC");
		$rows = $stm->fetchAll(PDO::FETCH_NUM);
		$user_id = -1;
		//var_dump($rows);
		if (isset($rows[0])) {
			foreach($rows as $row){
				if (password_verify($_POST['token'],$row[2])){
					$user_id = $row[0];
					$user_roles = $row[1];
					$sessions_id = $row[3];
					$stm = $pdo->exec("Update sessions SET sessions_timestamp = '0000-00-00 00:00:00' WHERE sessions.users_user_id = $user_id and sessions.sessions_id = $sessions_id");
					break;
				}
			}
			if ($user_id == -1){
				header('Content-Type: application/json');
				die("{\"error\":\"INVALID_TOKEN1\"}");
			}
		}
		else{
			header('Content-Type: application/json');
			die("{\"error\":\"INVALID_TOKEN2\"}");
		}	
			
			
		echo "{\"status\":\"LOGGED_OUT\",\"error\":\"NO_ERROR\"}";	
	}
	//

	$pdo = null;
	$stm = null;
?>