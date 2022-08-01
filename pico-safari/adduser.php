<?php
include 'settings.php';
//Database settings

function err($error) {
	header('Location: login.php?error=123'.$error);
}

$user = isset($_POST["user"]) ? $_POST["user"] : null;
$code = isset($_POST["code"]) ? $_POST["code"] : null;

//Check if a username was entered
if (!$user) {
	err("Sorry, you need to choose a username.");
}

//Check if username exists
$result = mysql_query("SELECT * FROM  `player` WHERE name = '".mysql_real_escape_string($user)."' LIMIT 1") or die ("Database Error:". mysql_error());
$num_rows = mysql_num_rows($result);
if($num_rows > 0) {
    err("Sorry, That username has already been taken");
} else {

	$result = mysql_query("INSERT INTO  `picosafari`.`player` (`name` ,`passcode`)
	VALUES (  
	'".mysql_real_escape_string($user)."',  
	'".mysql_real_escape_string($code)."'
	);") or die ("Database Error:". mysql_error());

	setcookie("username", $user, time()+60*60*24*30);
	header("Location: http://dev.layar.com/media/getbacktoapp.html");
	echo "<p>Account created and logged in.</p>
	<p><a href='layar://picosafari2011'>Back to Layar</a></p>";
}
?>