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

class Chat_User
{
  
  public function __construct($array, $layout, $chat_num)
  {
    $this->id = $array['id'];
    $this->chat_num = $chat_num;
    $this->nickname = $array['name'];
    $this->is_writing = $array['writing'];
    $this->afk = $array['afk'];
    $this->layout = $layout;
  }
  
  public function generate_html()
  {
    $user_html = $this->layout['user_html'];
    $user_html = str_replace("!!USER!!", $this->nickname, $user_html);
    
    if ($this->afk == 0)
      $user_status = "online";
    else
      $user_status = "afk";
    
    $user_html = str_replace("!!USER_STATUS!!", $user_status, $user_html);
    $user_html = str_replace("!!NUM!!", $this->chat_num, $user_html);
    $user_html = str_replace("!!USER_ID!!", "user_".$this->id, $user_html);
    return $user_html;
  }
  
  public function generate_additional_info()
  {
    if ($this->is_writing == true)
      return array("user_writing" => array($this->id));
    else
      return array();
  }
  
}

?>