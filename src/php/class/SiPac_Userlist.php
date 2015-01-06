<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2014 Jan Houben

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
	public $users_info;
  
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	public function save_user()
	{
		//go through every channel, the user has joined
		foreach ($this->chat->channel->ids as $channel)
		{
			//try to get user information
			$user_info = $this->chat->db->get_user($this->chat->nickname, $channel, $this->chat->id);
      
			if (!is_array($user_info) AND !empty($user_info))
			{
				$this->chat->debug->add("Failed to get a user (response: ".$user_info.";user: ".$this->chat->nickname.";channel: ".$channel.";id:".$this->chat->id.")", 0);
				break;
			}
      
			//if no infomation by this user are available
			if (empty($user_info))
			{
				//save the user
				$ip = $_SERVER['REMOTE_ADDR'];
				$user_array = array("id" => "user", "name" => $this->chat->nickname, "writing" => false, "afk" => $this->chat->settings->get('start_as_afk'), "info" => $this->chat->settings->get('user_infos'), "ip" => $ip, "channel" => $channel,
												"style" => $this->chat->settings->get('user_color')."|||");
				$user = new SiPac_User($user_array, $this->chat);
		
				$user->save_user(true);
				$this->chat->debug->add("added yourself to the db (name: '".$user_array['name']."'; id: ".$user_array['id'].")", 2, $user_array['channel']);
			}
			else //if the user is already in the db, just update the information
			{
				if ($channel == $this->chat->channel->active)
					$is_writing = $this->chat->is_writing;
				else
					$is_writing = false;
					
				$ip = $_SERVER['REMOTE_ADDR'];
				$user_array = array("id" => "user", "name" => $this->chat->nickname, "writing" => $is_writing, "afk" => $this->chat->afk->status, "info" => $this->chat->settings->get('user_infos'), "ip" => $ip, "channel" => $channel,
												"style" => $this->chat->settings->get('user_color')."|||");
				$user = new SiPac_User($user_array, $this->chat);
	
				$user->update_user();
			}
	
			unset($user_info);
		}
	}
	public function delete_old_users()
	{
		//search for users in every channel the user is online
		foreach ($this->chat->channel->ids as $channel)
		{
			//get all users
			$users = $this->chat->db->get_all_users($channel, $this->chat->id);
			if (!is_array($users))
			{
				$this->chat->debug->add("Failed to get all users (response: ".$users.";channel: ".$channel.";chat_id:".$this->chat->id.")", 0);
				break;
			}
			
			foreach($users as $user)
			{
				//if the last connection is too long ago
				if (time() - $user['online'] > $this->chat->settings->get('max_ping_remove'))
				{
					$values = array("user" => $user['name'], "channel" => $user['channel'], "last_update" => $user['online']);	
					if ($this->chat->proxy->check_custom_functions($values, "user_left") == true)
					{
						//delete the user
						$db_response = $this->chat->db->delete_user($user['name'], $user['channel'], $this->chat->id);
						if ($db_response !== true)
						{
							$this->chat->debug->add("Failed to delete a user (response: ".$db_response.";user: ".$user['name'].";channel: ".$user['channel'].";chat_id:".$this->chat->id.")", 0);
							break;
						}
						
						//save a message, that the user has left
						if ($user['info'] != "banned")
							$this->chat->message->send("<||user-left-notification|".$user['name']. "||>", $user['channel'], 1, 0, $user['online']);
						
						$this->chat->debug->add("deleted user '".$user['name']."' from db (id: ".$user['id'].")", 2, $user['channel']);
					}
				}
			}
		}
	}
	public function get_users()
	{
		//create the user_array
		$user_array = array();
		
		$user_class = array();
    
		//search for users in every channel the user is online
		foreach ($this->chat->channel->ids as $channel)
		{
			//get all users
			$users = $this->chat->db->get_all_users($channel, $this->chat->id);
			if (!is_array($users))
			{
				$this->chat->debug->add("Failed to get all users (response: ".$users.";channel: ".$channel.";id:".$this->chat->id.")", 0);
				break;
			}
			
			foreach($users as $user)
			{
				if ($user['task'] != "banned")
				{
					$user_class[$user['id']] = new SiPac_User($user, $this->chat);
					$this->users[$channel][] = $user_class[$user['id']];
					$this->users_info[$user['id']] = array("name" => $user['name'], "afk" => $user['afk']); 
					//add the status, if a user is writing
					$user_array[$channel]['user_writing']['id'][] = $user['id'];
					$user_array[$channel]['user_writing']['status'][] = $user_class[$user['id']]->is_writing;
		
					$user_array[$channel]['users'][$user['id']] = $user['name'];
		
					//generate the html code of the user
					$user_html = $this->chat->language->translate($user_class[$user['id']]->generate_html());
		
					//if this user isn't in the user array session, he also isn't on the client's window
					if (!isset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']]))
					{
						//javascript should add the user
						$user_array[$channel]['add_user'][] = $user_html;
						$user_array[$channel]['add_user_id'][] = $user['id']; 
						//save the user to the session user array 
						$_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']] = $user_html;
						$this->chat->debug->add("Add user '".$user['name']."' to userlist (id: ".$user['id'].")", 3, $user['channel']);
					}
					//if the the html code has changed
					else if ($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']] != $user_html)
					{
						//also change it with javascript
						$user_array[$channel]['change_user'][] = $user_html;
						$user_array[$channel]['change_user_id'][] = $user['id'];
						$_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$user['id']] = $user_html;
						$this->chat->debug->add("Changed user '".$user['name']."' in userlist (id: ".$user['id'].")", 3, $user['channel']);
					}
				}
			}
			
			if (isset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel]))
			{
				foreach ($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel] as $id => $user)
				{
					//if a user isn't in the db anymore but on the client
					if (!isset($user_class[$id]))
					{	
						//delete him with javascript
						$user_array[$channel]['delete_user'][] = $id;
						unset($_SESSION['SiPac'][$this->chat->id]['userlist'][$this->chat->client_num][$channel][$id]);
						$this->chat->debug->add("Deleted user in userlist (id: ".$id.")", 3, $channel);
					}
				}
			}
		}
		return $user_array;
	}
}

 ?>
