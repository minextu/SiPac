//AJAX//
httpobject = new XMLHttpRequest();

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



function add_chat(html_path, theme, id, client_num, channels, texts)
{
  chat_html_path = html_path;
  chat_objects_id[id] = chat_objects.length;
  chat_objects[chat_objects.length] = new Chat(theme, id, client_num, channels, texts);
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


    httpobject.open("POST", chat_html_path + "src/chat.php?task=get_chat", true);
    httpobject.onreadystatechange = function ()
    {
      if (httpobject.readyState == 4 && httpobject.status == 200)
      {
        try
        {
          try
          {
            var answer = JSON.parse(httpobject.responseText);
          }
          catch (e)
          {
            chat_error("Wrong answer: " + httpobject.responseText);
          }
          if (answer != undefined)
          {

            for (var i = 0; i < answer.length; i++)
            {
              chat_objects[i].handle_chat_tasks(answer[i]);
              if (chat_objects[i].first_start == true)
              {
                console.debug("chat " + i + " ready!");
                chat_objects[i].first_start = false;
              }
            }
            if (chat_error_num > 0)
              chat_error(undefined, true);
          }

          window.clearTimeout(chat_error_timeout);
          chat_timeout = window.setTimeout(chat_ajax, 500);
          chat_is_ajax = false;
        }
        catch (e)
        {
          chat_error(e);
        }
      }
    }
    httpobject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");


    httpobject.send("chat_string=" + encodeURIComponent(chat_ajax_text));

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

  chat_error_text = "Verbindung zum Server verloren";
  if (error != undefined)
    chat_error_text += " (" + error + ")";
  else
    chat_error_text += " (Timeout)";

  chat_error_information_text = chat_error_text + ". " + chat_error_num + ". Versuch...";

  for (var i = 0; i < chat_objects.length; i++)
  {
    if (old_chat_error_information_text != chat_error_information_text)
      chat_objects[i].information(chat_error_information_text, "error", true, undefined, true);
    if (old_chat_error_text != chat_error_text)
      chat_objects[i].add_debug_entries(new Array("<div class='chat_debug_entry'><span class='debug_warn'>" + chat_error_text + "</span></div>"));
  }

  if (error == undefined)
  {
    chat_is_ajax = false;
    chat_ajax();
  }
}

function Chat(theme, id, client_num, channels, texts)
{
  this.chat = document.getElementById(id);
  this.num = chat_objects.length;
  this.theme = theme;
  this.id = id;
  this.client_num = client_num;
  this.channels = channels;
  this.texts = texts;
  this.last_id = "none";
  this.first_start = true;
  this.new_channels = new Array();
  this.active_channel = "";
  this.enable_sound = true;
  this.add_channel(undefined, true);
  this.change_channel(this.channels[0]);
  this.user_info_status = new Array();

  this.username_key = 0;

  this.info_hide_timeout;

  this.messages_to_send = new Array();

  var element = this.chat.getElementsByClassName('chat_speech_bubble')[0];
  var style = window.getComputedStyle(element);
  this.speech_bubble_width = style.getPropertyValue('width');

  /* audio */
  this.new_post_audio = new Audio();
  this.new_post_audio.autobuffer = true;
  if (this.new_post_audio.canPlayType('audio/x-wav'))
  {
    this.new_post_audio.type = 'audio/x-wav';
    this.new_post_audio.src = chat_html_path + "themes/" + this.theme + "/sound/new_post.wav";
  }
  else
  {
    this.new_post_audio.type = 'audio/mpeg';
    this.new_post_audio.src = chat_html_path + "themes/" + this.theme + "/sound/new_post.mp3";
  }

  var chat_num = this.num;
  this.chat.getElementsByClassName('chat_message')[0].addEventListener("keydown", function (e)
  {
    chat_objects[chat_num].check_return(e)
  }, false);
  this.chat.getElementsByClassName('chat_send_button')[0].addEventListener("click", function ()
  {
    chat_objects[chat_num].send_message()
  }, false);
  this.chat.addEventListener("mousemove", new_messages_status, false);

  try
  {
    this.layout_init();
  }
  catch (e)
  {}
}

Chat.prototype.send_message = function (chat_message)
{
  if (chat_message == undefined)
  {
    chat_message = this.chat.getElementsByClassName('chat_message')[0].value;
    this.chat.getElementsByClassName('chat_message')[0].value = "";
  }
  chat_message = encodeURIComponent(chat_message);

  /*this.ajax_text = "send_message=" + chat_message;

  return this.ajax_text;*/

  this.messages_to_send[this.messages_to_send.length] = chat_message;

  chat_ajax();
};

Chat.prototype.get_chat = function ()
{
  if (this.chat.getElementsByClassName('chat_message')[0].value != "")
  {
    this.is_writing = true;
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
      this.information("Du hei&szlig;t jetzt: \"" + answer['get']['username'] + "\"", "info");

    try
    {
      this.chat.getElementsByClassName('chat_username')[0].innerHTML = answer['get']['username'] + ":";
    }
    catch (e)
    {}
  }
  this.username = answer['get']['username'];
  if (answer['get']['messages'] != undefined)
  {
    for (var i = 0; i < this.channels.length; i++)
    {
      if (this.new_channels[this.channels[i]] == true)
      {
        this.chat.getElementsByClassName('chat_conversation_channel_' + this.channels[i])[0].innerHTML = "";
        this.new_channels[this.channels[i]] = false;
      }

      if (answer['get']['messages'][this.channels[i]] != undefined)
        this.add_entries(this.channels[i], answer['get']['messages'][this.channels[i]], answer['get']['messages_user'][this.channels[i]], answer['get']['highlight']);
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

  this.add_debug_entries(answer['debug']);

  if (answer['get']['actions'] != undefined)
    this.handle_server_actions(answer['get']['actions']);

  if (answer['execute_custom_js'] == true)
    chat_custom_js(answer);


  if (answer['get']['afk'] != undefined)
    this.chat_afk = answer['get']['afk'];

  try
  {
    document.getElementById('chat_user_num')
      .innerHTML = answer['get']['userlist']['users'].length;
  }
  catch (e)
  {}

  try
  {
    this.chat_layout_tasks()
  }
  catch (e)
  {};

  try
  {
    if (this.is_writing)
      document.getElementById(this.id + "_" + this.active_channel + "_user_" + this.username_key).getElementsByClassName("chat_speech_bubble")[0].style.width = this.speech_bubble_width;
    else
      document.getElementById(this.id + "_" + this.active_channel + "_user_" + this.username_key).getElementsByClassName("chat_speech_bubble")[0].style.width = "0px";
   }
  catch(e){}
  
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
    }
    
    for (var i = 0; i < userlist_arr['users'].length; i++)
    {
      if (userlist_arr['users'][i] == this.username)
	this.username_key = i;
      console.debug(userlist_arr['users'][i] + " and " + this.username); 
    }
  }
  if (userlist_arr['change_user'] != undefined)
  {
    for (var i = 0; i < userlist_arr['change_user'].length; i++)
    {
      document.getElementById(this.id + "_" + channel + "_user_" + userlist_arr['change_user_id'][i])
        .innerHTML = userlist_arr['change_user'][i];
    }
    this.restore_user_info();
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
    this.restore_user_info();
  }

  if (userlist_arr['user_writing'] != undefined)
  {
    for (var i = 0; i < userlist_arr['user_writing'].length; i++)
    {
      if (userlist_arr['user_writing'][i] == "0")
        document.getElementById(this.id + "_" + channel + "_user_" + i)
          .getElementsByClassName("chat_speech_bubble")[0].style.width = "0px";
      else
        document.getElementById(this.id + "_" + channel + "_user_" + i)
          .getElementsByClassName("chat_speech_bubble")[0].style.width = this.speech_bubble_width;
    }
  }
};

Chat.prototype.add_entries = function (channel, entries, users, highlight)
{
  if (entries != undefined && entries.length > 0)
  {
    var chat_window = this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0];
    for (var i = 0; i < entries.length; i++)
    {
      chat_window.innerHTML += entries[i];
    }
    if (this.first_start)
      scroll(this.chat, "chat_conversation", 0, true);
    else
      scroll(this.chat, "chat_conversation", 20, true);

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
        if (this.enable_sound == true)
          chat_play_sound(this.new_post_audio);
      }
    }
  }
};

Chat.prototype.add_channel = function (channel, noadd)
{
  if (noadd == true && channel == undefined)
  {
    for (var i = 0; i < this.channels.length; i++)
    {
      this.add_channel(this.channels[i], true);
    }
  }
  else if (channel != undefined)
  {
    this.chat.getElementsByClassName("chat_channels_ul")[0].innerHTML += "<li id='" + this.id + "_channel_" + channel + "'><a href='javascript:void(0);' onclick='chat_objects[" + this.num + "].change_channel(this.innerHTML)'>" + channel + "</a></li>";
    this.chat.getElementsByClassName("chat_conversation")[0].innerHTML += "<div style='width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative;' class='chat_conversation_channel_" + channel + "'></div>";
    this.chat.getElementsByClassName("chat_userlist")[0].innerHTML += "<div style='width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative;' class='chat_userlist_channel_" + channel + "'></div>";

    this.chat.getElementsByClassName("chat_conversation_channel_" + channel)[0].innerHTML = "Blaba Chat wird geladen... und so";

    this.new_channels[channel] = true;

    if (noadd != true)
      this.channels[this.channels.length] = channel;
  }
  else
    alert("no channel name!");
};
Chat.prototype.change_channel = function (channel)
{
  for (var i = 0; i < this.channels.length; i++)
  {
    if (this.channels[i] == channel)
    {
      document.getElementById(this.id + "_channel_" + this.channels[i])
        .className = "chat_channel_selected";
      this.chat.getElementsByClassName("chat_conversation_channel_" + this.channels[i])[0].style.display = "block";
      this.chat.getElementsByClassName("chat_userlist_channel_" + this.channels[i])[0].style.display = "block";
      this.active_channel = this.channels[i];
    }
    else
    {
      document.getElementById(this.id + "_channel_" + this.channels[i])
        .className = "";
      this.chat.getElementsByClassName("chat_conversation_channel_" + this.channels[i])[0].style.display = "none";
      this.chat.getElementsByClassName("chat_userlist_channel_" + this.channels[i])[0].style.display = "none";
    }
  }
  scroll(this.chat, "chat_conversation", 0, true);
};

Chat.prototype.handle_server_actions = function (actions)
{
  for (var i = 0; i < actions.length; i++)
  {
    var action_parts = actions[i].split("|");

    if (action_parts[0] == "message" || action_parts[0] == "kick")
      chat_alert(action_parts[1]);
    if (action_parts[0] == "join")
    {
      this.add_channel(action_parts[1]);
      this.change_channel(action_parts[1]);
    }
    if (action_parts[0] == "kick")
    {
      chat_is_kicked = true;
      window.setTimeout(function ()
      {
        chat_stop();
      }, 100);
      this.information(action_parts[1], "error", true, false, true);
    }

    if (action_parts[0] != "message" && action_parts[0] != "kick" && action_parts[0] != "join")
      this.add_debug_entries(new Array("<div class='chat_debug_entry'><span class='debug_warn'>Unknown Action!</span></debug>"));
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
      info = "<span class='close_chat_information'>(<a href='#' onclick='chat_objects[" + this.num + "].information(undefined, undefined, undefined, true)'>schlie&szlig;en</a>)</span>" + info;

    info = "<br>" + info;

    if (type == "info")
      info = "<img src='" + chat_html_path + "themes/" + this.theme + "/icons/information.png' alt='I'> Info:" + info;
    else if (type == "error")
      info = "<img src='" + chat_html_path + "themes/" + this.theme + "/icons/exclamation.png' alt='I'> Fehler:" + info;
    else if (type == "warn")
      info = "<img src='" + chat_html_path + "themes/" + this.theme + "/icons/error.png' alt='I'> Warnung:" + info;
    else if (type == "success")
      info = "<img src='" + chat_html_path + "themes/" + this.theme + "/icons/check.png' alt='I'> Erfolgreich!" + info;

    info_msg_element_sub.innerHTML = info;
    var chat_num = this.num;
    if (nohide != true)
      this.info_hide_timeout = window.setTimeout(function ()
      {
        chat_objects[chat_num].information(undefined, undefined, undefined, true)
      }, 5000);
  }
};
Chat.prototype.add_smiley = function (code)
{
  this.chat.getElementsByClassName("chat_message")[0].value += code;
  this.chat.getElementsByClassName("chat_message")[0].focus();

};

Chat.prototype.add_debug_entries = function (debug_entries)
{
  if (debug_entries != undefined && debug_entries.length > 0)
  {
    var debug_box = this.chat.getElementsByClassName("chat_debug_box")[0];
    for (var i = 0; i < debug_entries.length; i++)
    {
      debug_box.innerHTML += debug_entries[i];
    }
    scroll(this.chat, "chat_debug_box", 20, true);
  }
};
Chat.prototype.ui_dropdown_sign = function (id, action)
{
  id = addslashes(id);

  if (action == "show")
    document.getElementById(id)
      .innerHTML = "<img src='" + chat_html_path + "themes/" + this.theme + "/icons/arrow_down.png'>";
  else if (action == "hide")
    document.getElementById(id)
      .innerHTML = "";
};
Chat.prototype.user_info = function (box_id)
{
  box_id = addslashes(box_id);
  if (this.user_info_status[box_id] == undefined || this.user_info_status[box_id] == "closed")
  {
    document.getElementById(box_id)
      .style.display = "block";
    this.user_info_status[box_id] = "opened";
  }
  else
  {
    document.getElementById(box_id)
      .style.display = "none";
    this.user_info_status[box_id] = "closed";
  }

};
Chat.prototype.restore_user_info = function ()
{
  for (var key in this.user_info_status)
  {
    if (this.user_info_status[key] == "closed")
    {
      try
      {
        document.getElementById(key)
          .style.display = "none";
      }
      catch (e)
      {
        this.user_info_status[key] = "closed";
      }
    }
    else
    {
      try
      {
        document.getElementById(key)
          .style.display = "block";
      }
      catch (e)
      {
        this.user_info_status[key] = "closed";
      }
    }
  }
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