<?php

class SiPacCommand_example extends SiPacCommand
{
	public $usage = "/example";
	public $description = "An example command";
	
	public function check_permission()
	{
		return true;
	}
	public function execute()
	{
		return array(
			"info_type" => "info",
			"info_text" => "Hello World!",
			"info_nohide" => true
		);
	}
}

?>