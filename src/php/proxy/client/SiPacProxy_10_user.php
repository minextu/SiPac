<?php

class SiPacProxy_user implements SiPacProxy
{
  
	public function set_variables($chat, $post)
	{
		$this->chat = $chat;
		$this->post = $post;
	}
	public function execute()
	{  
		$this->post['message'] = preg_replace('/(^|\s)(@\S+)/', ' [b]$2[/b]', $this->post['message']);
		$this->post['message'] = trim($this->post['message']);
		return $this->post;
	}
}
?>