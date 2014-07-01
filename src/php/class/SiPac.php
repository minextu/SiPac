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
	public $new_nickname;
	
	private $db_error;
	
	public function __construct($settings=false, $is_new=true, $chat_variables=false, $channels=false, $chat_num=false)
	{
		$this->init($settings, $is_new, $chat_variables, $channels, $chat_num);
	}
	
	//initiate the chat: load settings, check all variables
	public function init($settings, $is_new, $chat_variables, $channels, $chat_num)
	{
		$this->channel = new SiPac_Channel($this);
		
		/*
		if not already set,
		generate random id for this tab/window of the client
		(so the chat can be opened more than once in different tabs, but same with the same session)
		*/
		if ($chat_variables == false)
		{
			$this->client_num = "n" . time() . mt_rand(0, 10000);
			$this->id = $settings['chat_id'];
			$this->channel->active = false;
		}
		else
		{
			$this->client_num = $chat_variables['client_num'];
			$this->id = $chat_variables['chat_id'];
			$this->channel->active = $chat_variables['active_channel'];
		}
		
		
		//load all settings for this chat
		$this->settings = new SiPac_Settings($this->id);
		$this->settings->load($settings);
		
		//get the correct html path or load a custom
		$this->html_path = $this->settings->get("html_path");
		if ($this->html_path)
			$this->html_path = str_replace("//", "/", "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", realpath(dirname(__FILE__)."/../../..") . "/"));
		
		//check, if all mysql settings are given
		if ($this->settings->get('mysql_hostname') == false OR $this->settings->get('mysql_username') == false OR $this->settings->get('mysql_password') === false OR $this->settings->get('mysql_database') == false)
			die("Missing MySQL Settings!");
		else
		{
			//start mysql connection
			if ($this->settings->get('database_type') == "mysqli")
				$this->db = new SiPac_MySQL($this->settings->get('mysql_hostname'), $this->settings->get('mysql_username'), $this->settings->get('mysql_password'), $this->settings->get('mysql_database'), "mysqli");
			else if ($this->settings->get('database_type') == "mysql")
				$this->db = new SiPac_MySQL($this->settings->get('mysql_hostname'), $this->settings->get('mysql_username'), $this->settings->get('mysql_password'), $this->settings->get('mysql_database'), "mysql");
			else
				die("Unknown database type!");
			//check the connection
			$this->db_error = $this->db->check();
			if ($this->db_error !==  true)
				die($this->db_error);
		}
		
		if ($channels == false)
			$this->channel->add($this->settings->get('channels'));
		else
			$this->channel->add($channels, true);
		if ($is_new == true)
		{
			//check for the update.php
			if ($this->settings->get('debug') == false)
			{
				$update_file = dirname(__FILE__)."/../../update.php";	
				if (file_exists($update_file))
				{
					die("Please delete the update.php inside folder 'src' where SiPac is installed!");
				}
			}
			//check cache folder permissions
			$cache_folder = "/../../../cache/";	
			if (substr(decoct(fileperms(dirname(__FILE__).$cache_folder)), -3) != 777)
			{
				die("Wrong Permissions for the cache folder. Please change it to 777");
			}
			//check log folder permissions
			$log_folder = "/../../../log/";	
			if (substr(decoct(fileperms(dirname(__FILE__).$log_folder)), -3) != 777)
			{
				die("Wrong Permissions for the log folder. Please change it to 777");
			}
			
			//restore old channels
			$this->channel->restore_old();
			
			//clean the db (remove old messages)
			$this->db->clean_up($this->channel->ids, $this->settings->get('max_messages'), $this->id);
			
			//reset the Chat (reset a possible kick)
			$this->reset();
		}
		
		$this->channel->check();
		
		if ($chat_num ===false)
			$this->chat_num =  $GLOBALS['global_chat_num'] ;
		else
			$this->chat_num = $chat_num;

		$this->afk = new SiPac_Afk($this);
		
		$this->language = new SiPac_Language($this->settings);
		$this->language->load();
		
		$this->message= new SiPac_Message($this);
		
		$this->command = new SiPac_Command($this);
		$this->proxy = new SiPac_Proxy($this);

		$this->layout = new SiPac_Layout($this);
		$this->layout->load();
		
		$this->check_kick();
	}

	public function draw()
	{
		return $this->layout->draw();
	}
	
	private function reset()
	{
		unset($_SESSION['SiPac'][$this->id]['kick']);
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
				/*
				else if ($action_parts[0] == "message")
				$array['actions'][] = "message|" . $action_parts[1];
				else
					$chat_debug['warn'][] = "Action " . $action_parts[0] . " not defined!";*/
		
				$this->db->add_task("", $this->nickname, $channel, $this->id);
			}
		}
		return $array;
	}
	
	public function kick($client_text, $notification_text)
	{
		$_SESSION['SiPac'][$this->id]['kick'] = $client_text;
		foreach ($this->channel->ids as $channel)
		{
				$this->message->send($notification_text, $channel, 1);
				$this->db->delete_user($this->nickname, $channel, $this->id);
		}
	}
	
	private function check_kick()
	{
		if (!empty($_SESSION['SiPac'][$this->id]['kick']))
			die($this->language->translate($_SESSION['SiPac'][$this->id]['kick']));
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
					$this->message->send("<||rename-notification|".$_SESSION['SiPac'][$this->id]['nickname']." |".$this->nickname."||>", $channel, 1, 0);
					$this->db->delete_user($_SESSION['SiPac'][$this->id]['nickname'], $channel, $this->id);
					
					//add new user
					$ip = $_SERVER['REMOTE_ADDR'];
					$user_array = array("id" => "user", "name" => $this->nickname, "writing" => false, "afk" => false, "info" => $this->settings->get('user_infos'), "ip" => $ip, "channel" => $channel, "style" => $this->settings->get('user_color')."|||" );
					$user = new SiPac_User($user_array, $this);
					$user->save_user( false);
			}
		}
	
		$_SESSION['SiPac'][$this->id]['nickname'] = $this->nickname;
		$_SESSION['SiPac'][$this->id]['username_var'] = $this->settings->get('username_var');
		
		$this->db->update_nickname($this->nickname);
	}
}

?>