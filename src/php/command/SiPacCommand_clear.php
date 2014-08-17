<?php
class SiPacCommand_clear extends SiPacCommand
{
	public $usage = "/clear";
	public $description = "Deletes the cached themes.";
  
	public function check_permission()
	{
		return $this->chat->settings->get('can_clear_cache');
	}
	public function execute()
	{
		$cache_folder = dirname(__FILE__) . "/../../../cache/";	
		
		$delete = $this->delete_contents($cache_folder);
		
		if ($delete == true)
		{
			return array(
				"info_type" => "success",
				"info_text" => "Successfully deleted the cache. Please reload!"
			);
		}
		else
		{
			return array(
				"info_type" => "warn",
				"info_text" => "Cache already empty!"
			);
		}
	}
	
	private function delete_contents($dir, $delete_dir=false)
	{
		$files = glob($dir . '/*');
		if (empty($files))
			return false;
		
		foreach($files as $file) 
		{ 
			if(is_dir($file)) 
				$this->delete_contents($file, true); 
			else 
				unlink($file); 
		}
		if ($delete_dir == true)
			rmdir($dir);
		
		return true;
	}
}

?>