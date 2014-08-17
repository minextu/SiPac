<?php

class SiPacCommand_afk extends  SiPacCommand
{
	public $usage = "/afk [<reason>]";
	public $description = "Notifies everyone that you are away from your keyboard (AFK). Can also combined with a message.";

	public function check_permission()
	{
		if ($this->chat->settings->get('deactivate_afk') == false)
			return true;
		else
			return false;
	}
	public function execute()
	{
		if ($this->chat->afk->status == false)
		{
			if (!empty($this->parameters))
				$reason = $this->parameters;
			else
				$reason = false;
				
			$this->chat->afk->set(true, $reason);
		}
		else
		{
			$this->chat->afk->set(false);
			if  (!empty($this->parameters))
				return array("info_type"=>"warn", "info_text"=>"<||no-reason-for-not-afk-text||>");
		}
	}
}

?>