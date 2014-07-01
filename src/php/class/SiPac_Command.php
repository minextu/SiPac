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
class SiPac_Command
{
	private $chat;
	
	public function __construct($chat)
	{
		$this->chat= $chat;
	}
	
	public function check($message)
	{
		if (strpos($message, "/") === 0)
		{ //message is a command
			$command_parts = explode(" ", $message, 2);
			$command_name = str_replace("/", "", $command_parts[0]);
	
			if (isset($command_parts[1]))
				$command_parameters =  $command_parts[1];
			else
				$command_parameters = "";
			
			$command_return = $this->check_custom_command($command_name, $command_parameters);
			if ($command_return !== false)
				return $command_return;
			else
			{
				$command_class = "SiPacCommand_".$command_name;
				$command_path = dirname(__FILE__) ."/../command/".$command_class.".php";
				if (file_exists($command_path))
				{
					include_once($command_path);
					if (class_exists($command_class))
					{
						$command = new $command_class;
						return $this->execute($command, $command_parameters);
					}
					else
						return array("info_type"=>"error", "info_text" => 'Classname is not "'.$command_class.'"');
				}
				else
					return array("info_type"=>"warn", "info_text" => $this->chat->language->translate("<||command-not-found-text|".$command_name."||>"));
			}
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
				$command = new $command_class;
				return $this->execute($command, $command_parameters);
			}
			else
				die($command_class." doesn't exist!");
		}
		else
			return false;
	}
	
	private function execute($command, $command_parameters)
	{
		$command->set_variables($this->chat, $command_parameters);
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