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
	
	public function check($post_array, $type, $check_spam=true)
	{
		$proxy_folder = dirname(__FILE__)."/../proxy/".$type;
		$files = scandir($proxy_folder);
		foreach ($files as $file)
		{
			if ($file != "." && $file != "..") 
			{
				$class_name = str_replace(".php", "", $file);
				$class_name = preg_replace('/(?<=^|\s)SiPacProxy_([0-9]+)/i', 'SiPacProxy', $class_name);
				$proxy_name = str_replace("SiPacProxy_", "", $class_name);
					
				if (in_array($proxy_name, $this->chat->settings->get("disabled_".$type."_proxies")) == false AND ($proxy_name != "spam" OR $check_spam == true))
				{
					include_once($proxy_folder."/".$file);
						
					if (class_exists($class_name))
					{
						$proxy = new $class_name;
						$proxy->set_variables($this->chat, $post_array);
						
						$post_array = $proxy->execute();
						}
					else
						$this->chat->debug->add("Proxy found, but the classname is not '$class_name'", 1);
				}
			}
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
			$proxy_path = $proxy_folder."/SiPacProxy_".$proxy_name.".php";
			$class_name = "SiPacProxy_".$proxy_name;
			if (file_exists($proxy_path))
			{
				include_once($proxy_path);

				if (class_exists($class_name))
				{
					$proxy = new $class_name;
					$proxy->set_variables($this->chat, $post_array);
						
					$post_array = $proxy->execute();
				}
				else
					$this->chat->debug->add("Proxy found, but the classname is not '$class_name'", 1);
			}
			else
				$this->chat->debug->add($class_name.".php doesn't exist in 'conf/proxy/'!", 1);
		}
		return $post_array;
	}
	
	public function check_custom_functions($values, $function)
	{
		/*$function_folder = dirname(__FILE__)."/../../../conf/functions";
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
				$this->chat->debug->add('Classname is not "'.$function_name.'"', 0);
		}
		else*/
			return true;
	}
} 
