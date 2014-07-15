<?php

class SiPacProxy_bbcode implements SiPacProxy
{
  
	public function set_variables($chat, $post)
	{
		$this->chat = $chat;
		$this->post = $post;
	}
	public function execute()
	{  
		$this->post['message'] = preg_replace('=\[b\](.*)\[/b\]=Uis','<span style="font-weight:bold;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('=\[u\](.*)\[/u\]=Uis','<span style="font-weight:underline;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('=\[i\](.*)\[/i\]=Uis','<span style="font-weight:italic;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('#\[color=(.*)\](.*)\[/color\]#isU','<span style="color: $1">$2</span>', $this->post['message']);
		return $this->post;
	}
}
?>