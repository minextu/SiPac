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
trait SiPac_layout
{
  //generate and return the html code for the chat
  public function draw()
  {   
    $css_file = str_replace("\n", " ", file_get_contents($this->layout['path']."/chat.css"));
    $id_css   = "#".$this->id;
    
    $css_files_parts = explode("{", $css_file);
    
    $new_css_file = "";
    foreach ($css_files_parts as $part_num => $css_part)
    {
		if (!empty($css_part))
		{
			$css_parts = explode("}", $css_part);
			if (count($css_parts) == 1)
				$css_elements = explode(",",$css_parts[0]);
			else
			{
				$new_css_file = $new_css_file.$css_parts[0]."} ";
				$css_elements = explode( ",", $css_parts[1]);
			}
			foreach ($css_elements as  $element_key => $element)
			{
				if ($element_key > 0 )
					$new_css_file = $new_css_file.","; 
				if (strpos($element, ".chat_main") === false)
					$new_css_file = $new_css_file.$id_css.$element; 
				else
					$new_css_file = $new_css_file.str_replace(".chat_main", $id_css, $element);
			}
			$new_css_file = $new_css_file." { ";
		}
    }
    
    $this->js_chat = "var chat_text = new Array();";
    foreach ($this->text as $key => $text)
    {
      $this->js_chat = $this->js_chat."chat_text['$key'] = '".addslashes($text)."';";
    }
    
     /* generate js function arguments, to start the chat*/
    $this->js_chat =$this->js_chat. "add_chat('".$this->html_path."','" . $this->settings['theme'] . "','".$this->id."', '".$this->client_num."',";
    $this->js_chat = $this->js_chat."new Array(";
    foreach ($this->settings['channels'] as $key => $channel)
    {
      if ($key != 0)
	$this->js_chat = $this->js_chat.",";
      $this->js_chat = $this->js_chat."\"$channel\"";  
    }
    $this->js_chat = $this->js_chat."), chat_text";
    

    
    $this->js_chat = $this->js_chat.");";
    
    //save the html code of the layout
    $this->html_code = $this->layout['html'];
    
    //add the chat id to the div with the class 'chat_main'
    $this->html_code =str_replace('"chat_main"', '"chat_main" id="' . $this->id . '"', str_replace("'chat_main'", "'chat_main' id='".$this->id."'", $this->html_code));
    
    //load the chat.js
    $this->html_code = $this->html_code."<script class='sipac_script' type='text/javascript' src='".$this->html_path."src/javascript/chat.js'></script>";
    
    //load the layout css file via javascript
    $this->html_code = $this->html_code."
		<script class='sipac_script' type='text/javascript'>
		var style_obj = document.createElement('style');
		var text_obj = document.createTextNode('".trim($new_css_file)."');
		style_obj.appendChild(text_obj);
		document.getElementById('".$this->id."').appendChild(style_obj);
		".
		//start the chat, by calling the add_chat function
		$this->js_chat;
		
    //load all javascript functions by the layout
		
    if (!empty($this->layout['javascript_functions']))
    {
      foreach ($this->layout['javascript_functions'] as $name => $function)
      {
	$this->html_code = $this->html_code."chat_objects[chat_objects.length-1].$name = $function;";
	if ($name == "layout_init")
	  $this->html_code = $this->html_code."chat_objects[chat_objects.length-1].$name();";
      }
    }
    $this->html_code = $this->html_code."</script>";

    $this->html_code = str_replace("!!NUM!!", $this->chat_num, $this->html_code);
    $this->html_code = str_replace("!!USER_NUM!!", "<span class='chat_user_num'>?</span>", $this->html_code);
    $this->html_code = str_replace("!!SMILEYS!!", $this->generate_smileys(), $this->html_code);
    
    $GLOBALS['global_chat_num']  = $GLOBALS['global_chat_num'] + 1;
    
    $this->html_code = $this->translate($this->html_code);
    return $this->html_code;
  }
  
  private function generate_smileys()
  {
	$chat_smileys = "";
	if ($this->settings['smiley_width'] == "!!AUTO!!")
		$width = "";
	else
		$width = " width='" . $chat_settings['smiley_width'] . "px'";
	if ($this->settings['smiley_height'] == "!!AUTO!!" AND $this->settings['smiley_width'] == "!!AUTO!!" AND isset($this->layout['default_smiley_height']))
		$height = " height='" .$this->layout['default_smiley_height'] . "'";
	else if ($this->settings['smiley_height'] == "!!AUTO!!")
		$height = "";
	else
		$height = " width='" . $this->settings['smiley_height'] . "px'";
	foreach ($this->settings['smileys'] as $smiley_code => $smiley_url)
	{
		if (strpos($smiley_url, "http://") === false)
			$smiley_url = $this->html_path . "themes/" . $this->settings['theme'] . "/smileys/" . $smiley_url;
		$smiley_code  = htmlentities(str_replace('"', 'lol', addslashes($smiley_code)), ENT_QUOTES);
		$chat_smileys = $chat_smileys . "<span style='margin-right: 3px; cursor: pointer;' onclick='chat_objects[chat_objects_id[\"".$this->id."\"]].add_smiley(\" " . $smiley_code . "\");'><img$width$height src='" . $smiley_url . "' title='" . $smiley_code . "' alt='" . $smiley_code . "'></span>";
	}
	return $chat_smileys;
  }
  
  private function include_layout()
  {
    $is_mobile = check_mobile();
    
     $layout_path = dirname(__FILE__) . "/../../../../themes/".$this->settings['theme']."_mobile";
     
    if ($is_mobile && is_dir($layout_path))
      $this->is_mobile = true;
    else
      $layout_path = dirname(__FILE__) . "/../../../../themes/".$this->settings['theme'];
     
	if (file_exists($layout_path."/layout.php"))
		require($layout_path."/layout.php");
	else
		die($layout_path."/layout.php not found!");
    
    $layout_array['path'] = $layout_path;
    $layout_array['html'] = $chat_layout;
    $layout_array['user_html'] = $chat_layout_user_entry;
    $layout_array['default_smiley_height'] = $default_smiley_height;
    $layout_array['post_html'] = $chat_layout_post_entry;
    
    if (isset($chat_layout_notify_user))
		$layout_array['notify_user'] = $chat_layout_notify_user;
		    
    $layout_array['notify_html'] = $chat_layout_notify_entry;
    $layout_array['javascript_functions'] = $chat_layout_functions;
    
    $this->layout = $layout_array;
  }
}

?>