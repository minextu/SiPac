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

SiPac.prototype.handle_userlist = function(userlist)
{
	if (userlist != undefined)
	{
		for (key in this.channels)
		{
			var channel = this.channels[key];
			
			if (userlist[channel['id']] != undefined)
				this.handle_userlist_for_a_channel(userlist[channel['id']], channel['id']);
		}
	}	
};


SiPac.prototype.handle_userlist_for_a_channel = function (userlist_arr, channel)
{
	var userlist = this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0];

	if (userlist_arr['add_user'] != undefined && userlist_arr['add_user_id'] != undefined )
		this.add_users(userlist_arr['add_user'], userlist_arr['add_user_id'], channel, userlist);
	if (userlist_arr['change_user'] != undefined && userlist_arr['change_user_id'] != undefined)
		this.change_user(userlist_arr['change_user'], userlist_arr['change_user_id'], channel, userlist);
	if (userlist_arr['delete_user'] != undefined)
		this.delete_user(userlist_arr['delete_user'], channel, userlist);
  
	if (userlist_arr['user_writing'] != undefined)
	{
		for (var i = 0; i < userlist_arr['user_writing']['id'].length; i++)
		{
			var user_id = this.id + "_" + channel + "_user_" + userlist_arr['user_writing']['id'][i];
			if (typeof this.theme_functions['user_writing_status'] != "undefined")
				this.theme_functions['user_writing_status'](userlist_arr['user_writing']['status'][i], userlist_arr['users'][userlist_arr['user_writing']['id'][i]], user_id);
		}
	}
};

SiPac.prototype.add_users = function(users, user_ids, channel, userlist)
{
	for (var i = 0; i < users.length; i++)
	{
		var user = document.createElement("span");
		user.id = this.id + "_" + channel + "_user_" + user_ids[i];
		user.innerHTML += users[i];
		userlist.appendChild(user);
      
		if (users[user_ids[i]] == this.username)
			this.username_key = user_ids[i];
	}
};

SiPac.prototype.change_user = function(users, user_ids, channel, userlist)
{
	for (var i = 0; i < users.length; i++)
	{
		var user = document.createElement("span");
		user.id = this.id + "_" + channel + "_user_" + user_ids[i];
		user.innerHTML += users[i];
		userlist.replaceChild(document.getElementById(user.id), user);
	}
};

SiPac.prototype.delete_user = function(users, channel, userlist)
{
	for (var i = 0; i < users.length; i++)
	{
		var user = document.getElementById(this.id + "_" + channel + "_user_" + users[i]);
		userlist.removeChild(user);
	}
};