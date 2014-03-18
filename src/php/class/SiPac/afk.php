<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013 Jan Houben

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
trait SiPac_afk
{ 
	public function check_afk()
	{
		if (!isset($_SESSION['SiPac'][$this->id]['afk']))
		{
			$this->afk = $this->settings['start_as_afk'];
			$_SESSION['SiPac'][$this->id]['afk'] = $this->settings['start_as_afk'];
		}
		else if ($_SESSION['SiPac'][$this->id]['afk'] == false)
			$this->afk = false;
		else
			$this->afk = true;
			
		if ($this->afk == true AND $this->settings['auto_detect_no_afk'] == true AND $this->is_writing == "true")
			$this->afk = false;
	}
	
	private function check_afk_change()
	{
		if ($_SESSION['SiPac'][$this->id]['afk'] != $this->afk)
		{
			$_SESSION['SiPac'][$this->id]['afk'] = $this->afk;
			foreach ($this->channels as $channel)
			{
				if ($this->settings['deactivate_afk'] == false)
				{
					if ($this->afk == false)
						$this->send_message("<||user-now-not-afk-text|".$this->nickname."||>", $channel, 1);
					else
					{
						if (empty($this->afk_reason))
							$afk_text = "<||user-now-afk-no-reason-text|".$this->nickname."||>";
						else
							$afk_text = "<||user-now-afk-text|".$this->nickname."|".htmlentities($this->afk_reason)."||>";
							
						$this->send_message($afk_text, $channel, 1);
					}
				}
			}
		}
	}
}