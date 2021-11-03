<?php
	//createbiologin
	header('Content-Type: application/json');
	include '../Includes/db.php';
	include '../Includes/checktoken.php';
	
	$stm = $pdo->exec("DELETE FROM biologin WHERE users_user_id = $user_id");
	
	function gentoken($n) { 
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
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
	
	$errmsg = "NO_ERROR";
	$statusmsg = "FAILURE";
	$biotoken = "ERROR";
	
	$biotoken = gentoken(171);
	$biotokenHash = password_hash($biotoken, PASSWORD_DEFAULT);
	while (comptoken($biotokenHash,$rows)){
		$biotoken = gentoken(171);
		$biotokenHash = password_hash($biotoken, PASSWORD_DEFAULT);
	}
	$timestamp = date("Y-m-d H:i:s");
	$stm = $pdo->exec("INSERT INTO biologin (users_user_id, biologin_hash, biologin_timestamp) VALUES ($user_id, '$biotokenHash','$timestamp')");
	
	$statusmsg = "SUCCESS";

	$json = array("biotoken"=>$biotoken, "status"=>$statusmsg, "error"=>$errmsg);
	echo json_encode($json);
	$pdo = null;
	$stm = null;
?>