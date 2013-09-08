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
 
class Chat_MySQL
{
  private $host;
  private $db;
  private $user;
  private $pw;
  
  private $mysql_error;
  private $connected = false;
  
  public function __construct($host, $user, $pw, $db)
  {
    $this->host = $host;
    $this->db = $db;
    $this->user = $user;
    $this->pw = $pw;
  }
  public function check()
  {
    $this->mysql_error = $this->connect();
    return $this->mysql_error;
  }
  
  public function get_posts($chat_id)
  {
    $this->connect();
    $chat_mysql = mysql_query("SELECT * FROM chat_entries WHERE chat_id LIKE '".mysql_real_escape_string($chat_id)."' ORDER BY id ASC");
    
    $posts = array();
    while ($post = mysql_fetch_assoc($chat_mysql))
    {
      $posts[] = $post;
    }

    return $posts;
  }
  
  public function save_post($message, $chat_id, $channel, $extra, $user, $time)
  {
    $this->connect();
      
    $save_message_mysql = mysql_query("INSERT INTO chat_entries (user, message, extra, time, channel, chat_id) VALUES('" . mysql_real_escape_string($user) . "', '" . mysql_real_escape_string($message) . "','$extra', '$time', '" . mysql_real_escape_string($channel) . "', '$chat_id')");
    
    return $save_message_mysql;
  }
  
  public function get_all_users($chat_id)
  {
    //get all users in the chat
    $users_mysql = mysql_query("SELECT * FROM chat_users WHERE chat_id LIKE '".mysql_real_escape_string($chat_id)."'");
    
    $users = array();
    while ($user = mysql_fetch_assoc($users_mysql))
    {
      $users[] = $user;
    }
    return $users;
  }
  
  public function get_user($nickname, $chat_id)
  {
    //get all values of the user with the given nickname
    $user_info = mysql_query("SELECT * FROM chat_users WHERE name LIKE '".mysql_real_escape_string($nickname)."'");
    
    return mysql_fetch_assoc($user_info);
  }
  
  public function update_user($nickname, $chat_id, $time, $is_writing)
  {
    //variables to add later
    $is_afk = false;
    $user_info = "";
    $user_style = "";
    $channel = "";
    
    if ($is_writing == "true")
      $is_writing = 1;
    else
      $is_writing = 0;
      
    //update the user entry with all new values
    $update_user_mysql = mysql_query("UPDATE chat_users SET last_time = '" . $time . "', afk = '" . $is_afk . "', info = '" . mysql_real_escape_string($user_info) . "', writing = '" . mysql_real_escape_string($is_writing) . "', style = '" . $user_style . "' WHERE name = '" . mysql_real_escape_string($nickname) . "' AND chat_id = '" . mysql_real_escape_string($chat_id) . "'");
    
    
    
    return $update_user_mysql;
  }

  public function save_user($nickname, $chat_id)
  {
    //variables to add later
    $user_info = "";
    $user_style = "";
    $is_afk = "";
    $user_ip = "";
    $channel = "";
    
    //save the user with all given values
    $add_user_mysql = mysql_query("INSERT INTO chat_users (name, info, style, afk, writing, ip, last_time, channel, chat_id) VALUES ('" . mysql_real_escape_string($nickname) . "', '" . mysql_real_escape_string($user_info) . "', '" . $user_style . "', '" . $is_afk . "', 'false', '" . $user_ip . "', '" . time() . "', '" . mysql_real_escape_string($channel) . "', '$chat_id')");
  }
  
  public function delete_user($nickname, $chat_id)
  {
  
  
    $delete_user = mysql_query("DELETE FROM chat_users WHERE name LIKE '" . mysql_real_escape_string($nickname) . "' AND chat_id LIKE '".mysql_real_escape_string($chat_id)."'");
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
  }
}



?>