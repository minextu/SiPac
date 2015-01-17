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
SiPac.prototype.generate_ajax_request = function ()
{
	this.check_writing_status();

	this.properties_array = {};
	this.properties_array["last_message_id"] = this.last_message_id;
	this.properties_array["writing"] = this.is_writing; 
	this.properties_array["first_start"] = this.first_start;
	this.properties_array["active_channel"] = this.active_channel;
	this.properties_array["channels"] = this.channels;
	this.properties_array["send_messages"] = this.messages_to_send;

	this.changed_properties_array = {};
	
	if (typeof this.old_properties_array == "undefined")
		this.changed_properties_array = this.properties_array;
	else
	{
		for (property in this.properties_array)
		{
			if (this.properties_array[property].length !== this.old_properties_length_array[property] || this.properties_array[property] !== this.old_properties_array[property])
				this.changed_properties_array[property] = this.properties_array[property];
		}
	}
	
	this.old_properties_array = this.properties_array;
	this.old_properties_length_array = new Array();
	for (property in this.properties_array)
		this.old_properties_length_array[property] = this.properties_array[property].length;
	
	this.final_properties_array = this.changed_properties_array;
	this.final_properties_array["id"] = this.id;
	this.final_properties_array["client"] = this.client_num; 
	
	return this.changed_properties_array;
};

SiPac.prototype.parse_ajax_answer = function (answer)
{
	//if info text is given, show the information popup
	if (answer['info_text'] != undefined)
		this.information(answer['info_text'], answer['info_type'], answer['info_nohide']);
	
	//notify the user about a nickname change
	if (answer['get']['username'] != undefined && this.nickname != answer['get']['username'] && !this.first_start)
		this.information(this.text['name-change-text'].replace("%1", answer['get']['username']),  "info");
	
	this.handle_messages(answer['get']['posts'], answer['get']['post_users']);
	
	this.handle_userlist(answer['get']['userlist']);
	
	this.handle_debug(answer['debug']);

	if (answer['get']['tasks'] != undefined)
		this.handle_server_tasks(answer['get']['tasks']);

	this.last_message_id = answer['get']['last_id'];
	this.nickname = answer['get']['username']
	
	//change userlist number or the nickname text, if changed
	if (typeof answer['get']['userlist'][this.active_channel] != "undefined" && typeof answer['get']['userlist'][this.active_channel]['users'] != "undefined")
		this.handle_layout_changes(this.nickname, answer['get']['userlist'][this.active_channel]['users']);
	
	//delete all messages, that had been sent
	for (var i = 0; i < this.old_properties_length_array['send_messages']; i++)
	{
		var pending_message = this.chat.getElementsByClassName("chat_entry_sending_" + i)[0];
		try{pending_message.parentNode.removeChild(pending_message);}catch(e){}
		delete this.messages_to_send[i];
	}
	//if no new message was added, delete all empty messages
	if (this.old_properties_length_array['send_messages'] == this.messages_to_send.length)
		this.messages_to_send = new Array();
};

SiPac.prototype.handle_server_tasks = function (tasks)
{
	for (var i = 0; i < tasks.length; i++)
	{
		var task_parts = tasks[i].split("|");

		if (task_parts[0] == "kick" || task_parts[0] == "ban")
		{
			this.play_sound();
			//information popup will be displayed anyway, so no action required
			
		}
		else if (task_parts[0] == "message")
		{
			this.play_sound();
			alert(task_parts[1]);
		}
		else if (task_parts[0] == "join")
		{
			this.add_channel(task_parts[1], task_parts[2]);
			this.change_channel(task_parts[1]);
		}
		else  if (task_parts[0] == "invite")
		{
			if  (this.invite_enabled == true)
			{
				this.play_sound();
				
				//new_messages++;
				//new_messages_status(false);
				
				var confirm_return = confirm(this.text['user-invited-you-to-channel-text'].replace("%1", task_parts[3]).replace("%2", task_parts[2]));
				if (confirm_return == true)
				{
					this.add_channel(task_parts[1], task_parts[2]);
					this.change_channel(task_parts[1]);
				}
			}
		}
		else 
			this.add_debug_entry(0, "Unknown task '" + task_parts[0] + "'!"); 
	}
};