<?php
$verbose=false;
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	date_default_timezone_set('America/Denver');

require_once 'vendor/autoload.php';
use MongoDB\Client as Mongo;

	$path = explode("/", $_SERVER['DOCUMENT_ROOT']);
	$path[]='php_classes';
	$path[]='funct.php';
	$loaded=false;
	if(file_exists(implode("/", $path))){
		$loaded=true;
		require_once(implode("/", $path));
	}else{
		$loaded = false;
	}
	if($loaded){
		// Funct::LoadClass('analytics_collect');
		// echo("PHP Functions Loaded.");
	// }else{
		// echo("PHP Functions Failed To Load.");
	}

	Funct::LoadClass('settings');
	foreach (Settings::get_private() as $key => $value) {
		$$key = $value;
	}
	unset($key);
	unset($row);

?>