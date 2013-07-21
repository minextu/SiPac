<?php
if (strlen(session_id()) < 1) {
    session_start();
}

Header("Pragma: no-cache");
Header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
Header("Content-Type: text/html");


//$chat_html_path = str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(dirname(__FILE__))."/");



/* MAIN FUNCTIONS */
$chat_version = "0.0.2.1";
$chat_debug = array("all" => array(), "all_once" => array(), "warn" => array(), "warn_once" => array());

/* !! check errros, add default settings, save settings !! */
function check_chat($id=0, $client_num=0)
	{
		global $chat_settings;
		global $chat_id;
		global $chat_client_num;
		global $chat_debug;
		global $chat_html_path;
				
		if (!empty($client_num))
			$chat_client_num = $client_num;
		else if (!empty($_GET['client_num']))
			$chat_client_num = $_GET['client_num'];
		else
			{
				if (!empty($_POST['last_id']))
					$last_id	= $_POST['last_id'];
				else
					$last_id = "undefined";
					
				$array = array('info_type'=>"error", 'info_text'=>"Client ID Error (Outdate Files?)");
				$array['get'] = array("last_id" => $last_id);
				echo json_encode($array);
				die();
			}
		if (!empty($id))
			$chat_id = $id;
		else if (!empty($_GET['id']))
			$chat_id = $_GET['id'];
		else
			{
				if (!empty($_POST['last_id']))
					$last_id	= $_POST['last_id'];
				else
					$last_id = "undefined";
					
				$array = array('info_type'=>"error", 'info_text'=>"Empty chat id!");
				$array['get'] = array("last_id" => $last_id);
				echo json_encode($array);
				die();
			}
		if (isset($chat_settings))
			{
				$_SESSION[$chat_id]['is_kick'] = false;
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
			
		
		require_once ("default_conf.php");

		/*Load default settings and check if a setting is not set*/
		foreach($chat_default_settings as $setting => $default)
			{
				if (!isset($chat_settings[$setting]))
					{
						$chat_settings[$setting] = $default;
						$chat_debug['all_once'][] = "Setting $setting is unused!";
					}
			}
			
			
		
	
			
			if (!empty($_SESSION[$chat_id]['theme_no_afk']))
			{
				if ($chat_settings['deactivate_afk'] == false)
					$chat_debug['warn'][] = "AFK won't work in this theme!";
					
				$chat_settings['deactivate_afk'] = true;
			}
			
		if ($chat_settings['html_path'] == "!!AUTO!!")
		{
			$chat_html_path = str_replace("//", "/", "/".str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(dirname(__FILE__))."/"));
			$_SESSION[$chat_id]['settings']['html_path'] = $chat_html_path;
		}
		else
			$chat_html_path = $chat_settings['html_path'];
			
	}


/* if this is an ajax connection */
if (!empty($_GET['task']))
	{
		check_chat();
		/*Connect to mysql */
		if(!mysql_connect($chat_settings['host'], $chat_settings['user'], $chat_settings['pw'])){
				echo mysql_error();
			}else{
				if(!mysql_select_db($chat_settings['db'])){
					echo mysql_error();
				}
		}
		require_once (dirname(__FILE__)."/../conf/custom_functions.php");
		
		//check the channels
  	if (!empty($_POST['channels']))
  	{
  		$chat_channels_post = $_POST['channels'];
  		$chat_channels_tmp = explode("|||", $chat_channels_post);
  		foreach ($chat_channels_tmp as $channel)
  		{
  			if (array_search($channel, $chat_settings['channels']) !== false OR $chat_settings['can_join_channels'] == true)
  				$chat_channels[] = $channel;
  			else
  				$chat_debug['warn_once'][] = "Not allowed to view the Channel \"$channel\"!"; 
  		}
  	}
  	if (empty($_POST['channels']) OR empty($chat_channels))
  	{
			echo "You are in no channels!";	
			DIE();
  	}
  	if (!empty($_POST['active_channel']))
  	{
  		if (array_search($_POST['active_channel'], $chat_channels) !== false)
  			$chat_active_channel = $_POST['active_channel'];
  	}
  	if (empty($_POST['active_channel']) OR empty($chat_active_channel))
  	{
			echo "Wrong active channel!";	
			DIE();
  	}
	}
else
	{ /* if it is not an ajax connection*/
		/* include only functions.php to create the chat html*/
		require_once(dirname(__FILE__)."/chat_functions.php");
		return false;
	}
require_once(dirname(__FILE__)."/chat_functions.php");


	
/* FIRST START OR USERNAME CHANGE*/
if (!isset($_SESSION[$chat_id]['chat_ready']) OR isset($_SESSION[$chat_id]['chat_old_username_var']) AND $_SESSION[$chat_id]['chat_old_username_var'] != $chat_settings['username_var'] OR isset($_SESSION[$chat_id]['chat_custom_name']) AND $_SESSION[$chat_id]['chat_custom_name'] != $_SESSION[$chat_id]['chat_username'])
	{
		if (isset($_SESSION[$chat_id]['chat_ready']))
			$old_username = $_SESSION[$chat_id]['chat_username'];
		
		if (isset($_SESSION[$chat_id]['chat_custom_name']))
			$_SESSION[$chat_id]['chat_username'] = $_SESSION[$chat_id]['chat_custom_name'];
  	else if ($chat_settings['username_var'] == "!!AUTO!!")
  		$_SESSION[$chat_id]['chat_username'] = "Guest ".mt_rand(1, 1000);
  	else if ($chat_settings['username_var'] != "!!AUTO!!")
  		$_SESSION[$chat_id]['chat_username'] = $chat_settings['username_var'];
  		
  	if (isset($_SESSION[$chat_id]['chat_ready']))
  		{
  			$_SESSION[$chat_id]['chat_is_user_rename'] = true;
  			$delete_user = mysql_query("DELETE FROM chat_users WHERE name LIKE '".mysql_real_escape_string(htmlentities($old_username, ENT_QUOTES))."'");
  			
  			foreach ($chat_channels as $channel)
  			{
  				save_message("<||t0|".$old_username."|".$_SESSION[$chat_id]['chat_username']."||>", $channel, 1); //%1 is now %2
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
  			$_SESSION[$chat_id]['chat_ready'] = true;
  			$_SESSION[$chat_id]['chat_users'] = array();
				
		
  		}
 	}
//If this is the first ajax connection (conversation and userlist are empty)
if (!empty($_POST['first_start']) AND $_POST['first_start'] == "true")
{
	//Reset the Userlist
	$_SESSION[$chat_id]['chat_userlist'][$chat_client_num] = array();
	
	
	//Check all in use Language packs
	require (dirname(__FILE__)."/lang/en.php");
	$chat_orginal_text = $chat_text;
	require (dirname(__FILE__)."/lang/".$chat_settings['language'].".php");
	if (count($chat_text) != count($chat_orginal_text))
		$chat_debug['warn_once'][] = "The ".$chat_settings['language']." Language Pack is outdate!";
					
	if ($chat_settings['language'] != $chat_settings['log_language'])
	{
		require (dirname(__FILE__)."/lang/".$chat_settings['log_language'].".php");
		if (count($chat_text) != count($chat_orginal_text))
			$chat_debug['warn_once'][] = "The ".$chat_settings['log_language']." Language Pack is outdate!";
	}
	
}
	
/*  MAIN TASKS */

//SEND MESSAGE TASK - save a message in the db
if ($_GET['task'] == "send_message")
  {
  	if (isset($_POST['text']))
  		{
			  	$chat_message = str_replace("|", "&#x007C;",trim(htmlentities($_POST['text'])));
			  	$is_special_command = handle_special_commands(addslashes($chat_message));
			  	if ($chat_message != "" AND !$is_special_command)
			  		{
							$chat_message = handle_replace_commands($chat_message);
								 
									//save the message
									save_message($chat_message, $chat_active_channel);
							
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
    	}
    else
    	{
    		$chat_json_array['info_type'] = "error";
    		$chat_json_array['info_text'] = chat_translate("<||t11||>"); //nothing is entered
    	}
    
  	//get messages, so that you can see your sent message instantly
		if(!empty($_POST['last_id']) AND $_POST['last_id'] != "none")
			$last_id = $_POST['last_id'];
		else
			$last_id = 0;
			
		$chat_json_array['get'] = get_messages($last_id);
		

    $chat_json_array['debug'] = handle_debug();
    	

  }
//GET MESSAGES TASK - get all messages from the db
else if ($_GET['task'] == "get_chat")
  {
  	if ($chat_settings['deactivate_afk'] == false)
  	{
			if (isset($_SESSION[$chat_id]['chat_new_afk']) AND $_SESSION[$chat_id]['chat_new_afk'] == false AND $_SESSION[$chat_id]['chat_afk'] == true OR
					$chat_settings['auto_detect_no_afk'] AND $_POST['writing'] == "true" AND $_SESSION[$chat_id]['chat_afk'] == true)
				{
					$_SESSION[$chat_id]['chat_afk'] = false;
					foreach ($chat_channels as $channel)
  				{
						save_message("<||t19||>",$channel, 4);//is back again
					}
				}
			else if (isset($_SESSION[$chat_id]['chat_new_afk']) AND $_SESSION[$chat_id]['chat_new_afk'] == true AND $_SESSION[$chat_id]['chat_afk'] == false)
				{
					$_SESSION[$chat_id]['chat_afk'] = true;
					foreach ($chat_channels as $channel)
  				{
						save_message("<||t18||>",$channel,4); //is now away
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
  		
  	if (isset($_POST['writing']) AND $_POST['writing'] == "true")
  		$_SESSION[$chat_id]['chat_writing'] = true;
  	else
  		$_SESSION[$chat_id]['chat_writing'] = false;
  		
    if(!empty($_POST['last_id']) AND $_POST['last_id'] != "none")
      $last_id = $_POST['last_id'];
    else
    	$last_id = 0;
    	
   		
    	
  	$chat_json_array['get'] = get_messages($last_id);  

  	$chat_json_array['debug'] = handle_debug();
  }

echo json_encode($chat_json_array);

?>