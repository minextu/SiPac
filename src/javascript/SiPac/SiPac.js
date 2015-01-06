/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2015 Jan Houben

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

SiPac.prototype.check_writing_status = function()
{
	var value = this.chat.getElementsByClassName('chat_message')[0].value;
	if (value != undefined && value != "" && value[0] != "/")
	{
		if (typeof this.old_value == "undefined" || value != this.old_value)
		{
			this.is_writing = true;
		}
		else
			this.is_writing = false;
	}
	else
		this.is_writing = false;
	
	var chat = this;
	window.setTimeout(function() { chat.old_value = value }, 5000);	
}

SiPac.prototype.play_sound = function()
{
	if (this.sound_enabled)
		this.new_post_audio.play();
}