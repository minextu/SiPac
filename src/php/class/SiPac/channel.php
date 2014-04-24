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
trait SiPac_channel
{ 
	public $channels;
	public $channel_ids;
	public $new_channels = array();
	public $active_channel;
	
	private function add_channels($channels, $is_id=false)
	{
		foreach ($channels as $channel)
		{
			if ($is_id == true)
			{
				$this->channels[] = $this->create_channel_array($channel, true);
				$this->channel_ids[] = $channel;
			}
			else
			{
				$this->channels[] = $this->create_channel_array($channel);
				$this->channel_ids[] = $this->encode_channel($channel);
			}
		}
	}
	public function encode_channel($channel)
	{
		return rtrim(strtr(base64_encode($channel), '+/', '-_'), '=');
	}
	public function decode_channel($channel)
	{
		return base64_decode($channel);
	}
	public function get_channel_name($channel_id)
	{
		return $this->channels[array_search($channel_id, $this->channel_ids)]['title'];
	}
	public function get_channel_id($channel_title)
	{
		foreach ($this->channels as $channel)
		{
			if ($channel['title'] == $channel_title)
				return $channel['id'];
		}
	}
	public function create_channel_array($channel, $is_id=false)
	{
		if ($is_id == true)
			return  array("title" =>utf8_decode($this->decode_channel($channel)), "id" => $channel);
		else
			return array("title" => $channel, "id" => $this->encode_channel($channel));
	}
	private function check_channels()
	{
		if (isset($_SESSION['SiPac'][$this->id]['old_channels']))
		{
			foreach ($this->channels as $channel)
			{
				if (!in_array($channel, $_SESSION['SiPac'][$this->id]['old_channels']))
				{
					if ($this->settings['can_join_channels'] == false AND array_search($channel['title'], $this->settings['channels']) === false)
					{
						DIE("You are not allowed to join this channel!");
					}
					$this->new_channels[] = $channel['id'];
				}
			}
		}
	
		$_SESSION['SiPac'][$this->id]['old_channels'] = $this->channels;
	}
	private function restore_old_channels()
	{
		//restore old channels
		if (isset($_SESSION['SiPac'][$this->id]['old_channels'] ))
		{
			foreach ($_SESSION['SiPac'][$this->id]['old_channels'] as $channel)
			{
				if (array_search($channel, $this->channels) === false AND $this->settings['can_join_channels'] == true)
				{
					$this->channels[] = $channel;
				}	
			}
		}
	}
}