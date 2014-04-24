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
trait SiPac_settings
{ 
	public $settings = array();
	public $html_path;

	private function load_settings($settings=false, $id=false)
	{
		//get the chat id, either from the settings or the function variable $id
		if ($id !== false)
			$this->id = $id;
		else if ($settings !== false AND isset($settings['chat_id']))
			$this->id = $settings['chat_id'];
		else
		die("No chat id specified!");
    
		//if the settings are already given, load them
		if ($settings !== false)
			$this->settings = $settings;
		else if (isset($_SESSION['SiPac'][$this->id]['settings'])) //else load them from the php session (if set)
			$this->settings = $_SESSION['SiPac'][$this->id]['settings'];
		else
			die("No settings found!");
    
 
    
		$default_settings = return_default_settings();
		//if some settings are not set, load them from the default config
		foreach ($default_settings as $setting => $default)
		{
			if (!isset($this->settings[$setting]))
			{
				$this->settings[$setting]  = $default;
				//$chat_debug['all_once'][] = "Setting $setting is unused!";
			}
		}
		//save the settings in the session
		$_SESSION['SiPac'][$this->id]['settings'] = $this->settings;

		//get the correct html path or load a custom
		if ($this->settings['html_path'] == "!!AUTO!!")
			$this->html_path = str_replace("//", "/", "/" . str_replace($_SERVER['DOCUMENT_ROOT'], "", realpath(dirname(__FILE__)."/../../../..") . "/"));
		else
			$this->html_path = $this->settings['html_path'];
    
	}
}