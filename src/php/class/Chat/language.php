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
trait Chat_language
{
  private function include_language()
  {
    $language_path = dirname(__FILE__)."/../../../lang/";
    
	global $chat_text;
    (@include_once ($language_path.$this->settings['language'].".php")) OR die("Invalid Language");
    
    foreach ($chat_text as $key => $text)
    {
      $this->text[$key] = $text;
    }
    
  }
  
  public function translate($text)
  {
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
	
      if (isset($this->text[$translation_key]))
      {
	$translation = $this->text[$translation_key];
	
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
	     //$text = preg_replace('#<\|\|(.*)\|\|>#isU', "${'this->text[$1]'}", $text);
	  else
	    $translation =$translation. " Translation: Too many Arguments";
	}
      }
      else
	$translation = "No translation for: '".$translation_key."'";
      $text = str_replace($match[0], $translation, $text);
      unset($parts);
    }
    
    
    return $text;
  }
}