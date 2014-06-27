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
		if (empty($this->settings->get('mysql_hostname')) OR empty($this->settings->get('mysql_username')) OR $this->settings->get('mysql_password') === false OR empty($this->settings->get('mysql_database')))
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
		}
		$this->channel->check();
		
		if ($chat_num ===false)
			$this->chat_num =  $GLOBALS['global_chat_num'] ;
		else
			$this->chat_num = $chat_num;

		$this->afk = new SiPac_Afk($this);
		
		$this->language = new SiPac_Language($this->settings);
		$this->language->load();

		$this->command = new SiPac_Command($this);
		$this->proxy = new SiPac_Proxy($this);
		
		$this->layout = new SiPac_Layout($this);
		$this->layout->load();
	}

	public function draw()
	{
		return $this->layout->draw();
	}

	public function get_posts($last_id)
	{
		//load all posts
		$db_response = $this->db->get_posts($this->id, $this->channel->ids);

		$new_posts = array();
		$new_post_users = array();
		$new_post_messages = array();

		$updated_last_id = $last_id;

		foreach ($db_response as $post)
		{
			//check if the post is new
			if ($post['id'] > $last_id OR in_array($post['channel'], $this->channel->new))
			{
				$post_array = array("message"=>$post['message'], "type"=>$post['type'], "channel"=>$post['channel'],"user"=>$post['user'],"time"=>$post['time'], "style" => $post['style']);
				$post_array = $this->proxy->check($post_array, "client");
				
				$post_user_name = $post_array['user'];
				if ($post_array['type'] == 0) //normal post
				{
					$post_user = $post_array['user'];
			
					if ($post_array['user'] == $this->nickname)
						$post_type = "own";
					else
						$post_type = "others";
				}
				else if ($post_array['type'] == 1) //notify
				{
					$post_user = "";
					$post_type = "notify";
					$post_array['message'] = $this->language->translate($post_array['message']);
					
					if (isset($this->layout->arr['notify_user']))
						$post_array['message'] =   preg_replace('#\[user\](.*)\[/user\]#isU', str_replace("!!USER!!", "$1", $this->layout->arr['notify_user']), $post_array['message']);
					else
						$post_array['message'] =   preg_replace('#\[user\](.*)\[/user\]#isU', "$1", $post_array['message']);
				}
			
				if ($post_type == "notify")
					$post_html = $this->layout->arr['notify_html'];
				else
					$post_html = $this->layout->arr['post_html'];
					
				$post_html = str_replace("!!USER!!", $post_user, $post_html);
				$post_html = str_replace("!!MESSAGE!!", $post_array['message'], $post_html);
				$post_html = str_replace("!!TYPE!!", $post_type, $post_html);
				
				$message_style = explode("|||", $post_array['style']);
				$color = $message_style[0];
				
				$post_html = str_replace("!!USER_COLOR!!", $color, $post_html);
				
				if ($this->settings->get('time_24_hours'))
					$date = date("H:i", $post_array['time']);
				else
					$date = date("h:i A", $post_array['time']);
				
				if (date("d.m.Y", $post_array['time']) != date("d.m.Y", time()))
					$date = date($this->settings->get('date_format'), $post_array['time']). " " . $date;
				
				$post_html = str_replace("!!TIME!!", $date, $post_html);
				
				
				$new_posts[$post_array['channel']][] = $post_html;
				$new_post_users[$post_array['channel']][] = $post_user_name;
				$new_post_messages[$post_array['channel']][] = $post_array['message'];
			}
			//save the highest id
			$updated_last_id = $post['id'];
		}
		
		$last_id = $updated_last_id;
		//return all new posts and the highest id
		return array('posts' => $new_posts, 'post_users' => $new_post_users, 'post_messages' => $new_post_messages, 'last_id' => $last_id, 'username' => $this->nickname);
	}
	public function send_message($message, $channel, $type = 0, $user = 0, $time = 0)
	{
		//remove uneeded space
		$message = trim($message);
		
		if ($type == 0)
			$message =  htmlspecialchars($message);
			
		if (empty($user))
			$user = $this->nickname;
	
		if (empty($time))
			$time = time();
		
		if (!empty($message))
		{
			$command_return = $this->command->check($message) ;
			if ($command_return !== false)
			{
				return $command_return;
			}
			else
			{
				$message_style = $this->settings->get('user_color')."|||";
				
				$post_array = array("message"=>$message, "type"=>$type, "channel"=>$channel,"user"=>$user, "style" => $message_style, "time"=>$time);
				$post_array = $this->proxy->check($post_array, "server");
				
				$db_response = $this->db->save_post($post_array['message'], $this->id, $post_array['channel'], $post_array['type'], $post_array['user'], $post_array['style'], $post_array['time']);
				if ($db_response !== true)
					return array('info_type' => "error", 'info_text' => $db_response);
				else
					return array();
			}
		}
		else
			return array('info_type' => "error", 'info_text' => $this->language->translate("<||message-empty-text||>"));
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
					$array['tasks'][] = $user_info['task'];
				else if ($task_parts[0] == "invite")
					$array['tasks'][] = $user_info['task'];
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
				else
					$chat_debug['warn'][] = "Action " . $action_parts[0] . " not defined!";*/
		
		
				$this->db->add_task("", $this->nickname, $channel, $this->id);
			}
		}
		return $array;
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
					$this->send_message("<||rename-notification|".$_SESSION['SiPac'][$this->id]['nickname']." |".$this->nickname."||>", $channel, 1, 0);
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
