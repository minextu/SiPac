<?php

class SiPacCommand_kick implements SiPacCommand
{
  public $usage = "/kick <user> [<reason>]";
  public $description = "Forcibly kick the given user out of the chat with an optional reason.";
  public function set_variables($chat, $parameters)
  {
    $this->chat= $chat;
    $this->parameters = $parameters;
  }
  public function check_permission()
  {
    if ($this->chat->settings->get('can_kick') == true)
      return true;
    else
      return false;
  }
  
  public function execute()
  {
    if (!empty($this->parameters))
    {
		$parameter_parts = explode(" ", $this->parameters);
		
		$user = $parameter_parts[0];
		if (empty($parameter_parts[1]))
			$reason = "<||kick-no-reason-text||>";
		else
		{
			$reason = $parameter_parts[1];
			foreach ($parameter_parts as $key => $parameter)
			{
				if ($key > 1)
					$reason = $reason." ".$parameter;
			}
		}
		
		if ($this->chat->settings->get("show_kick_user") == true)
			$nickname = $this->chat->nickname;
		else
			$nickname = "";
		
		$kick_return = $this->chat->db->add_task("kick|".$nickname."|".$reason, $user, $this->chat->channel->active, $this->chat->id);
		
		if ($kick_return == false)
			return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
    }
    else
       return array("info_type"=>"error", "info_text"=>"<||no-user-entered-text||>");
  }
}

?>