<?php

class SiPacProxy_example extends SiPacProxy
{
	public function execute()
	{
		$post_type = $this->post['type'];
		if ($post_type == 0)
			$post_type_name = "Normal Message"; 
		else if ($post_type == 1)
			$post_type_name = "Info";
			
		$post_user = $this->post['user'];
		
		
		if ($post_type == 0 ) //if it's a normal message
			$this->post['message'] = str_ireplace("no", "yes", $this->post['message']); // replace every "no" with "yes"
  
		return $this->post; // return the modified post array
	}
}
?>