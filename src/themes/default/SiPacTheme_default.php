<?php

class SiPacTheme_default extends SiPacTheme
{
	public function get_settings()
	{
		$settings['smiley_height'] = 30;
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($nickname, $user_num, $smileys,$settings)
	{
		$js = $this->js_chat;
		$theme = $this->theme_js;
		
		return "
			<div class='chat_main'>
				<nav class='chat_channels_nav'>
					<span class='chat_header'>SiPac</span>
					<ul class='chat_channels_ul'>
					</ul>
					<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"<||enter-channel-name-text||>\"); if (channel_name != null) { $js.insert_command(\"join \" + channel_name, true); }'><img src='".$this->path."/icons/comment_add.png' alt='+'></a></span>
				</nav>
				<div class='chat_container'>
					<div class='chat_left'>
						<div class='chat_conversation'><noscript><||noscript-text||></noscript></div>
						<div class='chat_user_input'>
							<div class='chat_notice_msg'></div>
							<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
							<button class='chat_send_button'><||send-button-text||></button>
						</div>
					</div>
					<div class='chat_vr'></div>
					<div class='chat_right'>
						<div class='chat_element'>
							<div class='chat_element_head'><img src='".$this->path."/icons/user.png' alt=''><||userlist-head|$user_num||><span class='chat_element_arrow'></span></div>
							<div class='chat_element_text'>
								<div class='chat_userlist'></div>
							</div>
						</div>
						<div class='chat_element'>
							<div class='chat_element_head'><img src='".$this->path."/icons/cog.png' alt=''><||settings-head||><span class='chat_element_arrow'></span></div>
							<div class='chat_element_text'>$settings</div>
						</div>
						<div class='chat_element' style='text-align: center;'>
							<div class='chat_element_head'><img src='".$this->path."/icons/emoticon_grin.png' alt=''><||smileys-head||><span class='chat_element_arrow'></span></div>
							<div class='chat_element_text'>
							<span>$smileys</span>
							</div>
						</div>
					</div>
				</div>
			</div><!-- end: chat_main-class -->
		";
	}
	
	public function get_userlist_entry($nickname, $status, $afk, $info, $color, $id)
	{
		$js = $this->js_chat;
		$theme = $this->theme_js;
		
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		return 
		"
			<span class='chat_user_$afk'>
				<div class='chat_user' id='$id' onmouseover='$theme.user_options(\"$id\", \"show\");' onmouseout='$theme.user_options(\"$id\", \"hide\");'>
					<div  class='chat_user_name' $style>
						<span onclick='$theme.insert_user(this.innerHTML);' style='cursor: pointer;'>$nickname</span>
						<span class='chat_user_status'>[$status]</span>
					</div>
					
					<div class='chat_user_bottom' style='display: none;'>
						<div class='chat_user_info'>$info</div>
					</div>
				</div>
			</span>
		";
	}
	
	public function get_message_entry($message, $nickname, $type, $color, $time)
	{
		$js = $this->js_chat;
		$theme = $this->theme_js;
		
		if (!empty($color))
			$style = "style='color:$color;'";
		else
			$style = "";
		
		if ($type == "own" OR $type == "others")
		{
			return
			"
			<div class='chat_entry_$type'>
			<span onclick='$theme.insert_user(this.innerHTML);' class='chat_entry_user' $style>$nickname</span>:
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
		$theme = $this->theme_js;
		
		return "<span onclick='$theme.insert_user(this.innerHTML);' class='chat_entry_user'>$nickname</span>";
	}
	
	public function get_channel_tab($channel, $id, $change_function, $close_function)
	{
		return 
		"
		<li id='$id'>
		<span class='chat_channel_span'>
		<a class='chat_channel' href='javascript:void(0);' onclick='$change_function'><img src='".$this->path."/icons/comment.png' alt=''> $channel</a><a href='javascript:void(0);' onclick='$close_function' class='chat_channel_close'>X</a>
		</span>
		</li>
		";
	}
	
	public function get_information_popup($text, $head, $type, $close_function)
	{
		return
		"
			<div class='chat_notice_$type'>
				<span class='close_chat_information'>
					<a href='javascript:void(0)' onclick='$close_function'>
						<img src='".$this->path."/icons/delete.png' alt='(close)' title='close'>
					</a>
				</span>
				<img src='".$this->path."/icons/$type.png' alt='(close)' title='close'> $head<br>
				$text
			</div>
		";
	}
	
	public function get_js_functions()
	{
		$js = $this->js_chat;
		$theme = $this->theme_js;
		
		$functions['init'] = '
		function ()
		{
			this.SiPac = '.$js.';
			this.old_user_status = new Array();
			var chat_elements = this.SiPac.chat.getElementsByClassName("chat_element");
			for (var i = 0; i < chat_elements.length; i++)
			{
				chat_elements[i].getElementsByClassName("chat_element_head")[0].addEventListener("click", function() { '.$theme.'.show_hide_element(this) }, false);
				chat_elements[i].getElementsByClassName("chat_element_head")[0].style.cursor = "pointer";
			}
			this.debug_open = false;
		}
		';
		$functions['show_hide_element'] = '
		function (elem)
		{
			var elem_text = elem.parentNode.getElementsByClassName("chat_element_text")[0];
			var elem_arrow = elem.getElementsByClassName("chat_element_arrow")[0];
			if (elem_text.style.maxHeight == "0px")
			{
				elem_arrow.style.borderWidth = "10px 10px 0 10px";
				elem_arrow.style.borderColor = "#c5c5c5 transparent transparent transparent";
				elem_text.style.maxHeight = "500px";
			}
			else
			{
				elem_arrow.style.borderWidth = "0 10px 10px 10px";
				elem_arrow.style.borderColor = "transparent transparent #c5c5c5";
				elem_text.style.maxHeight = "0px";
			}
		}
		';
		$functions['add_element'] = "
		function (head, text, icon, class_name)
		{
			icon = '".$this->path."/icons/' + icon;
			if (class_name == undefined)
				class_name = 'chat_layout_default_element';
			var div = document.createElement('div');
			var chat = this;
			div.innerHTML += 
					'<div class=\"' + class_name + '\"><div class=\"chat_element\" style=\"text-align: center;\">' +
							'<div class=\"chat_element_head\" style=\"cursor: pointer;\" onclick=\"$theme.show_hide_element(this)\"><img src=\"' + icon + '\" alt=\"\">' + head + '<span class=\"chat_element_arrow\"></span></div>' +
							'<div class=\"chat_element_text\">' +
							 text +
							'</div>' +
					'</div></div>';

			this.SiPac.chat.getElementsByClassName('chat_right')[0].appendChild(div);
		}
		";
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
		$functions['add_debug'] ='
		function (type, text, channel)
		{
			if (this.debug_open == false)
				this.add_element("debug", "", "page.png", "chat_debug");
			this.debug_open = true;
			
			channel = this.SiPac.channels[this.SiPac.get_channel_key(channel)]["title"];
			switch (type)
			{
				case 0:
					var color = "red";
					break;
				case 1:
					var color = "orange";
					break;
				case 2:
					var color = "blue";
					break;
				case 3:
					var color = "green";
					break;
				default:
					var color = "black";
					break;
			}
			
			var debug = this.SiPac.chat.getElementsByClassName("chat_debug")[0].getElementsByClassName("chat_element_text")[0];
			debug.innerHTML += "<div style=\'color: " + color + "\' class=\'chat_entry_debug\'>" + channel + ": " + text + "</div>";
			if (this.SiPac.autoscroll_enabled)
				debug.scrollTop = debug.scrollHeight;
		}
		';
		$functions['notification_status'] ='
		function (status)
		{
			if (status == false)
				this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;
			else
				this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = true;
		}
		';
		$functions['sound_status'] ='
		function (status)
		{
			if (status == false)
				this.chat.getElementsByClassName("chat_sound_checkbox")[0].checked = false;
			else
				this.chat.getElementsByClassName("chat_sound_checkbox")[0].checked = true;
		}
		';
		$functions['autoscroll_status'] ='
		function (status)
		{
			if (status == false)
				this.chat.getElementsByClassName("chat_autoscroll_checkbox")[0].checked = false;
			else
				this.chat.getElementsByClassName("chat_autoscroll_checkbox")[0].checked = true;
		}
		';
		$functions['invite_status'] ='
		function (status)
		{
			if (status == false)
				this.chat.getElementsByClassName("chat_invite_checkbox")[0].checked = false;
			else
				this.chat.getElementsByClassName("chat_invite_checkbox")[0].checked = true;
		}
		';
		return $functions;
	}
}