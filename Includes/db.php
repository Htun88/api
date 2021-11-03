<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include("dbsettings.php");

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password, array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // set the emulation to false
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //echo "Connected successfully"; 
    }
catch(PDOException $e)
    {
    die ("Connection failed: " . $e->getMessage());
    }

?>