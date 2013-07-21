<?php

function command_example($var1, $var2)
	{
		global $chat_settings; //use this if you want to have access to the settings
		if (empty($var1))
			{
				return array('info_type' => "warn", 'info_text' => "var1 is empty!"); //show a message
			}
		else
			return true; //show no message
	}
	
		
?>