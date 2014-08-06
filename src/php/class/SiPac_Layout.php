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
class SiPac_Layout
{
	private $js_chat;
	private $html_code;
	public $settings;
	private $chat;
	
	public $is_mobile = false;
	public $theme;
	public $cache_folder;
	
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	
	public  function load()
	{
		$custom_layout_php_path = dirname(__FILE__) . "/../../../conf/themes/".$this->chat->settings->get('theme');
		$custom_layout_path = $this->chat->html_path."conf/themes/".$this->chat->settings->get("theme");
		
		$default_layout_php_path = dirname(__FILE__) . "/../../themes/".$this->chat->settings->get('theme');
		$default_layout_path = $this->chat->html_path."src/themes/".$this->chat->settings->get("theme");
		
		$is_mobile = check_mobile();
			
		$theme_class = "SiPacTheme_".$this->chat->settings->get('theme');
		
		
		if ($is_mobile == true AND file_exists($custom_layout_php_path."_mobile/$theme_class.php"))
		{
			$this->is_mobile = true;
			$layout_php_path = $custom_layout_php_path."_mobile";
			$layout_path = $custom_layout_path."_mobile";
			require("$layout_php_path/$theme_class.php");
			$this->theme = new $theme_class();
		}
		else if ($is_mobile == true AND file_exists($default_layout_php_path."_mobile/$theme_class.php"))
		{
			$this->is_mobile = true;
			$layout_php_path = $default_layout_php_path."_mobile";
			$layout_path = $default_layout_path."_mobile";
			require("$layout_php_path/$theme_class.php");
			$this->theme = new $theme_class();
		}
		else if (file_exists($custom_layout_php_path."/$theme_class.php"))
		{
			$layout_php_path = $custom_layout_php_path;
			$layout_path = $custom_layout_path;
			require("$layout_php_path/$theme_class.php");
			$this->theme = new $theme_class();
		}
		else if (file_exists($default_layout_php_path."/$theme_class.php"))
		{
			$layout_php_path = $default_layout_php_path;
			$layout_path = $default_layout_path;
			require("$layout_php_path/$theme_class.php");
			$this->theme = new $theme_class();
		}
		//Check for a deprecated theme
		else if (dirname(__FILE__) . "/../../../themes/".$this->chat->settings->get('theme')."/layout.php")
		{
			$layout_php_path = dirname(__FILE__) . "/../../../themes/".$this->chat->settings->get('theme')."_mobile";
			$layout_path = $this->chat->html_path."themes/".$this->chat->settings->get("theme")."_mobile";
			$is_mobile = check_mobile();
			if ($is_mobile && is_dir($layout_php_path))
				$this->is_mobile = true;
			else
			{
				$layout_php_path = dirname(__FILE__) . "/../../../themes/".$this->chat->settings->get('theme');
				$layout_path = $this->chat->html_path."themes/".$this->chat->settings->get("theme");
			}
			
			require(dirname(__FILE__)."/Compatibility/SiPac_Old_Theme_Wrapper.php");
			$this->theme = new SiPac_Old_Theme_Wrapper();
			$this->theme->prepare($layout_php_path, $this->chat->settings->get('theme'), $this->chat->chat_num);
			
			if ($this->chat->is_new == true)
				$this->chat->debug->add("You are using a deprecated theme. Consider updating the theme to the new format (see conf/themes/example/ for an example)", 1);
		}
		else
		{
			$this->chat->debug->error($layout_php_path."/$theme_class.php not found!");
			return false;
		}
		
		$js_chat = "chat_objects[".$this->chat->chat_num."]";
		$this->theme->set_variables($layout_path, $js_chat);
		
		$this->settings = $this->theme->get_settings();
		$this->settings['php_path'] = $layout_php_path;
		$this->settings['html_path'] = $layout_path;
	}
	
	//generate and return the html code for the chat
	public function draw()
	{   
		$this->cache_folder = md5($this->chat->id.$this->chat->html_path.$this->chat->settings->get('theme').$this->is_mobile.$this->chat->settings->get("language"));
		
		$GLOBALS['global_chat_num']  = $GLOBALS['global_chat_num'] + 1;
		if ($this->chat->settings->get('use_cache'))
		{
			$cache_folder = dirname(__FILE__) . "/../../../cache/".$this->cache_folder."/";
				
			if (is_dir($cache_folder) == false)
			{
				mkdir($cache_folder, 0777);
				
				$html_code = utf8_decode($this->chat->language->translate($this->generate_layout_html()));
				file_put_contents($cache_folder."layout.html", $html_code);
				file_put_contents($cache_folder."layout.css", $this->generate_layout_css());
			}
			
			return file_get_contents($cache_folder."layout.html").$this->generate_js();
		}
		else
		{
			$css = $this->generate_layout_css();
			return  $this->chat->language->translate($this->generate_layout_html($css)).$this->generate_js();
		}
	}
	private function generate_layout_html($css_code=false)
	{
		$user_num = "<span class='chat_user_num'>?</span>";
		$smileys = $this->generate_smileys();
		$settings = $this->theme->get_js_settings();
		
		
		//save the html code of the layout
		$html_code = $this->theme->get_layout($user_num, $smileys, $settings);
	
		//add the chat id to the div with the class 'chat_main'
		$html_code =str_replace('"chat_main"', '"chat_main" id="' . $this->chat->id . '"', str_replace("'chat_main'", "'chat_main' id='".$this->chat->id."'", $html_code));
	
		//load the chat.js
		$html_code = $html_code."<script class='sipac_script' type='text/javascript' src='".$this->chat->html_path."src/javascript/chat.js'></script>";
		if (empty($css_code))
		{
			$cache_folder = $this->chat->html_path."cache/".$this->cache_folder."/";
			$html_code = "<link rel='stylesheet' type='text/css' href='".$cache_folder."layout.css'>".$html_code;
		}
		else
		{
			//load the layout css file via javascript
			$html_code = $html_code."
			<script class='sipac_script' type='text/javascript'>
			var style_obj = document.createElement('style');
			var text_obj = document.createTextNode('".trim($css_code)."');
			style_obj.appendChild(text_obj);
			document.getElementById('".$this->chat->id."').appendChild(style_obj);
			</script>";
		}
		
		return $html_code;
	}
  
	private function generate_layout_css()
	{
		$css_file = $this->settings['php_path']."/".$this->settings['css_file'];
		$css_code = str_replace("\n", " ", file_get_contents($css_file));
		$id_css   = "#".$this->chat->id;
		
		$css_files_parts = explode("{", $css_file);
		$new_css_file = str_replace("##CHAT_MAIN##", $id_css, $css_code);
		
		return $new_css_file;
	}
  
	private function generate_js()
	{
		$js_chat = "<script class='sipac_script' type='text/javascript'>
		var chat_text = new Array();";
		foreach ($this->chat->language->text as $key => $text)
		{
			$js_chat = $js_chat."chat_text['$key'] = '".addslashes(utf8_decode($text))."';";
		}
	
		$channel_tab = $this->theme->get_channel_tab("!!CHANNEL!!", "!!ID!!", "!!CHANNEL_CHANGE_FUNCTION!!", "!!CHANNEL_CLOSE_FUNCTION!!");
		
		$js_chat = $js_chat."var chat_layout = new Array();";
		$js_chat = $js_chat."chat_layout['channel_tab'] = '".addslashes(trim(str_replace("	", " ", str_replace("\n", " ", $channel_tab))))."';";
	
		$js_chat = $js_chat."var chat_channels = new Array(";
		foreach ($this->chat->channel->list as $key => $channel)
		{
			if ($key != 0)
				$js_chat = $js_chat.",";
			$js_chat = $js_chat."\"".$channel['id']."\"";  
		}
		$js_chat = $js_chat.");";
	
		$js_chat = $js_chat."var chat_channel_titles = new Array(";
		foreach ($this->chat->channel->list as $key => $channel)
		{
			if ($key != 0)
				$js_chat = $js_chat.",";
			$js_chat = $js_chat."\"".$channel['title']."\"";  
		}
		$js_chat = $js_chat.");";
	
		/* generate js function arguments, to start the chat*/
		$js_chat =$js_chat. "add_chat('".$this->chat->html_path."','" . $this->settings['html_path'] . "','".$this->chat->id."', '".$this->chat->client_num."', chat_channels, chat_channel_titles, chat_text, chat_layout";
		$js_chat = $js_chat.",".$this->chat->settings->get('ajax_timeout').");";
		
		
		//load all javascript functions of the layout
		$javascript_functions = $this->theme->get_js_functions();
		if (!empty($javascript_functions))
		{
			foreach ($javascript_functions as $name => $function)
			{
				$js_chat = $js_chat."chat_objects[chat_objects.length-1].$name = $function;";
					if ($name == "layout_init")
						$js_chat = $js_chat."chat_objects[chat_objects.length-1].$name();";
			}
		}
		$js_chat = $js_chat."chat_objects[chat_objects.length-1].init();</script>";
	
		return $js_chat;
	}
	private function generate_smileys()
	{
		$chat_smileys = "";
		if (isset($this->settings['smiley_width']))
			$width = " width='" .$this->settings['smiley_width'] . "'";
		else
			$width = "";
		if (isset($this->settings['smiley_height']))
			$height = " height='" .$this->settings['smiley_height'] . "'";
		else
			$height = "";
		
		foreach ($this->chat->settings->get('smileys') as $smiley_code => $smiley_url)
		{
			if (strpos($smiley_url, "http://") === false)
				$smiley_url = $this->settings['html_path']."/smileys/" . $smiley_url;
			$smiley_code  = htmlentities($smiley_code, ENT_QUOTES);
			$chat_smileys = $chat_smileys . "<span style='margin-right: 3px; cursor: pointer;' onclick='chat_objects[chat_objects_id[\"".$this->chat->id."\"]].add_smiley(\" " . $smiley_code . "\");'><img$width$height src='" . $smiley_url . "' title='" . $smiley_code . "' alt='" . $smiley_code . "'></span>";
		}
		return $chat_smileys;
	}
}
?> 
