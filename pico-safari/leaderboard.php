<h1>Top Players</h1>
<ol>
<?php
//print_r($_GET);

//Database settings
include 'settings.php';

$user = isset($_GET["user"]) ? $_GET["user"] : null;

$result = mysql_query("SELECT p.*, SUM(pc.points) as points FROM player p, player_creature pc WHERE pc.player=p.id GROUP BY pc.player ORDER BY points DESC LIMIT 20") or die ("Database Error:". mysql_error());

while ($row = mysql_fetch_assoc($result))
{
	echo "<li>".$row['name']." - ".$row['points']."</li>";
}
?>
</ol>