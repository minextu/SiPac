<?php

class SiPacTheme_example extends SiPacTheme
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
						<ul class='chat_channels_ul'></ul>
						<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"<||enter-channel-name-text||>\"); if (channel_name != null) { $js.insert_command(\"join \" + channel_name, true); }'>+</a></span>
					</nav>
						<div class='chat_conversation'></div>
						<div class='chat_userlist'></div>
						
						<div class='chat_user_input'>
							<div class='chat_notice_msg'></div>
							<div>$smileys</div>
							<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
							<button class='chat_send_button'><||send-button-text||></button>
							<button onclick='$js.layout_test();'>layout function test</button>
						</div>
							
			</div><!-- end: chat_main-class -->
		";
	}
	
	public function get_js_functions()
	{
		$functions['layout_init'] = '
		function ()
		{
			this.old_user_status = new Array();
		}
		';
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
		$functions['layout_test'] = '
		function (status, username, user_id)
		{
			alert("test");
		}
		';
		return $functions;
	}
	/* optional methods */
	/*
	public function get_userlist_entry($nickname, $status, $info, $color, $id)
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
	}*/
}