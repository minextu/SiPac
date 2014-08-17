<?php

class SiPacCommand_name extends SiPacCommand
{
	public $usage = "/name <new name> [<user>]";
	public $description = "Gives the  user a new nickname. If <user> is not given, your own nickname will be renamed.";

	public function check_permission()
	{
		if ($this->chat->settings->get('can_rename') == true)
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
			else if ($this->chat->settings->get('can_rename_others') == true)
			{
				$user = $parameter_parts[1];
				if (substr($user, 0, 1) == "@")
					$user = substr($user, 1);
			}
			else
			{
				return array("info_type"=>"error", "info_text"=>"<||no-permissons-rename-other-user||>");
				return false;
			}
			
			$rename_return = $this->chat->db->add_task("new_name|".$parameter_parts[0], $user, $this->chat->channel->active, $this->chat->id);
			
			if ($rename_return == false)
				return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
		}
		else
			return array("info_type"=>"error", "info_text"=>"<||newname-not-entered-text||>");
	}
}

?>