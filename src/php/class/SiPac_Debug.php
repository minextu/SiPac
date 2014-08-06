<?php
/*
 *   SiPac is highly customizable PHP and AJAX chat
 *   Copyright (C) 2013 Jan Houben
 * 
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 * 
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, write to the Free Software Foundation, Inc.,
 *   51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
class SiPac_Debug
{ 
	private $debug;
	private $id;
	private $is_new;
	
	public function __construct($id, $is_new)
	{
		$this->id = $id;
		$this->is_new = $is_new;
		
		if (!empty($_SESSION['SiPac'][$this->id]['debug']))
		{
			$this->debug = $_SESSION['SiPac'][$this->id]['debug'];
		}
	}
	
	public function add($text, $type=3, $channel=0)
	{
		$this->debug[$type][$channel][] = $text;
		if ($this->is_new == true)
			$_SESSION['SiPac'][$this->id]['debug'] = $this->debug;
	}
	
	public function error($message)
	{
		$this->debug['error'] = $message;
		if ($this->is_new == true)
			die($message);
	}
	
	public function get_error()
	{
		if (isset($this->debug['error']))
			return $this->debug['error'];
		else
			return false;
	}
	
	public function get($type, $channel)
	{
		if ($type == "off" OR $type === false OR $type % 1 != 0 OR $type < 0)
			return false;
		else
		{
			$tmp_array = array();
			for ($i = 0; $i <= $type; $i++)
			{
				if (isset($this->debug[$i][$channel]))
				{
					foreach ($this->debug[$i][$channel] as $debug)
					{
						$new_array = array("type" => $i, "text" => $debug);
						if (array_search($new_array, $tmp_array) === false)
							$tmp_array[] = $new_array;
					}
					unset($_SESSION['SiPac'][$this->id]['debug'][$i][$channel]);
				}
			}
			return $tmp_array;
		}
	}
}
