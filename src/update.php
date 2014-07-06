<?php

echo "
<html>
<head>
	<title>SiPac Update</title>
</head>
<body>
<h1>SiPac Update</h1>
";
if (isset($_POST['mysql_send']))
{
	if (!empty($_POST['mysql_ext']) AND !empty($_POST['hostname']) AND !empty($_POST['username']) AND !empty($_POST['password']) AND !empty($_POST['database']))
	{
		if ($_POST['mysql_ext'] == "mysql")
		{
			require_once(dirname(__FILE__)."/php/class/SiPac_MySQL.php");
			$db = new SiPac_MySQL($_POST['hostname'], $_POST['username'], $_POST['password'], $_POST['database'], "mysql");
		}
		else if ($_POST['mysql_ext'] == "mysqli")
		{
			require_once(dirname(__FILE__)."/php/class/SiPac_MySQL.php");
			$db = new SiPac_MySQL($_POST['hostname'], $_POST['username'], $_POST['password'], $_POST['database'], "mysqli");
		}
		else
			die("Error: wrong MySQL extension!");
		
		require_once(dirname(__FILE__)."/php/class/SiPac_Channel.php");
		$channel = new SiPac_Channel(false, false);
		
		$mysql_return = $db->check(true);
		if ($mysql_return != true)
				echo "<div style='background:orange; color: white'>MySQL Error. Check the MySQL acess data.</div>";
		else if ($db->check_database() != true)
			echo "<div style='background:orange; color: white'>Wrong database!".$db->check_database()."</div>";
		else
		{
			$check_return = $db->check();
			if ($check_return === true)
				echo "<div style='background:orange; color: white'>Your MySQL tables are already up to date!</div>";
			else
			{
				$entries_mysql = $db->query("SELECT * from chat_entries");
				while ($entry = $db->fetch_object($entries_mysql))
				{
					$new_channel_name = $channel->encode($entry->channel);
					$new_chat_id  = preg_replace('/chat_/', "", $entry->chat_id, 1); 
					$update_entry = $db->query("UPDATE chat_entries SET channel='".$db->escape_string($new_channel_name)."', chat_id='".$new_chat_id."' WHERE id= '".$entry->id."'");
					echo $update_entry;
				}
				$users_mysql = $db->query("SELECT * from chat_users");
				while ($user = $db->fetch_object($users_mysql))
				{
					$new_channel_name = $channel->encode($user->channel);
					$new_chat_id  = preg_replace('/chat_/', "", $user->chat_id, 1); 
					$update_user = $db->query("UPDATE chat_users SET channel='".$db->escape_string($new_channel_name)."', chat_id='".$new_chat_id."' WHERE id= '".$user->id."'");
					echo $update_user;
				}
				
				echo $db->query ("ALTER TABLE chat_users RENAME TO sipac_users");
				echo $db->query ("ALTER TABLE chat_entries RENAME TO sipac_entries");
				echo $db->query("ALTER TABLE sipac_entries DROP highlight");
				echo $db->query("ALTER TABLE sipac_entries CHANGE extra type INT(3)");
				echo $db->query("ALTER TABLE sipac_users CHANGE action task mediumtext");
				echo $db->query("ALTER TABLE sipac_users CHANGE last_time online INT(10)");
				echo $db->query("ALTER TABLE sipac_entries ADD style MEDIUMTEXT NOT NULL AFTER type");
				echo "<div style='background:orange; color: white'>Update done Successfully! Please delete this file, after you are done!</div>";
			}
		}
	}
	else
		echo "<div style='background:orange; color: white'>You have to fill out everything!</div>";
}
echo "
<h4>Please enter the MySQL access data to update the SiPac MySQL tables!</h4>
<form method='POST' action='#'>
<table>
<tr>
<td>MySQL extension (leave as default, if you aren't sure):</td><td><select name='mysql_ext'><option value='mysqli'>MySQLi</option><option value='mysql'>MySQL</option></select></td>
</tr>
<tr>
<td>Hostname (mostly 'localhost'):</td><td><input type='text' value='localhost' name='hostname'></td>
</tr>
<tr>
<td>MySQL username:</td><td><input type='text' name='username'></td>
</tr>
<tr>
<td>MySQL password:</td><td><input type='password' name='password'></td>
</tr>
<tr>
<td>MySQL database:</td><td><input type='text' name='database'></td>
</tr>
<tr><td><input type='submit' name='mysql_send'></td></tr>
</table>
</form>
";


echo "
</body>
</html>";
?>
