<?php

class SiPacProxy_log implements SiPacProxy
{
  
	public function set_variables($chat, $post)
	{
		$this->chat = $chat;
		$this->post = $post;
	}
	public function execute()
	{
		$extra = $this->post['extra'];
		$chat_user = $this->post['user'];
		$message = $this->post['message'];
		
		$log_date     = date("d.m.Y", time());
		$log_time     = date("H:i:s", time());
		$log_year   = date("Y", time());
		$log_filename = date("m", time());
  
		$log_folder = "../../log/";
		
		if (substr(decoct(fileperms($log_folder)), -3) == 777)
		{
			if ($this->chat->settings['own_log_folder_for_chat_id'] == true)
				$log_folder = $log_folder . $this->chat->id . "/";
			else
				$log_folder = $log_folder . "global/";
				
			if (is_dir($log_folder) == false)
				mkdir($log_folder, 0777);
				
				
			$log_folder = $log_folder . $log_year;
				
			if (is_dir($log_folder) == false OR is_writable($log_folder))
			{
				if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']))
					$ip = $_SERVER['REMOTE_ADDR'];
				else
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				
				if ($extra == 0)
					$extra_name = "<||log-message||>"; //Message
				else if ($extra == 1)
					$extra_name = "<||log-info||>"; //Info
				else
					$extra_name = "?";
				
				if (is_dir($log_folder) == false)
					mkdir($log_folder, 0777);
				
				$chat_log_file = fopen($log_folder . '/' . $log_filename . '.log', "a+");
				$chat_log      = "\n" . $extra_name . " | ";
				
				$chat_log = $chat_log . $chat_user . ": ";
				
				$chat_log = $chat_log . html_entity_decode($message) . "		|$log_date, $log_time		$ip";
				fwrite($chat_log_file, $this->chat->translate($chat_log, $this->chat->settings['log_language']));
				fclose($chat_log_file);
				}
				else
					echo( "Wrong Permissions in Folder \"$log_folder\". Please change it to 777!");
			}
			else
				echo("Wrong Permissions in Folder \"log\". Please change it to 777!");
			
		return $this->post;
	}
}
?>