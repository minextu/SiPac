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
trait SiPac_command
{
	private function check_command($message)
	{
		if (strpos($message, "/") === 0)
		{ //message is a command
			$command_parts = explode(" ", $message, 2);
			$command_name = str_replace("/", "", $command_parts[0]);
	
			if (isset($command_parts[1]))
				$command_parameters =  $command_parts[1];
			else
				$command_parameters = "";
	  
			$command_class = "SiPacCommand_".$command_name;
			$command_path = dirname(__FILE__) ."/../../command/".$command_class.".php";
			if (file_exists($command_path))
			{
				include_once($command_path);
				if (class_exists($command_class))
				{
					$command = new $command_class;
					$command->set_variables($this, $command_parameters);
					if ($command->check_permission() == true)
					{
						$command_return = $command->execute();
	      
						if (is_array($command_return))
						{
							if (isset($command_return['info_text']))
								$command_return['info_text'] = $this->translate($command_return['info_text']);
							return $command_return;
						}
						else
							return array();
					}
					else
						return array("info_type"=>"warn", "info_text" =>$this->translate( '<||no-permissions-text||>'));
				}
				else
					return array("info_type"=>"error", "info_text" => 'Classname is not "'.$command_class.'"');
			}
			else
				return array("info_type"=>"warn", "info_text" => $this->translate("<||command-not-found-text|".$command_name."||>"));
		}
		else
			return false;
	} 
}