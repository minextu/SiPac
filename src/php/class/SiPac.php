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
	use SiPac_layout;
	use SiPac_language;
	use SiPac_command;
	use SiPac_proxy;
	
	//define all private variables
	public $client_num;
	public $id;
	public $chat_num;
	public $settings = array();
	public $channels;
	public $html_path;
	public $nickname;
	public $new_nickname;
	private $html_code;
	public $layout;
	
	private $db_error;
	private $text;
	
	public $active_channel;
	
	private $js_chat;
	public $is_writing = false;
	public $is_mobile = false;
	
	public function __construct($settings=false, $is_new=true, $chat_variables=false, $channels=false, $chat_num=false)
	{
		$this->init($settings, $is_new, $chat_variables, $channels, $chat_num);
	}
	
	//initiate the chat: load settings, check all variables
	public function init($settings, $is_new, $chat_variables, $channels, $chat_num)
	{
		/*
		if not already set,
		generate random id for this tab/window of the client
		(so the chat can be opened more than once in different tabs, but same with the same session)
		*/
		
		if ($chat_variables == false)
		{
			$this->client_num = "n" . time() . mt_rand(0, 10000);
			$id = false;
			$this->active_channel = false;
		}
		else
		{
			$this->client_num = $chat_variables['client_num'];
			$id = $chat_variables['chat_id'];
			$this->active_channel = $chat_variables['active_channel'];
		}
		
		//load all settings for this chat
		$this->load_settings($settings, $id);
		
		//check, if all mysql settings are given
		if (empty($this->settings['mysql_hostname']) OR empty($this->settings['mysql_username']) OR !isset($this->settings['mysql_password']) OR empty($this->settings['mysql_database']))
		die("Missing MySQL Settings!");
		else
		{
			//start mysql connection
			$this->db = new SiPac_MySQL($this->settings['mysql_hostname'], $this->settings['mysql_username'], $this->settings['mysql_password'], $this->settings['mysql_database']);
		
			//check the connection
			$this->db_error = $this->db->check();
			if ($this->db_error !==  true)
				die($this->db_error);
		}
		if ($this->channels == false)
			$this->channels = $this->settings['channels'];
		else
			$this->channels = $channels;
		
		if ($chat_num ===false)
			$this->chat_num =  $GLOBALS['global_chat_num'] ;
		else
			$this->chat_num = $chat_num;
		
		$this->include_language();
		
		$this->include_layout();
    
  }
  public function get_posts($last_id)
  {
    //load all posts
    $db_response = $this->db->get_posts($this->id, $this->channels);
 
    $new_posts = array();
    $new_post_users = array();
    $new_post_messages = array();
    
    $updated_last_id = $last_id;
    
    foreach ($db_response as $post)
    {
      //check if the post is new
      if ($post['id'] > $last_id)
      {
		$post_array = array("message"=>$post['message'], "extra"=>$post['extra'], "channel"=>$post['channel'],"user"=>$post['user'],"time"=>$post['time']);
		$post_array = $this->check_proxy($post_array, "client");
		
      	$post_user_name = $post_array['user'];
		if ($post_array['extra'] == 0)
		{
			$post_user = $post_array['user'].": ";
	  
			if ($post_array['user'] == $this->nickname)
				$post_type = "own";
			else
				$post_type = "others";
		}
		else 
		{
			$post_user = "";
			$post_type = "notify";
			$post_array['message'] = $this->translate($post_array['message']);
		}
	
	if ($post_type == "notify")
		$post_html = $this->layout['notify_html'];
	else
		$post_html = $this->layout['post_html'];
		
	$post_html = str_replace("!!USER!!", $post_user, $post_html);
	$post_html = str_replace("!!MESSAGE!!", $post_array['message'], $post_html);
	$post_html = str_replace("!!TYPE!!", $post_type, $post_html);
	
	if ($this->settings['time_24_hours'])
          $date = date("H:i", $post_array['time']);
        else
          $date = date("h:i A", $post_array['time']);
        
        if (date("d.m.Y", $post_array['time']) != date("d.m.Y", time()))
          $date = date($this->settings['date_format'], $post_array['time']). " " . $date;
          
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
  public function send_message($message, $channel, $extra = 0, $user = 0, $time = 0)
  {
		//remove uneeded space
		$message = trim($message);
		
		if (empty($user))
			$user = $this->nickname;
	
		if (empty($time))
			$time = time();
		
		if (!empty($message))
		{
			$command_return = $this->check_command($message) ;
			if ($command_return !== false)
			{
				return $command_return;
			}
			else
			{
			
				$post_array = array("message"=>$message, "extra"=>$extra, "channel"=>$channel,"user"=>$user,"time"=>$time);
				$post_array = $this->check_proxy($post_array, "server");
				
				$db_response = $this->db->save_post($post_array['message'], $this->id, $post_array['channel'], $post_array['extra'], $post_array['user'], $post_array['time']);
				if ($db_response !== true)
					return array('info_type' => "error", 'info_text' => $db_response);
				else
					return array();
				}
		}
		else
			return array('info_type' => "error", 'info_text' => $this->translate("<||message-empty-text||>"));
  }
  
  public function handle_userlist()
  {
    $this->userlist = new SiPac_Userlist($this);
    //save the user in the db
    $this->userlist->save_user();
    
    //get tasks (kick, ban, etc.)
    $this->userlist->get_tasks();
    
    //get other users
    $userlist_answer = $this->userlist->get_users();
    
    return $userlist_answer;
  }
  public function check_changes()
  {
    $this->check_name();
    return array();
  }
  public function check_name()
  {
		if (!empty($this->new_nickname))
		{
		$this->nickname =$this->new_nickname;
		unset($this->new_nickname);
		}
		else if (!empty($_SESSION['SiPac'][$this->id]['nickname']) AND $this->settings['username_var'] == $_SESSION['SiPac'][$this->id]['username_var'] )
		$this->nickname = $_SESSION['SiPac'][$this->id]['nickname'];
		else if ($this->settings['username_var'] == "!!AUTO!!")
		$this->nickname = "Guest " . mt_rand(1, 1000);
		else
		$this->nickname = $this->settings['username_var'];
    
		if (!empty($_SESSION['SiPac'][$this->id]['nickname'] ) AND $_SESSION['SiPac'][$this->id]['nickname']  != $this->nickname)
		{
			foreach($this->channels as $channel)
			{
					$this->send_message("<||rename-notification|".$_SESSION['SiPac'][$this->id]['nickname']." |".$this->nickname."||>", $channel, 1, 0);
					$this->db->delete_user($_SESSION['SiPac'][$this->id]['nickname'], $channel, $this->id);
					
					//add new user
					$ip = $_SERVER['REMOTE_ADDR'];
					$user_array = array("id" => "user", "name" => $this->nickname, "writing" => false, "afk" => false, "info" => "", "ip" => $ip);
					$user = new SiPac_User($user_array, $this);
					$user->save_user($channel, false);
			}
		}
       
		$_SESSION['SiPac'][$this->id]['nickname'] = $this->nickname;
		$_SESSION['SiPac'][$this->id]['username_var'] = $this->settings['username_var'];;
		
		$this->db->update_nickname($this->nickname);
  }
  private function load_settings($settings=false, $id=false)
  {
    //get the chat id, either from the settings or the function variable $id
    if ($id !== false)
      $this->id = $id;
    else if ($settings !== false AND isset($settings['chat_id']))
      $this->id = $settings['chat_id'];
    else
      die("No chat id specified!");
    
    //if the settings are already given, load them
    if ($settings !== false)
      $this->settings = $settings;
    else if (isset($_SESSION['SiPac'][$this->id]['settings'])) //else load them from the php session (if set)
      $this->settings = $_SESSION['SiPac'][$this->id]['settings'];
    else
      die("No settings found!");
    
 
    
    $default_settings = return_default_settings();
    //if some settings are not set, load them from the default config
    foreach ($default_settings as $setting => $default)
    {
      if (!isset($this->settings[$setting]))
      {
	$this->settings[$setting]  = $default;
	//$chat_debug['all_once'][] = "Setting $setting is unused!";
      }
    }
    //save the settings in the session
    $_SESSION['SiPac'][$this->id]['settings'] = $this->settings;

    //get the correct html path or load a custom
    if ($this->settings['html_path'] == "!!AUTO!!")
      $this->html_path = str_replace("//", "/", "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", realpath(dirname(__FILE__)."/../../..") . "/"));
    else
      $this->html_path = $this->settings['html_path'];
    
  }
  
}




?>
