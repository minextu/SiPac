<?php

class SiPacFunction_user_left implements SiPacFunction
{
  
	public function set_variables($chat, $values)
	{
		$this->chat = $chat;
		$this->values = $values;
	}
	public function execute()
	{
		$user_name = $this->values['user'];
		$channel = $this->values['channel'];
		$last_update = $this->values['last_update'];
		$chat_id = $this->chat->id;
		
		if ($user_name == "Test")
			return false;
		else
			return true;
	}
}
?>