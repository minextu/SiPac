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
SiPac.prototype.handle_debug = function(debug)
{
	if (debug !== undefined)
	{
		for (key in this.channels)
		{
			var channel = this.channels[key];
			
			if (debug[channel['id']] != undefined)
				this.add_debug_entries(debug[channel['id']], channel['id']);
		}
				
		if (debug[0] != undefined)
			this.add_debug_entries(debug[0], this.active_channel);	
	}
};
SiPac.prototype.add_debug_entries = function (debug_entries, channel)
{
	if (debug_entries != undefined)
	{
		for (var i in debug_entries)
		{
			this.add_debug_entry(debug_entries[i]['type'], debug_entries[i]['text'], channel);
		}
	} 
};
SiPac.prototype.add_debug_entry = function (type, text, channel)
{
	switch(type)
	{
		case 0:
			console.error(text);
			break;
		case 1:
			console.warn(text);
			break;
		default:
			console.debug(text);
			break;
	}
	
	if (channel == undefined)
		channel = this.active_channel;
	if (typeof this.theme_functions.add_debug != "undefined")
		this.theme_functions.add_debug(type, text, channel);
	else
		this.chat.getElementsByClassName('chat_conversation_channel_' + channel)[0].innerHTML += "<div class='chat_entry_debug'><span class='chat_entry_message'>" + text + "</span></div>";	
};