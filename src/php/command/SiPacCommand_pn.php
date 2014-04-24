<?php

class SiPacCommand_pn implements SiPacCommand
{
	public $usage = "/pn <user> <message>";
  
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
		$parameters = explode(" ", $this->parameters);
		
		if (isset($parameters[1]))
		{
			$user = $parameters[0];
			$message = $parameters[1];
			foreach ($parameters as $key => $parameter)
			{
				if ($key > 1)
					$message = $message." ".$parameter;
			}
			
			$channel_name_self = $user;
			$channel_name_user = $this->chat->nickname;
				
			$channel_id = $this->chat->client_num.mt_rand(0, 10000);
				
			$join_return = $this->chat->db->add_task("join|".$channel_id."|".$channel_name_user, $user, $this->chat->active_channel, $this->chat->id);
			if ($join_return == false)
				return array("info_type"=>"error", "info_text"=>"<||user-not-found-text|".$user."||>");
			else
			{	
				$join_return = $this->chat->db->add_task("join|".$channel_id."|".$channel_name_self, $this->chat->nickname, $this->chat->active_channel, $this->chat->id);
				$this->chat->send_message($message, $channel_id);
			}
		}
		else
			return array("info_type"=>"error", "info_text"=>"<||arguments-missing-text||>");
	}
}

?>