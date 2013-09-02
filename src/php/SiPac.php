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

//if this is an AJAX Connection
if (isset($_GET['task']) AND $_GET['task'] == "get_chat")
{
  $json_answer = array();
  if (isset($_POST['chat_string']))
  {
    //get the chat string, which contains all started chats
    $chat_string  = $_POST['chat_string'];
    //split all chats
    $chat_objects = explode("&&", $chat_string);
    
    //proceed every chat
    foreach ($chat_objects as $chat_object)
    {
      if (!empty($chat_object))
      {
	//split all transmitted variables
        $chat_variable_parts = explode("&", $chat_object);
        //save them
        foreach ($chat_variable_parts as $chat_variable_part)
        {
          $chat_variable                     = explode("=", $chat_variable_part);
          $chat_variables[$chat_variable[0]] = urldecode($chat_variable[1]);
        }
        //create the Chat class
        $chat = new Chat(false, false, $chat_variables['client_num'], $chat_variables['chat_id']);
	//obtain a nickname or load the old
        $chat->check_name();
        
        //save the writing status
        $chat->is_writing = $chat_variables['writing'];
        
        //create a temp json answer var to collect all tmp answer to a big json array
        $tmp_json_answer = array();
        
        //if one or more message were send
	if (isset($chat_variables['send_message']))
        {
	  $send_answer = array();
	  //split them
	  $messages_to_save = explode("|||", $chat_variables['send_message']);
          foreach ($messages_to_save as $message)
          {
	    // save the message and keep the answer of the save_message function (it could contain notifications)
            $send_answer = array_merge($send_answer, $chat->send_message($message));
          }
          //merge the json array with the send_answer array
	  $tmp_json_answer = array_merge($send_answer, $tmp_json_answer);
	}
	
        //get all new posts since the last request and save them in the var tmp json_answer
        $tmp_json_answer['get'] = $chat->get_posts($chat_variables['last_id']);
        
        //save user in the db and add other users to the userlist
        $tmp_json_answer['get']['userlist'] = $chat->handle_userlist();
        
	//save the tmp json array in the real one
	$json_answer[] = $tmp_json_answer;
      }
    }
    
  }
  echo json_encode($json_answer);
}
?>
