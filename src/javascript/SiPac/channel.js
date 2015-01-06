/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2014 Jan Houben

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
SiPac.prototype.get_channel_key = function (channel_id)
{
	for (var i = 0; i < this.channels.length; i++)
	{
		if (this.channels[i]['id'] === channel_id)
			return i;
	}
	return false;
};
SiPac.prototype.add_channel = function(channel_id, channel_title)
{

	var channel_exist = false;
	for (var i = 0; i < this.channels.length; i++)
	{
		if (this.channels[i]['id'] == channel_id)
			channel_exist = true;
	}
	if (channel_exist == false)
	{
		if (this.chat.getElementsByClassName("chat_channels_ul")[0] != undefined)
			this.chat.getElementsByClassName("chat_channels_ul")[0].insertAdjacentHTML("beforeend", this.generate_channel_html(channel_id, channel_title));
			
		var chat_conversation = document.createElement("div");
		chat_conversation.setAttribute("style", "width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative; display: none;");
		chat_conversation.className = "chat_conversation_channel_" + channel_id;
		chat_conversation.innerHTML = this.text['room-loading-text'];
		this.chat.getElementsByClassName("chat_conversation")[0].appendChild(chat_conversation);
		
		var chat_userlist = document.createElement("div");
		chat_userlist.setAttribute("style", "width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative; display: none;");
		chat_userlist.className = "chat_userlist_channel_" + channel_id;
		this.chat.getElementsByClassName("chat_userlist")[0].appendChild(chat_userlist);

		var channel_key  = this.channels.length;
		this.channels[channel_key] = {};
		this.channels[channel_key]['id'] = channel_id;
		this.channels[channel_key]['title'] = channel_title;
		this.channels[channel_key]['new'] = true;
	}
};

SiPac.prototype.change_channel = function (channel_id)
{
	try
	{
		document.getElementById(this.id + "_channel_" + this.active_channel).className = "chat_channel";
		this.chat.getElementsByClassName("chat_conversation_channel_" + this.active_channel)[0].style.display = "none";
		this.chat.getElementsByClassName("chat_userlist_channel_" + this.active_channel)[0].style.display = "none";
	}
	catch(e){}
	

	document.getElementById(this.id + "_channel_" + channel_id).className = "chat_channel_selected";
	this.chat.getElementsByClassName("chat_conversation_channel_" + channel_id)[0].style.display = "block";
	this.chat.getElementsByClassName("chat_userlist_channel_" + channel_id)[0].style.display = "block";
	this.active_channel = channel_id;
	this.scroll(true);
};

SiPac.prototype.close_channel = function (channel)
{
	if (this.channels.length > 1)
	{
		document.getElementById(this.id + "_channel_" +channel).parentNode.removeChild(document.getElementById(this.id + "_channel_" + channel));
		this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0].parentNode.removeChild(this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0]);
		this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0].parentNode.removeChild(this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0]);
	  
		this.channels.splice(this.get_channel_key(channel),1);
	  
		if (this.active_channel = channel)
			this.change_channel(this.channels[0]['id']);
	}
	else
		alert("You can't close the last channel left!");
};

SiPac.prototype.generate_channel_html = function(channel, channel_title)
{;
	return this.layout['channel_tab'].replace(
		/!!ID!!/g, this.id + "_channel_" + channel).replace(
		/!!CHANNEL_CHANGE_FUNCTION!!/g, "sipac_objects[" + this.num + "].change_channel(\"" +  channel + "\")").replace(
		/!!CHANNEL_CLOSE_FUNCTION!!/g, "sipac_objects[" + this.num + "].close_channel(\"" + channel + "\")").replace(
		/!!CHANNEL!!/g, channel_title);;
};
