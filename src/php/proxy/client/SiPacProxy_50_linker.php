<?php

class SiPacProxy_linker extends SiPacProxy
{
	public function execute()
	{  
		if (strpos($this->post['message'], "<a href") === false)
		{
			$this->post['message'] = str_replace("https://www.", "www.", $this->post['message'] );
			$this->post['message']  = str_replace("http://www.", "www.", $this->post['message'] );
			$this->post['message']  = str_replace("www.", "http://www.", $this->post['message'] );
			$this->post['message']  = preg_replace("/ ([\w]+:\/\/[\w-?+:,&%;#~!=\.\/\@]+[\w\/])/i", " <a href=\"$1\" target=\"_blank\">$1</a>", $this->post['message'] );
			$this->post['message']  = preg_replace("/([\w]+:\/\/[\w-?+:,&%;#~!=\.\/\@]+[\w\/]) /i", " <a href=\"$1\" target=\"_blank\">$1</a> ", $this->post['message'] );
			
			if (strpos($this->post['message'] , "http://") === 0 AND strrpos($this->post['message'] , "http://") === 0 OR strpos($this->post['message'] , "https://") === 0 AND strrpos($this->post['message'] , "https://") === 0)
				$this->post['message']  = preg_replace("/([\w]+:\/\/[\w-?+:,&%;#~!=\.\/\@]+[\w\/])/i", "<a href=\"$1\" target=\"_blank\">$1</a>", $this->post['message'] );
			
			$this->post['message']  = preg_replace("/([\w-?+:,&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i", "<a href=\"mailto:$1\">$1</a>", $this->post['message'] );
		}
		return $this->post;
	}
}
?>