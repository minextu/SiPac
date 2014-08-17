<?php

class SiPacCommand_banlist extends SiPacCommand
{
	public $usage = "/banlist";
	public $description = "Shows a list of banned users";

	public function check_permission()
	{
		if ($this->chat->settings->get('can_ban') == true)
			return true;
		else
			return false;
	}	
  
	public function execute()
	{
		$banlist = $this->chat->db->get_banned_users($this->chat->id);
		$banlist_text = "";
		
		$banned_users = array();
		foreach ($banlist as $key => $user)
		{
			if (!in_array($user['name'], $banned_users))
			{
				if (count($banned_users) != 0)
					$banlist_text = $banlist_text."<br>";
				$banlist_text = $banlist_text."<||banlist-user-entry|".$this->chat->layout->theme->get_nickname($user['name'])."|".$user['info']."|".date("d.m.y H:i", $user['online'])."||>";
				
				$banned_users[] = $user['name'];
			}
		}
		if ($banlist_text == "")
			$banlist_text = "<||banlist-no-banned-users-text||>";
		return array("info_type"=>"info", "info_text"=>"<||banlist-head||><br>".$banlist_text, "info_nohide" => true);
	}
}

?>