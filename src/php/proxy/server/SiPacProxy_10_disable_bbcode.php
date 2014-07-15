<?php

class SiPacProxy_disable_bbcode implements SiPacProxy
{
  
	public function set_variables($chat, $post)
	{
		$this->chat = $chat;
		$this->post = $post;
	}
	public function execute()
	{  
		if ($this->chat->settings->get('disable_bbcode') == true)
		{
			$this->post['message'] = preg_replace('=\[b\](.*)\[/b\]=Uis','$1', $this->post['message']);
			$this->post['message'] = preg_replace('=\[u\](.*)\[/u\]=Uis','$1', $this->post['message']);
			$this->post['message'] = preg_replace('=\[i\](.*)\[/i\]=Uis','$1', $this->post['message']);
			$this->post['message'] = preg_replace('#\[color=(.*)\](.*)\[/color\]#isU','$2', $this->post['message']);
		}
		return $this->post;
	}
}
?>