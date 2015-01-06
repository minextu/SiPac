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
SiPac.prototype.add_smiley = function (code)
{
	this.chat.getElementsByClassName("chat_message")[0].value += code;
	this.chat.getElementsByClassName("chat_message")[0].focus();
};

SiPac.prototype.insert_command = function(command, auto_send)
{
	if (auto_send)
		this.send_message("/" + command);
	else
		this.chat.getElementsByClassName('chat_message')[0].value = "/" + command;
};

SiPac.prototype.handle_layout_changes = function(nickname, users)
{
	if (typeof this.chat.getElementsByClassName('chat_username')[0] != "undefined")
		this.chat.getElementsByClassName('chat_username')[0].innerHTML = nickname;
	
	if (typeof this.chat.getElementsByClassName('chat_user_num')[0] != "undefined")
	{
		var user_num = 0;
		for (n in users) { user_num++; }
		this.chat.getElementsByClassName('chat_user_num')[0].innerHTML = user_num;
	}
	
	if (typeof this.layout_tasks != "undefined")
		this.layout_tasks();
};

SiPac.prototype.information = function(text, type, no_hide, close)
{
	if (typeof this.info_hide_timeout != "undefined")
		window.clearTimeout(this.info_hide_timeout);
	
	var info_msg_element = this.chat.getElementsByClassName("chat_notice_msg")[0];
	
	//try to close any old information popup 
	try{info_msg_element.removeChild(info_msg_element.firstChild);}catch(e){}
	//if the only use was to close the popup, there is no need to continue
	if (close === true)
		return false;
	
	var information_popup = document.createElement("span");
	information_popup.innerHTML = this.layout['information_popup'].replace(
		/!!TEXT!!/g, text).replace(
		/!!HEAD!!/g, this.text[type + "-head"]).replace(
		/!!TYPE!!/g, type).replace(
		/!!CLOSE_FUNCTION!!/g, "sipac_objects[" + this.num + "].information(undefined, undefined, undefined, true)");
	info_msg_element.appendChild(information_popup);
	
	//Set a timeout to close the popup, if no_hide is undefined or false
	if (no_hide != true)
	{
		var chat = this;
		this.info_hide_timeout = window.setTimeout(function (){ chat.information(undefined, undefined, undefined, true) }, 5000);
	}
};

SiPac.prototype.kick_user = function(user)
{
	var reason = prompt(this.text['reason-for-kick-text']);
	if (reason != null)
		this.insert_command("kick " + user + " " + reason, true);
};

SiPac.prototype.msg_user = function(user)
{
	var message = prompt(this.text['private-message-prompt-text']);
	if (message != null)
		this.insert_command("msg " + user + " " + message, true);
}; 

SiPac.prototype.scroll = function(bottom, old_scroll, old_channel, no_workaround) 
{
	var scroll_target = this.chat.getElementsByClassName("chat_conversation")[0];
	var scrollTop = scroll_target.scrollTop;

	if (old_scroll > scrollTop && this.active_channel == old_channel && this.autoscroll_enabled === true)
		this.disable_autoscroll();
	else if (scroll_target.scrollHeight - scroll_target.scrollTop === scroll_target.clientHeight && this.autoscroll_enabled === false)
		this.enable_autoscroll();
	
	if (bottom !== true)
	{
		if (this.autoscroll_enabled === true)
			scroll_target.scrollTop += 5;
				
		var chat = this;
		var active_channel = this.active_channel;
		window.setTimeout(function() { chat.scroll(false, scrollTop, active_channel) },30);
	}
	else
	{
		scroll_target.scrollTop += scroll_target.scrollHeight - scroll_target.clientHeight;
		
		if (no_workaround !== true)
		{
			//workaround for images being load after scroll.
			var chat = this;
			for (var i = 0; i < 1000; i+=50)
				window.setTimeout(function() { chat.scroll(true, undefined, undefined, true) },i);
		}
	}
};