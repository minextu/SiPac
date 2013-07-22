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
 
if (strlen(session_id()) < 1)
{
  session_start();
}

Header("Pragma: no-cache");
Header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
Header("Content-Type: text/html");


//$chat_html_path = str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(dirname(__FILE__))."/");


/* MAIN FUNCTIONS */
$chat_version = "0.0.2.2";

function check_chat($id = 0, $client_num = 0, $channels = 0, $active_channel = 0)
{
  global $chat_settings;
  global $chat_id;
  global $chat_client_num;
  global $chat_debug;
  global $chat_html_path;
  global $chat_channels;
  global $chat_active_channel;
  global $chat_new_channels;
  global $chat_default_settings;
  
  if (!empty($client_num))
    $chat_client_num = $client_num;
  else
  {
    $last_id = "undefined";
    
    $array        = array(
      'info_type' => "error",
      'info_text' => "Client ID Error (Outdate Files?)"
    );
    $array['get'] = array(
      "last_id" => $last_id
    );
    echo json_encode($array);
    die();
  }
  if (!empty($id))
    $chat_id = $id;
  else
  {
    $last_id = "undefined";
    
    $array        = array(
      'info_type' => "error",
      'info_text' => "Empty chat id!"
    );
    $array['get'] = array(
      "last_id" => $last_id
    );
    echo json_encode($array);
    die();
  }
  if (isset($chat_settings) AND !empty($chat_settings) AND count($chat_settings) > 0)
  {
    $_SESSION[$chat_id]['is_kick']  = false;
    $_SESSION[$chat_id]['settings'] = $chat_settings;
  }
  else if (isset($_SESSION[$chat_id]['settings']))
  {
    $chat_settings = $_SESSION[$chat_id]['settings'];
  }
  else
  {
    echo "No Settings found! (Maybe Cookies are off?)";
    die();
  }
  if (!empty($_SESSION[$chat_id]['is_kick']) AND !empty($_GET['task']))
  {
    echo "You were kicked";
    die();
  }
  require_once("default_conf.php");
  
  /*Load default settings and check if a setting is not set*/
  foreach ($chat_default_settings as $setting => $default)
  {
    if (!isset($chat_settings[$setting]))
    {
      $chat_settings[$setting]  = $default;
      $chat_debug['all_once'][] = "Setting $setting is unused!";
    }
  }
  
  if (empty($chat_settings['host']) OR empty($chat_settings['user']) OR !isset($chat_settings['pw']) OR empty($chat_settings['db']))
  {
    echo "Missing MySQL Settings!";
    DIE();
  }
  
  
  if (!empty($_SESSION[$chat_id]['theme_no_afk']))
  {
    if ($chat_settings['deactivate_afk'] == false)
      $chat_debug['warn'][] = "AFK won't work in this theme!";
    
    $chat_settings['deactivate_afk'] = true;
  }
  
  if ($chat_settings['html_path'] == "!!AUTO!!")
  {
    $chat_html_path                              = str_replace("//", "/", "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(dirname(__FILE__)) . "/"));
    $_SESSION[$chat_id]['settings']['html_path'] = $chat_html_path;
  }
  else
    $chat_html_path = $chat_settings['html_path'];
  
  if (!empty($_GET['task']))
  {
    
    //check the channels
    $chat_new_channels = array();
    if (!empty($channels))
    {
      $chat_channels_post = $channels;
      $chat_channels_tmp  = explode("|||", $chat_channels_post);
      foreach ($chat_channels_tmp as $channel)
      {
        if (array_search($channel, $chat_settings['channels']) !== false OR $chat_settings['can_join_channels'] == true)
        {
          $chat_channels[] = $channel;
          
          if (array_search($channel, $_SESSION[$chat_id][$chat_client_num]['old_channels']) === false)
          {
            $chat_new_channels[] = $channel;
            $chat_debug['all'][] = "New Channel: \"" . $channel . "\". Getting old Messages for this Channel...";
          }
        }
        else
          $chat_debug['warn_once'][] = "Not allowed to view the Channel \"$channel\"!";
      }
    }
    if (empty($channels) OR empty($chat_channels))
    {
      echo "You are in no channels!";
      DIE();
    }
    if (!empty($active_channel))
    {
      if (array_search($active_channel, $chat_channels) !== false)
        $chat_active_channel = $active_channel;
    }
    if (empty($active_channel) OR empty($chat_active_channel))
    {
      echo "Wrong active channel!";
      DIE();
    }
    
    
    $_SESSION[$chat_id][$chat_client_num]['old_channels'] = $chat_channels;
    
  }
}


if (empty($_GET['task']))
{
  require_once(dirname(__FILE__) . "/chat_functions.php");
  return false;
}
else if ($_GET['task'] == "get_chat")
{
  if (!empty($_POST['chat_string']))
  {
    $chat_string  = $_POST['chat_string'];
    $chat_objects = explode("&&", $chat_string);
    $chat_num     = -1;
    foreach ($chat_objects as $chat_object)
    {
      unset($GLOBALS['chat_id']);
      unset($GLOBALS['chat_client_num']);
      unset($GLOBALS['chat_debug']);
      unset($GLOBALS['chat_html_path']);
      unset($GLOBALS['chat_channels']);
      unset($GLOBALS['chat_active_channel']);
      unset($GLOBALS['chat_new_channels']);
      
      $chat_debug = array(
        "all" => array(),
        "all_once" => array(),
        "warn" => array(),
        "warn_once" => array()
      );
      
      if (!empty($chat_object))
      {
        $chat_num            = $chat_num + 1;
        $chat_variable_parts = explode("&", $chat_object);
        
        foreach ($chat_variable_parts as $chat_variable_part)
        {
          $chat_variable                     = explode("=", $chat_variable_part);
          $chat_variables[$chat_variable[0]] = urldecode($chat_variable[1]);
        }
        
        
        if (isset($chat_variables['chat_id']) AND isset($chat_variables['client_num']) AND isset($chat_variables['channels']) AND isset($chat_variables['active_channel']))
        {
          
          check_chat($chat_variables['chat_id'], $chat_variables['client_num'], $chat_variables['channels'], $chat_variables['active_channel']);
          /*Connect to mysql */
          if (!mysql_connect($chat_settings['host'], $chat_settings['user'], $chat_settings['pw']))
          {
            {
              echo mysql_error();
              DIE();
            }
          }
          else
          {
            if (!mysql_select_db($chat_settings['db']))
            {
              {
                echo mysql_error();
                DIE();
              }
            }
          }
          
          require_once(dirname(__FILE__) . "/chat_functions.php");
          require_once(dirname(__FILE__) . "/../conf/custom_functions.php");
          
          $chat_json_array = array();
          
          if (!empty($chat_settings['include_file']))
	  {
	    $chat_include = true;
	    include_once($chat_settings['include_file']);
	  }
	  
          if (!empty($chat_variables['first_start']) AND $chat_variables['first_start'] != "false")
          {
            //Check all in use Language packs
            require(dirname(__FILE__) . "/lang/en.php");
            $chat_orginal_text = $chat_text;
            require(dirname(__FILE__) . "/lang/" . $chat_settings['language'] . ".php");
            if (count($chat_text) != count($chat_orginal_text))
              $chat_debug['warn_once'][] = "The " . $chat_settings['language'] . " Language Pack is outdate!";
            
            if ($chat_settings['language'] != $chat_settings['log_language'])
            {
              require(dirname(__FILE__) . "/lang/" . $chat_settings['log_language'] . ".php");
              if (count($chat_text) != count($chat_orginal_text))
                $chat_debug['warn_once'][] = "The " . $chat_settings['log_language'] . " Language Pack is outdate!";
            }
            //delete the userlist session
	    $_SESSION[$chat_id]['chat_userlist'][$chat_client_num] = array();
          }
          
          chat_check_name();
          
          $json_answer_post = array();
          
          if (isset($chat_variables['send_message']))
          {
            $chat_messages_to_save = explode("|||", $chat_variables['send_message']);
            foreach ($chat_messages_to_save as $message)
            {
              $json_answer_post = array_merge($json_answer_post, chat_send_message($message));
            }
          }
          
          $get_chat_answer = get_chat($chat_variables);
          $json_answer[]   = array_merge(array_merge($json_answer_post, $get_chat_answer), $chat_json_array);
          
          unset($chat_variables);
        }
        else
        {
          echo "Missing Variables!";
          DIE();
        }
      }
      //delete old Settings
      unset($GLOBALS['chat_settings']);
    }
    echo json_encode($json_answer);
    
  }
  else
    echo "Missing Chat String";
  
}
else
{
  echo "No task";
}


function chat_check_name()
{
  global $chat_id;
  global $chat_settings;
  global $chat_channels;
  
  /* FIRST START OR USERNAME CHANGE*/
  if (!isset($_SESSION[$chat_id]['chat_ready']) OR isset($_SESSION[$chat_id]['chat_old_username_var']) AND $_SESSION[$chat_id]['chat_old_username_var'] != $chat_settings['username_var'] OR isset($_SESSION[$chat_id]['chat_custom_name']) AND $_SESSION[$chat_id]['chat_custom_name'] != $_SESSION[$chat_id]['chat_username'])
  {
    if (isset($_SESSION[$chat_id]['chat_ready']))
      $old_username = $_SESSION[$chat_id]['chat_username'];
    
    if (isset($_SESSION[$chat_id]['chat_custom_name']))
      $_SESSION[$chat_id]['chat_username'] = $_SESSION[$chat_id]['chat_custom_name'];
    else if ($chat_settings['username_var'] == "!!AUTO!!")
      $_SESSION[$chat_id]['chat_username'] = "Guest " . mt_rand(1, 1000);
    else if ($chat_settings['username_var'] != "!!AUTO!!")
      $_SESSION[$chat_id]['chat_username'] = $chat_settings['username_var'];
    
    if (isset($_SESSION[$chat_id]['chat_ready']))
    {
      $_SESSION[$chat_id]['chat_is_user_rename'] = true;
      $delete_user                               = mysql_query("DELETE FROM chat_users WHERE name LIKE '" . mysql_real_escape_string(htmlentities($old_username, ENT_QUOTES)) . "'");
      
      foreach ($chat_channels as $channel)
      {
        save_message("<||t0|" . $old_username . "|" . $_SESSION[$chat_id]['chat_username'] . "||>", $channel, 1); //%1 is now %2
      }
      
    }
    $_SESSION[$chat_id]['chat_old_username_var'] = $chat_settings['username_var'];
    
    if (!isset($_SESSION[$chat_id]['chat_ready']))
    {
      if ($chat_settings['start_as_afk'] == true)
        $_SESSION[$chat_id]['chat_afk'] = true;
      else
        $_SESSION[$chat_id]['chat_afk'] = false;
      
      $_SESSION[$chat_id]['chat_writing'] = false;
      $_SESSION[$chat_id]['chat_ready']   = true;
      $_SESSION[$chat_id]['chat_users']   = array();
      
    }
  }
}

function chat_send_message($message)
{
  global $chat_active_channel;
  $chat_json_array = array();
  
  $message            = str_replace("|", "&#x007C;", trim(htmlentities(urldecode($message))));
  $is_special_command = handle_special_commands(addslashes($message));
  
  if ($message != "" AND !$is_special_command)
  {
    //save the message
    save_message($message, $chat_active_channel);
  }
  else if (!$is_special_command)
  {
    $chat_json_array['info_type'] = "error";
    $chat_json_array['info_text'] = chat_translate("<||t11||>"); //nothing is entered
  }
  else
  {
    if (is_array($is_special_command))
      $chat_json_array = $is_special_command;
  }
  return $chat_json_array;
}
function get_chat($var)
{
  global $chat_settings;
  global $chat_channels;
  global $chat_id;
  
  if ($chat_settings['deactivate_afk'] == false)
  {
    if (isset($_SESSION[$chat_id]['chat_new_afk']) AND $_SESSION[$chat_id]['chat_new_afk'] == false AND $_SESSION[$chat_id]['chat_afk'] == true OR $chat_settings['auto_detect_no_afk'] AND $var['writing'] == "true" AND $_SESSION[$chat_id]['chat_afk'] == true)
    {
      $_SESSION[$chat_id]['chat_afk'] = false;
      foreach ($chat_channels as $channel)
      {
        save_message("<||t19||>", $channel, 4); //is back again
      }
    }
    else if (isset($_SESSION[$chat_id]['chat_new_afk']) AND $_SESSION[$chat_id]['chat_new_afk'] == true AND $_SESSION[$chat_id]['chat_afk'] == false)
    {
      $_SESSION[$chat_id]['chat_afk'] = true;
      foreach ($chat_channels as $channel)
      {
        save_message("<||t18||>", $channel, 4); //is now away
      }
    }
    unset($_SESSION[$chat_id]['chat_new_afk']);
  }
  else
  {
    if ($chat_settings['start_as_afk'] == true)
      $_SESSION[$chat_id]['chat_afk'] = true;
    else
      $_SESSION[$chat_id]['chat_afk'] = false;
  }
  
  if (isset($var['writing']) AND $var['writing'] == "true")
    $_SESSION[$chat_id]['chat_writing'] = true;
  else
    $_SESSION[$chat_id]['chat_writing'] = false;
  
  if (!empty($var['last_id']) AND $var['last_id'] != "none")
    $last_id = $var['last_id'];
  else
    $last_id = 0;
  
  
  $chat_json_array['get']   = get_messages($last_id);
  $chat_json_array['debug'] = handle_debug();
  
  
  return $chat_json_array;
}
?>