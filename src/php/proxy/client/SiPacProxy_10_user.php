<?php

class SiPacProxy_user extends SiPacProxy
{
	public function execute()
	{  
		$this->post['message'] = preg_replace('/(^|\s)(@\S+)/', ' [b]$2[/b]', $this->post['message']);
		$this->post['message'] = trim($this->post['message']);
		return $this->post;
	}
}
?>