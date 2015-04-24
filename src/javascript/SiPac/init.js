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
function add_chat(json_parameters)
{
	var parameters = JSON.parse(json_parameters);
	
	sipac_objects[sipac_new_id] = new SiPac(parameters);
	
	var chat_id = sipac_objects[sipac_new_id].id;
	sipac_objects_id[chat_id] = sipac_new_id;
	sipac_ajax_timeout = sipac_objects[sipac_new_id].ajax_timeout;
	sipac_ajax_reconnect_timeout = sipac_objects[sipac_new_id].ajax_reconnect_timeout;
	
	new_sipac_html_path = sipac_objects[sipac_new_id].chat_html_path;
	if (sipac_html_path !== false && new_sipac_html_path != sipac_html_path)
		console.warn("Multible Chats were only tested when using one SiPac installation. Erros may occur!");
	sipac_html_path = new_sipac_html_path;
	
	
	console.log(sipac_objects.length + ". SiPac Chat added.");
}

function SiPac(parameters)
{ 
	this.num = sipac_objects.length;
	this.first_start = true;
	this.last_message_id = 0;
	this.messages_to_send = new Array();
	
	this.channels = new Array();
	this.active_channel = false;
	
	this.theme_functions = new Array();
	this.parse_parameters(parameters);
	
	this.current_server_response_time = "?";
	this.server_response_time = []
	
	/* audio */
	this.new_post_audio = new Audio();
	this.new_post_audio.autobuffer = true;
	if (this.new_post_audio.canPlayType('audio/x-wav'))
	{
		this.new_post_audio.type = 'audio/x-wav';
		this.new_post_audio.src = this.theme_html_path + "/sound/new_post.wav";
	}
	else
	{
		this.new_post_audio.type = 'audio/mpeg';
		this.new_post_audio.src = this.theme_html_path + "/sound/new_post.mp3";
	}
}

//This method will be called by PHP, when the object has been saved in sipac_objects, so custom functions can work with that
SiPac.prototype.init = function()
{
	this.add_theme_functions(this.theme_functions);
	if (typeof this.theme_functions['init'] != 'undefined')
		this.theme_functions['init']();
	
	for (var i = 0; i < this.default_channels.length; i++)
	{
		this.add_channel(this.default_channels[i]['id'], this.default_channels[i]['title']);
	}
	this.change_channel(this.default_channels[0]['id']);
	
	this.add_event_listeners();
	this.restore_settings();
	this.scroll();
	
	//the next sipac object should use another id, since the chat is now initiated
	console.log(sipac_objects.length + ". SiPac Chat initiated.");
	sipac_new_id++;
};

SiPac.prototype.add_event_listeners = function()
{
	var chat_num = this.num;
    this.chat.getElementsByClassName('chat_message')[0].addEventListener("keydown", function (e) { sipac_objects[chat_num].check_return(e)}, false);
    this.chat.getElementsByClassName('chat_send_button')[0].addEventListener("click", function () { sipac_objects[chat_num].send_message() }, false);
};

SiPac.prototype.parse_parameters = function(parameters)
{
	this.chat = document.getElementById(parameters['id']);
	this.id = parameters['id'];
	this.client_num = parameters['client_num'];
	
	this.text = parameters['text'];
	this.layout = parameters['layout'];
	this.theme_functions = parameters['theme_functions'];
	this.default_channels = parameters['channels']
	
	this.ajax_timeout = parameters['ajax_timeout'];
	this.ajax_reconnect_timeout = parameters['ajax_reconnect_timeout'];
	this.chat_html_path = parameters['chat_html_path'];
	this.theme_html_path = parameters['theme_html_path'];
};

SiPac.prototype.add_theme_functions = function(theme_functions)
{
	this.theme_functions = sipac_theme_functions[this.num];
};