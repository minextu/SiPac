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
SiPac.prototype.handle_messages = function(messages, users, message_texts)
{
	if (messages != undefined)
	{
		for (key in this.channels)
		{
			var channel = this.channels[key];
			
			if (channel['new'] == true)
				this.chat.getElementsByClassName('chat_conversation_channel_' + channel['id'])[0].innerHTML = "";
			
			if (messages[channel['id']] != undefined)
				this.add_messages(channel['id'], users[channel['id']], messages[channel['id']], undefined, message_texts[channel['id']]);
			
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

	
	/*show the message in the conversation for faster response
	only show the message, when not empty, or the message is a command*/
	if (text.trim() != "" && text[0] != "/")
	{
		var users = new Array(this.nickname);
		var messages = new Array(
			this.layout['message_entry_own'].replace(
			/!!MESSAGE!!/g, text).replace(
			/!!NICKNAME!!/g, this.nickname).replace(
			/!!TIME!!/g, "...")
		)
		this.add_messages(channel, users, messages, message_id);
	}
};

SiPac.prototype.check_return = function(e)
{
	if (e.keyCode == 13)
		this.send_message();
};

SiPac.prototype.add_messages = function (channel, users, messages, sending_id, message_texts)
{
	var chat_window = this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0];
	var channel_tab = document.getElementById(this.id + "_channel_" + channel);
	var channel_key = this.get_channel_key(channel);
	
	for (var i = 0; i < messages.length; i++)
	{
		var message = document.createElement("span");
		if (sending_id !== undefined)
			message.className = "chat_entry_sending_" + sending_id;
		
		message.innerHTML = messages[i];
		chat_window.appendChild(message);
		
		if (users[i] != this.nickname && this.notifications_enabled == true && !this.first_start && !this.channels[channel_key]['new'])
			this.show_notification(users[i] + " (" + this.channels[channel_key]['title'] + ")", message_texts[i]);
	}

	if (channel != this.active_channel && !this.first_start)
	{
		if (typeof this.theme_functions['channel_new_messages'] != "undefined")
			this.theme_functions['channel_new_messages'](channel_tab, this.channels[channel_key]['title']);
		else
			channel_tab.className = "chat_channel_unread";
	}
		
	if (this.channels[channel_key]['new'] === false)
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