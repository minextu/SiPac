<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2015 Jan Houben

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
class SiPac_Afk
{ 
	public $status;
	private $chat;
	
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	public function load()
	{
		if (!isset($_SESSION['SiPac'][$this->chat->id]['afk']))
		{
			$this->status = $this->chat->settings->get('start_as_afk');
			$_SESSION['SiPac'][$this->chat->id]['afk'] = $this->chat->settings->get('start_as_afk');
		}
		else if ($_SESSION['SiPac'][$this->chat->id]['afk'] == false)
			$this->status = false;
		else
			$this->status = true;
			
		if ($this->status == true AND $this->chat->settings->get('auto_detect_no_afk') == true AND $this->chat->is_writing == "true")
			$this->set(false);
	}
	
	public function set($status, $reason=false)
	{
		$this->status =$status;
		
		if ($_SESSION['SiPac'][$this->chat->id]['afk'] != $this->status)
		{
			$_SESSION['SiPac'][$this->chat->id]['afk'] = $this->status;
			foreach ($this->chat->channel->ids as $channel)
			{
				if ($this->chat->settings->get('deactivate_afk') == false)
				{
					if ($this->status == false)
						$this->chat->message->send("<||user-now-not-afk-notification|".$this->chat->nickname."||>", $channel, 1);
					else
					{
						if (empty($reason))
							$afk_text = "<||user-now-afk-no-reason-notification|".$this->chat->nickname."||>";
						else
							$afk_text = "<||user-now-afk-notification|".$this->chat->nickname."|".$reason."||>";
							
						$this->chat->message->send($afk_text, $channel, 1);
					}
				}
			}
		}
	}
}