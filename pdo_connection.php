<?php

// Update your database credentials in config.php, not here please

define('DB_HOST',$database_host); 
define('DB_USERNAME',$database_user); 
define('DB_PASSWORD',$database_password);
define('DB_NAME',$database_name);

ob_start();
	try { 

	$str = sprintf('mysql:host=%s;dbname=%s',DB_HOST,DB_NAME);
	$PDO = new PDO($str, DB_USERNAME, DB_PASSWORD);
	$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch (Exception $e) {
	if (DEBUG_MODE) {
	die('Error: ' . $e->getMessage());
	}
	}


?>