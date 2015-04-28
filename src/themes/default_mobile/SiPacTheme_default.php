<?php

class SiPacTheme_default extends SiPacTheme
{
	public function get_settings()
	{
		$settings['smiley_height'] = 30;
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($nickname, $user_num, $smileys, $settings)
	{
		$js = $this->js_chat;
		$path = $this->path;
		$theme = $this->theme_js;
		
		return "
		<meta name='viewport' content='width=device-width, height=device-height, user-scalable=no'>
		<div class='chat_main'>
			<nav class='chat_channels_nav'>
				<span class='chat_header'>SiPac</span>
				<ul class='chat_channels_ul'>
				</ul>
				<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"<||enter-channel-name-text||>\"); if (channel_name != null) { $js.insert_command(\"join \" + channel_name, true); }'><img src='".$this->path."/icons/comment_add.png' alt='+'></a></span>
				<span class='chat_userlist_closed'  onclick='$theme.show_userlist(this)'><img src='$path/icons/user.png' alt='User'> ($user_num)</span>
				<span class='chat_settings_closed'  onclick='$theme.show_settings(this)'><img src='$path/icons/cog.png' alt='Settings'></span>
			</nav>
			<div class='chat_userlist'></div>
			<div class='chat_settings'>$settings</div>
			<div class='chat_container'>
				<div class='chat_left'>
					<div class='chat_conversation'></div>
					<div class='chat_user_input'>
						<div class='chat_notice_msg'></div>
						<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
						<button class='chat_send_button'><||send-button-text||></button>
					</div>
				</div>
			</div>
		</div><!-- end: chat_main-class -->
		";
	}
	
	public function get_userlist_entry($nickname, $status, $afk, $info, $color, $id)
	{
		$js = $this->js_chat;
		
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		return 
		"
			<div class='chat_user' id='$id' onmouseover='$theme.user_options(\"$id\", \"show\");' onmouseout='$theme.user_options(\"$id\", \"hide\");'>
			<div  class='chat_user_name' $style>
				<span onclick='$theme.insert_user(this.innerHTML);' style='cursor: pointer;'>$nickname</span>
				<span class='chat_user_status'>[$status]</span>
			</div>
			
			<div class='chat_user_bottom' style='display: none;'>
			<div class='chat_user_info'>$info</div>
			</div>
			</div>
		";
	}
	public function get_js_functions()
	{
		$js = $this->js_chat;
		$theme = $this->theme_js;
		
		$functions['init'] = '
		function()
		{
			this.SiPac = '.$js.';
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

			user = user.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '\"');
			var input = this.SiPac.chat.getElementsByClassName('chat_message')[0]
			var start_pos = input.selectionStart;
			var end_pos = input.selectionEnd;
			
			var value = '@' + user + ' ';
			
			if (start_pos >  0 && input.value.substr(start_pos-1, 1) != ' ')
				value = ' ' + value;
			
			input.value = input.value.substring(0, start_pos) + value + input.value.substring(end_pos, input.value.length);
			input.focus();
		}
		";
		$functions['user_writing_status'] = '
		function (status, username, user_id)
		{
			if (document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML != "[" + this.SiPac.text["writing-status"] + "]")
				this.old_user_status[username] = document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML;
		
			if (status == 1)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML = "[" + this.SiPac.text["writing-status"] + "]";
			else if (this.old_user_status[username] != undefined)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML =  this.old_user_status[username];
		}
		';
		$functions['show_userlist'] = '
		function (userlist_button)
		{
			if(userlist_button.className == "chat_userlist_closed")
			{
				userlist_button.className = "chat_userlist_opened";
				this.SiPac.chat.getElementsByClassName("chat_userlist")[0].style.width = "50%";
				this.SiPac.chat.getElementsByClassName("chat_userlist")[0].style.display = "block";
				try{this.show_settings(this.SiPac.chat.getElementsByClassName("chat_settings_opened")[0]);}catch(e){}
			}
			else if(userlist_button.className == "chat_userlist_opened")
			{
				userlist_button.className = "chat_userlist_closed";
				this.SiPac.chat.getElementsByClassName("chat_userlist")[0].style.width = "0%";
				this.SiPac.chat.getElementsByClassName("chat_userlist")[0].style.display = "none";
			}
		}
		';
		$functions['show_settings'] = '
		function (settings_button)
		{
			if(settings_button.className == "chat_settings_closed")
			{
				settings_button.className = "chat_settings_opened";
				this.SiPac.chat.getElementsByClassName("chat_settings")[0].style.width = "50%";
				this.SiPac.chat.getElementsByClassName("chat_settings")[0].style.display = "block";
				try{this.show_userlist(this.SiPac.chat.getElementsByClassName("chat_userlist_opened")[0]);}catch(e){}
			}
			else if(settings_button.className == "chat_settings_opened")
			{
				settings_button.className = "chat_settings_closed";
				this.SiPac.chat.getElementsByClassName("chat_settings")[0].style.width = "0%";
				this.SiPac.chat.getElementsByClassName("chat_settings")[0].style.display = "none";
			}
		}
		';
		return $functions;
	}
}