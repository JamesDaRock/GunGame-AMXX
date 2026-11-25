<?php

// default values from sql.cfg
$amx_sql_host	= "127.0.0.1";
$amx_sql_user	= "root";
$amx_sql_pass	= "";
$amx_sql_db		= "amx";

// MAKE SURE TO CHANGE THIS TO WHAT YOUR SERVER USES!
$gg_stats_mode	= 1;

$db = mysql_connect($amx_sql_host,$amx_sql_user,$amx_sql_pass);
mysql_select_db($amx_sql_db,$db);

for($i=1;$i<=10;$i++)
{
	$player = gg_get_player('rank',$i);
	if($player === false) break;

	$lastname = stripslashes($player['lastname']);
	$wins = $player['wins'];

	echo "<b>$i.</b> $lastname ($wins wins)<br />";
 }

mysql_close($db);

/* Gets an associative array of data for player.

    Data types are: 'authid', 'wins', 'lastname', 'timestamp', 'points', 'serverip'

    Additionally, you can search for 'rank'
*/
function gg_get_player($field,$value)
{
	global $db, $gg_stats_mode;

	if($field == 'rank')
	{
		if($gg_stats_mode == 1)
			$query = mysql_query('SELECT * FROM gg_stats ORDER BY `wins` DESC LIMIT ' . ($value-1) . ', 1;',$db);
		else
			$query = mysql_query('SELECT * FROM gg_stats ORDER BY `points` DESC LIMIT ' . ($value-1) . ', 1;',$db);
	}
	else $query = mysql_query('SELECT * FROM gg_stats WHERE `' . $field . '` = \'' . $value . '\';',$db);

	if(!mysql_num_rows($query)) return false;

	return mysql_fetch_assoc($query);
}

?>