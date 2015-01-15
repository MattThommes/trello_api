<?php

	require "vendor/autoload.php";
	use MattThommes\Debug;
	use MattThommes\Backend\Mysql;
	$debug = new Debug;

	$json_data = file_get_contents("export.json");
	$data = json_decode($json_data);

	foreach ($data->cards as $card) {
$debug->dbg($card);
	}

	//print_r($data);

?>