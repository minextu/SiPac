<?php
class SiPacCommand_clear implements SiPacCommand
{
	public $usage = "/clear";
	public $description = "Deletes the cached themes.";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return $this->chat->settings->get('can_clear_cache');
	}
	public function execute()
	{
		$cache_folder = dirname(__FILE__) . "/../../../cache/";	
		if (is_dir($cache_folder))
			$this->delete_contents($cache_folder);
			
		return array(
			"info_type" => "success",
			"info_text" => "Successfully deleted the cache. Please reload!",
			"info_nohide" => true
		);
	}
	
	private function delete_contents($dir, $delete_dir=false)
	{
		foreach(glob($dir . '/*') as $file) 
		{ 
			if(is_dir($file)) 
				$this->delete_contents($file, true); 
			else 
				unlink($file); 
		}
		if ($delete_dir == true)
			rmdir($dir);
	}
}

?>