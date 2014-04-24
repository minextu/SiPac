<?php

class SiPacCommand_join implements SiPacCommand
{
	public $usage = "/join <channel>";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return $this->chat->settings['can_join_channels'];
	}
	public function execute()
	{
		$channel = $this->parameters;
		$user = $this->chat->nickname;
		
		if (!empty($channel))
			$join_return = $this->chat->db->add_task("join|".$this->chat->encode_channel($channel)."|".$channel, $user, $this->chat->active_channel, $this->chat->id);
		else
			return array('info_type' => "error",'info_text' => "<||no-channel-entered-text||>");
	}
}

?>