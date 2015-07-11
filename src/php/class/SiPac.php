<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2015 Jan Houben

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
$GLOBALS['global_chat_num'] = 0;
class SiPac_Chat
{
	//define all variables
	public $client_num;
	public $id;
	public $chat_num;

	public $nickname;
	public $afk;
	public $is_writing = false;
	public $is_kicked = false;
	public $new_nickname;
	public $is_new = false;
	
	private $db_error;
	
	public function __construct($settings=false, $chat_variables=false, $chat_num=false)
	{
		$this->init($settings, $chat_variables, $chat_num);
	}
	
	//initiate the chat: load settings, check all variables
	public function init($settings, $chat_variables, $chat_num)
	{
		$this->channel = new SiPac_Channel();
		$this->debug = new SiPac_Debug();
		$this->settings = new SiPac_Settings();
		
		if ($chat_variables !== false)
		{
			$this->is_new = false;
			$chat_variables = $this->get_parameters($chat_variables);
		}
		else
		{
			$this->is_new = true;
			$this->set_default_parameters($settings);
		}
			

			
		
		$this->ip = $_SERVER['REMOTE_ADDR'];
		
		//get the correct html path or load a custom
		$this->html_path = $this->settings->get("html_path");
		if ($this->html_path)
			$this->html_path = str_replace("//", "/", "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", realpath(dirname(__FILE__)."/../../..") . "/"));
		
		//check, if all mysql settings are given
		if ($this->settings->get('mysql_hostname') == false OR $this->settings->get('mysql_username') == false OR $this->settings->get('mysql_password') === false OR $this->settings->get('mysql_database') == false)
		{
			$this->debug->error("Missing MySQL Settings!");
			return false;
		}
		else
		{
			//start mysql connection
			if ($this->settings->get('database_type') == "mysqli")
				$this->db = new SiPac_MySQL($this->settings->get('mysql_hostname'), $this->settings->get('mysql_username'), $this->settings->get('mysql_password'), $this->settings->get('mysql_database'), "mysqli");
			else if ($this->settings->get('database_type') == "mysql")
				$this->db = new SiPac_MySQL($this->settings->get('mysql_hostname'), $this->settings->get('mysql_username'), $this->settings->get('mysql_password'), $this->settings->get('mysql_database'), "mysql");
			else
			{
				$this->debug->error("Unknown database type!");
				return false;
			}
			//check the connection
			$this->db_error = $this->db->check();
			if ($this->db_error !==  true)
			{
				$this->debug->error($this->db_error);
				return false;
			}
		}
		$this->message= new SiPac_Message($this);
		
		//obtain a nickname or load the old
		$this->check_name();
		
		if ($this->is_new == true)
		{
			//check for the update.php
			if ($this->settings->get('development') == false)
			{
				$update_file = dirname(__FILE__)."/../../update.php";	
				if (file_exists($update_file))
				{
					$this->debug->add("Please delete the update.php inside folder 'src' where SiPac is installed!", 1);
				}
			}
			//check cache folder permissions
			$cache_folder = "/../../../cache/";	
			if (!is_dir(dirname(__FILE__).$cache_folder))
				$this->debug->error("The cache folder is missing!");
			if (substr(decoct(fileperms(dirname(__FILE__).$cache_folder)), -3) != 777)
				$this->debug->error("Wrong Permissions for the cache folder. Please change it to 777", 1);
			//check log folder permissions
			$log_folder = "/../../../log/";	
			if (substr(decoct(fileperms(dirname(__FILE__).$log_folder)), -3) != 777)
				$this->debug->add("Wrong Permissions for the log folder. Please change it to 777", 1);
			
			//restore old channels
			$this->channel->restore_old();
			
			//clean the db (remove old messages)
			$db_response = $this->db->clean_up($this->channel->ids, $this->settings->get('max_messages'), $this->id);
			if ($db_response !== true)
					$this->debug->add("Failed to clean up the db (response: ".$db_response.";)", 0);
			
			//reset the Chat (reset the userlist, or a possible kick)
			$this->reset();
		}
		
		$this->channel->check($this);
		
		if ($chat_num ===false)
			$this->chat_num =  $GLOBALS['global_chat_num'] ;
		else
			$this->chat_num = $chat_num;
		
		$this->afk = new SiPac_Afk($this);
		
		$this->language = new SiPac_Language($this->settings, $this->debug);
		$this->language->load();
		
		$this->command = new SiPac_Command($this);
		$this->proxy = new SiPac_Proxy($this);

		$this->layout = new SiPac_Layout($this);
		$this->layout->load();
			
		if ($this->check_kick() == true)
			return false;
		else 	if ($this->is_new == false AND $this->check_ban() == true)
			return false;
	}

	private function get_parameters($parameters)
	{
		$this->id = $parameters['id'];
		
		$this->init_important_classes(false);
		
		//get unchanged parameters
		if ($this->settings->get('last_parameters') !== false)
		{
			foreach ($this->settings->get('last_parameters') as $name => $value)
			{
				if (!isset($parameters[$name]))
				{
					$parameters[$name] = $value;
				}
			}
		}
		
		if (!isset($parameters['channels']) OR !isset($parameters['active_channel']) OR !isset($parameters['writing']))
			$this->debug->error("Parameters missing. Maybe you were offline too long. Please try to reload!", true);
			
		$this->channel->add_list($parameters['channels']);
		$this->client_num = $parameters['client'];
		$this->channel->active = $parameters['active_channel'];
		$this->is_writing = $parameters['writing'];
		
		$this->settings->set('last_parameters', $parameters);
		
		return $parameters;
	}
	private function set_default_parameters($settings)
	{
		$this->id = $this->encode_id($settings['chat_id']);
		
		$this->init_important_classes($settings);
		
		$this->channel->add($this->settings->get('channels'));
		//generate a random id for this tab/window of the client (so the chat can be opened more than once in different tabs, but with the same session)
		$this->client_num = "n" . time() . mt_rand(0, 10000);
		$this->channel->active = false;
		$this->is_writing = false;
	}
	
	private function init_important_classes($settings)
	{
		$this->debug->init($this->id, $this->is_new);
		$this->settings->init($this->id, $this->client_num, $this->debug);
		
		//load all settings for this chat
		$settings_return = $this->settings->load($settings);
		if ($settings_return === false)
			return false;
	
		$this->channel->init($this->id, $this->settings);
	}

	public function encode_id($id=false)
	{
		if ($id === false)
			$id = $this->id;
		return "SiPac_".rtrim(strtr(base64_encode($id), '+/', '-_'), '=');
	}
	public function decode_id($id=false)
	{
		if ($id === false)
			$id = $this->id;
		 return base64_decode(str_replace("SiPac_", "", $id));
	}
	
	public function draw()
	{
		return $this->layout->draw();
	}
	
	private function reset()
	{
		unset($_SESSION['SiPac'][$this->id]['kick']);
		foreach ($this->channel->ids as $channel)
		{
			$_SESSION['SiPac'][$this->id]['userlist'][$this->client_num][$channel] = array();
		}
	}
	
	public function handle_userlist()
	{
		$this->userlist = new SiPac_Userlist($this);

		//delete all old user
		$this->userlist->delete_old_users();

		//save the user in the db
		$this->userlist->save_user();

		//get other users
		$userlist_answer = $this->userlist->get_users();

		$userlist_array = $userlist_answer;

		return $userlist_array;
	}
	public function get_tasks()
	{
		$array = array();
		//go through every channel, the user has joined
		foreach ($this->channel->ids as $channel)
		{
			//get user information
			$user_info = $this->db->get_user($this->nickname, $channel, $this->id);
			
			if (!empty($user_info['task']))
			{
				$task_parts = explode("|", $user_info['task']);
				if ($task_parts[0] == "new_name")
					$this->new_nickname = $task_parts[1];
				else if ($task_parts[0] == "join")
				{
					$array['tasks'][] = $user_info['task'];
					$_SESSION['SiPac'][$this->id]['channel_titles'][$task_parts[1]] = $task_parts[2];
				}
				else if ($task_parts[0] == "invite")
					$array['tasks'][] = $user_info['task'];
				else if ($task_parts[0] == "kick")
				{
					$array['tasks'][] = $user_info['task'];
					if (!empty($task_parts[1]))
					{
						$notification_text = "<||user-kicked-user-notification|".$user_info['name']."|".$task_parts[1]."|".$task_parts[2]."||>";
						$client_text = "<||you-were-kicked-by-user-text|".$task_parts[1]."|".$task_parts[2]."||>";
					}
					else
					{
						$notification_text = "<||user-kicked-notification|".$user_info['name']."|".$task_parts[2]."||>";
						$client_text = "<||you-were-kicked-text|".$task_parts[2]."||>";
					}
					$this->kick($client_text, $notification_text);
				}
				else if ($task_parts[0] == "ban")
				{
					$array['tasks'][] = $user_info['task'];
					if (!empty($task_parts[1]))
					{
						$notification_text = "<||user-banned-user-notification|".$user_info['name']."|".$task_parts[1]."|".$task_parts[3]."||>";
						$client_text = "<||you-were-banned-by-user-text|".$task_parts[1]."|".$task_parts[3]."||>";
					}
					else
					{
						$notification_text = "<||user-banned-notification|".$user_info['name']."|".$task_parts[3]."||>";
						$client_text = "<||you-were-banned-text|".$task_parts[3]."||>";
					}
					$this->ban($user_info['name'], $task_parts[2], $client_text, $notification_text, $task_parts[3]);
				}
				else if ($task_parts[0] != "terminate")
					$this->debug->add("Task '". $task_parts[0] . "' is not defined!", 1);
			
				if ($task_parts[0] != "ban")
					$this->db->add_task("", $this->nickname, $channel, $this->id);
			}
		}
		return $array;
	}
	
	public function kick($client_text, $notification_text, $delete_user=true)
	{
		$_SESSION['SiPac'][$this->id]['kick'] = $client_text;
		$this->is_kicked = true;
		
		foreach ($this->channel->ids as $channel)
		{
				$this->message->send($notification_text, $channel, 1);
				
				if ($delete_user == true)
				{
					$db_response = $this->db->delete_user($this->nickname, $channel, $this->id);
					if ($db_response !== true)
					{
						$this->debug->add("Failed to delete a user (response: ".$db_response.";name: ".$this->nickname.";channel: ".$channel.";chat_id:".$this->id.")", 0);
						break;
					}
				}
		}
	}
	
	public function ban($user, $time, $client_text, $notification_text, $reason)
	{
		$time = time() + ((int)$time * 60 * 60);
		
		$db_response = $this->db->ban_user($user, $time, $reason, $this->id);
		if ($db_response !== true)
			$this->debug->add("Failed to ban the user (response: ".$db_response.";)", 0);
			
		$this->kick($client_text, $notification_text, false);
	}
	
	private  function check_kick()
	{
		if (!empty($_SESSION['SiPac'][$this->id]['kick']))
		{
			$this->is_kicked = true;
			$this->debug->error($this->language->translate($_SESSION['SiPac'][$this->id]['kick']));
			return true;
		}
		else
			return false;
	}
	
	private function check_ban()
	{
		$check_ban = $this->db->check_ban($this->nickname, $this->ip, $this->id);
		if ($check_ban !== false)
		{
			$this->is_kicked = true;
			if (!is_array($check_ban))
				$this->debug->error("Failed check ban status (response: ".$check_ban.";)");
			else
				$this->debug->error($this->language->translate("<||you-were-banned-text|".$check_ban['reason']."||>"));
				
			return true;
		}
		else
			return false;

	}
	
	public function check_changes()
	{
		//get tasks (kick, ban, etc.)
		$task_answer = $this->get_tasks();

		$this->check_name();

		return $task_answer;
	}
	public function check_name()
	{
		if (!empty($this->new_nickname))
		{
			$this->nickname =$this->new_nickname;
			unset($this->new_nickname);
		}
		else if (!empty($_SESSION['SiPac'][$this->id]['nickname']) AND $this->settings->get('username_var') == $_SESSION['SiPac'][$this->id]['username_var'] )
			$this->nickname = $_SESSION['SiPac'][$this->id]['nickname'];
		else if ($this->settings->get('username_var') == "!!AUTO!!")
			$this->nickname = "Guest" . mt_rand(1, 1000);
		else
			$this->nickname = htmlspecialchars($this->settings->get('username_var'));

		if (!empty($_SESSION['SiPac'][$this->id]['nickname'] ) AND $_SESSION['SiPac'][$this->id]['nickname']  != $this->nickname)
		{
			foreach($this->channel->ids as $channel)
			{
				$db_response = $this->db->delete_user($_SESSION['SiPac'][$this->id]['nickname'], $channel, $this->id);
				if ($db_response !== true)
				{
					$this->debug->add("Failed to delete a user (response: ".$db_response.";name: ".$this->nickname.";channel: ".$channel.";chat_id:".$this->id.")", 0);
					break;
				}
				$this->message->send("<||rename-notification|".$_SESSION['SiPac'][$this->id]['nickname']." |".$this->nickname."||>", $channel, 1, 0);
									
				//add new user
				$user_array = array("id" => "user", "name" => $this->nickname, "writing" => false, "afk" => false, "info" => $this->settings->get('user_infos'), "ip" => $this->ip, "channel" => $channel, "style" => $this->settings->get('user_color')."|||" );
				$user = new SiPac_User($user_array, $this);
				$user->save_user( false);
			}
		}
	
		$_SESSION['SiPac'][$this->id]['nickname'] = $this->nickname;
		$_SESSION['SiPac'][$this->id]['username_var'] = $this->settings->get('username_var');
		
		$this->db->update_nickname($this->nickname);
	}
	
	//terminate the chat, when the user closes the chat
	public function terminate()
	{
		foreach ($this->channel->ids as $channel)
		{
			$this->db->add_task("terminate", $this->nickname, $channel, $this->id);
		}
	}
}

?>