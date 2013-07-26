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
 
/*all chat functions*/
$chat_num = 0;
function draw_chat($id)
{
  global $chat_html_path;
  global $chat_text;
  global $chat_debug;
  global $chat_settings;
  global $chat_layout;
  global $chat_num;
  
  
  $chat_id         = "chat_" . $id;
  $chat_client_num = "n" . time() . mt_rand(0, 10000);
  if (empty($_SESSION[$chat_id]))
  {
    $_SESSION[$chat_id]                  = array();
    $_SESSION[$chat_id]['chat_username'] = "tmp";
  }
  else
    unset($_SESSION[$chat_id]['settings']);
  
  check_chat($chat_id, $chat_client_num);
  global $chat_settings;
  
  //delete the userlist session
  $_SESSION[$chat_id]['chat_userlist'][$chat_client_num] = array();
  
  $_SESSION[$chat_id][$chat_client_num]['old_channels'] = array();
  
  $_SESSION[$chat_id]['debug_shown'] = array();
  
  
  require(dirname(__FILE__) . "/../themes/" . $chat_settings['theme'] . "/layout.php");
  if (empty($chat_layout_user_entry))
  {
    return 'Missing $chat_layout_user_entry in layout.php. Please write at least "$chat_layout_user_entry = \'!!USER!!\';"';
    return false;
  }
  $chat_id_css   = "#$chat_id";
  $chat_css_file = str_replace("\n", " ", file_get_contents(dirname(__FILE__) . "/../themes/" . $chat_settings['theme'] . "/chat.css"));
  
  
  $chat_css_file_parts = explode("}", $chat_css_file);
  
  $chat_new_css = "";
  foreach ($chat_css_file_parts as $key => $part)
  {
    $chat_new_css = $chat_new_css . $part . "}";
    if ($key == count($chat_css_file_parts) - 2)
    {
      break;
    }
    
    if ($key != 0)
      $chat_new_css = $chat_new_css . $chat_id_css;
    
  }
  $chat_js_file = file_get_contents(dirname(__FILE__) . "/chat.js");
  
  $chat_html = chat_translate(str_replace('"chat_main"', '"chat_main" id="' . $chat_id . '"', str_replace("'chat_main'", "'chat_main' id='$chat_id'", $chat_layout))) . "
		<script class='chatengine_script' type='text/javascript'>
		".$chat_js_file."
		var style_obj = document.createElement('style');
		var text_obj = document.createTextNode('$chat_new_css');
		style_obj.appendChild(text_obj);
		document.getElementById('$chat_id').appendChild(style_obj);
		add_chat('$chat_html_path','" . $chat_settings['theme'] . "','$chat_id', '$chat_client_num',";
  
  
  
  $chat_html = $chat_html . "new Array(";
  foreach ($chat_settings['channels'] as $key => $channel)
  {
    
    if ($key != 0)
      $chat_html = $chat_html . ",";
    $chat_html = $chat_html . "\"$channel\"";
    
  }
  
  $chat_html = $chat_html . "), new Array(";
  foreach ($chat_text as $key => $text)
  {
    
    if ($key != 0)
      $chat_html = $chat_html . ",";
    $chat_html = $chat_html . "\"$text\"";
    
  }
  
  
  $chat_html = $chat_html . "));";
  
  if (!empty($chat_layout_functions))
  {
    foreach ($chat_layout_functions as $name => $function)
    {
      $chat_html = $chat_html."chat_objects[chat_objects.length-1].$name = ".chat_translate($function).";";
      if ($name == "layout_init")
	$chat_html = $chat_html."chat_objects[chat_objects.length-1].$name();";
    }
  }
  
  $chat_html = $chat_html."</script></div>";
  
  
  
  $chat_smileys = "";
  if ($chat_settings['smiley_width'] == "!!AUTO!!")
    $width = "";
  else
    $width = " width='" . $chat_settings['smiley_width'] . "px'";
  if ($chat_settings['smiley_height'] == "!!AUTO!!" AND $chat_settings['smiley_width'] == "!!AUTO!!" AND isset($default_smiley_height))
    $height = " height='" . $default_smiley_height . "'";
  else if ($chat_settings['smiley_height'] == "!!AUTO!!")
    $height = "";
  else
    $height = " width='" . $chat_settings['smiley_height'] . "px'";
  foreach ($chat_settings['smileys'] as $smiley_code => $smiley_url)
  {
    if (strpos($smiley_url, "http://") === false)
      $smiley_url = $chat_html_path . "themes/" . $chat_settings['theme'] . "/smileys/" . $smiley_url;
    $smiley_code  = htmlentities(str_replace('"', 'lol', addslashes($smiley_code)), ENT_QUOTES);
    $chat_smileys = $chat_smileys . "<span style='margin-right: 3px;' onclick='chat_objects[chat_objects_id[\"$chat_id\"]].add_smiley(\" " . $smiley_code . "\");'><img$width$height src='" . $smiley_url . "' title='" . $smiley_code . "' alt='" . $smiley_code . "'></span>";
  }
  
  $chat_html = str_replace("!!SMILEYS!!", $chat_smileys, $chat_html);
  $chat_html = str_replace("!!ID!!", $chat_id, $chat_html);
  $chat_html = str_replace("!!NUM!!", "$chat_num", $chat_html);
  $chat_html = str_replace("!!USER_NUM!!", "<span id='chat_user_num'>?</span>", $chat_html);
  
  unset($GLOBALS['chat_settings']);
  
  $chat_num++;
  
  return $chat_html;
  
}
function handle_replace_commands($chat_message)
{
  global $chat_settings;
  $chat_replace_commands = $chat_settings['replace_commands'];
  
  $chat_msg_command_arr = explode(" ", $chat_message);
  $chat_msg_command_ids = array();
  foreach ($chat_msg_command_arr as $chat_msg_key => $chat_msg_command)
  {
    if (stripos($chat_msg_command, "/") !== false)
      $chat_msg_command_ids[] = $chat_msg_key;
  }
  foreach ($chat_msg_command_ids as $id)
  {
    if (strpos($chat_msg_command_arr[$id], "/") === 0 AND strpos($chat_msg_command_arr[$id], ":") !== false)
      $command_pos_arr[] = $id;
  }
  if (isset($command_pos_arr))
  {
    foreach ($command_pos_arr as $command_pos)
    {
      foreach ($chat_replace_commands as $command => $replace)
      {
        $command_arr = explode(":", $command);
        if (stripos($chat_msg_command_arr[$command_pos], $command_arr[0] . ":") === 0)
        {
          $use_command         = $command_arr[0];
          $use_command_replace = $replace;
          break;
        }
      }
      if (isset($use_command))
      {
        $command_arguments_tmp = explode(":", $chat_msg_command_arr[$command_pos]);
        $command_arguments     = "";
        foreach ($command_arguments_tmp as $key => $argument)
        {
          if ($key > 0)
          {
            if ($key > 1)
              $command_arguments = $command_arguments . ":" . $argument;
            else
              $command_arguments = $command_arguments . $argument;
          }
        }
        $command_arguments = explode(",", $command_arguments);
        
        foreach ($command_arguments as $key => $argument)
        {
          $new_key             = $key + 1;
          $use_command_replace = str_replace("#$new_key#", $argument, $use_command_replace);
        }
        $chat_message            = str_replace($chat_msg_command_arr[$command_pos], $use_command_replace, $chat_message);
        $json_array['info_type'] = "error";
        $json_array['info_text'] = chat_translate($use_command_replace);
      }
      else
      {
        $json_array['info_type'] = "error";
        $json_array['info_text'] = chat_translate("Kommando nicht gefunden!");
      }
    }
  }
  return $chat_message;
}
function handle_special_commands($message)
{
  global $chat_settings;
  if (strpos($message, "/") === 0)
  {
    $array = array();
    $parts = explode(" ", $message);
    if (!isset($parts[1]))
      $parts[1] = "";
    
    foreach ($parts as $key => $part)
    {
      if ($key >= 1)
      {
        if ($key == 1)
          $parts[1] = $part;
        else
          $parts[1] = $parts[1] . " " . $part;
      }
    }
    $command_found = false;
    $commands      = array_merge($chat_settings['default_special_commands'], $chat_settings['special_commands']);
    foreach ($commands as $command => $command_function)
    {
      $command_part = explode(" ", $command);
      if ($command_part[0] == $parts[0])
      {
        $arguments = explode(",", $parts[1]);
        foreach ($arguments as $key => $argument)
        {
          $new_key          = $key + 1;
          $command_function = str_replace("#$new_key#", $argument, $command_function);
        }
        $command_found    = true;
        $command_function = preg_replace('/#[1234567890](.*?)#/si', '0', $command_function);
        eval("\$command_return = " . $command_function);
      }
      
    }
    if ($command_found)
    {
      if (is_array($command_return))
      {
        $array = $command_return;
        if (!empty($array['info_text']))
          $array['info_text'] = chat_translate($array['info_text']);
        
        return $array;
      }
      else
        return true;
    }
    else
    {
      $array['info_type'] = "error";
      $array['info_text'] = "Kommando '" . $parts[0] . "' nicht gefunden! (/help für alle Kommandos)";
      return $array;
    }
  }
  else
    return false;
}
function save_message($chat_message, $channel = 0, $extra = 0, $chat_user = 0, $chat_time = 0, $highlight = "none")
{
  global $chat_id;
  global $chat_debug;
  global $chat_settings;
  
  if (empty($channel))
  {
    echo "No channel defined in the save_message function!";
    DIE();
  }
  if (array_search($channel, $chat_settings['channels']) === false AND $chat_settings['can_join_channels'] == false)
  {
    echo "Not allowed to send messages in this channel!";
    DIE();
  }
  if (empty($chat_user))
    $chat_user = $_SESSION[$chat_id]['chat_username'];
  
  if (empty($chat_time))
    $chat_time = time();
  
  
  foreach ($chat_settings["default_proxy"] as $proxy)
  {
    eval("\$chat_message = " . $proxy . "(\$chat_message, \$extra, \$chat_user, \$chat_time, \$highlight);");
  }
  
  
  $save_message_mysql = mysql_query("INSERT INTO chat_entries (user, message, extra, highlight, time, channel, chat_id) VALUES('" . mysql_real_escape_string($chat_user) . "', '" . mysql_real_escape_string($chat_message) . "','$extra', '$highlight', '$chat_time', '" . mysql_real_escape_string($channel) . "', '$chat_id')");
  
  if ($extra == 0)
    $chat_debug['all'][] = "Message saved";
  else if ($extra == 1)
    $chat_debug['all'][] = "Notification saved";
  else if ($extra == 2)
    $chat_debug['all'][] = "System Message saved";
  else if ($extra == 3)
    $chat_debug['all'][] = "Highlighted Message saved";
  else if ($extra == 4)
    $chat_debug['all'][] = "/me saved";
  else
    $chat_debug['all'][] = "? saved";
  return true;
}
//GET MESSAGES FUNCTION - get all messages from the db
function get_messages($last_id)
{
  
  global $chat_id;
  global $chat_settings;
  global $chat_debug;
  global $chat_channels;
  global $chat_client_num;
  global $chat_new_channels;
  
  
  /* Delete all messages from the db, which are more than $chat_settings['max_messages']'*/
  if ($last_id == "undefined")
  {
    $chat_entries_num = mysql_num_rows((mysql_query("SELECT * FROM chat_entries WHERE chat_id = '$chat_id' ORDER BY id ASC")));
    
    if ($chat_settings['max_messages'] < 10)
      $max_keep_messages = 10;
    else
      $max_keep_messages = $chat_settings['max_messages'];
    
    $to_delete = $chat_entries_num - $max_keep_messages;
    
    if ($to_delete > 0)
    {
      $delete_old_posts_mysql = mysql_query("SELECT * FROM chat_entries WHERE chat_id = '$chat_id' ORDER BY id ASC LIMIT $to_delete");
      while ($delete_old_posts = mysql_fetch_object($delete_old_posts_mysql))
      {
        $delete_old_post = mysql_query("DELETE FROM chat_entries WHERE id = '$delete_old_posts->id'");
      }
    }
  }
  $old_last_id = $last_id;
  
  $array            = array();
  $array['actions'] = array();
  
  $chat_mysql             = mysql_query("SELECT * FROM chat_entries WHERE chat_id = '$chat_id' ORDER BY id ASC");
  $array['messages']      = array();
  $array['messages_user'] = array();
  $array['username']      = $_SESSION[$chat_id]['chat_username'];
  $array['highlight']     = false;
  
  foreach ($chat_channels as $channel)
  {
    $i[$channel] = 0;
  }
  
  $max_show_messages = 50;
  
  while ($into = mysql_fetch_object($chat_mysql))
  {
    if (array_search($into->channel, $chat_channels) !== false AND $into->id > $last_id AND $i[$into->channel] < $max_show_messages)
    {
      $chat_continue    = true;
      $array['last_id'] = $into->id;
    }
    else if ($last_id != "none" AND array_search($into->channel, $chat_channels) !== false AND $i[$into->channel] < $max_show_messages AND array_search($into->channel, $chat_new_channels) !== false)
    {
      $chat_continue = true;
    }
    else
      $chat_continue = false;
    
    if ($chat_continue)
    {
      $i[$into->channel]++;
      if ($last_id != "none" OR $i > $max_show_messages - $chat_settings['max_messages'])
      {
        
        
        if ($chat_settings['time_24_hours'])
          $date = date("H:i", $into->time);
        else
          $date = date("h:i A", $into->time);
        
        if (date("d.m.Y", $into->time) != date("d.m.Y", time()))
          $date = date($chat_settings['date_format'], $into->time) . " " . $date;
        
        $chat_message = $into->message;
        foreach ($chat_settings["default_afterproxy"] as $afterproxy)
        {
          eval("\$chat_message = " . $afterproxy . "(\$chat_message, \$into->extra, \$into->user, \$into->time, \$into->highlight);");
        }
        
        
        if ($into->user == $_SESSION[$chat_id]['chat_username'] AND $chat_settings['replace_own_username'])
          $chat_entry_user = "Sie";
        else
          $chat_entry_user = $into->user;
        
        $message_entry = "<div id='chat_entry_" . $into->id . "' class='";
        if ($into->extra == 0)
        {
          $message_entry   = $message_entry . "chat_entry";
          if ($into->user == $_SESSION[$chat_id]['chat_username'])
	    $message_entry = $message_entry . "_own";
	  else
	    $message_entry = $message_entry . "_others";
          $chat_entry_user = "[" . $chat_entry_user . "]:";
        }
        else if ($into->extra == 1 || $into->extra == 4)
        {
          $message_entry = $message_entry . "chat_notify";
          if ($into->extra == 4)
            $chat_entry_user = "*" . $into->user;
          else
            $chat_entry_user = "";
        }
        else if ($into->extra == 2)
        {
          $message_entry   = $message_entry . "chat_system_message";
          $chat_entry_user = "[<||t15||>][" . $chat_entry_user . "]:"; //Systemmessage
        }
        else if ($into->extra == 3)
        {
          if ($into->highlight != $_SESSION[$chat_id]['chat_username'] AND $into->user != $_SESSION[$chat_id]['chat_username'])
            $message_entry = $message_entry . "chat_entry";
          else
          {
            $message_entry      = $message_entry . "chat_answer_message";
            $array['highlight'] = true;
          }
          if ($into->highlight == $_SESSION[$chat_id]['chat_username'] AND $chat_settings['replace_own_username'])
            $highlight = "Sie";
          else
            $highlight = $into->highlight;
          
          $chat_entry_user = "[<||t33||>] <||t17|$chat_entry_user|$highlight||>"; //Privat Message, %1 to %2:
        }
        
        $message_entry = $message_entry . "'>";
        if ($chat_settings['rows'] == 2)
        {
          $message_entry = $message_entry . "
							      <span class='chat_entry_user'>" . chat_translate($chat_entry_user) . "</span>
							      <span class='chat_entry_date'>[" . $date . "]</span>
							      <br>
							      <span class='chat_entry_message'>" . chat_translate($chat_message) . "</span>
							    ";
        }
        else
        {
          $message_entry = $message_entry . "
							      <span class='chat_entry_user'>" . chat_translate($chat_entry_user) . "</span>
							      <span class='chat_entry_message'>" . chat_translate($chat_message) . "</span>
							      <span class='chat_entry_date'>[" . $date . "]</span>
							    ";
        }
        $message_entry                       = $message_entry . "</div>";
        $array['messages'][$into->channel][] = $message_entry;
        if ($into->extra != 1)
          $array['messages_user'][$into->channel][] = $into->user;
      }
      
    }
  }
  
  
  if (!isset($array['last_id']))
    $array['last_id'] = $last_id;
  
  if (count($array['messages']) != 0)
  {
    $chat_new_entries = 0;
    foreach ($array['messages'] as $channel_messages)
    {
      $chat_new_entries = $chat_new_entries + count($channel_messages);
    }
    $chat_debug['all'][] = $chat_new_entries . " new Entries (id:" . $array['last_id'] . "; old_id:" . $old_last_id . ")";
  }
  
  $array['userlist'] = get_save_user($chat_settings['max_ping_remove']);
  if (isset($array['userlist']['actions']))
  {
    $array['actions'] = array_merge($array['userlist']['actions'], $array['actions']);
    unset($array['userlist']['actions']);
  }
  
  foreach ($array['actions'] as $key => $action)
  {
    $array['actions'][$key] = chat_translate($action);
  }
  
  $array['afk'] = $_SESSION[$chat_id]['chat_afk'];
  return $array;
}


function get_save_user()
{
  global $chat_debug;
  global $chat_id;
  global $chat_settings;
  global $chat_html_path;
  global $chat_client_num;
  global $chat_channels;
  global $chat_active_channel;
  global $chat_num;
  
  if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    $user_ip = $_SERVER['REMOTE_ADDR'];
  else
    $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  
  //get users
  $array                                = array();
  $_SESSION[$chat_id]['chat_users']     = array();
  $_SESSION[$chat_id]['chat_users_afk'] = array();
  
  $chat_no_save_user = false;
  foreach ($chat_channels as $channel)
  {
    $user_entry     = array();
    $user_in_db     = "none";
    $get_user_mysql = mysql_query("SELECT * FROM chat_users WHERE chat_id = '$chat_id' AND channel = '" . mysql_real_escape_string($channel) . "' ORDER BY id asc");
    while ($get_user = mysql_fetch_object($get_user_mysql))
    {
      //If a user wasn't online since $chat_settings['max_ping_remove'], then remove
      if (time() - $get_user->last_time > $chat_settings['max_ping_remove'] OR $get_user->action == "delete")
      {
        $delete_user = mysql_query("DELETE FROM chat_users WHERE id LIKE '" . $get_user->id . "'");
        
        save_message("<||t1|" . stripslashes($get_user->name) . "||>", $get_user->channel, 1, 0, $get_user->last_time); //%1 has left the Chat
      }
      else
      {
        $chat_user_info_id = addslashes($get_user->name) . "_" . addslashes($get_user->chat_id) . "_" . addslashes($get_user->channel);
        /*$tmp_entry         = "<div onmouseover=\"chat_objects[$chat_num].ui_dropdown_sign('" . $chat_user_info_id . "_dropdown_info', 'show');\" onmouseout=\"chat_objects[$chat_num].ui_dropdown_sign('" . $chat_user_info_id . "_dropdown_info', 'hide');\" onclick=\"chat_objects[$chat_num].user_info('" . $chat_user_info_id . "_user_info');\" class='";
        */
        $chat_user_style_array = explode("|", $get_user->style);
        
        $chat_user_online_class  = $chat_user_style_array[0];
        $chat_user_afk_class     = $chat_user_style_array[1];
        $chat_user_custom_status = $chat_user_style_array[2];
        
        if ($chat_user_afk_class == "!!AUTO!!")
          $chat_user_afk_class = "afk_user";
        if ($chat_user_online_class == "!!AUTO!!")
          $chat_user_online_class = "online_user";
        
        /*
        if ($get_user->afk == 1)
          $tmp_entry = $tmp_entry . $chat_user_afk_class; //afk_user
        else
          $tmp_entry = $tmp_entry . $chat_user_online_class; //online_user
        
        $tmp_entry = $tmp_entry . "'>";
        
        $tmp_entry = $tmp_entry . "<span class='chat_speech_bubble'><img src='" . $chat_html_path . "themes/" . $chat_settings['theme'] . "/speech_bubble.gif' alt='writing' title='writing'></span>";
        
        
        if ($chat_user_custom_status != "!!AUTO!!")
          $tmp_entry = $tmp_entry . $get_user->name . "&nbsp;<span class='user_online_status'>[" . $chat_user_custom_status . "]"; //online
        else if ($get_user->afk == 0)
          $tmp_entry = $tmp_entry . $get_user->name . "&nbsp;<span class='user_online_status'>[<||t27||>]"; //online
        else
          $tmp_entry = $tmp_entry . $get_user->name . "&nbsp;<span class='user_afk_status'>[<||t28||>]"; //afk
        
        $tmp_entry = $tmp_entry . "</span><span id=\"" . $chat_user_info_id . "_dropdown_info\"></span></div><div id=\"" . $chat_user_info_id . "_user_info\" class='user_info_box'>";
        */
        $user_info_tmp = $get_user->info;
        if ($chat_settings['can_see_ip'])
          $user_info_tmp = "IP:" . $get_user->ip . "|||" . $user_info_tmp;
        
        if ($chat_settings['can_kick'])
          $user_info_tmp = "<a href='javascript:void(null);' onclick='chat_objects[$chat_num].kick_user(\"".addslashes($get_user->name) . "\");'><||t26|" . $get_user->name . "||></a>|||" . $user_info_tmp;
        
        $user_info = "";
        foreach (explode("|||", $user_info_tmp) as $info)
        {
          //user dropdown infos
          $user_info = $user_info . "<li>" . $info . "</li>";
        }
        
        require(dirname(__FILE__) . "/../themes/" . $chat_settings['theme'] . "/layout.php");
        $tmp_entry = $chat_layout_user_entry;
        

        
	if ($chat_user_custom_status != "!!AUTO!!")
	{
          $chat_user_afk = "online";
          $chat_user_status = $chat_user_custom_status;
        }
        else if ($get_user->afk == 0)
        {
          $chat_user_afk = "online";
          $chat_user_status = "<||t27||>"; //online
        }
        else
        {
          $chat_user_afk = "afk";
          $chat_user_status = "<||t28||>"; //afk
        }
        $tmp_entry = str_replace("!!USER!!", $get_user->name, $tmp_entry);
        $tmp_entry = str_replace("!!USER_ID!!", $chat_user_info_id, $tmp_entry);
        $tmp_entry = str_replace("!!NUM!!", $chat_num, $tmp_entry);
        $tmp_entry = str_replace("!!USER_AFK!!", $chat_user_afk, $tmp_entry);
        $tmp_entry = str_replace("!!USER_STATUS!!", $chat_user_status, $tmp_entry);
        $tmp_entry = str_replace("!!USER_INFO!!", $user_info, $tmp_entry);
        /*$tmp_entry = $tmp_entry . "$user_info</div>";*/
        
        if ($get_user->name == $_SESSION[$chat_id]['chat_username'])
        {
          $user_in_db = $get_user->id;
          if (!empty($get_user->action))
          {
            $action_parts = explode("|", $get_user->action);
            
            if ($action_parts[0] == "new_name")
              $_SESSION[$chat_id]['chat_custom_name'] = $action_parts[1];
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
            else if ($action_parts[0] == "join")
              $array['actions'][] = "join|" . $action_parts[1];
            else
              $chat_debug['warn'][] = "Action " . $action_parts[0] . " not defined!";
            
            
            $delete_action = mysql_query("UPDATE chat_users Set action = '' WHERE id ='" . $get_user->id . "'");
            
            
          }
        }
        
        $user_entry[] = chat_translate($tmp_entry);
        
        $chat_user_writing[]        = $get_user->writing;
        $array[$channel]['users'][] = $get_user->name;
        
        $_SESSION[$chat_id]['chat_users'][]     = $get_user->name;
        $_SESSION[$chat_id]['chat_users_afk'][] = $get_user->afk;
        
        
      }
      
      
    }
    
    if (isset($_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel]) AND count($_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel]) > count($user_entry))
    {
      for ($i = count($user_entry); $i < count($_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel]); $i++)
      {
        $chat_debug['all'][]              = "User deleted";
        $array[$channel]['delete_user'][] = $i;
        unset($_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel][$i]);
      }
    }
    foreach ($user_entry as $key => $user_entry_for)
    {
      if (!isset($_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel][$key]))
      {
        $chat_debug['all'][]                                                   = "User '" . $_SESSION[$chat_id]['chat_users'][$key] . "' added";
        $array[$channel]['add_user'][]                                         = $user_entry[$key];
        $array[$channel]['add_user_id'][]                                      = $key;
        $_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel][$key] = $user_entry[$key];
      }
      else if ($user_entry_for != $_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel][$key])
      {
        $array[$channel]['change_user'][]                                      = $user_entry[$key];
        $array[$channel]['change_user_id'][]                                   = $key;
        $_SESSION[$chat_id]['chat_userlist'][$chat_client_num][$channel][$key] = $user_entry[$key];
        $chat_debug['all'][]                                                   = "User '" . $_SESSION[$chat_id]['chat_users'][$key] . "' changed";
      }
      $array[$channel]['user_writing'][$key] = $chat_user_writing[$key];
    }
    
    
    
    
    
    
    
    
    if ($chat_no_save_user == false)
    {
      //save user
      $user_info = "";
      foreach ($chat_settings['user_infos'] as $info)
      {
        $user_info = $user_info . "$info|||";
      }
      foreach ($chat_settings['default_user_infos'] as $info)
      {
        $user_info = $user_info . "$info|||";
      }
      $user_info       = $user_info;
      //if the user is not in the db
      $chat_user_style = mysql_real_escape_string($chat_settings['user_online_class'] . "|" . $chat_settings['user_afk_class'] . "|" . $chat_settings['custom_status']);
      if ($user_in_db == "none")
      {
        
        
        $add_user_mysql = mysql_query("INSERT INTO chat_users (name, info, style, afk, writing, ip, last_time, channel, chat_id) VALUES ('" . mysql_real_escape_string($_SESSION[$chat_id]['chat_username']) . "', '" . mysql_real_escape_string($user_info) . "', '" . $chat_user_style . "', '" . $chat_settings['start_as_afk'] . "', '" . $chat_settings['start_as_afk'] . "', '" . $user_ip . "', '" . time() . "', '" . mysql_real_escape_string($channel) . "', '$chat_id')");
        
        if (!isset($_SESSION[$chat_id]['chat_is_user_rename']) OR $_SESSION[$chat_id]['chat_is_user_rename'] == false)
          save_message("<||t2|" . $_SESSION[$chat_id]['chat_username'] . "||>", $channel, 1); //%1 has joined the Chat
        else
          $_SESSION[$chat_id]['chat_is_user_rename'] = false;
      }
      //else just update the last_time and the afk status
      else
      {
        if ($channel == $chat_active_channel)
          $chat_is_writing = $_SESSION[$chat_id]['chat_writing'];
        else
          $chat_is_writing = false;
        $update_user_time = mysql_query("UPDATE chat_users SET last_time = '" . time() . "', afk = '" . $_SESSION[$chat_id]['chat_afk'] . "', info = '" . mysql_real_escape_string($user_info) . "', writing = '" . $chat_is_writing . "', style = '" . $chat_user_style . "' WHERE id = '" . $user_in_db . "' AND channel = '" . mysql_real_escape_string($channel) . "'");
      }
    }
  }
  
  return $array;
}
function handle_debug()
{
  global $chat_debug;
  global $chat_id;
  global $chat_settings;
  
  if (!empty($_SESSION[$chat_id]['debug']) OR $chat_settings['default_debug'] != "off" AND !isset($_SESSION[$chat_id]['debug']))
  {
    $array = array();
    
    if (isset($_SESSION[$chat_id]['debug']) AND $_SESSION[$chat_id]['debug'] == "all" OR $chat_settings['default_debug'] == "all" AND !isset($_SESSION[$chat_id]['debug']))
    {
      foreach ($chat_debug['all'] as $debug)
      {
        $array[] = array("text"=>$debug, "type" => "debug");
      }
      
      foreach ($chat_debug['all_once'] as $debug)
      {
        if (array_search($debug, $_SESSION[$chat_id]['debug_shown']) === false)
        {
          $array[] = array("text"=>$debug, "type" => "debug");
          $_SESSION[$chat_id]['debug_shown'][] = $debug;
        }
      }
      
    }
    
    foreach ($chat_debug['warn'] as $debug)
    {
      $array[] = array("text"=>$debug, "type" => "warn");
    }
    foreach ($chat_debug['warn_once'] as $debug)
    {
      if (array_search($debug, $_SESSION[$chat_id]['debug_shown']) === false)
      {
	$array[] = array("text"=>$debug, "type" => "warn");
        $_SESSION[$chat_id]['debug_shown'][] = $debug;
      }
    }
    return $array;
  }
}
function chat_translate($text, $lang = 0)
{
  global $chat_text;
  global $chat_settings;
  
  if ($lang === 0)
    $lang = $chat_settings['language'];
  
  
  require(dirname(__FILE__) . "/lang/$lang.php");
  
  
  $text_parts_start = explode("<||", $text);
  
  $new_text = $text_parts_start[0];
  foreach ($text_parts_start as $key => $part)
  {
    if ($key != 0)
    {
      $part_end = explode("||>", $part);
      
      if (!empty($part_end[0]))
      {
        $text_code_parts = explode("|", $part_end[0]);
        
        $text_id = str_replace("t", "", $text_code_parts[0]);
        
        if (!empty($chat_text[$text_id]) AND strlen($chat_text[$text_id]) > 0)
        {
          $meaning = $chat_text[$text_id];
          
          foreach ($text_code_parts as $key => $replace)
          {
            $meaning = str_replace("%" . ($key), $replace, $meaning);
          }
          
          
          $new_text = $new_text . $meaning;
        }
        else if (!empty($text_id) && is_numeric($text_id))
          $new_text = $new_text . "Missing Translation " . $text_id . "! (lang: " . $lang . ")";
        
      }
    }
    
    
    if (!empty($part_end[1]))
      $new_text = $new_text . $part_end[1];
  }
  
  return $new_text;
}
?>