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
	abstract public function set_variables($path, $js_chat);
	abstract public function get_layout($user_num, $smileys);
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
}
?>