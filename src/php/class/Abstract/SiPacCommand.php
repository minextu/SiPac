<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2014 Jan Houben

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
abstract class SiPacCommand
{
	final public function set_variables($chat, $channel, $parameters)
	{
		$this->chat = $chat;
		$this->channel = $channel;
		$this->parameters = $parameters;
	}
	
	public final function __construct()
	{
		if (!isset($this->usage))
			$this->debug->add('public $usage missing!', 1);
		else if (!isset($this->description))
			$this->debug->add('public public $description missing!', 1);
	}
	
	abstract public function check_permission();
	abstract public function execute();
}
?>