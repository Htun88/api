<?php
	//bioLogin
	header('Content-Type: application/json');
	include '../Includes/db.php';

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

	$biotoken = "";
	$username =	"";
	$errmsg = "NO_ERROR";
	$statusmsg = "FAILURE";
	$token ="ERROR";

	if (isset($_POST['username'])){
		$username = $_POST['username'];
	}

	if (isset($_POST['biotoken'])){
		$password = $_POST['biotoken'];
	}

	$stm = $pdo->prepare('SELECT user_id
		, `user_name`
		, `user_login_id`
		, `user_roles`
		, `biologin_hash` 
		FROM `users`
		, `biologin` 
		WHERE users.user_id=biologin.users_user_id 
		AND `active_status` = 0 
		AND `user_login_id` = :username');
	$stm->bindParam(':username', $username);
	$stm->execute();
		
	$rows = $stm->fetchAll(PDO::FETCH_NUM);
	if (isset($rows[0])) {
        $dbuser_id = $rows[0][0];
        $dbuser_name = $rows[0][1];
        $dbuser_login_id = $rows[0][2];
        $dbuser_role = $rows[0][3];
        $dbuser_password = $rows[0][4];
		if (password_verify($password, $dbuser_password)) {
			$statusmsg = "SUCCESS";
			$stm = $pdo->query("SELECT sessions_token FROM sessions");
			$rows = $stm->fetchAll(PDO::FETCH_NUM);			
			$token = gentoken(171);
			$tokenHash = password_hash($token, PASSWORD_DEFAULT);
			while (comptoken($tokenHash,$rows)){
				$token = gentoken(171);
				$tokenHash = password_hash($token, PASSWORD_DEFAULT);
			}
			//$stm = $pdo->exec("DELETE FROM sessions WHERE users_user_id = '$dbuser_id'"); // this line will only allow one session per user. Remove if multi-session is required.
			$timestamp = date("Y-m-d H:i:s");
			$stm = $pdo->exec("INSERT INTO sessions (users_user_id, sessions_token, sessions_timestamp) VALUES ($dbuser_id, '$tokenHash','$timestamp')");
		}
		else {
			$errmsg = "ACCOUNT_BIOTOKEN_INCORRECT";
		}
	}
	else {
		$errmsg = "ACCOUNT_DOES_NOT_EXIST";
	}
	$conn = null;

	$json = array("token"=>$token, "status"=>$statusmsg, "error"=>$errmsg, "username"=>$username);
	echo json_encode($json);
	$pdo = null;
	$stm = null;
?>