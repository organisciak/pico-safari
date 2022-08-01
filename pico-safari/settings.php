<?php

$config["server_ip"] = "picosafari.db.4138373.hostedresource.com";
$config["db_user"]	 = ""picosafari"";
$config["db_password"]	= "YOUR_PW_HERE";
$config["db_sid"]	 = "picosafari";
$config["layer_name"]	= "picosafari";

// Opens a connection to a mySQL server
$connection=mysql_connect ($config["server_ip"], $config["db_user"], $config["db_password"]);
// Set the active mySQL database
$db_selected = mysql_select_db($config["db_sid"], $connection);

?>