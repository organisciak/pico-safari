<?php
include 'settings.php';
//Database settings

function err($error) {
	header('Location: login.php?error='.$error);
}

//Check if a username was entered
$user = isset($_POST["user"]) ? $_POST["user"] : null;
if (!$user) {
	err("Sorry, you need to choose a username.");
}

//Check if username exists
$result = mysql_query("SELECT * FROM  `player` WHERE name = '".mysql_real_escape_string($user)."' LIMIT 1") or die ("Database Error:". mysql_error());
$num_rows = mysql_num_rows($result);
if($num_rows == NULL) {
    err("Sorry, That username has not been created yet. Are you sure you wrote it right?");
}

//Check if passcode correct
$r = mysql_fetch_row($result);
if($_POST["code"] != $r[2]) {
    err("Sorry, That passcode is incorrect.");
} else {
	setcookie("username", $user, time()+60*60*24*30, "/projects/2011/pico-sdh/", ".porganized.com");
	header("Location: http://dev.layar.com/media/getbacktoapp.html");
	echo "<p>Logged in.</p>
	<p><a href='layar://picosafari2011'>Back to Layar</a></p>";
}


?>