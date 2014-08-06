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
abstract class SiPacTheme
{
	public final function set_variables($path, $js_chat)
	{
		$this->path = $path;
		$this->js_chat = $js_chat;
	}
	abstract public function get_layout($user_num, $smileys, $settings);
	abstract public function get_js_functions();
	abstract public function get_settings();
  
	public function get_userlist_entry($nickname, $status, $afk, $info, $color, $id)
	{
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		return 
		"
			<div class='chat_user' id='$id'>
			<div class='chat_user_name' $style>$nickname<span class='chat_user_status'>[$status]</span></div>
			
			<div class='chat_user_bottom'>
			<div class='chat_user_info'>$info</div>
			</div>
			</div> 
		";
	}
  
	public function get_user_info($info_head, $info_text)
	{
		return "<div><span>$info_head:</span><span style='float: right'>$info_text</span></div>";
	}
  
	public function get_message_entry($message, $nickname, $type, $color, $time)
	{
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		if ($type == "own" OR $type == "others")
		{
			return
			"
				<div class='chat_entry_$type'>
				<span class='chat_entry_user' $style>$nickname</span>:
				<span class='chat_entry_message'>$message</span>
				<span class='chat_entry_date'>$time</span>
				</div>  
			";
		}
		else if ($type == "notify")
		{
			return 
			"
			<div class='chat_entry_notify'>
			<span class='chat_entry_message'>$message</span>
			<span class='chat_entry_date'>$time</span>
			</div>
			";
		}
	}
	
	public function get_nickname($nickname)
	{
		return "<span class='chat_entry_user'>$nickname</span>";
	}
	
	public function get_channel_tab($channel, $id, $change_function, $close_function)
	{
		return 
		"
			<li id='$id'>
			<span class='chat_channel_span'>
			<a class='chat_channel' href='javascript:void(0);' onclick='$change_function'>$channel</a><a href='javascript:void(0);' onclick='$close_function' class='chat_channel_close'>X</a>
			</span>
			</li>
		";
	}
	
	public function get_post_date($date, $time_format, $date_format)
	{
		$date_text = date($time_format, $date);
				
		if (date("d.m.Y", $date) != date("d.m.Y", time()))
			$date_text = date($date_format, $date). " " . $date_text;
					
		return $date_text;
	}
	
	public function get_js_settings()
	{
		$js = $this->js_chat;
		
		return "
		<input checked='checked' class='chat_notification_checkbox' type ='checkbox' onclick='if ($js.notifications_enabled == true) { $js.disable_notifications(); } else { $js.enable_notifications();} '><||enable-desktop-notifications-text||>
		<br><input type ='checkbox' class='chat_autoscroll_checkbox' checked='checked' onclick='if ($js.autoscroll_enabled == true) { $js.disable_autoscroll() } else { $js.enable_autoscroll() } '><||enable-autoscroll-text||>
		<br><input type ='checkbox' class='chat_sound_checkbox' checked='checked' onclick='if ($js.sound_enabled == true) { $js.disable_sound() } else { $js.enable_sound() } '><||enable-sound-text||>
		<br><input type ='checkbox' class='chat_invite_checkbox' checked='checked' onclick='if ($js.invite_enabled == true) { $js.disable_invite() } else { $js.enable_invite() } '><||enable-invite-text||>
		";
	}
}
?>