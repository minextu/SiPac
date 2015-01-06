<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2015 Jan Houben

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
class SiPac_Command
{
	private $chat;
	private $channel;
	private $command_list = array();
	
	public function __construct($chat)
	{
		$this->chat= $chat;
		$this->get_available_commands();
	}
	
	public function get_available_commands()
	{
		$command_folder = dirname(__FILE__) ."/../command";
		$files = scandir($command_folder);
		foreach ($files as $file)
		{
			if ($file != "." && $file != "..") 
			{
				$command_name = str_replace(".php", "", $file);
					
				if (in_array($command_name, $this->chat->settings->get("disabled_commands")) == false)
				{
					$this->command_list[] = $command_name;
				}
			}
		}
	}
	public function check($message, $channel)
	{
		$this->channel = $channel;
		
		if (strpos($message, "/") === 0)
		{ //message is a command
			$command_parts = explode(" ", $message, 2);
			$command_name = str_replace("/", "", $command_parts[0]);
			$command_class = "SiPacCommand_".$command_name;
	
			if (isset($command_parts[1]))
				$command_parameters =  $command_parts[1];
			else
				$command_parameters = "";
			
			$command_return = $this->check_custom_command($command_name, $command_parameters);
			if ($command_return !== false)
				return $command_return;
			else if (in_array($command_class, $this->command_list) === true)
			{
				$command_path = dirname(__FILE__) ."/../command/".$command_class.".php";
				include_once($command_path);
				if (class_exists($command_class))
				{
					$command = new $command_class;
					return $this->execute($command, $command_parameters);
				}
				else
					$this->chat->debug->add("Command found, but the classname is not '$command_class'", 1);
				
			}
			else
				return array("info_type"=>"warn", "info_text" => $this->chat->language->translate("<||command-not-found-text|".$command_name."||>"));
		}
		else
			return false;
	} 
	private function check_custom_command($command_name, $command_parameters)
	{
		if (in_array($command_name, $this->chat->settings->get('custom_commands')))
		{
			$command_class = "SiPacCommand_".$command_name;
			$command_path = dirname(__FILE__) ."/../../../conf/command/".$command_class.".php";
			if (file_exists($command_path))
			{
				include_once($command_path);
				if (class_exists($command_class))
				{
					$command = new $command_class;
					return $this->execute($command, $command_parameters);
				}
				else
				{
					$this->chat->debug->add("Command found, but the classname is not '$command_class'", 1);
					return false;
				}
			}
			else
			{
				$this->chat->debug->add($command_class.".php doesn't exist in 'conf/command/'!", 1);
				return false;
			}
		}
		else
			return false;
	}
	
	private function execute($command, $command_parameters)
	{
		$command->set_variables($this->chat, $this->channel, $command_parameters);
		if ($command->check_permission() == true)
		{
			$command_return = $command->execute();
	      
			if (is_array($command_return))
			{
				if (isset($command_return['info_text']))
					$command_return['info_text'] = $this->chat->language->translate($command_return['info_text']);
				return $command_return;
			}
			else
				return array();
			}
			else
				return array("info_type"=>"warn", "info_text" =>$this->chat->language->translate( '<||no-permissions-text||>'));
	}
}