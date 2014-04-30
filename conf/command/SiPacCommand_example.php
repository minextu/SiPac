<?php

class SiPacCommand_example implements SiPacCommand
{
	public $usage = "/example";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
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