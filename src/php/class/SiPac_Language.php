 
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
class SiPac_Language
{
	public $text;
	public $default_language;
	private $settings;
	
	public function __construct($settings)
	{
		$this->settings = $settings;
		$this->default_language = $this->settings->get('language');
	}
	
	public function load()
	{
		$this->text = $this->get_language($this->default_language);
	}
	
	private function get_language($language)
	{
		$language_path = dirname(__FILE__)."/../../lang/";
		
		global $chat_text;
		(@include_once ($language_path.$language.".php")) OR die("Invalid Language");
		
		return $chat_text;
	}
  
	public function translate($text, $language=false)
	{
		if ($language == false OR $language == $this->default_language)
			$language_text = $this->text;
		else
			$language_text = $this->get_language($language);
			
		preg_match_all('#<\|\|(.*)\|\|>#isU', $text, $matches, PREG_SET_ORDER);
    
    
		foreach ($matches as $match)
		{
			if (strpos($match[1], "|"))
			{
				$parts = explode("|", $match[1]);
				$translation_key = $parts[0];
			}
			else
				$translation_key = $match[1];
	
			if (isset($language_text[$translation_key]))
			{
				$translation = $language_text[$translation_key];
	
				if (isset($parts))
				{
					$translation_argument_num = substr_count($translation, "%");
					if (count($parts) - 1 == $translation_argument_num)
					{
						$translation = preg_replace("#%(.+)#isU", "||parts$1||", $translation);
	    
						for ($i = 1; $i <= $translation_argument_num; $i++)
						{
							$translation = str_replace("||parts$i||", $parts[$i], $translation);
						}
					}
					else
						$translation =$translation. " Translation: Too many Arguments";
				}
			}
			else
				$translation = "No translation for: '".$translation_key."'";
			$text = str_replace($match[0], $translation, $text);
			unset($parts);
		}
   
   return $text.$language;
	}
}