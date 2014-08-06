<?php

class SiPacCommand_unban implements SiPacCommand
{
  public $usage = "/ban <user>]";
  public $description = "Removes a user from the banlist";
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
		$user = $this->parameters;
		if (substr($user, 0, 1) == "@")
			$user = substr($user, 1);
		
		$remove_ban = $this->chat->db->unban_user($user, $this->chat->id);
		
		if ($remove_ban === true)
			return array("info_type"=>"success", "info_text"=>"<||user-no-longer-banned-text|".$user."||>");
		else
			return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
	}
	else
		return array("info_type"=>"error", "info_text"=>"<||no-user-entered-text||>");
  }
}

?>