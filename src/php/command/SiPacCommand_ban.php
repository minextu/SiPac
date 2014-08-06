<?php

class SiPacCommand_ban implements SiPacCommand
{
	public $usage = "/ban <user> <time> [<reason>]";
	public $description = "Bans the given user from the chat for the given time (in hours) with an optional reason.";
	public function set_variables($chat, $parameters)
	{
		$this->chat= $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		if ($this->chat->settings->get('can_ban') == true)
			return true;
		else
			return false;
	}
  
	public function execute()
	{
		if (!empty($this->parameters))
		{
			$parameter_parts = explode(" ", $this->parameters);
			if (!empty($parameter_parts[1]) AND is_numeric($parameter_parts[1]) AND $parameter_parts[1] % 1 == 0)
			{
				$user = $parameter_parts[0];
				if (substr($user, 0, 1) == "@")
					$user = substr($user, 1);
				
				if (empty($parameter_parts[2]))
					$reason = $this->chat->language->translate("<||ban-no-reason-text||>");
				else
				{
					$reason = $parameter_parts[2];
					foreach ($parameter_parts as $key => $parameter)
					{
						if ($key > 2)
							$reason = $reason." ".$parameter;
					}
				}
				
				if ($this->chat->settings->get("show_ban_user") == true)
					$nickname = $this->chat->nickname;
				else
					$nickname = "";
				
				$time = $parameter_parts[1];
				
				$ban_return = $this->chat->db->add_task("ban|".$nickname."|".$time."|".$reason, $user, $this->chat->channel->active, $this->chat->id);
				
				if ($ban_return == false)
					return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
			}
			else
				return array("info_type" => "error","info_text" => "<||arguments-missing-text||>");
	}
	else
		return array("info_type"=>"error", "info_text"=>"<||no-user-entered-text||>");
	}
}

?>