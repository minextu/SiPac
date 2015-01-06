<?php

class SiPacProxy_disable_bbcode extends SiPacProxy
{
	public function execute()
	{  
		if ($this->chat->settings->get('disable_bbcode') == true)
		{
			$this->post['message'] = preg_replace('-\[b\](.*?)\[/b\]-','$1', $this->post['message']);
			$this->post['message'] = preg_replace('-\[u\](.*?)\[/u\]-','$1', $this->post['message']);
			$this->post['message'] = preg_replace('-\[i\](.*?)\[/i\]-','$1', $this->post['message']);
			$this->post['message'] = preg_replace('-\[s\](.*?)\[/s\]-','$1', $this->post['message']);
			$this->post['message'] = preg_replace('-\[img\](https?://.*?\.(?:jpg|jpeg|png|gif|bmp|tif|svg))\[/img\]-','$1', $this->post['message']);
			$this->post['message'] = preg_replace('-\[color=([^;]*?)\](.*?)\[/color\]-','$2', $this->post['message']);
		}
		return $this->post;
	}
}
?>