<?php
// set the expiration date to one hour ago
if ($_COOKIE['username'] && $_GET['logout']) {
	setcookie("username", "", time()-3600);
	echo "<p style='background:yellow'>You've been logged out.</p>";
} else if ($_COOKIE['username']) {
	echo "<p style='background:yellow'>You're already logged in. <a href='login.php?logout=true'>Log out?</a></p>";
}
$error = $_GET['error'];
echo "<p style='background:red'>".$error."</p>";
?>
<h1>Create User</h1>
<form action="adduser.php" method="post">
Name: <input type="text" name="user" /><br/>
Passcode: <input name="code" maxlength="4" type="number" /><br/>
<input type="submit" />
</form>

<h1 id="login">Log In</h1>
<form action="loguser.php" method="post">
Name: <input type="text" name="user" /><br/>
Passcode: <input  name="code" maxlength="4" type="number" /><br/>
<input type="submit" />
</form>