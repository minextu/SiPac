<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2015 Jan Houben

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
	
	private $js_files = array("SiPac/init.js", "SiPac/SiPac.js", "SiPac/channel.js", "SiPac/ajax.js", "SiPac/userlist.js", "SiPac/message.js", "SiPac/settings.js", "SiPac/notification.js", "SiPac/layout.js", "SiPac/debug.js", "main_request.js", "global_variables.js");
	
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
		else
		{
			$this->chat->debug->error($layout_php_path."/$theme_class.php not found!");
			return false;
		}
		
		$js_chat = "sipac_objects[".$this->chat->chat_num."]";
		$this->theme->set_variables($layout_path, $js_chat);
		
		$this->settings = $this->theme->get_settings();
		$this->settings['php_path'] = $layout_php_path;
		$this->settings['html_path'] = $layout_path;
	}
	
	//generate the layout files for cache, if needed
	public function create_theme_cache()
	{
		$this->cache_id = md5($this->chat->id.$this->chat->html_path.$this->chat->settings->get('theme').$this->is_mobile.$this->chat->settings->get("language"));
		$this->cache_folder = dirname(__FILE__) . "/../../../cache/".$this->cache_id."/";
		$this->cache_folder_html = $this->chat->html_path."cache/".$this->cache_id."/";
				
		if (is_dir($this->cache_folder) == false)
		{
			//create the cache directory and create all cached files, content will be added at the end of this function
			mkdir($this->cache_folder, 0777);
			touch($this->cache_folder."layout.html");
			touch($this->cache_folder."layout.css");
			touch($this->cache_folder."layout.js");
		}
			
		$html_code = utf8_decode($this->chat->language->translate($this->generate_layout_html()));
		$css_code = $this->generate_layout_css();
		
		//setup custom javascript functions
		$js_function_string = "sipac_theme_functions[sipac_new_id] = new Array();\n";
		$js_functions = $this->theme->get_js_functions();
		foreach ($js_functions as $name => $function)
		{
			$js_function_string = $js_function_string.preg_replace('/function\s*\((.*)\)/','sipac_theme_functions[sipac_new_id]["'.$name.'"] = function ($1)', $function);
		}
		
		//update all cached files, if changed
		if (file_get_contents($this->cache_folder."layout.html") !== $html_code)
		{
			$this->chat->debug->add("layout.html updated", 2);
			file_put_contents($this->cache_folder."layout.html", $html_code);
		}
			
		if (file_get_contents($this->cache_folder."layout.css") !== $css_code)
		{
			$this->chat->debug->add("layout.css updated", 2);
			file_put_contents($this->cache_folder."layout.css", $css_code);
		}
		
		if (file_get_contents($this->cache_folder."layout.js") !== $js_function_string)
		{
			$this->chat->debug->add("layout.js updated", 2);
			file_put_contents($this->cache_folder."layout.js", $js_function_string);
		}
		
	}
	
	//generate and return the html code for the chat
	public function draw()
	{   
		$this->create_theme_cache();
		
		$GLOBALS['global_chat_num']  = $GLOBALS['global_chat_num'] + 1;

		return file_get_contents($this->cache_folder."layout.html").$this->generate_js();
	}
	private function generate_layout_html()
	{
		$user_num = "<span class='chat_user_num'>?</span>";
		$nickname = "<span class='chat_username'>".$this->chat->nickname."</span>";
		$smileys = $this->generate_smileys();
		$settings = $this->theme->get_js_settings();
		
		
		//save the html code of the layout
		$html_code = $this->theme->get_layout($nickname, $user_num, $smileys, $settings);
	
		//add the chat id to the div with the class 'chat_main'
		$html_code =str_replace('"chat_main"', '"chat_main" id="' . $this->chat->id . '"', str_replace("'chat_main'", "'chat_main' id='".$this->chat->id."'", $html_code));

		
		//load all js files
		foreach ($this->js_files as $file)
		{
			$html_code = $html_code."<script class='sipac_script' type='text/javascript' src='".$this->chat->html_path."src/javascript/$file'></script>";
		}
		//load custom layout functions
		$html_code = $html_code."<script class='sipac_script' type='text/javascript' src='".$this->cache_folder_html."layout.js'></script>";
		
		//load layout css file
		$html_code = "<link rel='stylesheet' type='text/css' href='".$this->cache_folder_html."layout.css'>".$html_code;
		
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
		/* generate the needed parameters for add_chat(), to start the chat */
		$layout = array(
			"channel_tab" => $this->theme->get_channel_tab("!!CHANNEL!!", "!!ID!!", "!!CHANNEL_CHANGE_FUNCTION!!", "!!CHANNEL_CLOSE_FUNCTION!!"),
			"information_popup" => $this->theme->get_information_popup("!!TEXT!!", "!!HEAD!!", "!!TYPE!!", "!!CLOSE_FUNCTION!!"),
			"message_entry_own" => $this->theme->get_message_entry("!!MESSAGE!!", "!!NICKNAME!!", "own", "!!COLOR!!", "!!TIME!!"));

		$parameter_array = array(
			"chat_html_path" => $this->chat->html_path, 
			"theme_html_path" => $this->settings['html_path'], 
			"id" => $this->chat->id, 
			"client_num" => $this->chat->client_num, 
			"text" => $this->chat->language->text, 
			"layout" => $layout, 
			"channels" => $this->chat->channel->list,
			"ajax_timeout" => $this->chat->settings->get('ajax_timeout'));
		
		$json_parameters = addslashes(json_encode($parameter_array));
	
	
		/* call the add_chat() with the generated parameters */
		$js_chat ="<script type='text/javascript'> add_chat('".$json_parameters."');";
		
		//initiate the chat, by calling the init function.
		$js_chat = $js_chat."sipac_objects[sipac_objects.length-1].init();</script>";#
	
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
			$chat_smileys = $chat_smileys . "<span style='margin-right: 3px; cursor: pointer;' onclick='sipac_objects[sipac_objects_id[\"".$this->chat->id."\"]].add_smiley(\" " . $smiley_code . "\");'><img$width$height src='" . $smiley_url . "' title='" . $smiley_code . "' alt='" . $smiley_code . "'></span>";
		}
		return $chat_smileys;
	}
}
?> 
