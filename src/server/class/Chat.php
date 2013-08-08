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
  
 
  public function __construct($settings)
  {
    $this->init($settings, true);
  }
  
  //initiate the chat: load settings, check all variables
  public function init($settings, $is_new, $client_num=false)
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
    $this->load_settings($settings);
    
    
    
  }
  public function draw()
  {
    return $this->client_num;
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
    else if (isset($_SESSION[$chat_id]['settings'])) //else load them from the php session (if set)
      $this->settings = $_SESSION[$chat_id]['settings'];
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
    if (empty($this->settings['mysql_hostname']) OR empty($this->settings['mysql_username']) OR !isset($this->settings['mysql_password']) OR empty($this->settings['mysql_database']))
      die("Missing MySQL Settings!");
      
    
  }
}




?>
