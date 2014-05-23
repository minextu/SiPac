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
class SiPac_Userlist
{
	private $chat;
	public $users;
  
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	public function save_user()
	{
		//go through every channel, the user has joined
		foreach ($this->chat->channel_ids as $channel)
		{
			//try to get user information
			$user_info = $this->chat->db->get_user($this->chat->nickname, $channel, $this->chat->id);
      
			//if no infomation by this user are available
			if (empty($user_info))
			{
				//save the user
				$ip = $_SERVER['REMOTE_ADDR'];
				$user_array = array("id" => "user", "name" => $this->chat->nickname, "writing" => false, "afk" => $this->chat->settings['start_as_afk'], "info" => $this->chat->settings['user_infos'], "ip" => $ip, "channel" => $channel);
				$user = new SiPac_User($user_array, $this->chat);
		
				$user->save_user(true);
			}
			else //if the user is already in the db, just update the information
			{
				$ip = $_SERVER['REMOTE_ADDR'];
				$user_array = array("id" => "user", "name" => $this->chat->nickname, "writing" => $this->chat->is_writing, "afk" => $this->chat->afk, "info" => $this->chat->settings['user_infos'], "ip" => $ip, "channel" => $channel);
				$user = new SiPac_User($user_array, $this->chat);
	
				$user->update_user();
			}
	
			unset($user_info);
		}
	}
	public function delete_old_users()
	{
		//search for users in every channel the user is online
		foreach ($this->chat->channel_ids as $channel)
		{
			//get all users
			$users = $this->chat->db->get_all_users($channel, $this->chat->id);
			foreach($users as $user)
			{
				//if the last connection is too long ago
				if (time() - $user['online'] > $this->chat->settings['max_ping_remove'])
				{
					//delete the user
					$this->chat->db->delete_user($user['name'], $user['channel'], $this->chat->id);
					//save a message, that the user has left
					$this->chat->send_message("<||user-left-notification|".$user['name']. "||>", $user['channel'], 1, 0, $user['online']);
				}
			}
		}
	}
	public function get_users()
	{
		//create the user_array
		$user_array = array();
		
		$user_class = array();
		
		//if the userlist array is not already there, create it
		if (!isset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num]))
			$_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num] = array();
    
		//search for users in every channel the user is online
		foreach ($this->chat->channel_ids as $channel)
		{
			//get all users
			$users = $this->chat->db->get_all_users($channel, $this->chat->id);
			foreach($users as $user)
			{
				$user_class[$user['id']] = new SiPac_User($user, $this->chat);
				$this->users[$channel][] = $user_class[$user['id']];
	
				//add the status, if a user is writing
				$user_array[$channel]['user_writing']['id'][] = $user['id'];
				$user_array[$channel]['user_writing']['status'][] = $user_class[$user['id']]->is_writing;
	
				$user_array[$channel]['users'][$user['id']] = $user['name'];
	
				//generate the html code of the user
				$user_html = $this->chat->translate($user_class[$user['id']]->generate_html());
	
				//if this user isn't in the user array session, he also isn't on the client's window
				if (!isset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']]))
				{
					//javascript should add the user
					$user_array[$channel]['add_user'][] = $user_html;
					$user_array[$channel]['add_user_id'][] = $user['id']; 
					//save the user to the session user array 
					$_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']] = $user_html;
				}
				//if the the html code has changed
				else if ($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']] != $user_html)
				{
					//also change it with javascript
					$user_array[$channel]['change_user'][] = $user_html;
					$user_array[$channel]['change_user_id'][] = $user['id'];
					$_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']] = $user_html;
				}
			}
			foreach ($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel] as $id => $user)
			{
				//if a user isn't in the db anymore but on the client
				if (!isset($user_class[$id]))
				{	
					//delete him with javascript
					$user_array[$channel]['delete_user'][] = $id;
					unset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$id]);
				}
			}
		}
		return $user_array;
	}
}

 ?>
