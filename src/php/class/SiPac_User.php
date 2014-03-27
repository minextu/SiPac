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
		$user_info_tmp = $this->info;
        if ($this->settings['can_see_ip'])
          $user_info_tmp = "IP:" . $this->ip . "|||" . $user_info_tmp;
        
        if ($this->settings['can_kick'])
          $user_info_tmp = "<a href='javascript:void(null);' onclick='chat_objects[".$this->chat_num."].kick_user(\"".addslashes($this->nickname) . "\");'><||kick-user|" . $this->nickname . "||></a>|||" . $user_info_tmp;
        
        $user_info = "";
        foreach (explode("|||", $user_info_tmp) as $info)
        {
          //user dropdown infos
          $user_info = $user_info . "<li>" . $info . "</li>";
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
  
	public function save_user($add_notify)
	{
		$this->chat->db->save_user($this->nickname, $this->channel, $this->ip, $this->chat->id);
		
		if ($add_notify)
		{
			//send a message, that this user jas joined the channel
			$this->chat->send_message("<||user-join-notification|".$this->nickname. "||>", $this->channel, 1, 0);
		}
	}
  
}

?>