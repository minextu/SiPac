<?php
function rrmdir($dir) { 
  foreach(glob($dir . '/*') as $file) { 
    if(is_dir($file)) rrmdir($file); else unlink($file); 
  } rmdir($dir); 
}
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
			rrmdir($cache_folder);
			
		return array(
			"info_type" => "info",
			"info_text" => "Successfully deleted the cache. Please reload!",
			"info_nohide" => true
		);
	}
}

?>