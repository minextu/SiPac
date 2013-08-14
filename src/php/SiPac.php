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
 
$chat_version = "0.0.3.0";

//initiate the session, if not already started
if (strlen(session_id()) < 1)
  session_start();


//Tell the Browser, not to use cache
Header("Pragma: no-cache");
Header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
Header("Content-Type: text/html");


//include all classes
require_once(dirname(__FILE__)."/include_classes.php");


if (isset($_GET['task']) AND $_GET['task'] == "get_chat")
{
  $json_anwer = array();
  if (isset($_POST['chat_string']))
  {
    $chat_string  = $_POST['chat_string'];
    $chat_objects = explode("&&", $chat_string);
    foreach ($chat_objects as $chat_object)
    {
      if (!empty($chat_object))
      {
        $chat_variable_parts = explode("&", $chat_object);
        
        foreach ($chat_variable_parts as $chat_variable_part)
        {
          $chat_variable                     = explode("=", $chat_variable_part);
          $chat_variables[$chat_variable[0]] = urldecode($chat_variable[1]);
        }
        
        $chat = new Chat(false, false, $chat_variables['client_num'], $chat_variables['chat_id']);
        
        $chat->check_name();
        
        $json_anwer[]['get'] = $chat->get_posts($chat_variables['last_id']);
       
      }
    }
    
  }
  echo json_encode($json_anwer);
}
?>
