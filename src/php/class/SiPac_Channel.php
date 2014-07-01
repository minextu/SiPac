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
class SiPac_Channel
{ 
	private $chat;
	
	public $list;
	public $ids;
	public $new = array();
	public $active;
	
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	
	public function add($channels, $is_id=false)
	{
		foreach ($channels as $channel)
		{
			if ($is_id == true)
			{
				$this->list[] = $this->create_array($channel, true);
				$this->ids[] = $channel;
			}
			else
			{
				$this->list[] = $this->create_array($channel);
				$this->ids[] = $this->encode($channel);
			}
		}
	}
	public function encode($channel)
	{
		return rtrim(strtr(base64_encode($channel), '+/', '-_'), '=');
	}
	public function decode($channel)
	{
		if (!empty($_SESSION['SiPac'][$this->chat->id]['channel_titles'][$channel]))
			$title =  $_SESSION['SiPac'][$this->chat->id]['channel_titles'][$channel];
		else
			$title = base64_decode($channel);
		return $title;
	}
	public function get_name($channel_id)
	{
		return $this->list[array_search($channel_id, $this->ids)]['title'];
	}
	public function get_id($channel_title)
	{
		foreach ($this->list as $channel)
		{
			if ($channel['title'] == $channel_title)
				return $channel['id'];
		}
	}
	public function create_array($channel, $is_id=false)
	{
		if ($is_id == true)
		{
			$title = utf8_decode($this->decode($channel));
			return  array("title" => $title, "id" => $channel);
		}
		else
			return array("title" => $channel, "id" => $this->encode($channel));
	}
	public function check()
	{
		if (isset($_SESSION['SiPac'][$this->chat->id]['old_channels']))
		{
			foreach ($this->list as $channel)
			{
				if (!in_array($channel, $_SESSION['SiPac'][$this->chat->id]['old_channels']))
				{
					if ($this->chat->settings->get('can_join_channels') == false AND array_search($channel['title'], $this->chat->settings->get('channels')) === false)
					{
						DIE("You are not allowed to join this channel!");
					}
					$this->new[] = $channel['id'];
				}
			}
		}
	
		$_SESSION['SiPac'][$this->chat->id]['old_channels'] = $this->list;
	}
	public function restore_old()
	{
		//restore old channels
		if (isset($_SESSION['SiPac'][$this->chat->id]['old_channels'] ))
		{
			foreach ($_SESSION['SiPac'][$this->chat->id]['old_channels'] as $channel)
			{
				if (array_search($channel, $this->list) === false AND $this->chat->settings->get('can_join_channels') == true)
				{
					$this->list[] = $channel;
				}	
			}
		}
	}
} 
