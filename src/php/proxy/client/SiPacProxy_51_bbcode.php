<?php

class SiPacProxy_bbcode extends SiPacProxy
{
	public function execute()
	{  
		$this->post['message'] = preg_replace('=\[b\](.*)\[/b\]=Uis','<span style="font-weight:bold;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('=\[u\](.*)\[/u\]=Uis','<span style="font-weight:underline;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('=\[i\](.*)\[/i\]=Uis','<span style="font-weight:italic;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('=\[s\](.*)\[/s\]=Uis','<span style="text-decoration:line-through;">$1</span>', $this->post['message']);
		$this->post['message'] = preg_replace('=\[img\](.*)\[/img\]=Uis','<img src="$1" alt="$1" style="max-width: 100%;  max-height: 300px;"></img>', $this->post['message']);
		$this->post['message'] = preg_replace('#\[color=(.*)\](.*)\[/color\]#isU','<span style="color: $1">$2</span>', $this->post['message']);
		return $this->post;
	}
}
?>