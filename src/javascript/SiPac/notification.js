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
SiPac.prototype.notification_request_permission = function()
{
	var Notification = window.Notification || window.mozNotification || window.webkitNotification;
	if (Notification.permission != "granted")
	{
		var chat = this;
		Notification.requestPermission(function (permission) 
		{
			if (permission == "granted")
				chat.enable_notifications();
		});
		return false;
	}
	else
		return true;
};

SiPac.prototype.show_notification = function(head, message)
{
	var Notification = window.Notification || window.mozNotification || window.webkitNotification;
	
	var instance = new Notification(head, 
						 {
							body: message
						}
	);
		
	setTimeout(function(){instance.close();}, '5000');
		
	instance.onclick = function () 
	{
		// Something to do
	};
	instance.onerror = function () 
	{
		// Something to do
	};
	instance.onshow = function () 
	{
		// Something to do
	};
	instance.onclose = function () 
	{
		// Something to do
	};
};