<?php

class SiPacProxy_bbcode extends SiPacProxy
{
	public function execute()
	{  
		$this->post['message'] = preg_replace('-\[b\](.*?)\[/b\]-','<span style="font-weight:bold;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('-\[u\](.*?)\[/u\]-','<span style="text-decoration:underline;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('-\[i\](.*?)\[/i\]-','<span style="font-style: italic;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('-\[s\](.*?)\[/s\]-','<span style="text-decoration:line-through;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('-\[img\](https?://.*?\.(?:jpg|jpeg|png|gif|bmp|tif|svg))\[/img\]-i','<img src="$1" alt="$1" style="max-width: 100%;  max-height: 300px;"></img>', $this->post['message']);
		$this->post['message'] = preg_replace('-\[color=([^;]*?)\](.*?)\[/color\]-','<span style="color: $1;">$2</span>', $this->post['message']);
		return $this->post;
	}
}
?>