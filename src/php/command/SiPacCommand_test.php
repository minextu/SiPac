<?php

class SiPacCommand_test implements SiPacCommand
{
	public $usage = "/test <type> <name>";
	public $description = "Executes a custom proxy or function [type]. To test the Proxy \"SiPacProxy_example\" you would enter \"/test proxy example\"";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return true;
	}
	public function execute()
	{
		$parameters = explode(" ", $this->parameters);
		if (!empty($parameters[1]))
		{
			if ($parameters[0] == "proxy")
			{
				$post_array = array("message" =>"test message", "type" => 0, "channel" =>"Test_Channel","user"=>"testuser","time"=> time());
				$proxy_folder = dirname(__FILE__)."/../../../conf/proxy";
				$proxy_name = $parameters[1];
				
				include_once($proxy_folder."/SiPacProxy_".$proxy_name.".php");
				$class_name = "SiPacProxy_".$proxy_name;
				if (class_exists($class_name))
				{
					$proxy = new $class_name;
					$proxy->set_variables($this->chat, $post_array);
							
					$post_array = $proxy->execute();
				}
				else
					die('Classname is not "'.$proxy_name.'"');		
					
				return array("info_type" => "info", "info_text" => print_r($post_array, true), 'info_nohide' => true);
			}
			else if ($parameters[0] == "function")
			{
				$values_array = array( "channel" =>"Test_Channel","user"=>"testuser","last_update"=> time());
				$proxy_folder = dirname(__FILE__)."/../../../conf/functions";
				$proxy_name = $parameters[1];
				
				include_once($proxy_folder."/SiPacFunction_".$proxy_name.".php");
				$class_name = "SiPacFunction_".$proxy_name;
				if (class_exists($class_name))
				{
					$proxy = new $class_name;
					$proxy->set_variables($this->chat, $values_array);
							
					$answer = $proxy->execute();
				}
				else
					die('Classname is not "'.$proxy_name.'"');		
					
				return array("info_type" => "info", "info_text" => $answer, 'info_nohide' => true);
			}
		}
		else
			return array("info_type" => "error", "info_text" => "<||arguments-missing-text||>");
	}
}

?>