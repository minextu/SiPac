<?php

class SiPacCommand_afk implements SiPacCommand
{
	public $usage = "/afk [<reason>]";
	public $description = "Notifies everyone that you are away from your keyboard (AFK). Can also combined with a message.";
	public function set_variables($chat, $parameters)
	{
		$this->chat= $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		if ($this->chat->settings['deactivate_afk'] == false)
			return true;
		else
			return false;
	}
	public function execute()
	{
		if ($this->chat->afk == false)
		{
			$this->chat->afk = true;
			if (!empty($this->parameters))
				$this->chat->afk_reason = $this->parameters;
		}
		else
		{
			$this->chat->afk = false;
			if  (!empty($this->parameters))
				return array("info_type"=>"warn", "info_text"=>"<||no-reason-for-not-afk-text||>");
		}
	}
}

?>