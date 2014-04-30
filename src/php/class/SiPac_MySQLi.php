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
 
class SiPac_MySQLi
{
  private $host;
  private $db;
  private $user;
  private $pw;
  
  private $mysql_error;
  private $connected = false;
  
  private $nickname;
  
  private $columns = array("sipac_entries" => array("id", "user", "message", "type", "time", "channel", "chat_id"),
														   "sipac_users" =>  array("id", "name", "task", "info", "style", "afk", "writing", "ip", "online", "channel", "chat_id"));
  
  public function __construct($host, $user, $pw, $db)
  {
    $this->host = $host;
    $this->db = $db;
    $this->user = $user;
    $this->pw = $pw;
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
			$check_columns_mysql = mysqli_query($this->link, "SHOW COLUMNS FROM $table");
			$check_columns = array();
			if ($check_columns_mysql != false AND mysqli_num_rows($check_columns_mysql) > 0) 
			{
				while ($row = mysqli_fetch_assoc($check_columns_mysql))
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
  public function check_database()
  {
	$sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="'.mysqli_real_escape_string($this->link, $this->db).'"';

	$query = mysqli_query($this->link, $sql);
	if ($query === false) {
		throw new Exception(mysqli_error($this->link), mysqli_errno($this->link));
	}

	// extract the value
		$row = mysqli_fetch_object($query);
	$dbExists = (bool) $row->exists;
	 return $dbExists;
  }
  public function get_posts($chat_id, $channels)
  {
    $this->connect();
    $chat_mysql = mysqli_query($this->link, "SELECT * FROM sipac_entries WHERE chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."' ORDER BY id ASC");
    
    $posts = array();
    
    if ($chat_mysql == false)
     echo mysqli_error($this->link);
    else
    {
      while ($post = mysqli_fetch_assoc($chat_mysql))
      {
	
	  $posts[] = $post;
      }
    }
    return $posts;
  }
  
  public function save_post($message, $chat_id, $channel, $type, $user, $time)
  {
    $this->connect();
      
    $save_message_mysql = mysqli_query($this->link, "INSERT INTO sipac_entries (user, message, type, time, channel, chat_id) VALUES('" . mysqli_real_escape_string($this->link, $user) . "', '" . mysqli_real_escape_string($this->link, $message) . "','$type', '$time', '" . mysqli_real_escape_string($this->link, $channel) . "', '$chat_id')");
    if ($save_message_mysql == false)
      return mysqli_error($this->link);
    else
		return $save_message_mysql;
  }
  
  public function get_all_users($channel,$chat_id)
  {
    //get all users in the chat
    $users_mysql = mysqli_query($this->link, "SELECT * FROM sipac_users WHERE chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."' AND channel LIKE '".mysqli_real_escape_string($this->link, $channel)."'");
    
    $users = array();
    
    if ($users_mysql == false)
      echo mysqli_error($this->link);
    else
    {
      while ($user = mysqli_fetch_assoc($users_mysql))
      {
	$users[] = $user;
      }
    }
    return $users;
  }
  
  public function get_user($nickname, $channel, $chat_id)
  {
    //get all values of the user with the given nickname
    $user_info = mysqli_query($this->link, "SELECT * FROM sipac_users WHERE name LIKE '".mysqli_real_escape_string($this->link, $nickname)."' AND channel LIKE '".mysqli_real_escape_string($this->link, $channel)."' AND chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."'");
    if ($user_info == false)
      echo mysqli_error($this->link);
    else
      return mysqli_fetch_assoc($user_info);
  }
  
  public function update_user($nickname, $channel, $chat_id, $time, $is_writing, $afk)
  {
    //variables to add later
    $is_afk = $afk;
    $user_style = "";
    $channel = $channel;
    
    if ($is_writing == "true")
      $is_writing = 1;
    else
      $is_writing = 0;
      
    //update the user entry with all new values
    $update_user_mysql = mysqli_query($this->link, "UPDATE sipac_users SET online = '" . $time . "', afk = '" . $is_afk . "', writing = '" . mysqli_real_escape_string($this->link, $is_writing) . "', style = '" . $user_style . "' WHERE name = '" . mysqli_real_escape_string($this->link, $nickname) . "' AND channel = '" . mysqli_real_escape_string($this->link, $channel) . "' AND chat_id = '" . mysqli_real_escape_string($this->link, $chat_id) . "'");
    
    
    
    return $update_user_mysql;
  }

  public function save_user($nickname, $channel, $user_info, $user_ip, $chat_id)
  {
    //variables to add later
    $user_style = "";
    $is_afk = "";
    
    //save the user with all given values
    $add_user_mysql = mysqli_query($this->link, "INSERT INTO sipac_users (name, info, style, afk, writing, ip, online, channel, chat_id) VALUES ('" . mysqli_real_escape_string($this->link, $nickname) . "', '" . mysqli_real_escape_string($this->link, $user_info) . "', '" . $user_style . "', '" . $is_afk . "', 'false', '" . $user_ip . "', '" . time() . "', '" . mysqli_real_escape_string($this->link, $channel) . "', '$chat_id')");
  }
  
  public function delete_user($nickname, $channel, $chat_id)
  {
  
  
    $delete_user = mysqli_query($this->link, "DELETE FROM sipac_users WHERE name LIKE '" . mysqli_real_escape_string($this->link, $nickname) . "' AND channel LIKE '".mysqli_real_escape_string($this->link, $channel)."' AND chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."'");
  }
  
  public function add_task($task, $user, $channel, $chat_id)
  {
    if ($user === false)
      $user = $this->nickname;
      
	$count = mysqli_query($this->link, "SELECT * from sipac_users WHERE  name LIKE '".mysqli_real_escape_string($this->link, $user)."' AND channel LIKE '".mysqli_real_escape_string($this->link, $channel)."' AND chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."'");
	if (mysqli_num_rows($count) > 0)
	{
		$add_task = mysqli_query($this->link, "UPDATE sipac_users SET task = '".mysqli_real_escape_string($this->link, $task)."' WHERE name LIKE '".mysqli_real_escape_string($this->link, $user)."' AND channel LIKE '".mysqli_real_escape_string($this->link, $channel)."' AND chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."'");
		return true;
    }
    else
		return false;
  }
  
	public function clean_up($channels, $max_messages, $chat_id)
	{
		foreach ($channels as $channel)
		{
			$remove_old_posts = mysqli_query($this->link, 
			"DELETE from sipac_entries 
				WHERE id IN (select id from (select id from sipac_entries 
					WHERE chat_id LIKE '".mysqli_real_escape_string($this->link, $chat_id)."' AND channel LIKE '".mysqli_real_escape_string($this->link, $channel)."' ORDER BY id DESC LIMIT $max_messages, 1000) 
				x) ");
			
			if ($remove_old_posts == false)
			{
				echo mysqli_error($this->link);
				break;
				return false;
			}
		}
		return true;
	}
  
  public function update_nickname($nickname)
  {
    $this->nickname = $nickname;
  }
  
  public function query($sql)
  {
	$query = mysqli_query($this->link, $sql);
	if ($query == true)
		return true;
	else
		return mysqli_error($this->link);
  }
  private function connect()
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



?>