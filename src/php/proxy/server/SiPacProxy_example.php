<?php

class SiPacProxy_example implements SiPacProxy
{
  
	public function set_variables($chat, $post)
	{
		$this->chat = $chat;
		$this->post = $post;
	}
	public function execute()
	{
		$post_type = $this->post['extra'];
		if ($post_type == 0)
			$post_type_name = "Normal Message"; 
		else if ($post_type == 1)
			$post_type_name = "Info";
			
		$post_user = $this->post['user'];
		
		
		if ($post_user == "Pflanze" AND $post_type == 0 ) //if the user 'Pflanze' wrote this post and it's a normal message
		{
			$this->post['message'] = str_ireplace("ja", "verklagen", $this->post['message']);
			$this->post['message'] = str_ireplace("nein", "nicht verklagen", $this->post['message']);
		}
  
		return $this->post; // return the modified post array
	}
}
?>