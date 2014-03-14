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
trait SiPac_proxy
{
	public function check_proxy($post_array, $type)
	{
		$proxy_folder = dirname(__FILE__)."/../../proxy/".$type;
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
						$proxy->set_variables($this, $post_array);
					
						$post_array = $proxy->execute();
					}
					else
						die('Classname is not "'.$class_name.'"');
				}
			}
			closedir($handle);
		}
		return $post_array;
	}
}