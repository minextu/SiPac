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
 
/* GLOBAL CONFIG 

Please don't change anything here. 
If you want to change theese settings, use "$chat_settings[setting_name_here] = setting;" like in the sample index.php

*/

date_default_timezone_set('Europe/Berlin');
$chat_default_settings = array(
  "default_user_infos" => array(
  "<a href='javascript:void();'>Kicken</a>",
  "<a href='javascript:void();'>Bannen</a>",
  "<a href='javascript:void();'>Direkt schreiben</a>"
  ),
  "language" => "en",
  "log_language" => "en",
  "html_path" => "!!AUTO!!",
  "theme" => "default",
  "rows" => '1',
  "replace_own_username" => false,
  "deactivate_afk" => false,
  "auto_detect_no_afk" => true,
  "smileys" => array(
    ':)' => "happy.png",
    ';)' => "winking.png",
    ":D" => "smile.png",
    ":(" => "sad.png",
    ":'(" => "cry.png",
    ">:|" => "angry.png",
    ":O" => "huh.png",
    "o_o" => "glasses.png",
    ":/" => "worried.png",
    "*?*" => "question.png"
  ),
  "smiley_width" => "!!AUTO!!",
  "smiley_height" => "!!AUTO!!",
  "time_24_hours" => true,
  "date_format" => "d.m.y",
  "channels" => array(
    "Main"
  ),
  "can_join_channels" => true,
  "max_ping_remove" => 30,
  "username_var" => "!!AUTO!!",
  "custom_status" => "!!AUTO!!",
  "start_as_afk" => false,
  "can_rename" => true,
  "can_kick" => true,
  "can_ban" => true,
  "show_kick_user" => true,
  "show_ban_user" => true,
  "can_write_as_fake_user" => true,
  "can_rename_others" => true,
  "can_see_ip" => true,
  "user_infos" => array(
    "Info",
    "More Infp",
    "Or something"
  ),
  "replace_commands" => array(
    '/link:#1#,#2#' => "<a href='#1#' target='_blank'>#2#</a>",
    '/picture:#1#' => "<img src='#1#'>"
  ),
  "special_commands" => array(),
  "can_see_debug" => true,
  "default_debug" => "warn",
  "own_log_folder_for_chat_id" => true,
  "max_messages" => "50",
  
  
  
  "include_file" => false,
  "user_afk_class" => "!!AUTO!!",
  "user_online_class" => "!!AUTO!!"
);



/* !!!!!!!!!!!!!!!!!!!!!! chat command functions !!!!!!!!!!!!!!!!!!!!*/

$chat_default_settings["default_special_commands"] = array(
  '/help command' => "command_help('#1#');",
  '/me message,user' => "command_me('#1#', '#2#');",
  '/afk reason' => "command_afk('#1#');",
  '/name new name,user' => "command_name('#1#', '#2#');",
  "/join channel" => "command_join('#1#');",
  '/systemmessage message' => "command_systemmessage('#1#');",
  "/kick name,reason" => "command_kick('#1#', '#2#');",
  //"/ban name,time(in days),reason" => "command_ban('#1#', '#2#', '#3#');",
  "/about" => "command_about();",
  "/debug type" => "command_debug('#1#');"
);

function command_help($command_syntax = false)
{
  global $chat_settings;
  if ($command_syntax == false)
    $array = array(
      'info_type' => "info",
      'info_text' => "<||t4||>:"
    ); //You can use the following Commands
  else
    $array = array(
      'info_type' => "info",
      'info_text' => "<||t5||>:"
    ); //Command Syntax
  
  foreach (array_merge($chat_settings['default_special_commands'], $chat_settings['special_commands']) as $command => $action)
  {
    if ($command_syntax == false OR strpos($command, "/" . $command_syntax) !== false)
    {
      $array['info_text'] = $array['info_text'] . "<br>$command";
      $command_found      = true;
    }
  }
  if (!isset($command_found))
  {
    $array['info_type'] = "error";
    if ($command_syntax != false)
      $array['info_text'] = "<||t6|$command_syntax||>"; //Command %1 not found!
    else
      $array['info_text'] = "<||t7||>"; //No Commands found!
  }
  else
  {
    $array['info_nohide'] = true;
  }
  return $array;
}
function command_me($text, $user = 0)
{
  global $chat_settings;
  global $chat_active_channel;
  global $chat_id;
  $message = $text;
  if (empty($message))
  {
    $random_sentences = array(
      "<||t41||>", //has something better to do
      "<||t42|<b>". $_SESSION[$chat_id]['chat_users'][mt_rand(0, count($_SESSION[$chat_id]['chat_users']) - 1)]."</b>||>", //has fallen in love with %1
      " <||t43||>", //wishes he had not forgotten the text
      "<||t44||>", //thinks he/she is beautiful
      "<||t45|<b>" . $_SESSION[$chat_id]['chat_users'][mt_rand(0, count($_SESSION[$chat_id]['chat_users']) - 1)] . "</b>||>" //is watching for %1
    );
    
    $message = $random_sentences[mt_rand(0, count($random_sentences) - 1)];
  }
  if (!empty($user) AND $chat_settings['can_write_as_fake_user'] OR $user == 0)
  {
    save_message($message, $chat_active_channel, 4, $user); //1: the message; 2: the extra cole in the chat table (0 is a normal message, 1 is a notify without the username, 2 is a system message, 3 is a hightligthed message,4 is a notify with the user name), 3: is the optional fake user
    return true;
  }
  else
    return array(
      'info_type' => "error",
      'info_text' => "<||t8||>"
    ); //no permissions
}
function command_afk($reason=0)
{
  global $chat_settings;
  global $chat_id;
  
  if ($chat_settings['deactivate_afk'] == false)
  {
    if ($_SESSION[$chat_id]['chat_afk'] == false)
    {
      $_SESSION[$chat_id]['chat_new_afk'] = true;
      $_SESSION[$chat_id]['afk_reason'] = $reason;
      return true;
    }
    else if ($_SESSION[$chat_id]['chat_afk'] == true)
    {
      $_SESSION[$chat_id]['chat_new_afk'] = false;
      if (!empty($reason))
	return array('info_type' => "warn",'info_text' => "<||t46||>"); //You can't give a reason for not being afk!
    }
  }
  else
    return array(
      'info_type' => "error",
      'info_text' => "<||t47||>" //afk is not enable
    );
}
function command_name($new_name, $user = 0)
{
  global $chat_settings;
  global $chat_id;
  $new_name = $new_name;
  
  if (!empty($new_name) AND $chat_settings['can_rename'])
  {
    if (!empty($user) AND $chat_settings['can_rename_others'])
    {
      $count = mysql_query("SELECT * from chat_users WHERE name LIKE '" . mysql_real_escape_string($user) . "'");
      if (mysql_num_rows($count) > 0)
        $change_username = mysql_query("UPDATE chat_users SET action = 'new_name|" . mysql_real_escape_string($new_name) . "' WHERE name LIKE '" . mysql_real_escape_string($user) . "'");
      else
        return array(
          'info_type' => "error",
          'info_text' => "<||t9|'" . addslashes($user) . "'||>"
        ); //User %1 not found
    }
    else if (empty($user))
    {
      $_SESSION[$chat_id]['chat_custom_name'] = $new_name;
      return true;
    }
    else
      return array(
        'info_type' => "error",
        'info_text' => "<||t8||>"
      ); //no permissions
  }
  else if (empty($new_name))
    return array(
      'info_type' => "warn",
      'info_text' => "<||t10||>"
    ); //You didn't enter a new name!
  else
    return array(
      'info_type' => "error",
      'info_text' => "<||t8||>"
    ); //no permissions
}
function command_join($channel = 0)
{
  global $chat_settings;
  global $chat_id;
  global $chat_active_channel;
  if ($chat_settings['can_join_channels'] == true OR array_search($channel, $chat_settings['channels']) !== false)
  {
    if (!empty($channel))
    {
      $join_string = "join|" . mysql_real_escape_string($channel);
      
      $join_channel = mysql_query("UPDATE chat_users SET action = '" . $join_string . "' WHERE name LIKE '" . mysql_real_escape_string($_SESSION[$chat_id]['chat_username']) . "' AND channel LIKE '" . $chat_active_channel . "'");
      return true;
    }
    else
      return array(
        'info_type' => "error",
        'info_text' => "<||t48||>" //You must enter a channel!
      );
  }
  else
    return array(
      'info_type' => "error",
      'info_text' => "<||t8||>"
    ); //no permissions
}
function command_systemmessage($message)
{
  global $chat_active_channel;
  if (empty($message))
  {
    return array(
      'info_type' => "info",
      'info_text' => "<||t49||>" //You didn't enter a message!
    ); //show a message
  }
  else
    save_message($message, $chat_active_channel, 2);
}

/*function command_answer($user, $message)
{
  global $chat_id;
  global $chat_active_channel;
  if (empty($user) OR empty($message))
  {
    return array(
      "info_type" => "error",
      "info_text" => "Kein Benutzer oder keine Nachricht angegeben!"
    );
  }
  else if ($user == $_SESSION[$chat_id]['chat_username'])
  {
    return array(
      "info_type" => "error",
      "info_text" => "Sie k&ouml;nnen sich nicht selber antworten!"
    );
  }
  else
  {
    save_message($message, $chat_active_channel, 3, 0, 0, $user);
  }
}
*/
function command_kick($user, $reason = 0)
{
  global $chat_settings;
  global $chat_id;
  
  if (!empty($user))
  {
    if ($chat_settings['can_kick'])
    {
      if (empty($reason))
        $reason = "<||t22||>";
      
      $count = mysql_query("SELECT * from chat_users WHERE name LIKE '" . mysql_real_escape_string($user) . "'");
      if (mysql_num_rows($count) > 0)
      {
        $kick_string = "kick|" . chat_translate(mysql_real_escape_string($reason));
        if ($chat_settings['show_kick_user'])
          $kick_string = $kick_string . "|" . mysql_real_escape_string($_SESSION[$chat_id]['chat_username']);
        
        $kick_user = mysql_query("UPDATE chat_users SET action = '" . $kick_string . "' WHERE name LIKE '" . mysql_real_escape_string($user) . "'");
        
        return array(
          'info_type' => "info",
          'info_text' => "<||t21|'" . addslashes($user) . "'||>"
        ); //User %1 will be kicked
      }
      else
        return array(
          'info_type' => "error",
          'info_text' => "<||t9|'" . addslashes($user) . "'||>"
        ); //User %1 not found
    }
    else
      return array(
        'info_type' => "error",
        'info_text' => "<||t8||>"
      ); //no permissions
  }
  else
    return array(
      "info_type" => "error",
      "info_text" => "<||t50||>" //no user specified
    );
}
/*function command_ban($user, $time = 0, $reason = 0)
{
  global $chat_settings;
  global $chat_id;
  
  if (!empty($user))
  {
    if (is_numeric($time))
    {
      if ($chat_settings['can_ban'])
      {
        if (empty($reason))
          $reason = "<||t22||>";
        
        $count = mysql_query("SELECT * from chat_users WHERE name LIKE '" . mysql_real_escape_string($user) . "'");
        if (mysql_num_rows($count) > 0)
        {
          $ban_string = "ban|" . $time . chat_translate(mysql_real_escape_string($reason));
          if ($chat_settings['show_ban_user'])
            $ban_string = $ban_string . "|" . mysql_real_escape_string($_SESSION[$chat_id]['chat_username']);
          
          $ban_user = mysql_query("UPDATE chat_users SET action = '" . $ban_string . "' WHERE name LIKE '" . mysql_real_escape_string($user) . "'");
          
          return array(
            'info_type' => "info",
            'info_text' => "<||t21|'" . addslashes($user) . "'||>"
          ); //User %1 will be kicked
        }
        else
          return array(
            'info_type' => "error",
            'info_text' => "<||t9|'" . addslashes($user) . "'||>"
          ); //User %1 not found
      }
      else
        return array(
          'info_type' => "error",
          'info_text' => "<||t8||>"
        ); //no permissions
    }
    else
      return array(
        "info_type" => "error",
        "info_text" => "Ungültige Zeit Angabe!"
      );
  }
  else
    return array(
      "info_type" => "error",
      "info_text" => "Kein Benutzer angegeben!"
    );
}*/

function command_about()
{
  global $chat_version;
  return array(
    "info_type" => "info",
    "info_text" => "<i>SiPac v$chat_version</i> was developed by
  <a href='http://finastry.next-play.de/index.php?page=profile&user=Kim'>Kim Westesen</a> and <a href='http://nexttrex.de/Profil/Jan.html'>Jan Houben</a>, to have a highly customizable PHP and AJAX chat.<p>Thanks to <a href='http://www.famfamfam.com/'>famfamfam</a>
for the <a href='http://www.famfamfam.com/lab/icons/silk/'>Silk-Icons</a>.</p>
  If you have any questions, contact <a href='matilo:SiPac@next-game.de'>SiPac@next-game.de</a> ;-)",
    "info_nohide" => true
  );
}

function command_debug($type)
{
  global $chat_id;
  global $chat_settings;
  
  if ($type == "warn" OR $type == "all")
  {
    if ($chat_settings['can_see_debug'])
    {
      $_SESSION[$chat_id]['debug'] = $type;
      return array(
        "info_type" => "success",
        "info_text" => "<||t51||>" // debug enabled
      );
    }
    else
      return array(
        'info_type' => "error",
        'info_text' => "<||t8||>"
      ); //no permissions
  }
  else if ($type == "off")
  {
    $_SESSION[$chat_id]['debug'] = false;
    return array(
      "info_type" => "success",
      "info_text" => "<||t51||>" // debug enabled
    );
  }
  else
    return array(
      "info_type" => "warn",
      "info_text" => "<||t52||>" //type has to be warn, all or off (see /help debug)
    );
}


/*!!!!!!!!!!!!!!!!!!!!!!!!! Proxy functions  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/


$chat_default_settings["default_proxy"] = array(
  'proxy_log'
);
function proxy_log($message, $extra, $chat_user, $chat_time, $highlight)
{
  global $chat_settings;
  global $chat_id;
  global $chat_debug;
  
  
  $log_date     = date("d.m.Y", time());
  $log_time     = date("H:i:s", time());
  $log_folder   = date("Y", time());
  $log_filename = date("m", time());
  
  $log_folder = "../log/";
  
  if (substr(decoct(fileperms($log_folder)), -3) == 777)
  {
    if ($chat_settings['own_log_folder_for_chat_id'] == true)
      $log_folder = $log_folder . $chat_id . "/";
    else
      $log_folder = $log_folder . "global/";
    
    if (is_dir($log_folder) == false)
      mkdir($log_folder, 0777);
    
    
    $log_folder = $log_folder . date("Y", time());
    
    if (is_dir($log_folder) == false OR is_writable($log_folder))
    {
      if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['REMOTE_ADDR'];
      else
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      
      if ($extra == 0)
        $extra_name = "<||t13||>"; //Message
      else if ($extra == 1)
        $extra_name = "<||t14||>"; //Info
      else if ($extra == 2)
        $extra_name = "<||t15||>"; //System Message
      else if ($extra == 3)
        $extra_name = "<||t16||>"; //Highlight
      else if ($extra == 4)
        $extra_name = "me";
      else
        $extra_name = "?";
      
      if (is_dir($log_folder) == false)
        mkdir($log_folder, 0777);
      
      $chat_log_file = fopen($log_folder . '/' . $log_filename . '.log', "a+");
      $chat_log      = "\n" . $extra_name . " | ";
      
      if (!empty($highlight) AND $highlight != "none")
        $chat_log = $chat_log . "<||t17|$chat_user|$highlight||> "; //%1 to %2
      else
        $chat_log = $chat_log . $chat_user . ": ";
      
      $chat_log = $chat_log . html_entity_decode($message) . "		|$log_date, $log_time		$ip";
      fwrite($chat_log_file, chat_translate($chat_log, $chat_settings['log_language']));
      fclose($chat_log_file);
    }
    else
      $chat_debug['warn_once'][] = "Wrong Permissions in Folder \"$log_folder\". Please change it to 777!";
  }
  else
    $chat_debug['warn_once'][] = "Wrong Permissions in Folder \"log\". Please change it to 777!";
  
  return $message;
}



$chat_default_settings["default_afterproxy"] = array(
  'afterproxy_linker',
  'afterproxy_bbcode',
  'afterproxy_smileys'
);

//transform a url into a a tag link
function afterproxy_linker($message, $extra, $chat_user, $chat_time, $highlight)
{
  if (strpos($message, "<a href") === false)
  {
    $message = str_replace("https://www.", "www.", $message);
    $message = str_replace("http://www.", "www.", $message);
    $message = str_replace("www.", "http://www.", $message);
    $message = preg_replace("/ ([\w]+:\/\/[\w-?+:,&%;#~!=\.\/\@]+[\w\/])/i", " <a href=\"$1\" target=\"_blank\">$1</a>", $message);
    $message = preg_replace("/([\w]+:\/\/[\w-?+:,&%;#~!=\.\/\@]+[\w\/]) /i", " <a href=\"$1\" target=\"_blank\">$1</a> ", $message);
    
    if (strpos($message, "http://") === 0 AND strrpos($message, "http://") === 0 OR strpos($message, "https://") === 0 AND strrpos($message, "https://") === 0)
      $message = preg_replace("/([\w]+:\/\/[\w-?+:,&%;#~!=\.\/\@]+[\w\/])/i", "<a href=\"$1\" target=\"_blank\">$1</a>", $message);
    
    $message = preg_replace("/([\w-?+:,&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i", "<a href=\"mailto:$1\">$1</a>", $message);
  }
  return $message;
}
function afterproxy_bbcode($message, $extra, $chat_user, $chat_time, $highlight)
{
  $message = preg_replace('#\[b\](.*)\[/b\]#isU', "<b>$1</b>", $message);
  $message = preg_replace('#\[i\](.*)\[/i\]#isU', "<i>$1</i>", $message);
  $message = preg_replace('#\[u\](.*)\[/u\]#isU', "<u>$1</u>", $message);
  $message = preg_replace('#\[color=(.*)\](.*)\[/color\]#isU', "<span style=\"color: $1\">$2</span>", $message);
  
  return	$message;
}
function afterproxy_smileys($message, $extra, $chat_user, $chat_time, $highlight)
{
  global $chat_settings;
  global $chat_html_path;
  if ($extra != 1)
  {
    foreach ($chat_settings['smileys'] as $smiley_code => $smiley_url)
    {
      if (strpos($smiley_url, "http://") === false)
        $smiley_url = $chat_html_path . "themes/" . $chat_settings['theme'] . "/smileys/" . $smiley_url;
      
      $smiley_code_html = addslashes(htmlentities($smiley_code, ENT_QUOTES));
      $smiley_code      = str_replace("|", "&#x007C;", " " . htmlentities($smiley_code));
      
      $message = str_replace($smiley_code, "<img style='max-height: 20px;margin-right: 3px;' src='" . $smiley_url . "' title='" . $smiley_code_html . "' alt='" . $smiley_code_html . "'>", " " . $message);
      $message = trim($message);
    }
  }
  return $message;
}

?>