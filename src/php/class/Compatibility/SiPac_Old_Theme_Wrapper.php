<?php

class SiPac_Old_Theme_Wrapper extends SiPacTheme
{
	public function set_variables($path, $js_chat)
	{
		$this->path = $path;
		$this->js_chat = $js_chat;
	}
	
	public function prepare($php_path, $theme,  $num)
	{
		$this->php_path = $php_path;
		$this->num = $num;
		$this->theme = $theme;
		
		require_once($php_path."/layout.php");
		
		$layout_array['path'] = $php_path;
		$layout_array['html'] = $chat_layout;
		$layout_array['user_html'] = $chat_layout_user_entry;
		$layout_array['default_smiley_height'] = $default_smiley_height;
		$layout_array['post_html'] = $chat_layout_post_entry;
		
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
		
		$layout_array['notify_html'] = $chat_layout_notify_entry;
		$layout_array['javascript_functions'] = $chat_layout_functions;
		$this->arr = $layout_array;
	}
	public function get_settings()
	{
		$settings['smiley_height'] = $this->arr['default_smiley_height'];
		$settings['css_file'] = "chat.css";
		
		return $settings;
	}
	
	public function get_layout($user_num, $smileys)
	{
		$js = $this->js_chat;
		
		$layout = str_replace("!!USER_NUM!!", $user_num, $this->arr['html']);
		$layout = str_replace("!!SMILEYS!!", $smileys, $layout);
		$layout = str_replace("!!NUM!!", $this->num, $layout);
		
		return $layout;
	}
	
	public function get_userlist_entry($nickname, $status, $info, $color, $id)
	{
		$layout = str_replace("!!USER!!", $nickname, $this->arr['user_html']);
		$layout = str_replace("!!NUM!!", $this->num, $layout);
		$layout = str_replace("!!USER_STATUS!!", $status, $layout);
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
