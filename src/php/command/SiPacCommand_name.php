<?php

class SiPacCommand_name implements SiPacCommand
{
  public $usage = "/name <new name> [<user>]";
  public function set_variables($chat, $parameters)
  {
    $this->chat= $chat;
    $this->parameters = $parameters;
  }
  public function check_permission()
  {
    if ($this->chat->settings['can_rename'] == true)
      return true;
    else
      return false;
  }
  
  public function execute()
  {
    if (!empty($this->parameters))
    {
		$parameter_parts = explode(" ", $this->parameters);
		if (empty($parameter_parts[1]))
			$user = $this->chat->nickname;
		else if ($this->chat->settings['can_rename_others'] == true)
			$user = $parameter_parts[1];
		else
		{
			return array("info_type"=>"error", "info_text"=>"<||no-permissons-rename-other-user||>");
			return false;
		}
		
		$rename_return = $this->chat->db->add_task("new_name|".$parameter_parts[0], $user, $this->chat->active_channel, $this->chat->id);
		
		if ($rename_return == false)
			return array("info_type"=>"error", "info_text"=>"$rename_return <||user-not-found-text|".htmlentities($user)."||>");
    }
    else
       return array("info_type"=>"error", "info_text"=>"<||newname-not-entered-text||>");
  }
}

?>