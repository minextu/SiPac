<?php

class SiPacCommand_me implements SiPacCommand
{
	public $usage = "/me <message>";
	public function set_variables($chat, $parameters)
	{
		$this->chat= $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return true;
	}
  
	public function execute()
	{
		$message = $this->parameters;
		
		if (empty($message))
		{
			$random_user = $this->chat->userlist->users[$this->chat->active_channel][mt_rand(0, count($this->chat->userlist->users[$this->chat->active_channel]) - 1)]->nickname;
			$random_sentences = array(
			"<||me-random-text-1||>", //has something better to do
			"<||me-random-text-2|[user]".$random_user ."[/user]||>", //has fallen in love with %1
			" <||me-random-text-3||>", //wishes he had not forgotten the text
			"<||me-random-text-4||>", //thinks he/she is beautiful
			"<||me-random-text-5|[user]".$random_user."[/user]||>" //is watching for %1
			);
			
			$message = $random_sentences[mt_rand(0, count($random_sentences) - 1)];
		}
		
		$this->chat->send_message("*[user]".$this->chat->nickname."[/user] ".$message, $this->chat->active_channel, 1);
	}
}

?> 
