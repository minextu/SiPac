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
			$check_columns_mysql = mysql_query("SHOW COLUMNS FROM $table");
			$check_columns = array();
			if ($check_columns_mysql != false AND mysql_num_rows($check_columns_mysql) > 0) 
			{
				while ($row = mysql_fetch_assoc($check_columns_mysql))
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
	$sql = 'SELECT COUNT(*) AS `exists` FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMATA.SCHEMA_NAME="'.mysql_real_escape_string($this->db).'"';

	$query = mysql_query($sql);
	if ($query === false) {
		throw new Exception(mysql_error(), mysql_errno());
	}

	// extract the value
		$row = mysql_fetch_object($query);
	$dbExists = (bool) $row->exists;
	 return $dbExists;
  }
  public function get_posts($chat_id, $channels)
  {
    $this->connect();
    $chat_mysql = mysql_query("SELECT * FROM sipac_entries WHERE chat_id LIKE '".mysql_real_escape_string($chat_id)."' ORDER BY id ASC");
    
    $posts = array();
    
    if ($chat_mysql == false)
     echo mysql_error();
    else
    {
      while ($post = mysql_fetch_assoc($chat_mysql))
      {
	
	  $posts[] = $post;
      }
    }
    return $posts;
  }
  
  public function save_post($message, $chat_id, $channel, $type, $user, $color, $time)
  {
    $this->connect();
      
    $save_message_mysql = mysql_query("INSERT INTO sipac_entries (user, message, type, time, channel, chat_id) VALUES('" . mysql_real_escape_string($user) . "', '" . mysql_real_escape_string($message) . "','$type', '$time', '" . mysql_real_escape_string($channel) . "', '$chat_id')");
    if ($save_message_mysql == false)
      return mysql_error();
    else
		return $save_message_mysql;
  }
  
  public function get_all_users($channel,$chat_id)
  {
    //get all users in the chat
    $users_mysql = mysql_query("SELECT * FROM sipac_users WHERE chat_id LIKE '".mysql_real_escape_string($chat_id)."' AND channel LIKE '".mysql_real_escape_string($channel)."'");
    
    $users = array();
    
    if ($users_mysql == false)
      echo mysql_error();
    else
    {
      while ($user = mysql_fetch_assoc($users_mysql))
      {
	$users[] = $user;
      }
    }
    return $users;
  }
  
  public function get_user($nickname, $channel, $chat_id)
  {
    //get all values of the user with the given nickname
    $user_info = mysql_query("SELECT * FROM sipac_users WHERE name LIKE '".mysql_real_escape_string($nickname)."' AND channel LIKE '".mysql_real_escape_string($channel)."' AND chat_id LIKE '".mysql_real_escape_string($chat_id)."'");
    if ($user_info == false)
      echo mysql_error();
    else
      return mysql_fetch_assoc($user_info);
  }
  
  public function update_user($nickname, $channel, $chat_id, $time, $is_writing, $is_afk, $user_info, $user_style)
  {
	$is_writing =  $is_writing == "true" ? 1 : 0;
    //update the user entry with all new values
    $update_user_mysql = mysql_query("UPDATE sipac_users SET online = '" . $time . "', afk = '" . $is_afk ."', writing = '$is_writing', style = '" . $user_style . "', info = '". mysql_real_escape_string($user_info)."' WHERE name = '" . mysql_real_escape_string($nickname) . "' AND channel = '" . mysql_real_escape_string($channel) . "' AND chat_id = '" . mysql_real_escape_string($chat_id) . "'");
    
    return $update_user_mysql;
  }

  public function save_user($nickname, $channel, $user_info, $user_style, $user_ip, $chat_id)
  {
    //variables to add later
    $is_afk = "";
    
    //save the user with all given values
    $add_user_mysql = mysql_query("INSERT INTO sipac_users (name, info, style, afk, writing, ip, online, channel, chat_id) VALUES ('" . mysql_real_escape_string($nickname) . "', '" . mysql_real_escape_string($user_info) . "', '" . $user_style . "', '" . $is_afk . "', 'false', '" . $user_ip . "', '" . time() . "', '" . mysql_real_escape_string($channel) . "', '$chat_id')");
  }
  
  public function delete_user($nickname, $channel, $chat_id)
  {
  
  
    $delete_user = mysql_query("DELETE FROM sipac_users WHERE name LIKE '" . mysql_real_escape_string($nickname) . "' AND channel LIKE '".mysql_real_escape_string($channel)."' AND chat_id LIKE '".mysql_real_escape_string($chat_id)."'");
  }
  
  public function add_task($task, $user, $channel, $chat_id)
  {
    if ($user === false)
      $user = $this->nickname;
      
	$count = mysql_query("SELECT * from sipac_users WHERE  name LIKE '".mysql_real_escape_string($user)."' AND channel LIKE '".mysql_real_escape_string($channel)."' AND chat_id LIKE '".mysql_real_escape_string($chat_id)."'");
	if (mysql_num_rows($count) > 0)
	{
		$add_task = mysql_query("UPDATE sipac_users SET task = '".mysql_real_escape_string($task)."' WHERE name LIKE '".mysql_real_escape_string($user)."' AND channel LIKE '".mysql_real_escape_string($channel)."' AND chat_id LIKE '".mysql_real_escape_string($chat_id)."'");
		return true;
    }
    else
		return false;
  }
  
	public function clean_up($channels, $max_messages, $chat_id)
	{
		foreach ($channels as $channel)
		{
			$remove_old_posts = mysql_query
			("DELETE from sipac_entries 
				WHERE id IN (select id from (select id from sipac_entries 
					WHERE chat_id LIKE '".mysql_real_escape_string($chat_id)."' AND channel LIKE '".mysql_real_escape_string($channel)."' ORDER BY id DESC LIMIT $max_messages, 1000) 
				x) ");
			
			if ($remove_old_posts == false)
			{
				echo mysql_error();
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
	$query = mysql_query($sql);
	if ($query == true)
		return true;
	else
		return mysql_error();
  }
  private function connect()
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
}



?>