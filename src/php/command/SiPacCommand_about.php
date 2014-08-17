<?php

class SiPacCommand_about extends SiPacCommand
{
	public $usage = "/about";
	public $description = "Shows the script's version and other information";
  
	public function check_permission()
	{
		return true;
	}
	public function execute()
	{
		global $SiPac_version;
		return array(
			"info_type" => "info",
			"info_text" => "<i>SiPac v$SiPac_version</i> was developed by
			<a target='_blank' href='http://nexttrex.de/'>Jan Houben</a> to have a highly customizable PHP and AJAX chat.
			<p>
				Thanks to <a target='_blank' href='http://finastry.next-play.de/'>Kim Westesen</a> for all the support and <a target='_blank' href='http://www.famfamfam.com/'>famfamfam</a>
				for the <a target='_blank'  href='http://www.famfamfam.com/lab/icons/silk/'>Silk-Icons</a>.
			</p>
			If you have any questions, contact <a href='matilo:SiPac@nexttrex.de'>SiPac@nexttrex.de</a> ;)",
			"info_nohide" => true
		);
	}
}

?>