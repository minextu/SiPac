<?php

class SiPacCommand_join extends SiPacCommand
{
	public $usage = "/join <channel>";
	public $description = "Makes the client to join the given channel.";
  
	public function check_permission()
	{
		return $this->chat->settings->get('can_join_channels');
	}
	public function execute()
	{
		$channel = $this->parameters;
		$user = $this->chat->nickname;
		
		if (!empty($channel))
			$join_return = $this->chat->db->add_task("join|".$this->chat->channel->encode($channel)."|".$channel, $user, $this->chat->channel->active, $this->chat->id);
		else
			return array('info_type' => "error",'info_text' => "<||no-channel-entered-text||>");
	}
}

?>