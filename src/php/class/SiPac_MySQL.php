<?php
/*
SiPac is highly customizable PHP and AJAX chat
Copyright (C) 2013 Jan Houben

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

class SiPac_MySQL
{
	private $host;
	private $db;
	private $user;
	private $pw;

	private $mysql_error;
	private $connected = false;

	private $nickname;

	private $columns = array("sipac_entries" => array("id", "user", "message", "type", "style", "time", "channel", "chat_id"),
															"sipac_users" =>  array("id", "name", "task", "info", "style", "afk", "writing", "ip", "online", "channel", "chat_id"));

	public function __construct($host, $user, $pw, $db, $plugin)
	{
		$this->host = $host;
		$this->db = $db;
		$this->user = $user;
		$this->pw = $pw;
		$this->plugin = $plugin;
	}
	
	public function check($no_table_check=false)
	{
		$this->mysql_error = $this->connect();
		if ($this->mysql_error == true)
		{
			if ($no_table_check == true)
				return true;
			else
				return $this->check_tables();
		}
		else
			return $this->mysql_error;
	}
	private function check_tables()
	{
		foreach ($this->columns as $table=>$columns)
		{
			$check_columns_mysql = $this->query("SHOW COLUMNS FROM $table");
			$check_columns = array();
			if ($check_columns_mysql != false AND $this->num_rows($check_columns_mysql) > 0) 
			{
				while ($row = $this->fetch_assoc($check_columns_mysql))
				{
					$check_columns[$row['Field']] = $row;
				}
			}
			foreach ($columns as $colum)
			{
				if (!isset($check_columns[$colum]))
				{
					$missing_columns[] = $colum;
				}
			}
		}
		if (isset($missing_columns))
		{
			return 'Wrong or outdated MySQL tables!  If you just updated SiPac, run update.php. To do so, navigate  in your web browser to the "src"-directory where SiPac is installed (http://example.com/SiPac/src/update.php)';
		}
		else
			return true;
	}

	public function query($sql)
	{
		$this->connect();
		
		if ($this->plugin == "mysql")
			return mysql_query($sql);
		else
			return mysqli_query($this->link, $sql);
	}
	public function fetch_assoc($mysql)
	{
		if ($this->plugin == "mysql")
			return mysql_fetch_assoc($mysql);
		else
			return mysqli_fetch_assoc($mysql);
	}
	
	public function fetch_array($mysql)
	{
		if ($this->plugin == "mysql")
			return mysql_fetch_array($mysql);
		else
			return mysqli_fetch_array($mysql);
	}
	
	public function fetch_object($mysql)
	{
		if ($this->plugin == "mysql")
			return mysql_fetch_object($mysql);
		else
			return mysqli_fetch_object($mysql);
	}
	public function num_rows($mysql)
	{
		if ($this->plugin == "mysql")
			return mysql_num_rows($mysql);
		else
			return mysqli_num_rows($mysql);
	}

	public function escape_string($string)
	{
		$this->connect();
		
		if ($this->plugin == "mysql")
			return mysql_real_escape_string($string);
		else
			return mysqli_real_escape_string($this->link, $string);
	}

	public function error()
	{
		if ($this->plugin == "mysql")
			return mysql_error();
		else
			return mysqli_error($this->link);
	}
	public function check_database()
	{
		$sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="'.$this->escape_string($this->db).'"';

		$query = $this->query($sql);
		if ($query === false) 
			throw new Exception($this->error(), mysql_errno());
		
		// extract the value
		$row = $this->fetch_object($query);
		$dbExists = (bool) $row->exists;
		return $dbExists;
	}
	public function get_posts($chat_id, $channels, $min_id=0)
	{
		$chat_mysql = $this->query("SELECT * FROM sipac_entries WHERE chat_id LIKE '".$this->escape_string($chat_id)."' AND id > $min_id ORDER BY id ASC");

		$posts = array();

		if ($chat_mysql == false)
			return $this->error();
		else
		{
			while ($post = $this->fetch_assoc($chat_mysql))
			{
				$posts[] = $post;
			}
		}
		return $posts;
	}

	public function save_post($message, $chat_id, $channel, $type, $user, $style, $time)
	{
		$save_message_mysql = $this->query("INSERT INTO sipac_entries (user, message, type, style, time, channel, chat_id) VALUES('" . $this->escape_string($user) . "', '" . $this->escape_string($message) . "','$type', '".$this->escape_string($style)."', '$time', '" . $this->escape_string($channel) . "', '$chat_id')");
		if ($save_message_mysql == false)
			return $this->error();
		else
			return $save_message_mysql;
	}

	public function get_all_users($channel,$chat_id)
	{
		//get all users in the chat
		$users_mysql = $this->query("SELECT * FROM sipac_users WHERE chat_id LIKE '".$this->escape_string($chat_id)."' AND channel LIKE '".$this->escape_string($channel)."'");

		$users = array();

		if ($users_mysql == false)
			return $this->error();
		else
		{
			while ($user = $this->fetch_assoc($users_mysql))
			{
				$users[] = $user;
			}
		}
		return $users;
	}

	public function get_user($nickname, $channel, $chat_id)
	{
		//get all values of the user with the given nickname
		$user_info = $this->query("SELECT * FROM sipac_users WHERE name LIKE '".$this->escape_string(addslashes($nickname))."' AND channel LIKE '".$this->escape_string($channel)."' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
		if ($user_info == false)
			return $this->error();
		else
			return $this->fetch_assoc($user_info);
	}

	public function update_user($nickname, $channel, $chat_id, $time, $is_writing, $is_afk, $user_info, $user_style)
	{
		$is_writing =  $is_writing == "true" ? 1 : 0;
		//update the user entry with all new values
		$update_user_mysql = $this->query("UPDATE sipac_users SET online = '" . $time . "', afk = '" . $is_afk ."', writing = '$is_writing', style = '" . $user_style . "', info = '". $this->escape_string($user_info)."' WHERE name = '" . $this->escape_string($nickname) . "' AND channel = '" . $this->escape_string($channel) . "' AND chat_id = '" . $this->escape_string($chat_id) . "'");
		if ($update_user_mysql == false)
			return $this->error();
		else
			return $update_user_mysql;
	}
	
	public function ban_user($nickname,  $time, $chat_id)
	{
		$update_user_mysql = $this->query("UPDATE sipac_users SET online = '" . $time . "', info = 'banned' WHERE name = '" . $this->escape_string($nickname) . "' AND chat_id = '" . $this->escape_string($chat_id) . "'");
		if ($update_user_mysql == false)
			return $this->error();
		else
			return $update_user_mysql;
	}
	
	public function unban_user($nickname, $chat_id)
	{
		$count = $this->query("SELECT * from sipac_users WHERE  info ='banned' AND name LIKE '".$this->escape_string($nickname)."' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
		if ($this->num_rows($count) > 0)
		{
			$unban_user_mysql = $this->query("DELETE from sipac_users WHERE info ='banned' AND name = '" . $this->escape_string($nickname) . "' AND chat_id = '" . $this->escape_string($chat_id) . "'");
			if ($unban_user_mysql == false)
				return $this->error();
			else
				return $unban_user_mysql;
		}
		else
			return false;
	}
	
	public function check_ban($nickname, $chat_id)
	{
		$user_info_mysql = $this->query("SELECT info,name,chat_id FROM sipac_users WHERE name LIKE '".$this->escape_string(addslashes($nickname))."' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
		if ($user_info_mysql == false)
			return $this->error();
		else
		{
			$user_info = $this->fetch_array($user_info_mysql);
			if ($user_info['info'] == "banned")
				return true;
			else
				return false;
		}
	}
	
	public function get_banned_users($chat_id)
	{
		$users_mysql = $this->query("SELECT * FROM sipac_users WHERE  info LIKE 'banned' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
		if ($users_mysql == false)
			return $this->error();
		else
		{
			$users = array();
			while ($user = $this->fetch_assoc($users_mysql))
			{
				$users[] = $user;
			}
			return $users;
		}
	}
	public function save_user($nickname, $channel, $is_afk, $user_info, $user_style, $user_ip, $chat_id)
	{
		//save the user with all given values
		$add_user_mysql = $this->query("INSERT INTO sipac_users (name, info, style, afk, writing, ip, online, channel, chat_id) VALUES ('" . $this->escape_string($nickname) . "', '" . $this->escape_string($user_info) . "', '" . $user_style . "', '" . $is_afk . "', 'false', '" . $user_ip . "', '" . time() . "', '" . $this->escape_string($channel) . "', '".$this->escape_string($chat_id)."')");
		if ($add_user_mysql == false)
			return $this->error();
		else
			return $add_user_mysql;
	}

	public function delete_user($nickname, $channel, $chat_id)
	{
		$delete_user = $this->query("DELETE FROM sipac_users WHERE name LIKE '" . $this->escape_string(addslashes($nickname)) . "' AND channel LIKE '".$this->escape_string($channel)."' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
		if ($delete_user == false)
			return $this->error();
		else
			return $delete_user;
	}

	public function add_task($task, $user, $channel, $chat_id)
	{
		if ($user === false)
			$user = $this->nickname;
			
		$count = $this->query("SELECT * from sipac_users WHERE  name LIKE '".$this->escape_string(addslashes($user))."' AND channel LIKE '".$this->escape_string($channel)."' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
		if ($this->num_rows($count) > 0)
		{
			$add_task = $this->query("UPDATE sipac_users SET task = '".$this->escape_string($task)."' WHERE name LIKE '".$this->escape_string(addslashes($user))."' AND channel LIKE '".$this->escape_string($channel)."' AND chat_id LIKE '".$this->escape_string($chat_id)."'");
			if ($add_task == false)
				echo $this->error();
			else
				return $add_task;
		}
		else
			return false;
	}

	public function clean_up($channels, $max_messages, $chat_id)
	{
		foreach ($channels as $channel)
		{
			$remove_old_posts = $this->query
			("DELETE from sipac_entries 
				WHERE id IN (select id from (select id from sipac_entries 
				WHERE chat_id LIKE '".$this->escape_string($chat_id)."' AND channel LIKE '".$this->escape_string($channel)."' ORDER BY id DESC LIMIT $max_messages, 1000) 
				x) ");
				
			if ($remove_old_posts == false)
			{
				return $this->error();
				break;
			}
		}
		return true;
	}

	public function update_nickname($nickname)
	{
		$this->nickname = $nickname;
	}
	private function connect()
	{
		if ($this->plugin == "mysql")
		{
			if ($this->connected == false)
			{
				/*Connect to mysql */
				if (!mysql_connect($this->host, $this->user, $this->pw))
					return mysql_error();
				else if (!mysql_select_db($this->db))
					return mysql_error();
				else
				{
					$this->connected = true;
					return true;
				}
			}
			else
				return true;
		}
		else
		{
			if ($this->connected == false)
			{
				/*Connect to mysql */
				$this->link = mysqli_connect($this->host, $this->user, $this->pw);
				if ($this->link == false)
					return false;
				else if (!mysqli_select_db($this->link, $this->db))
					return mysqli_error($this->link);
				else
				{
					$this->connected = true;
					return true;
				}
			}
			else
				return true;
		}
	}
}
?>