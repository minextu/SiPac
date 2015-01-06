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
class SiPac_User
{
	public function __construct($array, $chat)
	{
		$this->id = $array['id'];
		$this->nickname = $array['name'];
		$this->channel = $array['channel'];
		$this->is_writing = $array['writing'];
		$this->afk = $array['afk'];
		$this->info = $array['info'];
		$this->ip = $array['ip'];
		$this->style = $array['style'];

		$this->color = explode("|||", $this->style)[0];

		$this->chat = $chat;
		$this->chat_num = $chat->chat_num;
		$this->theme = $this->chat->layout->theme;
		$this->settings = $this->chat->settings;
		$this->db = $this->chat->db;
	}

	public function generate_html()
	{
		if ($this->afk == 0)
		{
			$user_status = "<||online-status-text||>";
			$user_afk = "online";
		}
		else
		{
			$user_status = "<||afk-status-text||>";
			$user_afk = "afk";
		}

		$user_html = $this->theme->get_userlist_entry($this->nickname, $user_status,  $user_afk, $this->generate_user_info(), $this->color, $this->id);

		return $user_html;
	}

	public function generate_user_info()
	{
		$user_info_tmp = array();
		if ($this->settings->get('can_kick'))
			$user_info_tmp['<||kick-head||>'] = "<a href='javascript:void(null);' onclick='sipac_objects[".$this->chat_num."].kick_user(\"".addslashes($this->nickname) . "\");'><||kick-user|" . $this->nickname . "||></a>";
		//if ($this->settings->get('can_ban'))
			//$user_info_tmp['<||ban-head||>'] = "<a href='javascript:void(null);' onclick='chat_objects[".$this->chat_num."].ban_user(\"".addslashes($this->nickname) . "\");'><||ban-user|" . $this->nickname . "||></a>";
		if ($this->settings->get('show_private_message_link'))
			$user_info_tmp['<||private-message-head||>'] = "<a href='javascript:void(null);' onclick='sipac_objects[".$this->chat_num."].msg_user(\"".addslashes($this->nickname) . "\");'><||send-private-message-text||></a>";
		if ($this->settings->get('can_see_ip'))
			$user_info_tmp['IP'] = $this->ip;
		
		if (!empty($this->info))
		{
			$infos = explode("|||", $this->info);
			foreach ($infos as $info)
			{
				if (!empty($info))
				{
					$info_parts = explode("||", $info);
					if (isset($info_parts[1]))
						$user_info_tmp[$info_parts[0]] = $info_parts[1];
				}
			}
		}
			
		$user_info = "";
		foreach ($user_info_tmp as $info_head => $info)
		{
			//user dropdown infos
			$user_info = $user_info.$this->theme->get_user_info($info_head, $info);
		}
		return $user_info;
	}

	public function generate_additional_info()
	{
		if ($this->is_writing == true)
			return array("user_writing" => array($this->id));
		else
			return array();
	}

	private function get_user_info_string()
	{
		if (is_array($this->info))
		{
			$user_info = "";
			foreach ($this->info as $info_head => $info)
			{
				$user_info = $user_info.$info_head."||".$info."|||";
			}
		}
		else
			$user_info =$this->info;
		return $user_info;
	}
	public function update_user()
	{
		$user_info = $this->get_user_info_string();
		$user_style = $this->style;
		$db_response = $this->chat->db->update_user($this->nickname, $this->channel, $this->chat->id, time(), $this->is_writing, $this->afk, $user_info, $user_style);
		if ($db_response !== true)
			$this->chat->debug->add("Failed to update users (response: ".$db_response.";user: ".$this->nickname.";channel: ".$this->channel.";id:".$this->chat->id.")", 0);
	}
	public function save_user($add_notify)
	{
		$user_info = $this->get_user_info_string();
		$user_style = $this->style;
		
		$db_response = $this->chat->db->save_user($this->nickname, $this->channel, $this->afk, $user_info, $user_style, $this->ip, $this->chat->id);
		if ($db_response !== true)
		{
			$this->chat->debug->add("Failed to save users (response: ".$db_response.";user: ".$this->nickname.";channel: ".$this->channel.";id:".$this->chat->id.")", 0);
			return false;
		}
		
		if ($add_notify)
		{
			//send a message, that this user jas joined the channel
			$this->chat->message->send("<||user-join-notification|".$this->nickname. "||>", $this->channel, 1, 0);
		}
	}

}

?>