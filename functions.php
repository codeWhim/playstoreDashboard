<?php

function getConfig() {
 	global $PDO; 
	$sam = $PDO->query("SELECT * FROM `config`"); 
 	if ($sam != FALSE) {
 		while($row = $sam->fetch())  {
 			$config[$row['name']] = $row['value'];
 		}
 		if(!empty($config)){return $config;}
 	}
	return FALSE;
}

function setConfig($name,$value){
	global $PDO;
	$sam = $PDO->prepare("UPDATE `config` SET `value`=:value WHERE `name`=:name");
	$sam->execute(array(':name'=>$name,':value'=>$value));
	return $sam->rowCount();
}

function iif($condition,$true,$false){
	if($condition){
		return $true;
	}else{
		return $false;
	}
}

?>