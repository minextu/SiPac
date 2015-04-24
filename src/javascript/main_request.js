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

var response_key = 0;

function sipac_main_request(single)
{
	var httpobject = new XMLHttpRequest();
	var server_start = new Date();
	
	if (sipac_objects.length != 0)
	{
		sipac_objects_ajax = new Array();
		
		for (var i = 0; i < sipac_objects.length; i++)
		{
			sipac_objects_ajax[i] = sipac_objects[i].generate_ajax_request();
		}


		httpobject.open("POST", sipac_html_path + "src/php/SiPac.php?task=get_chat", true);
		httpobject.onload = function ()
		{
			try
				{var answer = JSON.parse(httpobject.responseText);}
			catch(e)
			{
					sipac_objects[0].information(httpobject.responseText, "error");
					sipac_objects[0].add_debug_entry(0, httpobject.responseText);
			}
				
			var chat_object_answer = answer['SiPac'];
			for (var i = 0; i < chat_object_answer.length; i++)
			{
				if (chat_object_answer[i]["error"] != undefined)
					sipac_objects[i].information(chat_object_answer[i]["error"], "error");
				else
				{
					sipac_objects[i].parse_ajax_answer(chat_object_answer[i]);
					if (sipac_objects[i].first_start == true)
					{
						console.debug((i+1) + ". SiPac Chat ready!");
						sipac_objects[i].first_start = false;
					}
				}
				
				//calculate server response time
				var now = new Date();
				var current_server_response_time = now.getTime() - server_start.getTime();
				server_response_time = sipac_objects[i].server_response_time;
				server_response_time[response_key] = current_server_response_time;
				if (response_key < 30)
					response_key++;
				else
					response_key = 0;
			
				sipac_objects[i].server_response_time = server_response_time;
				sipac_objects[i].current_server_response_time = current_server_response_time;
			}
			if (single != true)
				window.setTimeout(sipac_main_request, sipac_ajax_timeout);
			
		}


		httpobject.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;');

		//encode the json_string, to allow quotes and ampersands to be send
		httpobject.send("sipac_string=" +encodeURIComponent(JSON.stringify(sipac_objects_ajax)));


	}
	else if (single != true)
		window.setTimeout(sipac_main_request, sipac_ajax_timeout);
}
