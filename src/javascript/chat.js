/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013 Jan Houben

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
//AJAX//
httpobject = new XMLHttpRequest();
chat_ajax_timeout = 1000;

chat_extra_send = "";
chat_extra_send_objects = new Array();

function chat_init()
{
  chat_objects = new Array();
  chat_objects_id = new Array();
  chat_timeout = undefined;
  chat_error_timeout = undefined;
  chat_error_num = 0;
  chat_error_text = "";
  chat_error_information_text = "";
  chat_is_ajax = false;
  old_title = document.title;
  new_messages = 0;
  
  old_title = document.title;
  chat_ajax();
}

if (typeof chat_objects == 'undefined')
{
  chat_init();
}

window.chat_stop = function()
{
	delete chat_objects;
	window.clearTimeout(chat_timeout);
	window.clearTimeout(chat_error_timeout);
	delete SiPacHttpRequest;
}

function add_chat(html_path, theme_path, id, client_num, channels,channel_titles, texts, layout, ajax_timeout)
{
  chat_html_path = html_path;
  chat_objects_id[id] = chat_objects.length;
  chat_objects[chat_objects.length] = new Chat(theme_path, id, client_num, channels,channel_titles, texts, layout);
	chat_ajax_timeout = ajax_timeout;
}

function chat_ajax()
{
  window.clearTimeout(chat_timeout);
  window.clearTimeout(chat_error_timeout);

  if (chat_objects.length != 0 && chat_is_ajax == false)
  {
    chat_is_ajax = true;
    chat_error_timeout = window.setTimeout(chat_error, 20000);


    var chat_ajax_text = "";
    for (var i = 0; i < chat_objects.length; i++)
    {
      chat_ajax_text += "&&";
      chat_ajax_text += chat_objects[i].get_chat();

      if (chat_objects[i].messages_to_send.length > 0)
        chat_ajax_text += "&send_message=";
      for (var ii = 0; ii < chat_objects[i].messages_to_send.length; ii++)
      {
        if (ii != 0)
          chat_ajax_text += "|||";
        chat_ajax_text += escape(chat_objects[i].messages_to_send[ii]);
      }
      chat_objects[i].messages_to_send = new Array();
    }


    httpobject.open("POST", chat_html_path + "src/php/SiPac.php?task=get_chat", true);
    httpobject.onreadystatechange = function ()
    {
      if (httpobject.readyState == 4 && httpobject.status == 200)
      {
     //   try
       // {
          try
          {
            var answer = JSON.parse(httpobject.responseText);
			var chat_object_answer = answer['SiPac'];
          }
          catch (e)
          {
            chat_error("Wrong answer: " + httpobject.responseText);
          }
          if (chat_object_answer != undefined)
          {

            for (var i = 0; i < chat_object_answer.length; i++)
            {
			if (chat_object_answer[i]["error"] != undefined)
				chat_objects[i].information(chat_object_answer[i]["error"], "error", false, false, false);
			else
			{
				chat_objects[i].handle_chat_tasks(chat_object_answer[i]);
				if (chat_objects[i].first_start == true)
				{
					console.debug("chat " + i + " ready!");
					chat_objects[i].first_start = false;
				}
			}
			if (chat_error_num > 0)
				chat_error(undefined, true);
		}
          }

          for (var i = 0; i < chat_extra_send_objects.length; i++)
		  {
			chat_extra_send_objects[i].responseText = answer['SiPac_custom_request_answer'][i];
			chat_extra_send_objects[i].onreadystatechange();
		  }
          chat_extra_send_objects = new Array();
          
          window.clearTimeout(chat_error_timeout);
          chat_timeout = window.setTimeout(chat_ajax, chat_ajax_timeout);
          chat_is_ajax = false;
       // }
        //catch (e)
        //{
          //chat_error(e);
        //}
      }
    }
    httpobject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    var chat_ajax_text_comp = "chat_string=" + encodeURIComponent(chat_ajax_text);
    if (chat_extra_send != "")
      chat_ajax_text_comp += "&" + chat_extra_send;
    
	chat_extra_send = "";
	
    httpobject.send(chat_ajax_text_comp);

  }
  else if (chat_objects.length == 0)
    chat_timeout = window.setTimeout(chat_ajax, 1000);

}

function chat_error(error, clear)
{
  if (clear == true)
  {
    chat_error_num = 0;
    chat_error_text = "";
    chat_error_information_text = "";

    for (var i = 0; i < chat_objects.length; i++)
    {
      chat_objects[i].information(undefined, undefined, undefined, true);
    }

    return false;
  }
  chat_error_num++;

  var old_chat_error_text = chat_error_text;
  var old_chat_error_information_text = chat_error_information_text;

  chat_error_text = "Connection lost";
  if (error != undefined)
    chat_error_text += " (" + error + ")";
  else
    chat_error_text += " (Timeout)";

  chat_error_information_text = chat_error_text + ". " + chat_error_num + ". Try...";
  for (var i = 0; i < chat_objects.length; i++)
  {
    if (old_chat_error_information_text != chat_error_information_text)
      chat_objects[i].information(chat_error_information_text, "error", true, undefined, true);
    if (old_chat_error_text != chat_error_text)
      chat_objects[i].add_debug_entry(0, chat_error_text);
  }

  if (error == undefined)
  {
    chat_is_ajax = false;
    chat_ajax();
  }
}

function Chat(theme_path, id, client_num, channels, channel_titles, texts, layout)
{
  this.chat = document.getElementById(id);
  this.num = chat_objects.length;
  this.theme_path = theme_path;
  this.layout_array  = layout;
  this.id = id;
  this.client_num = client_num;
  this.channels = channels;
  this.channel_titles = channel_titles;
  this.texts = texts;
  this.last_id = 0;
  this.first_start = true;
  this.new_channels = new Array();
  this.active_channel = "";
  
  this.notifications_enabled = false;
  this.invite_enabled = true;
  this.sound_enabled = true;
  this.autoscroll_enabled = true;

  this.add_channel(undefined, undefined, true);
  this.change_channel(this.channels[0]);


  this.username_key = 0;

  this.info_hide_timeout;

  this.alert_status = 2;
  
  this.old_message = "";
  this.messages_to_send = new Array();

  /* audio */
  this.new_post_audio = new Audio();
  this.new_post_audio.autobuffer = true;
  if (this.new_post_audio.canPlayType('audio/x-wav'))
  {
    this.new_post_audio.type = 'audio/x-wav';
    this.new_post_audio.src = this.theme_path + "/sound/new_post.wav";
  }
  else
  {
    this.new_post_audio.type = 'audio/mpeg';
    this.new_post_audio.src = this.theme_path + "/sound/new_post.mp3";
  }
}
Chat.prototype.init = function()
{
  var chat_num = this.num;
  try{
    this.chat.getElementsByClassName('chat_message')[0].addEventListener("keydown", function (e) { chat_objects[chat_num].check_return(e)}, false);
  }catch(e){
    this.add_debug_entry(0, "Missing chat_message in this theme!");
  }try{
    this.chat.getElementsByClassName('chat_send_button')[0].addEventListener("click", function () { chat_objects[chat_num].send_message() }, false);
  }catch(e){
    this.add_debug_entry(0, "Missing chat_send_button in this theme!");}
	if (this.chat.getElementsByClassName("chat_channels_ul")[0] == undefined)
		this.add_debug_entry(1, "Missing chat_channels_ul in theme!");
	
  this.chat.addEventListener("mousemove", new_messages_status, false);
  
  if (this.autoscroll_enabled)
	scroll(this.chat, "chat_conversation", 0, true);
  
  this.restore_settings();
  
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
Chat.prototype.send_message = function (chat_message)
{
  if (chat_message == undefined)
  {
    chat_message = this.chat.getElementsByClassName('chat_message')[0].value;
    this.chat.getElementsByClassName('chat_message')[0].value = "";
  }
  chat_message = encodeURIComponent(chat_message);

  this.messages_to_send[this.messages_to_send.length] = chat_message;

  chat_ajax();
};

Chat.prototype.get_chat = function ()
{
  if (this.chat.getElementsByClassName('chat_message') != undefined && this.chat.getElementsByClassName('chat_message')[0].value != "" && this.chat.getElementsByClassName('chat_message')[0].value != this.old_message)
  {
    this.is_writing = true;
    var num = this.num;
    var value = chat_objects[num].chat.getElementsByClassName('chat_message')[0].value;
    
    window.setTimeout(function() { chat_objects[num].old_message = value }, 5000)
    new_messages_status(true);
  }
  else
    this.is_writing = false;


  this.ajax_text = "chat_id=" + this.id + "&client_num=" + this.client_num + "&last_id=" + this.last_id + "&writing=" + this.is_writing + "&first_start=" + this.first_start + "&active_channel=" + this.active_channel + "&channels=";
  for (var i = 0; i < this.channels.length; i++)
  {
    if (i != 0)
      this.ajax_text += "|||";

    this.ajax_text += this.channels[i];
  }

  return this.ajax_text;
};

Chat.prototype.handle_chat_tasks = function (answer)
{
  if (this.first_start == true)
    this.chat.getElementsByClassName('chat_conversation_channel_' + this.active_channel)[0].innerHTML = "";

  if (answer['info_text'] != undefined)
    this.information(answer['info_text'], answer['info_type'], answer['info_nohide']);

  this.last_id = answer['get']['last_id'];
  if (answer['get']['username'] != undefined && this.username != answer['get']['username'] && this.first_start != true || this.first_start == true)
  {
    if (this.first_start != true)
      this.information(this.texts['name-change-text'].replace("%1", answer['get']['username']),  "info");

    try
    {
      this.chat.getElementsByClassName('chat_username')[0].innerHTML = answer['get']['username'] + ":";
    }
    catch (e)
    {}
  }
  this.username = answer['get']['username'];
  if (answer['get']['posts'] != undefined)
  {
    for (var i = 0; i < this.channels.length; i++)
    {
      if (this.new_channels[this.channels[i]] == true)
        this.chat.getElementsByClassName('chat_conversation_channel_' + this.channels[i])[0].innerHTML = "";
      
      if (answer['get']['posts'][this.channels[i]] != undefined)
      {
        this.add_entries(this.channels[i], answer['get']['posts'][this.channels[i]], answer['get']['post_users'][this.channels[i]], answer['get']['post_messages'][this.channels[i]]);
      }
      if (this.new_channels[this.channels[i]] == true)
      {
        this.new_channels[this.channels[i]] = false;
	  if (this.autoscroll_enabled)
		scroll(this.chat, "chat_conversation", 0, true);
      }
    }
  }
  if (answer['get']['userlist'] != undefined)
  {
    for (var i = 0; i < this.channels.length; i++)
    {
      if (answer['get']['userlist'][this.channels[i]] != undefined)
        this.handle_userlist(answer['get']['userlist'][this.channels[i]], this.channels[i]);
    }
  }
    for (var i = 0; i < this.channels.length; i++)
    {
			if (answer['debug'][this.channels[i]] != undefined)
				this.add_debug_entries(answer['debug'][this.channels[i]], this.channels[i]);
		}
		
		if (answer['debug'][0] != undefined)
			this.add_debug_entries(answer['debug'][0], this.active_channel);

  if (answer['get']['tasks'] != undefined)
    this.handle_server_tasks(answer['get']['tasks']);

  if (answer['execute_custom_js'] == true)
    chat_custom_js(answer);

 try
  {
    var user_num = 0;
    for (e in answer['get']['userlist'][this.active_channel]['users']) { user_num++; }
    this.chat.getElementsByClassName('chat_user_num')[0].innerHTML = user_num;
  }
  catch (e)
  {}

	if (typeof this.layout_tasks != "undefined")
		this.layout_tasks();
};

Chat.prototype.handle_userlist = function (userlist_arr, channel)
{

  if (this.first_start == true)
    this.chat.getElementsByClassName('chat_userlist_channel_' + channel)[0].innerHTML = "";


  chat_userlist = this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0];


  if (userlist_arr['add_user'] != undefined)
  {
    for (var i = 0; i < userlist_arr['add_user'].length; i++)
    {
      chat_userlist.innerHTML += "<span id='" + this.id + "_" + channel + "_user_" + userlist_arr['add_user_id'][i] + "'>" + userlist_arr['add_user'][i] + "</span>";
      
      if (userlist_arr['users'][userlist_arr['add_user_id'][i]] == this.username)
	this.username_key = userlist_arr['add_user_id'][i];
    }
  }
  if (userlist_arr['change_user'] != undefined)
  {
    for (var i = 0; i < userlist_arr['change_user'].length; i++)
    {
      document.getElementById(this.id + "_" + channel + "_user_" + userlist_arr['change_user_id'][i])
        .innerHTML = userlist_arr['change_user'][i];
    }
  }

  if (userlist_arr['delete_user'] != undefined)
  {
    for (var i = 0; i < userlist_arr['delete_user'].length; i++)
    {
      try
      {
        var tag = document.getElementById(this.id + "_" + channel + "_user_" + userlist_arr['delete_user'][i]);
        tag.parentNode.removeChild(tag);
      }
      catch (e)
      {}
    }
  }

  if (userlist_arr['user_writing'] != undefined)
  {
    for (var i = 0; i < userlist_arr['user_writing']['id'].length; i++)
    {
		if (typeof this.layout_user_writing_status != "undefined")
			this.layout_user_writing_status(userlist_arr['user_writing']['status'][i], userlist_arr['users'][userlist_arr['user_writing']['id'][i]], this.id + "_" + channel + "_user_" + userlist_arr['user_writing']['id'][i]);
    }
  }
};

Chat.prototype.add_entries = function (channel, entries, users, messages)
{
  if (entries != undefined && entries.length > 0)
  {
    var chat_window = this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0];
    for (var i = 0; i < entries.length; i++)
    {
      chat_window.innerHTML += entries[i];
	if (users[i] != this.username && this.notifications_enabled == true && !this.first_start)
		  this.show_notification(users[i] + " (" + this.channel_titles[this.channels.indexOf(channel)] + ")", messages[i]);
    }
    if (this.autoscroll_enabled)
    {
	if (this.first_start)
		scroll(this.chat, "chat_conversation", 0, true);
	else
		scroll(this.chat, "chat_conversation", 20, true);
    }
	if (channel != this.active_channel && !this.first_start)
		this.channel_new_messages(channel, this.channel_titles[this.channels.indexOf(channel)]);
	
    if (this.first_start != true)
    {
      var no_sound = true;
      if (users != undefined)
      {
        for (var i = 0; i < users.length; i++)
        {
          if (users[i] != this.username)
            no_sound = false;
        }
      }
      else
        no_sound = false;
      if (no_sound == false)
      {
        new_messages++;
        new_messages_status(false);
        if (this.sound_enabled == true)
          chat_play_sound(this.new_post_audio);
      }
    }
  }
};

Chat.prototype.channel_new_messages = function(channel, channel_title)
{
	document.getElementById(this.id + "_channel_" +channel).className = "chat_channel_unread";
};

Chat.prototype.generate_channel_html = function(channel, channel_title)
{
	return this.layout_array['channel_tab'].replace(
		"!!ID!!", this.id + "_channel_" + channel).replace(
		"!!CHANNEL_CHANGE_FUNCTION!!", "chat_objects[" + this.num + "].change_channel(\"" +  channel + "\")").replace(
		"!!CHANNEL_CLOSE_FUNCTION!!", "chat_objects[" + this.num + "].close_channel(\"" + channel + "\")").replace(
		"!!CHANNEL!!", channel_title);
};
Chat.prototype.add_channel = function (channel, channel_title, noadd)
{
  if (noadd == true && channel == undefined)
  {
    for (var i = 0; i < this.channels.length; i++)
    {
      this.add_channel(this.channels[i], this.channel_titles[i], true);
    }
  }
  else if (channel != undefined)
  {
	  var channel_exist = false;
	  for (var ii = 0; ii < this.channels.length; ii++)
	  {
		if (this.channels[ii] == channel)
			channel_exist = true;
	  }
	  if (channel_exist == false || noadd == true)
	  {
			if (this.chat.getElementsByClassName("chat_channels_ul")[0] != undefined)
				this.chat.getElementsByClassName("chat_channels_ul")[0].innerHTML +=	 this.generate_channel_html(channel, channel_title);
			
			this.chat.getElementsByClassName("chat_conversation")[0].innerHTML += "<div style='width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative; display: none;' class='chat_conversation_channel_" + channel + "'></div>";
			this.chat.getElementsByClassName("chat_userlist")[0].innerHTML += "<div style='width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative; display: none;' class='chat_userlist_channel_" + channel + "'></div>";

			this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0].innerHTML = this.texts['room-loading-text'];

			this.new_channels[channel] = true;

			if (noadd != true)
			{
			this.channels[this.channels.length] = channel;
			this.channel_titles[this.channel_titles.length] = channel_title;
			}
	  }
  }
  else
    alert("no channel name!");
};
Chat.prototype.change_channel = function (channel)
{
	try
	{
	document.getElementById(this.id + "_channel_" + this.active_channel).className = "chat_channel";
	this.chat.getElementsByClassName("chat_conversation_channel_" + this.active_channel)[0].style.display = "none";
	this.chat.getElementsByClassName("chat_userlist_channel_" + this.active_channel)[0].style.display = "none";
	}catch(e){}
	
	try{
	document.getElementById(this.id + "_channel_" + channel).className = "chat_channel_selected";}catch(e){}
	
	this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0].style.display = "block";
	this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0].style.display = "block";
	this.active_channel = channel;
	if (this.autoscroll_enabled)
		scroll(this.chat, "chat_conversation", 0, true);
};
Chat.prototype.close_channel = function (channel)
{
	if (this.channels.length > 1)
	{
      document.getElementById(this.id + "_channel_" +channel).parentNode.removeChild(document.getElementById(this.id + "_channel_" + channel));
      this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0].parentNode.removeChild(this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0]);
      this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0].parentNode.removeChild(this.chat.getElementsByClassName("chat_userlist_channel_" + channel)[0]);
	  
	  this.channels.splice(this.channels.indexOf(channel),1);
	  this.channel_titles.splice(this.channel_titles.indexOf(channel),1);
		  
      if (this.active_channel = channel)
		  this.change_channel(this.channels[0]);
	}
	else
		alert("You can't close the last channel left!");
};
Chat.prototype.handle_server_tasks = function (tasks)
{
  for (var i = 0; i < tasks.length; i++)
  {
    var task_parts = tasks[i].split("|");

    if (task_parts[0] == "kick")
    {
	    if (task_parts[1] == "")
		alert(this.texts['you-were-kicked-text'].replace("%1", task_parts[2]));
	    else
		    alert(this.texts['you-were-kicked-by-user-text'].replace("%1", task_parts[1]).replace("%2", task_parts[2]));
    }
    else if (task_parts[0] == "ban")
    {
	    if (task_parts[1] == "")
		    alert(this.texts['you-were-banned-text'].replace("%1", task_parts[3]));
	    else
		    alert(this.texts['you-were-banned-by-user-text'].replace("%1", task_parts[1]).replace("%2", task_parts[3]));
    }
    else if (task_parts[0] == "message")
      this.alert(task_parts[1]);
    else if (task_parts[0] == "join")
    {
      this.add_channel(task_parts[1], task_parts[2]);
      this.change_channel(task_parts[1]);
    }
   else  if (task_parts[0] == "invite" && this.invite_enabled == true)
	{
        if (this.sound_enabled == true)
          chat_play_sound(this.new_post_audio);
		
		new_messages++;
        new_messages_status(false);
		
		var confirm_return = confirm(this.texts['user-invited-you-to-channel-text'].replace("%1", task_parts[3]).replace("%2", task_parts[2]));
		if (confirm_return == true)
		{
			this.add_channel(task_parts[1], task_parts[2]);
			this.change_channel(task_parts[1]);
		}
	}
    else if (task_parts[0] == "kick")
    {
      chat_is_kicked = true;
      window.setTimeout(function ()
      {
        chat_stop();
      }, 100);
      this.information(task_parts[1], "error", true, false, true);
    }
   else 
      this.add_debug_entry("Unknown task '" + task_parts[0] + "'!", 0); 
  }
}

Chat.prototype.information = function (info, type, nohide, onlyhide, noclose)
{
  var info_msg_element = this.chat.getElementsByClassName("chat_notice_msg")[0];
  info_msg_element.innerHTML = "";

  window.clearTimeout(this.info_hide_timeout);


  if (onlyhide)
  {
    info_msg_element.style.visibility = "hidden";
  }
  else
  {
    var info_msg_element_sub = document.createElement('div');
    info_msg_element.appendChild(info_msg_element_sub);
    info_msg_element.style.visibility = "visible";

    if (type == "success")
      info_msg_element_sub.className = "chat_notice_success";
    else if (type == "warn")
      info_msg_element_sub.className = "chat_notice_warn";
    else if (type == "error")
      info_msg_element_sub.className = "chat_notice_error";
    else if (type == "info")
      info_msg_element_sub.className = "chat_notice_info";

    if (noclose != true)
	    info = "<span class='close_chat_information'><a href='javascript:void(0)' onclick='chat_objects[" + this.num + "].information(undefined, undefined, undefined, true)'><img src='" + this.theme_path + "/icons/delete.png' alt='(close)' title='close'></a></span>" + info;

    info = "<br>" + info;

    if (type == "info")
	    info = "<img src='" + this.theme_path + "/icons/information.png' alt='I'> " + this.texts['info-head'] + info; //Info:
    else if (type == "error")
	    info = "<img src='" + this.theme_path + "/icons/exclamation.png' alt='I'> " + this.texts['error-head'] + info; //Error:
    else if (type == "warn")
	    info = "<img src='" + this.theme_path + "/icons/error.png' alt='I'> " + this.texts['warning-head'] + info; //Warning: 
    else if (type == "success")
	    info = "<img src='" + this.theme_path + "/icons/check.png' alt='I'> " + this.texts['success-head'] + info; //Success:

    info_msg_element_sub.innerHTML = info;
    var chat_num = this.num;
    if (nohide != true)
      this.info_hide_timeout = window.setTimeout(function ()
      {
        chat_objects[chat_num].information(undefined, undefined, undefined, true)
      }, 5000);
  }
};
Chat.prototype.save_settings = function()
{
	var today = new Date();
	var expires = new Date(today.getTime() + 365 * 24 * 60 * 60 * 1000);
	expires = expires.toGMTString();
	document.cookie="SiPac_settings_" + this.id + "=" + this.notifications_enabled + "|" + this.sound_enabled + "|"  + this.autoscroll_enabled + "|" + this.invite_enabled + "; expires=" + expires + ";";
}
Chat.prototype.restore_settings = function()
{
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
}
Chat.prototype.disable_sound = function()
{
	this.sound_enabled = false;
	if (typeof this.layout_sound_status != "undefined")
		this.layout_sound_status(false);
	else
		try{this.chat.getElementsByClassName("chat_sound_checkbox")[0].checked = false;}catch(e){}
	
	this.save_settings();
};
Chat.prototype.enable_sound = function()
{
	this.sound_enabled = true;
	if (typeof this.layout_sound_status != "undefined")
		this.layout_sound_status(true);
	else
		try{this.chat.getElementsByClassName("chat_sound_checkbox")[0].checked = true;}catch(e){}
	
	this.save_settings();
};
Chat.prototype.disable_autoscroll = function()
{
	this.autoscroll_enabled = false;
	if (typeof this.layout_autoscroll_status != "undefined")
		this.layout_autoscroll_status(false);
	else
		try{this.chat.getElementsByClassName("chat_autoscroll_checkbox")[0].checked = false;}catch(e){}
	
	this.save_settings();
};
Chat.prototype.enable_autoscroll = function()
{
	this.autoscroll_enabled = true;
	if (typeof this.layout_autoscroll_status != "undefined")
		this.layout_autoscroll_status(true);
	else
		try{this.chat.getElementsByClassName("chat_autoscroll_checkbox")[0].checked = true;}catch(e){}
	
	this.save_settings();
};
Chat.prototype.disable_invite= function()
{
	this.invite_enabled = false;
	if (typeof this.layout_invite_status != "undefined")
		this.layout_invite_status(false);
	else
		try{this.chat.getElementsByClassName("chat_invite_checkbox")[0].checked = false;}catch(e){}
	
	this.save_settings();
};
Chat.prototype.enable_invite = function()
{
	this.invite_enabled = true;
	if (typeof this.layout_invite_status != "undefined")
		this.layout_invite_status(true);
	else
		try{this.chat.getElementsByClassName("chat_invite_checkbox")[0].checked = true;}catch(e){}
	
	this.save_settings();
};
Chat.prototype.disable_notifications = function()
{
	this.notifications_enabled = false;
	if (typeof this.layout_notification_status != "undefined")
		this.layout_notification_status(false);
	else
		try{this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;}catch(e){}
		
	this.save_settings();
};
Chat.prototype.enable_notifications = function()
{
	var Notification = window.Notification || window.mozNotification || window.webkitNotification;
	if (Notification.permission != "granted")
	{
		this.notifications_enabled = false;
		if (typeof this.layout_notification_status != "undefined")
			this.layout_notification_status(false);
		else
			try{this.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;}catch(e){}
			
		this.save_settings();
		var chat = this;
		Notification.requestPermission(function (permission) 
		{
			if (permission == "granted")
			{
				chat.notifications_enabled = true;
				if (typeof chat.layout_notification_status != "undefined")
					chat.layout_notification_status(true);
				else
					try{chat.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = true;}catch(e){}
				
				chat.show_notification(chat.texts['desktop-notifications-enabled-head'],chat.texts['desktop-notifications-enabled-text']);
				
				chat.save_settings();
			}
			else
			{
				chat.notifications_enabled = false;
				if (typeof chat.layout_notification_status != "undefined")
					chat.layout_notification_status(false);
				else
					try{chat.chat.getElementsByClassName("chat_notification_checkbox")[0].checked = false;}catch(e){}
				
				chat.save_settings();
			}
		});
	}
	else
	{
		this.notifications_enabled = true;
		if (typeof this.layout_notification_status != "undefined")
			this.layout_notification_status(true);
		this.show_notification(this.texts['desktop-notifications-enabled-head'],this.texts['desktop-notifications-enabled-text']);
		
		this.save_settings();
	}
};
Chat.prototype.show_notification = function(head, message)
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
Chat.prototype.add_smiley = function (code)
{
  this.chat.getElementsByClassName("chat_message")[0].value += code;
  this.chat.getElementsByClassName("chat_message")[0].focus();

};
Chat.prototype.add_debug_entries = function (debug_entries, channel)
{
  if (debug_entries != undefined)
  {
    for (var i in debug_entries)
    {
      this.add_debug_entry(debug_entries[i]['type'], debug_entries[i]['text'], channel);
    }
  } 
}
Chat.prototype.add_debug_entry = function (type, text, channel, timeout)
{
	if (timeout != true)
	{
		switch(type)
		{
			case 0:
				console.error(text);
				break;
			case 1:
				console.warn(text);
				break;
			default:
				console.debug(text);
				break;
		}
	}
	if (this.first_start == true)
	{
		var chat = this;
		window.setTimeout(function() { chat.add_debug_entry(type, text, channel, true) }, 100);			
		return false;
	}
	
	if (channel == undefined)
		channel = this.active_channel;
	if (typeof this.layout_add_debug != "undefined")
		this.layout_add_debug(type, text, channel);
	else
	{
		this.chat.getElementsByClassName('chat_conversation_channel_' + channel)[0].innerHTML += "<div class='chat_entry_debug'><span class='chat_entry_message'>" + text + "</span></div>";	
	}
  if (this.autoscroll_enabled)
	scroll(this.chat, "chat_conversation", 20, true);
};
Chat.prototype.insert_command = function(command, auto_send)
{
  if (auto_send)
    this.send_message("/" + command);
  else
    this.chat.getElementsByClassName('chat_message')[0].value = "/" + command;
};

Chat.prototype.alert = function(text, action)
{
	/*
  if (action == "close")
  {
    this.alert_status = 2;
    try
    {
      this.chat.getElementsByClassName('chatalertcontent')[0].parentNode.removeChild(this.chat.getElementsByClassName('chatalertcontent')[0]);
      this.chat.getElementsByClassName('chatalertbg')[0].parentNode.removeChild(this.chat.getElementsByClassName('chatalertbg')[0]);
    }catch(e){}
  }
  else
  {
    this.alert(undefined, "close");
    var chat_message = "<div class='chatalertbg'></div><div class='chatalertcontent'>";
    if (action != "noclose")
      chat_message += "<span style='float:right;'><img class='link' onclick='chat_objects[" + this.num + "].alert(undefined, \"close\")' src='" + chat_html_path + "themes/" + this.theme + "/icons/delete.png' alt='close'></span><br>";

    chat_message+= "<div class='chat_alert'>" + text;
    chat_message += "</div><br></div>";
    this.chat.innerHTML += chat_message;
    
    this.init();
    
    this.alert_status = 1;
  }	
  */
	alert(text);
};
Chat.prototype.prompt = function(text, id, action, button_text)
{
	/*
  var alert_text = text;
  alert_text += "<p><input type='text' onkeydown='if (event.keyCode == 13) { " + action + " chat_objects[" + this.num + "].alert(undefined, \"close\");}' id='" + id + "'></p><button onclick='" + action + " chat_objects[" + this.num + "].alert(undefined, \"close\");'>" + button_text + "</button></p>";
  this.alert(alert_text)
  */
	alert(' in development');
};

Chat.prototype.kick_user = function(user)
{			//Reason for the Kick:																																		kick user
   var reason = prompt(this.texts['reason-for-kick-text']);
   if (reason != null)
	this.insert_command("kick " + user + " " + reason, true);
}

Chat.prototype.msg_user = function(user)
{
	var message = prompt(this.texts['private-message-prompt-text']);
   if (message != null)
	this.insert_command("msg " + user + " " + message, true);
};

Chat.prototype.check_return = function (e)
{
  if (e.keyCode == 13)
    this.send_message();
};

function chat_play_sound(audio)
{
  audio.play();
}

function new_messages_status(delete_nm)
{
  if (!delete_nm)
  {
    if (new_messages >= 1)
    {
      document.title = " (" + new_messages + ") " + old_title;
    }
  }
  else
  {
    new_messages = 0;
    document.title = old_title;
  }
}

function scroll(id, classname, speed, first)
{
  var y;
  if (id.getElementsByClassName(classname)[0] && id.getElementsByClassName(classname)[0].scrollTop)
    y = id.getElementsByClassName(classname)[0].scrollTop;

  if (first == true)
    y = -10;

  if (y < id.getElementsByClassName(classname)[0].scrollHeight - id.getElementsByClassName(classname)[0].offsetHeight)
  {
    if (speed == 0)
      id.getElementsByClassName(classname)[0].scrollTop = id.getElementsByClassName(classname)[0].scrollHeight;
    else
    {
      id.getElementsByClassName(classname)[0].scrollTop += 5;
      window.setTimeout(function ()
      {
        scroll(id, classname, speed)
      }, speed);
    }
  }
}

function addslashes(str)
{
  str = str.replace(/\\/g, '\\\\');
  str = str.replace(/\'/g, '\\\'');
  str = str.replace(/\"/g, '\\"');
  str = str.replace(/\0/g, '\\0');
  return str;
}

window.SiPacHttpRequest = function ()
{
	this.readyState = 4;
	this.status = 200;
	this.file = false;
	this.responseText = "";
	this.location = location.pathname;
	this.location = this.location.substring(0, this.location.lastIndexOf('/'));
}
SiPacHttpRequest.prototype.open = function(type, file, async)
{
	if (async != true)
		alert("only async ajax supported");
	else
	{
		if (file.search("http://") != -1)
			alert("Only internal links supported")
		if (file.charAt(0) == "/")
			this.file = file;
		else
			this.file = this.location + "/" + file;
	}
};
SiPacHttpRequest.prototype.onreadystatechange = function()
{
	
};
SiPacHttpRequest.prototype.setRequestHeader = function(x, y)
{
	
};
SiPacHttpRequest.prototype.send = function(post)
{
	if (chat_is_ajax == false)
	{
		this.post = post;
		if (chat_extra_send != "")
			chat_extra_send += "&";
		chat_extra_send += post + "&SiPacHttpFile=" + encodeURIComponent(this.file);
		
		chat_extra_send_objects[chat_extra_send_objects.length] = this;
	}
	else
	{
		var httpobject = this;
		window.setTimeout(function() { httpobject.send(post) }, 10);
	}
};