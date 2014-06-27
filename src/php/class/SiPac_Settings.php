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
 
 class SiPac_Settings
{
	private $settings;
	private $id;
	
	public function __construct($id)
	{
		$this->id = $id;
	}
	
	public function set($setting, $value)
	{
		$this->settings[$setting] = $value;
		$_SESSION['SiPac'][$this->id]['settings'][$setting] = $value;
		return true;
	}
	
	public function get($setting)
	{
		if (isset($this->settings[$setting]))
			return $this->settings[$setting];
		else
			return false;
	}
	
	public function load($settings=false)
	{
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
	}
	
}

