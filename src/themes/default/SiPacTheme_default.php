<?php

class SiPacTheme_default extends SiPacTheme
{
	public function set_variables($path, $js_chat)
	{
		$this->path = $path;
		$this->js_chat = $js_chat;
	}
	
	public function get_settings()
	{
		$settings['smiley_height'] = 30;
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($user_num, $smileys)
	{
		$js = $this->js_chat;
		
		return "
			<div class='chat_main'>
				<nav class='chat_channels_nav'>
					<span class='chat_header'>SiPac</span>
					<ul class='chat_channels_ul'>
					</ul>
					<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"<||enter-channel-name-text||>\"); if (channel_name != null) { $js.insert_command(\"join \" + channel_name, true); }'>+</a></span>
				</nav>
				<div class='chat_container'>
					<div class='chat_left'>
						<div class='chat_conversation'></div>
						<div class='chat_user_input'>
							<div class='chat_notice_msg'></div>
							<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
							<button class='chat_send_button'><||send-button-text||></button>
						</div>
					</div>
					<div class='chat_vr'></div>
					<div class='chat_right'>
						<div class='chat_element'>
							<div class='chat_element_head'><||userlist-head|$user_num||></div>
							<div class='chat_userlist'></div>
						</div>
						<div class='chat_element'>
							<div class='chat_element_head'><span style='float: left'><img src='".$this->path."/icons/cog.png' alt=''></span><||settings-head||></div>
							<input type ='checkbox' checked='checked' onclick='if ($js.enable_sound == true) { $js.enable_sound = false; } else { $js.enable_sound = true; } '><||enable-sound-text||>
							<br><input checked='checked' class='chat_notification_checkbox' type ='checkbox' onclick='if ($js.notifications_enabled == true) { $js.disable_notifications(); } else { $js.enable_notifications();} '><||enable-desktop-notifications-text||>
						</div>
							<div class='chat_element' style='text-align: center;'>
							<div class='chat_element_head'><||smileys-head||></div>
							<span>$smileys</span>
						</div>
					</div>
				</div>
			</div><!-- end: chat_main-class -->
		";
	}
	
	public function get_userlist_entry($nickname, $status, $info, $color, $id)
	{
		$js = $this->js_chat;
		
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		return 
		"
			<div class='chat_user' id='$id' onmouseover='$js.user_options(\"$id\", \"show\");' onmouseout='$js.user_options(\"$id\", \"hide\");'>
			<div  class='chat_user_name' $style>
				<span onclick='$js.insert_user(this.innerHTML);' style='cursor: pointer;'>$nickname</span>
				<span class='chat_user_status'>[$status]</span>
			</div>
			
			<div class='chat_user_bottom' style='display: none;'>
			<div class='chat_user_info'>$info</div>
			</div>
			</div>
		";
	}
	
	public function get_message_entry($message, $nickname, $type, $color, $time)
	{
		$js = $this->js_chat;
		
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		if ($type == "own" OR $type == "others")
		{
			return
			"
			<div class='chat_entry_$type'>
			<span onclick='$js.insert_user(this.innerHTML);' class='chat_entry_user' $style>$nickname</span>:
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
		$js = $this->js_chat;
		
		return "<span onclick='$js.insert_user(this.innerHTML);' class='chat_entry_user'>$nickname</span>";
	}
	
	public function get_js_functions()
	{
		$functions['layout_init'] = '
		function ()
		{
			this.old_user_status = new Array();
		}
		';
		$functions['user_options'] = "
		function (user_id, action)
		{
			if(action == 'show')
			{
				document.getElementById(user_id).getElementsByClassName('chat_user_bottom')[0].style.display = 'block';
			}
			else if(action == 'hide')
			{
				document.getElementById(user_id).getElementsByClassName('chat_user_bottom')[0].style.display = 'none';
			}
		}
		";
		$functions['insert_user'] = "
		function (user)
		{
			var input = this.chat.getElementsByClassName('chat_message')[0]
			var start_pos = input.selectionStart;
			var end_pos = input.selectionEnd;
			
			var value = '@' + user + ' ';
			
			if (start_pos >  0 && input.value.substr(start_pos-1, 1) != ' ')
				value = ' ' + value;
			
			input.value = input.value.substring(0, start_pos) + value + input.value.substring(end_pos, input.value.length);
			input.focus();
		}
		";
		$functions['layout_user_writing_status'] = '
		function (status, username, user_id)
		{
			if (document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML != "[" + this.texts["writing-status"] + "]")
				this.old_user_status[username] = document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML;
		
			if (status == 1)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML = "[" + this.texts["writing-status"] + "]";
			else if (this.old_user_status[username] != undefined)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML =  this.old_user_status[username];
		}
		';
		$functions['layout_notification_status'] ='
		function (status)
		{
			if (status == false)
				this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;
			else
				this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = true;
		}
		';
		return $functions;
	}
}