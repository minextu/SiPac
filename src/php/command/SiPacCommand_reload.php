<?php
function rrmdir($dir) { 
  foreach(glob($dir . '/*') as $file) { 
    if(is_dir($file)) rrmdir($file); else unlink($file); 
  } rmdir($dir); 
}
class SiPacCommand_reload implements SiPacCommand
{
	public $usage = "/reload";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return $this->chat->settings['can_reload_cache'];
	}
	public function execute()
	{
		$cache_folder = dirname(__FILE__) . "/../../../cache/".md5($this->chat->id)."/";	
		if (is_dir($cache_folder))
			rrmdir($cache_folder);
			
		return array(
			"info_type" => "info",
			"info_text" => "Successfully deleted the cache for this chat! Please reload.",
			"info_nohide" => true
		);
	}
}

?>