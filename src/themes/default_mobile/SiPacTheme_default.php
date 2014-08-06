<?php

class SiPacTheme_default extends SiPacTheme
{
	public function get_settings()
	{
		$settings['smiley_height'] = 30;
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($user_num, $smileys, $settings)
	{
		$js = $this->js_chat;
		$path = $this->path;
		
		return "
		<meta name='viewport' content='width=device-width, height=device-height, user-scalable=no'>
		<div class='chat_main'>
			<nav class='chat_channels_nav'>
				<span class='chat_header'>SiPac</span>
				<ul class='chat_channels_ul'>
				</ul>
				<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"<||enter-channel-name-text||>\"); if (channel_name != null) { $js.insert_command(\"join \" + channel_name, true); }'><img src='".$this->path."/icons/comment_add.png' alt='+'></a></span>
				<span class='chat_userlist_closed'  onclick='$js.layout_show_userlist(this)'><img src='$path/icons/user.png' alt='User'> ($user_num)</span>
				<span class='chat_settings_closed'  onclick='$js.layout_show_settings(this)'><img src='$path/icons/cog.png' alt='Settings'></span>
			</nav>
			<div class='chat_userlist'></div>
			<div class='chat_settings'>$settings</div>
			<div class='chat_container'>
				<div class='chat_left'>
					<div class='chat_conversation'></div>
					<div class='chat_user_input'>
						<div class='chat_user_writing'></div>
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
	public function get_js_functions()
	{
		$functions['layout_init'] = '
		function()
		{
			this.user_writing = new Array();
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
		$functions['layout_user_writing_status'] = '
		function (status, username, user_id)
		{
			if (user_id != this.id + "_" + this.active_channel + "_user_" + this.username_key)
			{
		
				if (status == 1)
				{
					var is_writing = false;
					for (var i = 0; i < this.user_writing.length; i++)
					{
						if (this.user_writing[i] == username)
						{
							is_writing = true;
						}
					}
					if (is_writing == false)
						this.user_writing[this.user_writing.length] = username;
					var writing_text = "";
					for (var i = 0; i < this.user_writing.length; i++)
					{
						if (i != 0)
						{
							if (i = this.user_writing.length - 1)
								writing_text += " and ";
							else
								writing_text += ", ";
						}
						writing_text += this.user_writing[i];
					}
					if (this.user_writing.length > 1)
						writing_text += " are writing...";
					else
						writing_text+= " is writing...";
					
					this.chat.getElementsByClassName("chat_user_writing")[0].innerHTML = writing_text;
				}
				else
				{
					for (var i = 0; i < this.user_writing.length; i++)
					{
						if (this.user_writing[i] == username)
						{
							this.user_writing = new Array();
							break;
						}
					}
					
					if (this.user_writing.length == 0)
						this.chat.getElementsByClassName("chat_user_writing")[0].innerHTML = "";
				}
			}
		}
		';
		$functions['layout_show_userlist'] = '
		function (userlist_button)
		{
			if(userlist_button.className == "chat_userlist_closed")
			{
				userlist_button.className = "chat_userlist_opened";
				this.chat.getElementsByClassName("chat_userlist")[0].style.width = "50%";
				this.chat.getElementsByClassName("chat_userlist")[0].style.display = "block";
				try{this.layout_show_settings(this.chat.getElementsByClassName("chat_settings_opened")[0]);}catch(e){}
			}
			else if(userlist_button.className == "chat_userlist_opened")
			{
				userlist_button.className = "chat_userlist_closed";
				this.chat.getElementsByClassName("chat_userlist")[0].style.width = "0%";
				this.chat.getElementsByClassName("chat_userlist")[0].style.display = "none";
			}
		}
		';
		$functions['layout_show_settings'] = '
		function (settings_button)
		{
			if(settings_button.className == "chat_settings_closed")
			{
				settings_button.className = "chat_settings_opened";
				this.chat.getElementsByClassName("chat_settings")[0].style.width = "50%";
				this.chat.getElementsByClassName("chat_settings")[0].style.display = "block";
				try{this.layout_show_userlist(this.chat.getElementsByClassName("chat_userlist_opened")[0]);}catch(e){}
			}
			else if(settings_button.className == "chat_settings_opened")
			{
				settings_button.className = "chat_settings_closed";
				this.chat.getElementsByClassName("chat_settings")[0].style.width = "0%";
				this.chat.getElementsByClassName("chat_settings")[0].style.display = "none";
			}
		}
		';
		return $functions;
	}
}