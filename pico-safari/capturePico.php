<?php
#print_r($_GET);

//Database settings
include 'settings.php';


// GET parameters
$center_lat = $_GET["lat"];
$center_lng = $_GET["lon"];
$user = isset($_GET["user"]) ? $_GET["user"] : null;
$pico = isset($_GET["id"]) ? $_GET["id"] : null;

$radius = 0.02; // In km

//Check if you're close enough

$query = 
	"SELECT cm.name, ci.master, ci.id as creature, latitude, longitude, ci.times_caught, ci.catch_limit,
	(6378*acos(cos(radians('" . mysql_real_escape_string($center_lat) . "'))*cos(radians(latitude))*cos(radians(longitude)-radians('" . mysql_real_escape_string($center_lng) . "'))+sin(radians('" . mysql_real_escape_string($center_lat) . "'))*sin(radians(latitude)))) 
	AS distance, cm.type, ci.availability
	FROM creature_instance ci, creature_master cm
	WHERE ci.master = cm.id AND ci.id=".mysql_real_escape_string($pico)."
	HAVING distance<'" . mysql_real_escape_string($radius) . "'";
	
	#echo $query;
$result = mysql_query($query);
#print_r(mysql_fetch_assoc($result));
$num_rows = mysql_num_rows($result);
// If we don’t get any hits lets send back error/nothing.
if (!$result or $num_rows==0)
	{
		echo "You're not close enough! You need to be within 20 meters to catch a pico.";
		exit; // Exit as we don’t want to run code below this if error/nothing.
}
else {
	//Determine point count, based on number of previous catches.
	$countCatches = mysql_query("SELECT * FROM `player_creature` WHERE creature=".mysql_real_escape_string($pico));
	$catchCount = mysql_num_rows($countCatches);	
	$points = $catchCount <5 ? 5-$catchCount : 1;
	
	//Determine the user's id
	$userq= mysql_query("SELECT id FROM player WHERE name =  '".mysql_real_escape_string($user)."' LIMIT 0 , 30");
	$r = mysql_fetch_row($userq);  
    $userid= $r[0];  
	
	//Check if user has already caught that Pico
	$result= mysql_query("SELECT * FROM player_creature WHERE player =  '".mysql_real_escape_string($userid)."' and creature = '".mysql_real_escape_string($pico)."' LIMIT 0 , 30");
	$num_rows = mysql_num_rows($result); 
    if (!$result or $num_rows!=0)
	{
		echo "You've already caught this Pico!";
		exit;
	}  
	
	
	//Save the catch
	mysql_query("INSERT INTO  player_creature (player, creature, points, timestamp) VALUES (
		'".mysql_real_escape_string($userid)."',  
		'".mysql_real_escape_string($pico)."',  
		'".mysql_real_escape_string($points)."',
		NOW( ));")  or die('Database error. Sorry! ('. mysql_error().')');
	
	echo "You caught the pico! ".$catchCount." ".($catchCount == 1 ? "person has" : "people have")." caught it before you. You've recieved ".$points." points for the catch.";
}

?>