<?php

class SiPac_Old_Theme_Wrapper extends SiPacTheme
{
	public function prepare($php_path, $theme,  $num)
	{
		$this->php_path = $php_path;
		$this->num = $num;
		$this->theme = $theme;
		
		require_once($php_path."/layout.php");
		
		$layout_array['path'] = $php_path;
		$layout_array['html'] = $chat_layout;
		
		if (isset($chat_layout_user_entry))
			$layout_array['user_html'] = $chat_layout_user_entry;
		else
		{
			$layout_array['user_html'] = "
			<div class='chat_user' id='!!USER_ID!!' onmouseover='chat_objects[!!NUM!!].user_options(\"!!USER_ID!!\", \"show\");' onmouseout='chat_objects[!!NUM!!].user_options(\"!!USER_ID!!\", \"hide\");'>
			<div class='chat_user_top'>
			<div class='chat_user_name'>!!USER!!<span class='chat_user_status'>[!!USER_STATUS!!]</span></div>
			</div><!-- end: chat_user_top-class -->
			<div class='chat_user_bottom' style='display: none;'>
			<ul>
			!!USER_INFO!!
			</ul>
			</div><!-- end: chat_user_bottom-class -->
			</div><!-- end: chat_user-class -->
			";
		}
			
		$layout_array['default_smiley_height'] = $default_smiley_height;
		
		if (isset($chat_layout_post_entry))
			$layout_array['post_html'] = $chat_layout_post_entry;
		else
		{
			$layout_array['post_html'] =  "
			<div class='chat_entry_!!TYPE!!'>
			<span class='chat_entry_user'>!!USER!!</span>
			<span class='chat_entry_message'>!!MESSAGE!!</span>
			<span class='chat_entry_date'>!!TIME!!</span>
			</div>
			";
		}
		
		if (isset($chat_layout_user_info_entry))
			$layout_array['user_info_entry'] = $chat_layout_user_info_entry;
		else
			$layout_array['user_info_entry'] = "<li>!!INFO_HEAD!!: !!INFO!!</li>";
		
		if (isset($chat_layout_notify_user))
			$layout_array['notify_user'] = $chat_layout_notify_user;
		
		if (isset($chat_layout_channel_tab))
			$layout_array['channel_tab'] = $chat_layout_channel_tab;
		else
			$layout_array['channel_tab'] = "<li id='!!ID!!'><a href='javascript:void(0);' onclick='!!CHANNEL_CHANGE_FUNCTION!!'>!!CHANNEL!!</a></li>";
		
		if (isset($chat_layout_notify_entry))
			$layout_array['notify_html'] = $chat_layout_notify_entry;
		else
		{
			$layout_array['notify_html'] = "
			<div class='chat_entry_notify'>
			<span class='chat_entry_message'>!!MESSAGE!!</span>
			<span class='chat_entry_date'>!!TIME!!</span>
			</div>
			";
		}
		
		if (isset($chat_layout_functions))
			$layout_array['javascript_functions'] = $chat_layout_functions;
		else
		{
			$chat_layout_functions['layout_init'] = '
			function layout_init()
			{
			this.old_user_status = new Array();
			}
			';
			$chat_layout_functions['user_options'] = "
			function user_options(user_id, action)
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
			$chat_layout_functions['layout_user_writing_status'] = '
			function layout_user_writing_status (status, username, user_id)
			{
			if (document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML != "[" + this.texts["writing-status"] + "]")
				this.old_user_status[username] = document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML;
			
			if (status == 1)
				document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML = "[" + this.texts["writing-status"] + "]";
			else if (this.old_user_status[username] != undefined)
			{
			document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML =  this.old_user_status[username];
			}
			}
			';
			$layout_array['javascript_functions'] = $chat_layout_functions;
		}
		$this->arr = $layout_array;
	}
	public function get_settings()
	{
		$settings['smiley_height'] = $this->arr['default_smiley_height'];
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($user_num, $smileys, $settings)
	{
		$js = $this->js_chat;
		
		$layout = str_replace("!!USER_NUM!!", $user_num, $this->arr['html']);
		$layout = str_replace("!!SMILEYS!!", $smileys, $layout);
		$layout = str_replace("!!NUM!!", $this->num, $layout);
		
		return $layout;
	}
	
	public function get_userlist_entry($nickname, $status, $afk, $info, $color, $id)
	{
		$layout = str_replace("!!USER!!", $nickname, $this->arr['user_html']);
		$layout = str_replace("!!NUM!!", $this->num, $layout);
		$layout = str_replace("!!USER_STATUS!!", $status, $layout);
		$layout = str_replace("!!USER_AFK!!", $afk, $layout);
		$layout = str_replace("!!USER_INFO!!", $info, $layout);
		$layout = str_replace("!!USER_COLOR!!", $color, $layout);
		$layout = str_replace("!!USER_ID!!", $id, $layout);
		
		return $layout;
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
			$layout = $this->arr['post_html'];
		}
		else if ($type == "notify")
		{
			$layout = $this->arr['notify_html'];
		}
		
		$layout = str_replace("!!NUM!!", $this->num, $layout);
		$layout = str_replace("!!USER!!", $nickname, $layout);
		$layout = str_replace("!!MESSAGE!!", $message, $layout);
		$layout = str_replace("!!TYPE!!", $type, $layout);
		$layout = str_replace("!!USER_COLOR!!", $color, $layout);
		$layout = str_replace("!!TIME!!", $time, $layout);
		
		return $layout;
	}
	
	public function get_nickname($nickname)
	{
		$js = $this->js_chat;
		
		if (!empty($this->arr['notify_user']))
			return str_replace("!!USER!!", $nickname, $this->arr['notify_user']);
		else
			return "<span class='chat_entry_user'>$nickname</span>";
	}
	public function get_user_info($info_head, $info_text)
	{
		return str_replace("!!INFO_HEAD!!", $info_head, str_replace("!!INFO!!", $info_text, $this->arr['user_info_entry']));
	}

	public function get_channel_tab($channel, $id, $change_function, $close_function)
	{
		return $this->arr['channel_tab'];
	}
	
	public function get_js_functions()
	{
		return $this->arr['javascript_functions'];
	}
}
