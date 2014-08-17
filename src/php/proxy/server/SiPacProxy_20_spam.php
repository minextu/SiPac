<?php

class SiPacProxy_spam extends SiPacProxy
{
	public function execute()
	{
		$max_count = $this->chat->settings->get("spam_max_count");
		$count_interval = $this->chat->settings->get("spam_count_interval");
		$channel = $this->post['channel'];
		
		
		$spam_count = $this->chat->settings->get("proxy-spam_count_".$channel);
		
		if ($spam_count === false)
		{
			$this->chat->settings->set("proxy-spam_count_".$channel, 1);
			$this->chat->settings->set("proxy-spam_time_".$channel, time());
			$this->chat->settings->set("proxy-spam_last_text_".$channel, $this->post['message']);
		}
		else if (time() - $this->chat->settings->get("proxy-spam_time_".$channel)  < $count_interval)
		{
			if ($this->chat->settings->get("proxy-spam_last_text_".$channel) == $this->post['message'])
			{
				$this->chat->settings->set("proxy-spam_count_".$channel, $spam_count+2);
			}
			else
				$this->chat->settings->set("proxy-spam_count_".$channel, $spam_count+1);
			
			$this->chat->settings->set("proxy-spam_time_".$channel, time());
			$this->chat->settings->set("proxy-spam_last_text_".$channel, $this->post['message']);
			
			if ($spam_count >= $max_count)
			{
				$this->chat->settings->set("proxy-spam_count_".$channel, false);
				
				$kick_user = "Spam Bot";
				$reason = "Spam";
				
				$kick_return = $this->chat->db->add_task("kick|".$kick_user."|".$reason, $this->chat->nickname, $this->chat->channel->active, $this->chat->id);
			}
		}
		else
			$this->chat->settings->set("proxy-spam_count_".$channel, false);
		
		return $this->post;
	}
}
?>