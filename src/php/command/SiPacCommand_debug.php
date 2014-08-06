<?php

class SiPacCommand_debug implements SiPacCommand
{
	public $usage = "/debug <type>";
	public $description = "Changes the type of debug messages to show. [type] can be 'off' or a number between 0 and 3";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return $this->chat->settings->get("can_change_debug_level");
	}
	public function execute()
	{
		if (!empty($this->parameters) OR $this->parameters == 0)
		{
			if ($this->parameters == "off" OR is_numeric($this->parameters) AND $this->parameters % 1 == 0 AND $this->parameters >= 0 AND $this->parameters <= 3)
			{
				$this->chat->settings->set("debug_level", $this->parameters);
				return array("info_type" => "success","info_text" => "<||debug-changed-text||>");
			}
			else
				return array("info_type" => "error","info_text" => "<||arguments-missing-text||>");
		}
		else
			return array("info_type" => "error","info_text" => "<||arguments-missing-text||>");
	}
}

?>