<?php

class SiPacCommand_about implements SiPacCommand
{
	public $usage = "/about";
  
	public function set_variables($chat, $parameters)
	{
		$this->chat = $chat;
		$this->parameters = $parameters;
	}
	public function check_permission()
	{
		return true;
	}
	public function execute()
	{
		global $chat_version;
		return array(
			"info_type" => "info",
			"info_text" => "<i>SiPac v$chat_version</i> was developed by
			<a href='http://finastry.next-play.de/index.php?page=profile&user=Kim'>Kim Westesen</a> and <a href='http://nexttrex.de/Profil/Jan.html'>Jan Houben</a>, to have a highly customizable PHP and AJAX chat.<p>Thanks to <a href='http://www.famfamfam.com/'>famfamfam</a>
			for the <a href='http://www.famfamfam.com/lab/icons/silk/'>Silk-Icons</a>.</p>
			If you have any questions, contact <a href='matilo:SiPac@nexttrex.de'>SiPac@nexttrex.de</a> ;-)",
			"info_nohide" => true
		);
	}
}

?>