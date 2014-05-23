<?php

class SiPacCommand_invite implements SiPacCommand
{
	public $usage = "/invite <user> <channel> [<force>]";
	public $description = "Invites a user  to the given channel. When <force> is set to true, the user will join the channel, without beeing asked for permisson.";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return $this->chat->settings['can_invite'];
	}
	public function execute()
	{
		$parameters = explode(" ", $this->parameters);
		
		if (isset($parameters[1]))
		{
			$user = $parameters[0];
			$channel = $parameters[1];
			
			if (isset($parameters[2]) AND $parameters[2] == "true")
			{
				if ($this->chat->settings['can_force_invite'] == true)
				{
					$join_return = $this->chat->db->add_task("join|".$this->chat->encode_channel($channel)."|".$channel, $user, $this->chat->active_channel, $this->chat->id);
					if ($join_return == false)
							return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
				}
				else
					return array('info_type' => "error",'info_text' => "<||no-permisson-text||>");
			}
			else
			{
				$invite_return = $this->chat->db->add_task("invite|".$this->chat->encode_channel($channel)."|".$channel."|".$this->chat->nickname, $user, $this->chat->active_channel, $this->chat->id);
				if ($invite_return == false)
					return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
			}
		}
		else
			return array('info_type' => "error",'info_text' => "<||arguments-missing-text||>");
	}
}

?>