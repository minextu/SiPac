<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2014 Jan Houben

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
$SiPac_version = "0.1.90-git";


//initiate the session, if not already started
if (strlen(session_id()) < 1)
  session_start();

//Thanks to selfhtml.org for this function
function check_mobile() 
{
	$agents = array(
	'Windows CE', 'Pocket', 'Mobile',
	'Portable', 'Smartphone', 'SDA',
	'PDA', 'Handheld', 'Symbian',
	'WAP', 'Palm', 'Avantgo',
	'cHTML', 'BlackBerry', 'Opera Mini',
	'Nokia', 'webOS', 'android', 'Android'
	);

	//Check Browseragent, for a mobile browser
	for ($i=0; $i<count($agents); $i++)
	{
		if(isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], $agents[$i]) !== false)
		{
			if (!isset($_GET['mobile']) OR $_GET['mobile'] != "false")
				return true;
		}
	}
	if (isset($_GET['mobile']) AND $_GET['mobile'] != "false")
		return true;
    
	return false;
}

//include the default config
require_once(dirname(__FILE__)."/default_conf.php");

//include all classes
require_once(dirname(__FILE__)."/include_classes.php");

//if this is an AJAX Connection
if (isset($_GET['task']) AND $_GET['task'] == "get_chat")
{
	//Tell the Browser, to not use cache
	Header("Pragma: no-cache");
	Header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
	//Header("Content-Type: application/json");

	$json_answer = array();
	
	if (!empty($_POST['sipac_string']))
	{
		
		//get the chat string, which contains all started chats
		$chat_string  = $_POST['sipac_string'];
		//decode the json code
		$chat_objects = json_decode($chat_string, true);
		
		//proceed every chat
		foreach ($chat_objects as $chat_num => $chat_variables)
		{
			if (!empty($chat_variables))
			{
				if (isset($chat_variables['client']) AND isset($chat_variables['id']))
				{
					//create the Chat class
					$SiPac = new SiPac_Chat(false, $chat_variables, $chat_num);
					
					if ($SiPac->debug->get_error() !== false)
					{
						$json_answer[] = array("error" => $SiPac->debug->get_error());
						continue;
					}
					
					//if the chat is started for the first time, delete the old cached userlist
					if (isset($chat_variables['first_start']) AND $chat_variables['first_start'] == "true")
						unset($_SESSION['SiPac'][$SiPac->id]['userlist'][$SiPac->client_num]);
						
					//active channel has to be a valid channel
					if (array_search($SiPac->channel->active, $SiPac->channel->ids) === false)
						DIE("You haven't joined this channel!");
	  
					//load afk status
					$SiPac->afk->load();
	  
					//create a temp json answer var to collect all tmp answer to a big json array
					$tmp_json_answer = array();
	  
					//save user in the db and add other users to the userlist
					$tmp_json_answer['get']['userlist'] = $SiPac->handle_userlist();
					
					//if one or more message were send
					if (!empty($chat_variables['send_messages']))
					{
						$send_answer = array();
						//split them
						$messages_to_save = $chat_variables['send_messages'];
						foreach ($messages_to_save as $message)
						{
							// save the message and keep the answer of the save_message function (it could contain notifications)
							if ($SiPac->is_kicked === false)
								$send_answer = array_merge($send_answer, $SiPac->message->send($message['text'], $message['channel']));
						}
						//merge the json array with the send_answer array
						$tmp_json_answer = array_merge($send_answer, $tmp_json_answer);
					}
	  
					//get all new posts since the last request and save them in the var tmp json_answer
					$new_messages = $SiPac->message->get($SiPac->settings->get('last_parameters')['last_message_id']);
					if (is_array($new_messages))
						$tmp_json_answer['get'] = array_merge($tmp_json_answer['get'], $new_messages);
	  
					$check_changes['get'] = $SiPac->check_changes();
	  
					//check_changes can contain messages, so merge with the orginal json_answer
					$tmp_json_answer['get'] = array_merge($tmp_json_answer['get'], $check_changes['get']);
	  
					//add debug entries
					foreach ($SiPac->channel->ids as $channel)
					{
						$tmp_json_answer['debug'][$channel] = $SiPac->debug->get($SiPac->settings->get('debug_level'), $channel);
					}
					//if (!empty($tmp_json_answer['debug'][0]))
						$tmp_json_answer['debug'][0] = $SiPac->debug->get($SiPac->settings->get('debug_level'), 0);
					
					if ($SiPac->debug->get_error() !== false)
						$tmp_json_answer = array("error" => $SiPac->debug->get_error());
					//save the tmp json array in the real one
					$json_answer[] = $tmp_json_answer;
				}
			}
		}
    
	}
	$json_answer_fin['SiPac'] = $json_answer;
	echo json_encode($json_answer_fin);
}
?>
