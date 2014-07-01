<?php
/*
 *   SiPac is highly customizable PHP and AJAX chat
 *   Copyright (C) 2013 Jan Houben
 * 
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 * 
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 * 
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, write to the Free Software Foundation, Inc.,
 *   51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
class SiPac_Message
{
	private  $chat;
	
	public function __construct($chat)
	{
		$this->chat = $chat;
	}
	
	public function send($message, $channel, $type = 0, $user = 0, $time = 0)
	{
		//remove uneeded space
		$message = trim($message);
		
		if ($type == 0)
			$message =  htmlspecialchars($message);
		
		if (empty($user))
			$user = $this->chat->nickname;
		
		if (empty($time))
		{
			$time = time();
			$check_spam = true;
		}
		else
			$check_spam = false;
		
		if (!empty($message))
		{
			$command_return = $this->chat->command->check($message) ;
			if ($command_return !== false)
			{
				return $command_return;
			}
			else
			{
				$message_style = $this->chat->settings->get('user_color')."|||";
				
				$post_array = array("message"=>$message, "type"=>$type, "channel"=>$channel,"user"=>$user, "style" => $message_style, "time"=>$time);
				
				$post_array = $this->chat->proxy->check($post_array, "server", $check_spam);
				
				$db_response = $this->chat->db->save_post($post_array['message'], $this->chat->id, $post_array['channel'], $post_array['type'], $post_array['user'], $post_array['style'], $post_array['time']);
				if ($db_response !== true)
					return array('info_type' => "error", 'info_text' => $db_response);
				else
					return array();
			}
		}
		else
			return array('info_type' => "error", 'info_text' => $this->chat->language->translate("<||message-empty-text||>"));
	}

	public function get($last_id)
	{
		//load all posts
		$db_response = $this->chat->db->get_posts($this->chat->id, $this->chat->channel->ids);
		
		$new_posts = array();
		$new_post_users = array();
		$new_post_messages = array();
		
		$updated_last_id = $last_id;
		
		foreach ($db_response as $post)
		{
			//check if the post is new
			if ($post['id'] > $last_id OR in_array($post['channel'], $this->chat->channel->new))
			{
				$post_array = array("message"=>$post['message'], "type"=>$post['type'], "channel"=>$post['channel'],"user"=>$post['user'],"time"=>$post['time'], "style" => $post['style']);
				$post_array = $this->chat->proxy->check($post_array, "client");
				
				$post_user_name = $post_array['user'];
				if ($post_array['type'] == 0) //normal post
				{
					$post_user = $post_array['user'];
					
					if ($post_array['user'] == $this->chat->nickname)
						$post_type = "own";
					else
						$post_type = "others";
				}
				else if ($post_array['type'] == 1) //notify
				{
					$post_user = "";
					$post_type = "notify";
					$post_array['message'] = $this->chat->language->translate($post_array['message']);
					
					if (isset($this->chat->layout->arr['notify_user']))
						$post_array['message'] =   preg_replace('#\[user\](.*)\[/user\]#isU', str_replace("!!USER!!", "$1", $this->chat->layout->arr['notify_user']), $post_array['message']);
					else
						$post_array['message'] =   preg_replace('#\[user\](.*)\[/user\]#isU', "$1", $post_array['message']);
				}
				
				if ($post_type == "notify")
					$post_html = $this->chat->layout->arr['notify_html'];
				else
					$post_html = $this->chat->layout->arr['post_html'];
				
				$post_html = str_replace("!!USER!!", $post_user, $post_html);
				$post_html = str_replace("!!MESSAGE!!", $post_array['message'], $post_html);
				$post_html = str_replace("!!TYPE!!", $post_type, $post_html);
				
				$message_style = explode("|||", $post_array['style']);
				$color = $message_style[0];
				
				$post_html = str_replace("!!USER_COLOR!!", $color, $post_html);
				
				if ($this->chat->settings->get('time_24_hours'))
					$date = date("H:i", $post_array['time']);
				else
					$date = date("h:i A", $post_array['time']);
				
				if (date("d.m.Y", $post_array['time']) != date("d.m.Y", time()))
					$date = date($this->chat->settings->get('date_format'), $post_array['time']). " " . $date;
				
				$post_html = str_replace("!!TIME!!", $date, $post_html);
				
				
				$new_posts[$post_array['channel']][] = $post_html;
				$new_post_users[$post_array['channel']][] = $post_user_name;
				$new_post_messages[$post_array['channel']][] = $post_array['message'];
			}
			//save the highest id
			$updated_last_id = $post['id'];
		}
		
		$last_id = $updated_last_id;
		//return all new posts and the highest id
		return array('posts' => $new_posts, 'post_users' => $new_post_users, 'post_messages' => $new_post_messages, 'last_id' => $last_id, 'username' => $this->chat->nickname);
	}
	
}
