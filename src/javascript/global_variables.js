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
function sipac_set_vars()
{
	sipac_new_id = 0;
	sipac_objects = new Array();
	sipac_objects_id = {};
	sipac_theme_functions = new Array();
	sipac_ajax_timeout = 1000;
	sipac_ajax_reconnect_timeout = 40000;
	sipac_html_path = false;
  
	//terminate the chat, when the user closes the chat
	window.onbeforeunload = function()
	{
		var httpobject = new XMLHttpRequest();
		//httpobject.timeout = sipac_ajax_reconnect_timeout;
		httpobject.open("POST", sipac_html_path + "src/php/SiPac.php?task=terminate_chat", false);
		httpobject.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		
		var post = "";
		for (var i = 0; i < sipac_objects.length; i++)
		{
			if (i !== 0)
				post += "&";
			
			post += "ids[" + sipac_objects[i].id + "]=" + sipac_objects[i].client_num;
		}
		httpobject.send(post);
	};
	
	
	// Handle page visibility change (reset title to default)
	document.addEventListener("mousemove", sipac_update_title, false);
	if (typeof document["hidden"] != "undefined") 
	{
		document.addEventListener("visibilitychange", 
							function() 
							{ 
								if (!document["hidden"])
									sipac_update_title(true) 
							}, false);
	}
	
	//start sipac ajax request
	sipac_main_request();
}

var sipac_new_messages = 0;
function sipac_update_title(clear)
{
	if (!clear)
	{
		if (sipac_new_messages == 0)
			sipac_old_title = document.title;
		sipac_new_messages++;
		document.title = " (" + sipac_new_messages + ") " + sipac_old_title;
	}
	else if (sipac_new_messages != 0)
	{
		document.title = sipac_old_title;
		sipac_new_messages = 0;
	}
}



if (typeof sipac_objects == 'undefined')
{
	sipac_set_vars();
}