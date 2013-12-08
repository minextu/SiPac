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
class Chat
{
  //define all private variables
  public $client_num;
  public $id;
  public $chat_num;
  public $settings = array();
  public $channels;
  private $html_path;
  public $nickname;
  public $new_nickname;
  private $html_code;
  public $layout;
  
  private $db_error;
  
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
      $this->db = new Chat_MySQL($this->settings['mysql_hostname'], $this->settings['mysql_username'], $this->settings['mysql_password'], $this->settings['mysql_database']);
      
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
  //generate and return the html code for the chat
  public function draw()
  {   
    $css_file = str_replace("\n", " ", file_get_contents($this->layout['path']."/chat.css"));
    $id_css   = "#".$this->id;
    
    /* generate js function arguments, to start the chat*/
    $this->js_chat = "add_chat('".$this->html_path."','" . $this->settings['theme'] . "','".$this->id."', '".$this->client_num."',";
    $this->js_chat = $this->js_chat."new Array(";
    foreach ($this->settings['channels'] as $key => $channel)
    {
      if ($key != 0)
	$this->js_chat = $this->js_chat.",";
      $this->js_chat = $this->js_chat."\"$channel\"";  
    }
    $this->js_chat = $this->js_chat."), new Array(";
    /*foreach ($chat_text as $key => $text)
    {
      if ($key != 0)
	$this->js_chat = $this->js_chat.",";
      $this->js_chat = $this->js_chat."\"$text\"";
    }*/
    $this->js_chat = $this->js_chat."));";
    
    //save the html code of the layout
    $this->html_code = $this->layout['html'];
    
    //add the chat id to the div with the class 'chat_main'
    $this->html_code =str_replace('"chat_main"', '"chat_main" id="' . $this->id . '"', str_replace("'chat_main'", "'chat_main' id='".$this->id."'", $this->html_code));
    
    //load the chat.js
    $this->html_code = $this->html_code."<script class='sipac_script' type='text/javascript' src='".$this->html_path."src/javascript/chat.js'></script>";
    
    //load the layout css file via javascript
    $this->html_code = $this->html_code."
		<script class='sipac_script' type='text/javascript'>
		var style_obj = document.createElement('style');
		var text_obj = document.createTextNode('$css_file');
		style_obj.appendChild(text_obj);
		document.getElementById('".$this->id."').appendChild(style_obj);
		".
		//start the chat, by calling the add_chat function
		$this->js_chat;
		
    //load all javascript functions by the layout
		
    if (!empty($this->layout['javascript_functions']))
    {
      foreach ($this->layout['javascript_functions'] as $name => $function)
      {
	$this->html_code = $this->html_code."chat_objects[chat_objects.length-1].$name = $function;";
	if ($name == "layout_init")
	  $this->html_code = $this->html_code."chat_objects[chat_objects.length-1].$name();";
      }
    }
    $this->html_code = $this->html_code."</script>";

    $this->html_code = str_replace("!!NUM!!", $this->chat_num, $this->html_code);
    $this->html_code = str_replace("!!USER_NUM!!", "<span class='chat_user_num'>?</span>", $this->html_code);
    
    $GLOBALS['global_chat_num']  = $GLOBALS['global_chat_num'] + 1;
    
    return $this->html_code;
  }
  
  private function include_layout()
  {
    $is_mobile = check_mobile();
    
     $layout_path = dirname(__FILE__) . "/../../../themes/".$this->settings['theme']."_mobile";
     
    if ($is_mobile && is_dir($layout_path))
      $this->is_mobile = true;
    else
      $layout_path = dirname(__FILE__) . "/../../../themes/".$this->settings['theme'];
      
    require($layout_path."/layout.php");
    
    $layout_array['path'] = $layout_path;
    $layout_array['html'] = $chat_layout;
    $layout_array['user_html'] = $chat_layout_user_entry;
    $layout_array['post_html'] = $chat_layout_post_entry;
    $layout_array['javascript_functions'] = $chat_layout_functions;
    
    $this->layout = $layout_array;
  }
  private function include_language()
  {
    global $chat_text;
    $language_path = dirname(__FILE__)."/../../lang/";
    require($language_path.$this->settings['language'].".php");
  }
  public function get_posts($last_id)
  {
    //load all posts
    $db_response = $this->db->get_posts($this->id, $this->channels);
 
    $new_posts = array();
    $new_post_users = array();
    
    $updated_last_id = $last_id;
    
    foreach ($db_response as $post)
    {
      //check if the post is new
      if ($post['id'] > $last_id)
      {
	if ($post['extra'] == 0)
	{
	  $post_user = $post['user'].": ";
	  
	  if ($post['user'] == $this->nickname)
	    $post_type = "own";
	  else
	    $post_type = "others";
	}
	else 
	{
	  $post_user = "";
	  $post_type = "notify";
	}
	$post_html = $this->layout['post_html'];
	$post_html = str_replace("!!USER!!", $post_user, $post_html);
	$post_html = str_replace("!!MESSAGE!!", $post['message'], $post_html);
	$post_html = str_replace("!!TYPE!!", $post_type, $post_html);
	
	if ($this->settings['time_24_hours'])
          $date = date("H:i", $post['time']);
        else
          $date = date("h:i A", $post['time']);
        
        if (date("d.m.Y", $post['time']) != date("d.m.Y", time()))
          $date = date($this->settings['date_format'], $post['time']). " " . $date;
          
	$post_html = str_replace("!!TIME!!", $date, $post_html);
	  
	
	$new_posts[$post['channel']][] = $post_html;
	$new_post_users[$post['channel']][] = $post_user;
      }
      //save the highest id
      $updated_last_id = $post['id'];
    }
    $last_id = $updated_last_id;
    //return all new posts and the highest id
    return array('posts' => $new_posts, 'post_users' => $new_post_users, 'last_id' => $last_id, 'username' => $this->nickname);
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
	$db_response = $this->db->save_post($message, $this->id, $channel, $extra, $user, $time);
	if ($db_response !== true)
	  return array('info_type' => "error", 'info_text' => $db_response);
	else
	  return array();
      }
    }
    else
      return array('info_type' => "error", 'info_text' => "Nothing entered");
  }
  public function handle_userlist()
  {
    $this->userlist = new Chat_Userlist($this);
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
    else if (!empty($_SESSION['SiPac'][$this->id]['nickname']))
      $this->nickname = $_SESSION['SiPac'][$this->id]['nickname'];
    else if ($this->settings['username_var'] == "!!AUTO!!")
      $this->nickname = "Guest " . mt_rand(1, 1000);
    else
      $this->nickname = $this->settings['username_var'];
      
    if (!empty($_SESSION['SiPac'][$this->id]['nickname'] ) AND $_SESSION['SiPac'][$this->id]['nickname']  != $this->nickname)
    {
      foreach($this->channels as $channel)
      {
	$this->send_message($_SESSION['SiPac'][$this->id]['nickname'] . " is now ".$this->nickname, $channel, 1, 0);
	$this->db->delete_user($_SESSION['SiPac'][$this->id]['nickname'], $channel, $this->id);
	$this->db->save_user($this->nickname, $channel, $this->id);
       }
    }
       
    $_SESSION['SiPac'][$this->id]['nickname'] = $this->nickname;
    $this->db->update_nickname($this->nickname);
  }
  private function check_command($message)
  {
    
     if (strpos($message, "/") === 0)
     { //message is a command
	$command_parts = explode(" ", $message, 2);
	$command_name = str_replace("/", "", $command_parts[0]);
	
	if (isset($command_parts[1]))
	  $command_parameters =  $command_parts[1];
	else
	  $command_parameters = "";
	  
	$command_class = "SiPacCommand_".$command_name;
	$command_path = dirname(__FILE__) ."/../command/".$command_class.".php";
	if (file_exists($command_path))
	{
	  include_once($command_path);
	  if (class_exists($command_class))
	  {
	    $command = new $command_class;
	    $command->set_variables($this, $command_parameters);
	    if ($command->check_permission() == true)
	    {
	      $command_return = $command->execute();
	      
	      if (is_array($command_return))
		return $command_return;
	      else
		return array();
	    }
	    else
	      return array("info_type"=>"warn", "info_text" => 'No Permissions!');
	  }
	  else
	    return array("info_type"=>"error", "info_text" => 'Classname is not "'.$command_class.'"');
	}
	else
	  return array("info_type"=>"warn", "info_text" => "Command not found!");
     }
     else
      return false;
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
