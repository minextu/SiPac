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
SiPac.prototype.save_settings = function()
{
	var today = new Date();
	var expires = new Date(today.getTime() + 365 * 24 * 60 * 60 * 1000);
	expires = expires.toGMTString();
	document.cookie="SiPac_settings_" + this.id + "=" + this.notifications_enabled + "|" + this.sound_enabled + "|"  + this.autoscroll_enabled + "|" + this.invite_enabled + "; expires=" + expires + ";";
};

SiPac.prototype.set_defauklt_settings = function()
{
	this.notifications_enabled = false;
	this.invite_enabled = true;
	this.sound_enabled = true;
	this.autoscroll_enabled = true;
};

SiPac.prototype.restore_settings = function()
{
	this.set_defauklt_settings();
  
	var cookie = document.cookie;
	var cookies = cookie.split(";")
	for (var i = 0; i < cookies.length; i++)
	{
		var cookie_info = cookies[i].split("=");
		if (cookie_info[0].replace(" ", "") == "SiPac_settings_" + this.id)
		{
			var settings = cookie_info[1].split("|");
			if (settings[3] != undefined)
			{
				this.notifications_enabled = (settings[0] === "true");
				this.sound_enabled = (settings[1] === "true");
				this.autoscroll_enabled = (settings[2] === "true");
				this.invite_enabled = (settings[3] === "true");
			}
		}
	}
	
	this.update_checkboxes();
};

SiPac.prototype.update_checkboxes = function()
{
	if (this.notifications_enabled == true)
		this.enable_notifications();
	else
		this.disable_notifications();
	
	if (this.sound_enabled == true)
		this.enable_sound();
	else
		this.disable_sound();
	
	if (this.autoscroll_enabled == true)
		this.enable_autoscroll();
	else
		this.disable_autoscroll();
	
	if (this.invite_enabled == true)
		this.enable_invite();
	else
		this.disable_invite();
};

SiPac.prototype.disable_sound = function()
{
	this.sound_enabled = false;
	if (typeof this.layout_sound_status != "undefined")
		this.layout_sound_status(false);
	else
		try{this.chat.getElementsByClassName("chat_sound_checkbox")[0].checked = false;}catch(e){}
	
	this.save_settings();
};
SiPac.prototype.enable_sound = function()
{
	this.sound_enabled = true;
	if (typeof this.layout_sound_status != "undefined")
		this.layout_sound_status(true);
	else
		try{this.chat.getElementsByClassName("chat_sound_checkbox")[0].checked = true;}catch(e){}
	
	this.save_settings();
};
SiPac.prototype.disable_autoscroll = function()
{
	this.autoscroll_enabled = false;
	if (typeof this.layout_autoscroll_status != "undefined")
		this.layout_autoscroll_status(false);
	else
		try{this.chat.getElementsByClassName("chat_autoscroll_checkbox")[0].checked = false;}catch(e){}
	
	this.save_settings();
};
SiPac.prototype.enable_autoscroll = function()
{
	this.autoscroll_enabled = true;
	if (typeof this.layout_autoscroll_status != "undefined")
		this.layout_autoscroll_status(true);
	else
		try{this.chat.getElementsByClassName("chat_autoscroll_checkbox")[0].checked = true;}catch(e){}
	
	this.save_settings();
};
SiPac.prototype.disable_invite= function()
{
	this.invite_enabled = false;
	if (typeof this.layout_invite_status != "undefined")
		this.layout_invite_status(false);
	else
		try{this.chat.getElementsByClassName("chat_invite_checkbox")[0].checked = false;}catch(e){}
	
	this.save_settings();
};
SiPac.prototype.enable_invite = function()
{
	this.invite_enabled = true;
	if (typeof this.layout_invite_status != "undefined")
		this.layout_invite_status(true);
	else
		try{this.chat.getElementsByClassName("chat_invite_checkbox")[0].checked = true;}catch(e){}
	
	this.save_settings();
};
SiPac.prototype.disable_notifications = function()
{
	this.notifications_enabled = false;
	if (typeof this.layout_notification_status != "undefined")
		this.layout_notification_status(false);
	else
		try{this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;}catch(e){}
		
	this.save_settings();
};
SiPac.prototype.enable_notifications = function()
{
	//first uncheck the notification checkbox, until permission is granted
	this.notifications_enabled = false;
	if (typeof this.layout_notification_status != "undefined")
		this.layout_notification_status(false);
	else
		try{this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;}catch(e){}
			
	if (this.notification_request_permission() === true)
	{
		this.notifications_enabled = true;
		this.show_notification(this.text['desktop-notifications-enabled-head'],this.text['desktop-notifications-enabled-text']);
		if (typeof this.layout_notification_status != "undefined")
			this.layout_notification_status(true);
		else
			try{this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = true;}catch(e){}
	}
	
		this.save_settings();
};