<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2014 Jan Houben

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
 
 /*
 Make the old way, to start the chat compatible to the new version
 */
function draw_chat($chat_id)
{
  global $chat_settings;
  
  require_once(dirname(__FILE__)."/php/SiPac.php");
  
  $chat_settings['chat_id'] = $chat_id;
  
  $chat_settings['mysql_hostname'] = $chat_settings['host'];
  $chat_settings['mysql_username'] = $chat_settings['user'];
  $chat_settings['mysql_password'] = $chat_settings['pw'];
  $chat_settings['mysql_database'] = $chat_settings['db'];
  
  if (isset($chat_settings['design']))
	$chat_settings['theme'] = $chat_settings['design'];
  
  $chat = new SiPac_Chat($chat_settings);
  $chat->debug->add("You are using a deprecated methode to draw the chat!", 1);
  return $chat->draw();
}
?>
