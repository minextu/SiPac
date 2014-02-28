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
 class Chat_Userlist
 {
  private $chat;
  private $users;
  
  public function __construct($chat)
  {
    $this->chat = $chat;
  }
  public function save_user()
  {
   //go through every channel, the user has joined
    foreach ($this->chat->channels as $channel)
    {
      //try to get user information
      $user_info = $this->chat->db->get_user($this->chat->nickname, $channel, $this->chat->id);
      
      //if no infomation by this user are available
      if (empty($user_info))
      {
      	//save the user
      	$ip = $_SERVER['REMOTE_ADDR'];
		$user_array = array("id" => "user", "name" => $this->chat->nickname, "writing" => false, "afk" => false, "info" => "", "ip" => $ip);
		$user = new Chat_User($user_array, $this->chat);
		
		$user->save_user($channel, true);
      }
      else //if the user is already in the db, just update the information
	$this->chat->db->update_user($this->chat->nickname, $channel, $this->chat->id, time(), $this->chat->is_writing);
	
      unset($user_info);
    }
  }
  public function get_tasks()
  {
   //go through every channel, the user has joined
    foreach ($this->chat->channels as $channel)
    {
      //get user information
      $user_info = $this->chat->db->get_user($this->chat->nickname, $channel, $this->chat->id);
      
      if (!empty($user_info['action']))
      {
	$action_parts = explode("|", $user_info['action']);
	if ($action_parts[0] == "new_name")
	  $this->chat->new_nickname = $action_parts[1];
	/*
	else if ($action_parts[0] == "kick")
	{
	  $array['actions'][] = "kick|<||t23|" . $action_parts[1] . "||>";
	  if (!empty($action_parts[2]))
	    save_message("<||t24|" . $get_user->name . "|" . $action_parts[1] . "|" . $action_parts[2] . "||>", $get_user->channel, 1); //%1 was kicked by %3. Reason: %2
	  else
	      save_message("<||t25|" . $get_user->name . "|" . $action_parts[1] . "||>", $get_user->channel, 1); //%1 was kicked. Reason: %2
	  $delete_user                   = mysql_query("DELETE FROM chat_users WHERE id LIKE '" . $get_user->id . "'");
	  $_SESSION[$chat_id]['is_kick'] = true;
	  $chat_no_save_user             = true;
	}
	else if ($action_parts[0] == "message")
	  $array['actions'][] = "message|" . $action_parts[1];
	else if ($action_parts[0] == "join")
	  $array['actions'][] = "join|" . $action_parts[1];
	else
	    $chat_debug['warn'][] = "Action " . $action_parts[0] . " not defined!";*/
	      
	      
	$this->chat->db->add_task("", $this->chat->nickname, $channel, $this->chat->id);
      }
    }
  }
  public function get_users()
  {
    //create the user_array
    $user_array = array();
    
    //if the userlist array is not already there, create it
    if (!isset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num]))
      $_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num] = array();
    
    //search for users in every channel the user is online
    foreach ($this->chat->channels as $channel)
    {
      //get all users
      $users = $this->chat->db->get_all_users($channel, $this->chat->id);
      foreach($users as $user)
      {
	//if the last connection is too long ago
      if (time() - $user['last_time'] > $this->chat->settings['max_ping_remove'])
	{
	  //delete the user
	  $this->chat->db->delete_user($user['name'], $user['channel'], $this->chat->id);
	  //save a message, that the user has left
	  $this->chat->send_message("<||user-left-notification|".$user['name']. "||>", $user['channel'], 1, 0, $user['last_time']);
	  
	  continue;
	}
	$this->users[$user['id']] = new Chat_User($user, $this->chat);
	
	//add the status, if a user is writing
	$user_array[$channel]['user_writing']['id'][] = $user['id'];
	$user_array[$channel]['user_writing']['status'][] = $this->users[$user['id']]->is_writing;
	
	$user_array[$channel]['users'][$user['id']] = $user['name'];
	
	//generate the html code of the user
	$user_html = $this->chat->translate($this->users[$user['id']]->generate_html());
	
	//if this user isn't in the user array session, he also isn't on the client's window
	if (!isset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$user['id']]))
	{
	  //javascript should add the user
	  $user_array[$channel]['add_user'][] = $user_html;
	  $user_array[$channel]['add_user_id'][] = $user['id']; 
	  //save the user to the session user array 
	  $_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$user['id']] = $user_html;
	}
	//if the the html code has changed
	else if ($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$user['id']] != $user_html)
	{
	  //also change it with javascript
	  $user_array[$channel]['change_user'][] = $user_html;
	  $user_array[$channel]['change_user_id'][] = $user['id'];
	  $_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$user['id']] = $user_html;
	}
	
      }
      
      foreach ($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num] as $id => $user)
      {
	//if a user isn't in the db anymore but on the client
	if (!isset($this->users[$id]))
	{
	  //delete him with javascript
	  $user_array[$channel]['delete_user'][] = $id;
	  unset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$id]);
	}
      }
    }
    return $user_array;
  }
 }
 
 
 
 
 ?>
