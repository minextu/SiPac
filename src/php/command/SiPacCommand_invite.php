<?php

class SiPacCommand_invite implements SiPacCommand
{
	public $usage = "/invite <user> [<force>]";
	public $description = "Invites a user  to the current selected channel. When <force> is set to true, the user will join the channel, without beeing asked for permisson.";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return $this->chat->settings->get('can_invite');
	}
	public function execute()
	{
		$parameters = explode(" ", $this->parameters);
		
		if (!empty($parameters[0]))
		{
			$user = $parameters[0];
			if (substr($user, 0, 1) == "@")
				$user = substr($user, 1);
				
			$channel = $this->chat->channel->active;
			
			if (isset($parameters[1]) AND $parameters[1] == "true")
			{
				if ($this->chat->settings->get('can_force_invite') == true)
				{
					$join_return = $this->chat->db->add_task("join|".$channel."|".$this->chat->channel->decode($channel), $user, $this->chat->channel->encode($this->chat->settings->get('channels')[0]), $this->chat->id);
					if ($join_return == false)
						return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
					else 
						return array("info_type"=>"success", "info_text"=>"<||user-invited-successfully-text|".$user."||>");
				}
				else
					return array('info_type' => "error",'info_text' => "<||no-permisson-text||>");
			}
			else
			{
				$invite_return = $this->chat->db->add_task("invite|".$channel."|".$this->chat->channel->decode($channel)."|".$this->chat->nickname, $user, $this->chat->channel->encode($this->chat->settings->get('channels')[0]), $this->chat->id);
				if ($invite_return == false)
					return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
				else
				 	return array("info_type"=>"success", "info_text"=>"<||user-invited-successfully-text|".$user."||>");
			}
		}
		else
			return array('info_type' => "error",'info_text' => "<||arguments-missing-text||>");
	}
}

?>