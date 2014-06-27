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
class SiPac_Proxy
{
	private $chat;
	
	public function __construct($chat)
	{
		$this->chat= $chat;
	}
	
	public function check($post_array, $type)
	{
		$proxy_folder = dirname(__FILE__)."/../proxy/".$type;
		if ($handle = opendir($proxy_folder))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file != "." && $file != "..") 
				{
					include_once($proxy_folder."/".$file);
					$class_name = str_replace(".php", "", $file);
				
					if (class_exists($class_name))
					{
						$proxy = new $class_name;
						$proxy->set_variables($this->chat, $post_array);
					
						$post_array = $proxy->execute();
					}
					else
						die('Classname is not "'.$class_name.'"');
				}
			}
			closedir($handle);
		}
		
		$post_array = $this->check_custom_proxy($post_array, $type);
		
		return $post_array;
	}
	
	private function check_custom_proxy($post_array, $type)
	{
		if ($type == "client")
			$custom_proxy_array = $this->chat->settings->get('custom_client_proxies');
		else
			$custom_proxy_array = $this->chat->settings->get('custom_server_proxies');
		
		$proxy_folder = dirname(__FILE__)."/../../../conf/proxy";
		
		foreach ($custom_proxy_array as $proxy_name)
		{
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
		}
		return $post_array;
	}
	
	public function check_custom_functions($values, $function)
	{
		$function_folder = dirname(__FILE__)."/../../../conf/functions";
		if ($this->chat->settings->get($function."_function") != false)
		{
			$function_name = $this->chat->settings->get($function."_function");
			
			include_once($function_folder."/SiPacFunction_".$function_name.".php");
			$class_name = "SiPacFunction_".$function_name;
			
			if (class_exists($class_name))
			{
				$function = new $class_name;
				$function->set_variables($this->chat, $values);
					
				return $function->execute();
			}
			else
				die('Classname is not "'.$function_name.'"');
		}
		else
			return true;
	}
} 
