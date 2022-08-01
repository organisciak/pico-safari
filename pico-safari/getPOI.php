<?php
header('Content-type: application/json');

//PHP GetPOIs for use with Pico Safari and Layar Augmented reality browser.
//Code by Peter Organisciak from a foundation by Fredrick Davidson 
//(see http://layar.com/developer-fredrik-davidsson-shares-his-code/)

//Database settings
include 'settings.php';

$debug = ($_GET["debug"]);
$config["layer_name"] = "picosafari2011";

// Get parameters from the Layar API.
$center_lat = $_GET["lat"];
$center_lng = $_GET["lon"];
$radius = ($_GET["radius"]/1000);
$timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : null;
$developerId = isset($_GET["developerId"]) ? $_GET["developerId"] : null;
$developerHash = isset($_GET["developerHash"]) ? $_GET["developerHash"] : null;
$user = isset($_GET["SEARCHBOX"]) ? $_GET["SEARCHBOX"] : null;
$theme = isset($_GET["SEARCHBOX_2"]) ? $_GET["SEARCHBOX_2"] : null;
$limit = isset($_GET['CUSTOM_SLIDER']) ? $_GET['CUSTOM_SLIDER'] : 10;


#$user = isset($_COOKIE['username']) ? $_COOKIE['username'] : $user;


$message = null;
//Show by Behaviour option. 1=Currently available, 2=day, 3=night, 4=all
$behaviour = isset($_GET["RADIOLIST"]) ? $_GET["RADIOLIST"] : 1;

if ($debug) {print_r($_GET);};
//Show caught (1)/uncaught (2). Outputs array.
$showCatches = isset($_GET["CHECKBOXLIST"]) ? explode(",", $_GET["CHECKBOXLIST"]) : null;

//Check if player exists, if not create a profile
$result = mysql_query("SELECT p.id FROM `player` p WHERE p.name='".mysql_real_escape_string($user)."'") or die('Query failed: ' . mysql_error());


$num=mysql_numrows($result);	
if($num==0) {
	#echo "creating user";
	$result = mysql_query("INSERT INTO  `picosafari`.`player` (`name`) VALUES ('".mysql_real_escape_string($user)."');") or die('Query failed: ' . mysql_error());
	$message = "Welcome to Pico Safari, ".$user."!";
}

if (isset($user)) {
$caughtQuery = "SELECT pc.creature FROM player_creature pc, player p WHERE p.name='".$user."' and p.id = pc.player";
$caughtPicosResults = mysql_query($caughtQuery);
#echo mysql_fetch_assoc($caughtPicosResults);

while ($row = mysql_fetch_assoc($caughtPicosResults)) { 
	$caughtPicos[] = $row['creature']; 
	}
 }

 
$queryparts["SELECT"] = "SELECT cm.name, ci.master, ci.id as creature, latitude, longitude, ci.times_caught, ci.catch_limit,(6378*acos(cos(radians('" . mysql_real_escape_string($center_lat) . "'))*cos(radians(latitude))*cos(radians(longitude)-radians('" . mysql_real_escape_string($center_lng) . "'))+sin(radians('" . mysql_real_escape_string($center_lat) . "'))*sin(radians(latitude)))) AS distance, cm.type, ci.availability";
$queryparts["FROM"] = "FROM creature_instance ci, creature_master cm";
$queryparts["WHERE"] = "WHERE ci.master = cm.id";
$queryparts["HAVING"] = "HAVING distance<'" . mysql_real_escape_string($radius) . "'";
$queryparts["LIMIT"] = "LIMIT 0, ".$limit;

switch ($behaviour) {
    case 1:
        $queryparts["WHERE"] = $queryparts["WHERE"]." and ci.`availability` = 1";
        break;
    case 2:
	//get picos with type 0 or 2
        $queryparts["WHERE"] = $queryparts["WHERE"]." and (cm.type = 0 or cm.type = 2)";
        break;
    case 3:
	//get picos with type 1 or 2
        $queryparts["WHERE"] = $queryparts["WHERE"]." and (cm.type = 1 or cm.type = 2)";
        break;
	case 4:
	//don't limit query any more
		break;
}

if ($theme != null) {
	$queryparts["SELECT"] = $queryparts["SELECT"].", ad.adventure_name";
	$queryparts["FROM"] = $queryparts["FROM"].", ADVENTURE ad";
	$queryparts["WHERE"] = $queryparts["WHERE"]." AND ad.adventure_id = ci.adventure_id AND ad.adventure_name =  '".$theme."'";
}

$query = $queryparts["SELECT"]." ".$queryparts["FROM"]." ".$queryparts["WHERE"]." ".$queryparts["HAVING"]." ".$queryparts["LIMIT"];
//echo "!!!".$query."!!!";

$result = mysql_query($query);
$num_rows = mysql_num_rows($result);
// If we don’t get any hits lets send back error/nothing.
if (!$result or $num_rows==0)
	{
		$arr = array("hotspots"=> "", "layer"=>$config["layer_name"], "errorString"=>"Sorry, no destinations close to you right now!", "morePages"=>false, "errorCode"=>21, "nextPageKey"=>null, "radius"=>$radius*1000);
		echo json_encode($arr);
		exit; // Exit as we don’t want to run code below this if error/nothing.
}

// Lets start building valid return.
$returnJSONArray = array(
	"layer"=>$config["layer_name"], 
	"errorString"=>"ok", "morePages"=>false, 
	"errorCode"=>0, "nextPageKey"=>null, 
	"radius"=>$radius*1000, 
	"showMessage"=>$message,
	"actions" => array(array(
			"uri" => "http://www.porganized.com/projects/2011/pico-sdh/login.php",
			"label" => "Login"
		),array(
			"uri" => "http://www.porganized.com/projects/2011/pico-sdh/login.php?logout=true",
			"label" => "Logout"
		),
		
		)
	);

while ($row = mysql_fetch_assoc($result))
{
	$actionsArr = array();
	$isCaught = isset($caughtPicos) ? in_array($row['creature'], $caughtPicos) : null;
	if ($isCaught == true) {
		$returnPico = in_array(1, $showCatches);
		$type = 1;
		$hidden = "";
		$line4 = "You have caught this Pico";
		array_push($actionsArr, array(
			"uri" => "http://www.porganized.com/projects/2011/pico-sdh/pico.php?id=".$row['creature'],
			"label" => "See Pico Page"
		));
	} else {
		$returnPico = in_array(2, $showCatches);
		$type = 0;
		$hidden = "&hidden=true";
		//Check whether there is a catchlimit and if that limit has not yet been reached
		if ($row['catch_limit']!=-1 && $row['catch_limit'] > $row['times_caught']) {
			$line4 = "This pico has ".($row['catch_limit']-$row['times_caught'])." catch".(($row['catch_limit']-$row['times_caught'])==1 ? "" : "es")." left.";
		} else {
			$line4 = "You can catch this pico when you're within 20 metres.";
		}
		array_push($actionsArr, array(
			"uri" => "http://www.porganized.com/projects/2011/pico-sdh/capturePico.php?id=".$row['creature']."&user=".$user,
			"label" => "Catch Pico",
			"activityType" => 36,
			//"autoTriggerRange" => 20,
			"activityMessage" => "Catching Pico...",
			"contentType" => "text/plain",
			//"showActivity" => false,
			"autoTriggerOnly" => false,
			"params" => array("lat", "lon")
		));
		
		/*array_push($actionsArr, array(
			"uri" => "http://www.porganized.com/projects/2011/pico-sdh/chirp2.mp3",
			"label" => "Chirp",
			//"activityType" => 36,
			"autoTriggerRange" => 20,
			//"activityMessage" => "Catching Pico...",
			"contentType" => "audio/mpeg",
			"showActivity" => false,
			"autoTriggerOnly" => true,
			//"params" => array("lat", "lon")
		));*/
	}
	//Add website link to all Picos
	array_push($actionsArr, array(
			"uri" => "http://www.porganized.com/projects/2011/pico-sdh/leaderboard.php?user=".$user,
			"label" => "Leaderboard",
			"activityType" => 35,
			"params" => array("lat", "lon", "SEARCHBOX")
		));
	if (isset($returnPico)) {
		$returnJSONArray["hotspots"][] = array(
		"distance" => $row['distance']*1000,
		"attribution" => "Pico Safari",
		"id" => $row['master'],
		"imageURL" => "http://www.porganized.com/projects/2011/pico-sdh/picoImage.php?id=".$row['master']."&size=75".$hidden,
		"lat" => (int) round($row['latitude']*1000000),
		"lon" => (int) round($row['longitude']*1000000),
		"line2" => null,
		"line3" => null,
		"line4" => $line4,
		"type" => $type,
		"title" => $row['name'],//$row['creature_key'],
		"dimension" => 2,
		//"alt" => 0,
		"actions" => $actionsArr,
		"transform" => array(
			"rel" => true,
			//"angle" => 90,
			"scale" => 2.0
		), 
		"object"  => array(
			"baseURL" =>
		"http://www.porganized.com/projects/2011/pico-sdh/",
			"full" => "picoImage.php?id=".$row['master']."&size=400".$hidden,
			//"icon" => $row['master']."_75.png",
			"reduced" => "picoImage.php?id=".$row['master']."&size=100".$hidden,
			"size" => 2
		),
		//"relativeAlt" => 0

		);
	}
}
echo json_encode($returnJSONArray);
?>