<?php

class SiPacProxy_smileys extends SiPacProxy
{
	public function execute()
	{  
		foreach ($this->chat->settings->get('smileys') as $smiley_code => $smiley_url)
		{
			if (strpos($smiley_url, "http://") === false)
				$smiley_url = $this->chat->layout->settings['html_path'] . "/smileys/" . $smiley_url;
		
			$smiley_code_html = addslashes(htmlentities($smiley_code, ENT_QUOTES));
			$smiley_code      = " ".htmlentities($smiley_code);
		
			$this->post['message'] = str_replace($smiley_code, " <img style='max-height: 20px;margin-right: 3px;' src='" . $smiley_url . "' title='" . $smiley_code_html . "' alt='" . $smiley_code_html . "'>", " " . $this->post['message']);
		}
		$this->post['message'] = trim($this->post['message']);
		
		return $this->post;
	}
}
?>