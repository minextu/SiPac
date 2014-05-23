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
    
    $this->chat = $chat;
	$this->chat_num = $chat->chat_num;
	$this->layout = $this->chat->layout;
    $this->settings = $this->chat->settings;
    $this->db = $this->chat->db;
  }
  
  public function generate_html()
  {
    $user_html = $this->layout['user_html'];
    $user_html = str_replace("!!USER!!", $this->nickname, $user_html);
    
    if ($this->afk == 0)
      $user_status = "<||online-status-text||>";
    else
      $user_status = "<||afk-status-text||>";
    
    $user_html = str_replace("!!USER_STATUS!!", $user_status, $user_html);
    $user_html = str_replace("!!NUM!!", $this->chat_num, $user_html);
    $user_html = str_replace("!!USER_ID!!", "user_".$this->id, $user_html);
    $user_html = str_replace("!!USER_INFO!!", $this->generate_user_info(), $user_html);
    return $user_html;
  }
  
  public function generate_user_info()
  {
		$user_info_tmp = array();
        if ($this->settings['can_kick'])
			$user_info_tmp['Kick'] = "<a href='javascript:void(null);' onclick='chat_objects[".$this->chat_num."].kick_user(\"".addslashes($this->nickname) . "\");'><||kick-user|" . $this->nickname . "||></a>";
		if ($this->settings['show_private_message_link'])
			$user_info_tmp['Message'] = "<a href='javascript:void(null);' onclick='chat_objects[".$this->chat_num."].msg_user(\"".addslashes($this->nickname) . "\");'>Send a private message</a>";
        if ($this->settings['can_see_ip'])
			$user_info_tmp['IP'] = $this->ip;
       
		if (!empty($this->info))
		{
			$infos = explode("|||", $this->info);
			foreach ($infos as $info)
			{
				if (!empty($info))
				{
					$info_parts = explode("||", $info);
					$user_info_tmp[$info_parts[0]] = $info_parts[1];
				}
			}
		}
		
		$user_info = "";
        foreach ($user_info_tmp as $info_head => $info)
        {
          //user dropdown infos
          $user_info = $user_info .str_replace("!!INFO_HEAD!!", $info_head, str_replace("!!INFO!!", $info, $this->layout['user_info_entry']));
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
  
	public function get_user_info_string()
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
		$this->chat->db->update_user($this->nickname, $this->channel, $this->chat->id, time(), $this->is_writing, $this->afk, $user_info);
	}
	public function save_user($add_notify)
	{
		$user_info = $this->get_user_info_string();
		
		$this->chat->db->save_user($this->nickname, $this->channel, $user_info, $this->ip, $this->chat->id);
		
		if ($add_notify)
		{
			//send a message, that this user jas joined the channel
			$this->chat->send_message("<||user-join-notification|".$this->nickname. "||>", $this->channel, 1, 0);
		}
	}
  
}

?>