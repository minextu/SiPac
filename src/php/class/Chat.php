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

class Chat
{
  //define all private variables
  private $client_num;
  private $id;
  private $settings = array();
  private $html_path;
  private $nickname;
  private $html_code;
  
  private $db_error;
  
  private $js_chat;
  
 
  public function __construct($settings=false, $is_new=true, $client_num=false, $id=false)
  {
    $this->init($settings, $is_new, $client_num, $id);
  }
  
  //initiate the chat: load settings, check all variables
  public function init($settings=false, $is_new=true, $client_num=false, $id=false)
  {
    /*
    if not already set,
    generate random id for this tab/window of the client
    (so the chat can be opened more than once in different tabs, but same with the same session)
    */
    if ($client_num == false)
      $this->client_num = "n" . time() . mt_rand(0, 10000);
    else
      $this->client_num = $client_num;
    
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
    
    
    
  }
  //generate and return the html code for the chat
  public function draw()
  {
    $layout_path = dirname(__FILE__) . "/../../../themes/".$this->settings['theme'];
    require_once($layout_path."/layout.php");
    
    $css_file = str_replace("\n", " ", file_get_contents($layout_path."/chat.css"));
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
    $this->html_code = $chat_layout;
    
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
		$this->js_chat
		."
		</script>";

    return $this->html_code;
  }
  public function get_posts($last_id)
  {
    //load all posts
    $db_response = $this->db->get_posts($this->id);
 
    $new_posts = array();
    $new_post_users = array();
    
    foreach ($db_response as $post)
    {
      //check if the post is new
      if ($post['id'] > $last_id)
      {
	$new_posts[] = "<div>".$post['user'].": ".$post['message']."</div>";
	$new_post_users[] = $post['user'];
      }
      //save the highest id
      $updated_last_id = $post['id'];
    }
    $last_id = $updated_last_id;
    //return all new posts and the highest id
    return array('posts' => array("Main" => $new_posts), 'post_users' => $new_post_users, 'last_id' => $last_id);
  }
  public function send_message($message)
  {
    //remove uneeded space
    $message = trim($message);
    
    if (!empty($message))
    {
      $db_response = $this->db->save_post($message);
      if ($db_response !== true)
	return array('info_type' => "error", 'info_text' => $db_response);
      else
	return array();
    }
    else
      return array('info_type' => "error", 'info_text' => "Nothing entered");
  }
  public function check_name()
  {
    if ($this->settings['username_var'] == "!!AUTO!!")
      $this->nickname = "Guest " . mt_rand(1, 1000);
    else
      $this->nickname = $this->settings['username_var'];
  
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
    else if (isset($_SESSION[$this->id]['settings'])) //else load them from the php session (if set)
      $this->settings = $_SESSION[$this->id]['settings'];
    else
      die("No settings found!");
    
    
    //include the default config
    require_once(dirname(__FILE__)."/../default_conf.php");
    
    //if some settings are not set, load them from the default config
    foreach ($chat_default_settings as $setting => $default)
    {
      if (!isset($this->settings[$setting]))
      {
	$this->settings[$setting]  = $default;
	//$chat_debug['all_once'][] = "Setting $setting is unused!";
      }
    }
    //save the settings in the session
    $_SESSION[$this->id]['settings'] = $this->settings;

    //get the correct html path or load a custom
    if ($this->settings['html_path'] == "!!AUTO!!")
      $this->html_path = str_replace("//", "/", "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", realpath(dirname(__FILE__)."/../../..") . "/"));
    else
      $this->html_path = $this->settings['html_path'];
    
  }
}




?>
