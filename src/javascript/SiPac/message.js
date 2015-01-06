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
SiPac.prototype.handle_messages = function(messages, users)
{
	if (messages != undefined)
	{
		for (key in this.channels)
		{
			var channel = this.channels[key];
			
			if (channel['new'] == true)
				this.chat.getElementsByClassName('chat_conversation_channel_' + channel['id'])[0].innerHTML = "";
			
			if (messages[channel['id']] != undefined)
				this.add_messages(channel['id'], users[channel['id']], messages[channel['id']]);
			
			if (channel['new'] == true)
			{
				channel['new'] = false;
				this.scroll(true);
			}
		}
	}
};

SiPac.prototype.send_message = function(text, channel)
{
	//if no text was given, get it from the message input
	if (text == undefined)
	{
		text = this.chat.getElementsByClassName('chat_message')[0].value;
		this.chat.getElementsByClassName('chat_message')[0].value = "";
	}
	//if no channel was given, use the active channel
	if (channel == undefined)
		channel = this.active_channel;

	var message_id = this.messages_to_send.length;
	this.messages_to_send[message_id] = {};
	this.messages_to_send[message_id]['text'] =text;
	this.messages_to_send[message_id]['channel'] = channel;

	//add_messages for faster response
};

SiPac.prototype.check_return = function(e)
{
	if (e.keyCode == 13)
		this.send_message();
};

SiPac.prototype.add_messages = function (channel, users, messages)
{
	var chat_window = this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0];

	for (var i = 0; i < messages.length; i++)
	{
		var message = document.createElement("span");
		message.innerHTML = messages[i];
		chat_window.appendChild(message);
		
		if (users[i] != this.nickname && this.notifications_enabled == true && !this.first_start)
			this.show_notification(users[i] + " (" + this.channels[this.get_channel_key(channel)]['title'] + ")", messages[i]);
	}

	//if (channel != this.active_channel && !this.first_start)
		//this.channel_new_messages(channel, this.channel_titles[this.channels.indexOf(channel)]);
		
	if (this.channels[this.get_channel_key(channel)]['new'] === false)
	{
		var no_sound = true;
		for (var i = 0; i < users.length; i++)
		{
			if (users[i] != this.nickname)
				no_sound = false;
		}
		
		if (no_sound == false)
		{
			//play sound
			this.play_sound();
		}
	}
};