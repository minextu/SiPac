//AJAX//

var httpobject = false;
//IE7, Firefox, etc
if (typeof XMLHttpRequest != 'undefined') 
  {
    httpobject = new XMLHttpRequest();
  }
// IE6, IE5
if (!httpobject) 
  {
    try 
      {
        httpobject = new ActiveXObject("Msxml2.XMLHTTP");
      }
    catch(e) 
      {
        try 
	  {
	    httpobject = new ActiveXObject("Microsoft.XMLHTTP");
	  }
	catch(e) 
	  {
	    httpobject = null;
	  }
      }	
  }
  
function addslashes(str) 
	{
		str=str.replace(/\\/g,'\\\\');
		str=str.replace(/\'/g,'\\\'');
		str=str.replace(/\"/g,'\\"');
		str=str.replace(/\0/g,'\\0');
		return str;
	}
	
chat_extra_send = undefined;
/* audio */
new_post_audio = new Audio();
new_post_audio.autobuffer = true;
if (new_post_audio.canPlayType('audio/x-wav')) 
	{
		new_post_audio.type= 'audio/x-wav';
		new_post_audio.src = html_path + "themes/" + chat_design + "/sound/new_post.wav";
	}
else 
	{
		new_post_audio.type= 'audio/mpeg';
		new_post_audio.src = html_path + "themes/" + chat_design + "/sound/new_post.mp3";
	}
	
/* js settings */
var get_interval = 500; //time in milliseconds



/* Main chat functions */

var chat_status = "none",
		last_id = "none",
		chat_username = "",
		chat_sound = true,
		chat_afk = false,
		new_messages = 0,
		chat_is_kicked = false,

		chat_active_channel = "",

		element = document.getElementsByClassName('chat_speech_bubble')[0],
    style = window.getComputedStyle(element),
    chat_speech_bubble_width = style.getPropertyValue('width'),
		old_title = document.title;
try
{
	chat_stop();
}
catch(e)
{
var keep_up_timeout;
var get_chat_timeout;
var chat_timeout;
}
chat_init();
function chat_init(notfirst, error)
{
	
	document.getElementById("chat_main").addEventListener("mousemove", new_messages_status, false);
	
	httpobject.abort();
	
	
	if (notfirst == true)
		get_chat(undefined, undefined, true);
	else
		{
			chat_add_channel(undefined, true);
			chat_change_channel(chat_channels[0]);
			try
			{
				chat_layout_init();
			}catch(e){}
			

			
			
			get_chat(true, undefined, error);
			document.getElementById("chat_message").focus();
		}
}
function chat_stop()
{
	httpobject.abort();
	window.clearTimeout(chat_timeout);
	window.clearTimeout(get_chat_timeout);
	window.clearTimeout(keep_up_timeout);
	httpobject.abort();
}
function chat_restart()
{
	document.getElementById("chat_message").addEventListener("keydown", key_operations, false);
	scroll("chat_conversation", 0, true);
	scroll('chat_debug_box', 0, true);
}
document.getElementById("chat_message").addEventListener("keydown", key_operations, false);

function key_operations(e)
{
  if(check_enter(e) == true)
   {
     send_message();
   }
}
function check_enter(e)
{
	if(e.keyCode == 13)
		return true;
	else
		return false;
}
var old_error;
var timeout_try;
function chat_error(noinfo, answer)
{
	if (answer != undefined)
		var php_error_split = answer.split("<br />");
		
	if (php_error_split != undefined && php_error_split[1] != undefined)
		{
			var php_error = "<br>(PHP Fehler:" + php_error_split[1] + ")";
			var error = php_error;
		}
	else if (answer != undefined)
		{
			var php_error = "<br>(Fehler:" + answer + ")";
			var error = php_error;
		}
	else
		{
			var php_error = "";
			var error = " (timeout)";
			if (old_error == error)
				timeout_try++;
			else
				timeout_try = 1;
		}
	
	if (noinfo != true || old_error != error)
		{	
			
			chat_information("Verbindung zum Server verloren" + error + ". " + timeout_try + ". Versuch...", "error", true); //Connection lost%1. %2. Try...
			if (php_error != "")
				add_debug_entries(new Array("<div class='chat_debug_entry'><span class='debug_warn'>" + php_error + "</span></debug>"));
				
			old_error = error;
		}
	httpobject.abort();
	chat_status = "none";
	window.clearTimeout(get_chat_timeout);
	chat_init(true, true);
}
function scroll (id, speed, first)
  {
	var y;
	if (document.getElementById(id) && document.getElementById(id).scrollTop) 
	  y = document.getElementById(id).scrollTop;
	
	if (first == true)
	  y = -10;
	
	if (y < document.getElementById(id).scrollHeight - document.getElementById(id).offsetHeight) 
	  {
	    if (speed == 0)
	      document.getElementById(id).scrollTop = document.getElementById(id).scrollHeight;
	    else
	      {
					document.getElementById(id).scrollTop += 5; 
					window.setTimeout(function() { scroll(id, speed) }, speed);
	      }
	  }
  }
function chat_alert(text, action)
	{
	
		if (action == "close")
      {
				chat_alert_status = 2;
				try
				{
				document.getElementById('chatalertcontent').parentNode.removeChild(document.getElementById('chatalertcontent'));
				document.getElementById('chatalertbg').parentNode.removeChild(document.getElementById('chatalertbg'));
				}catch(e){}
      }
    else
      {
      	chat_alert(undefined, "close");
				var chat_message = "<div id='chatalertbg'></div><div id='chatalertcontent'>";
				if (action != "noclose")
					chat_message += "<span style='float:right;'><img class='link' onclick='chat_alert(undefined, \"close\")' src='" + html_path + "themes/" + chat_design + "/icons/delete.png' alt='close'></span><br>";

				chat_message+= "<div id='chat_alert'>" + text;
				chat_message += "</div><br></div>";
				document.getElementById('chat_main').innerHTML += chat_message;
				chat_alert_status = 1;
      }	
   	chat_restart();
	}
function chat_prompt(text, id, action, button_text)
	{
		var alert_text = text;
		alert_text += "<p><input type='text' onkeydown='if (check_enter(event) == true) { " + action + " chat_alert(undefined, \"close\");}' id='" + id + "'></p><button onclick='" + action + " chat_alert(undefined, \"close\");'>" + button_text + "</button></p>";
		chat_alert(alert_text)
	}


function chat_functions_menu()
	{
		if(document.getElementById("functions_box").style.display == "none")
			document.getElementById("functions_box").style.display = "block";
		else if(document.getElementById("functions_box").style.display == "block")
			document.getElementById("functions_box").style.display = "none";
	}

function chat_insert_command(command, user)
	{
		switch(command)
			{
				case "kick": //send_message("/kick lukas, document.getElementById(\'chat_kick_reason\').value");
					//var kick_reason = "document.getElementById(\"chat_kick_reason\").value";
					//chat_prompt("Grund zum Kicken:", "chat_kick_reason", 'send_message("/kick ' + user + ', '  + kick_reason + '', 'Nutzer kicken');
					chat_prompt("Grund zum Kicken:", "chat_kick_reason", "send_message(\"/kick " + user + ",\" + document.getElementById(\"chat_kick_reason\").value);", "Nutzer kicken");
				break;
				default:
					send_message("/" + command);
				break;
			}
	}

function chat_play_sound()
	{
		new_post_audio.play();
	}
var info_hide_timeout;
function chat_information(info, type, nohide, onlyhide, noclose)
	{
		var info_msg_element = document.getElementById("chat_notice_msg");
		info_msg_element.innerHTML = "<div id='chat_notice_sub_msg'></div>";
		var info_msg_element_sub = document.getElementById("chat_notice_sub_msg");
		window.clearTimeout(info_hide_timeout);
		if (onlyhide)
			{
				info_msg_element.style.visibility = "hidden"; 
				info_msg_element.innerHTML = "";
			}
		else
			{
				info_msg_element.style.visibility = "visible";
				info_msg_element.className = "chat_notice";
				
				if(type == "success")
					info_msg_element_sub.id = "chat_notice_success";
				else if(type == "warn")
					info_msg_element_sub.id = "chat_notice_warn";
				else if(type == "error")
					info_msg_element_sub.id = "chat_notice_error";
				else if(type == "info")
					info_msg_element_sub.id = "chat_notice_info";
				}
				if (noclose != true)
					info = "<span class='close_chat_information'>(<a href='#' onclick='chat_information(undefined, undefined, undefined, true)'>schlie&szlig;en</a>)</span>" + info;
				
				info = "<br>" + info;
				
				if (type =="info")
					info = "<img src='" + html_path + "themes/" + chat_design + "/icons/information.png' alt='I'> Info:"+ info;
				else if (type == "error")
					info = "<img src='" + html_path + "themes/" + chat_design + "/icons/exclamation.png' alt='I'> Fehler:"+ info;
				else if (type == "warn")
					info = "<img src='" + html_path + "themes/" + chat_design + "/icons/error.png' alt='I'> Warnung:"+ info;
				else if (type == "success")
					info = "<img src='" + html_path + "themes/" + chat_design + "/icons/check.png' alt='I'> Erfolgreich!"+ info;
					
				info_msg_element_sub.innerHTML = info;
				if (nohide != true)
					info_hide_timeout = window.setTimeout(function(){ chat_information(undefined, undefined, undefined, true)}, 5000);
	  	}
	


function chat_sound_status(status)
{
  if(chat_sound == false && status == undefined || status == true)
  	{
    	chat_sound = true;
    }
  else
  	{
    	chat_sound = false;
  	}
}
var user_info_status = new Array();
function user_info(box_id)
{
	box_id = addslashes(box_id);
	if (user_info_status[box_id] == undefined || user_info_status[box_id] == "closed")
		{
			document.getElementById(box_id).style.display = "block";
			user_info_status[box_id] = "opened";
		}
	else
		{
			document.getElementById(box_id).style.display = "none";
			user_info_status[box_id] = "closed";
		}
		
}

function ui_dropdown_sign(id, action)
{
	id = addslashes(id);
	
  if(action == "show")
    document.getElementById(id).innerHTML = "<img src='" + html_path + "themes/" + chat_design + "/icons/arrow_down.png'>";
  else if(action == "hide")
    document.getElementById(id).innerHTML = "";
}

function chat_answer(user, msg)
{
	if (msg == undefined)
  	chat_prompt("Nachricht an " + user + ":", "chat_answer_text", "chat_answer(\"" + user + "\", document.getElementById(\"chat_answer_text\").value);", "Einf&uuml;gen");
  else
  	{
			var cmd = "/answer " + user + "," + msg;
			document.getElementById("chat_message").value = cmd;
  	}
}

function restore_user_info()
{
	for (var key in user_info_status)
		{
			if (user_info_status[key] == "closed")
				{
					try
						{
							document.getElementById(key).style.display = "none";
						}
					catch(e)
						{
							user_info_status[key] = "closed";
						}
				}
			else
				{
					try
						{
							document.getElementById(key).style.display = "block";
						}
					catch(e)
						{
							user_info_status[key] = "closed";
						}
				}
		}		
}

function generate_titled_link(option)
{
  if(option == "open_form")
  {
    var content = "<form name='create_link'>" +
    "<p>URL: <input type='text' value='http://' name='url'></p>" +
    "<p>Beschriftung: <input type='text' value='Klick hier' name='title'></p>" +
    "<p><input type='button' onclick='generate_titled_link(\"create_link\"); chat_alert(undefined, \"close\")' value='Link erzeugen'></p>"
    "</form>";
    
    chat_alert(content);
  }else if(option == "create_link"){
    var url = document.create_link.url.value;
    var title = document.create_link.title.value;
    
    var cmd = "/link:" + url + "," + title;
    document.getElementById("chat_message").value = cmd;
  }
}

function add_smiley(code)
 {
     document.getElementById("chat_message").value += code;
     document.getElementById("chat_message").focus();
 }

function send_message(chat_message)
{
	if (chat_message == undefined)
		{
			var chat_message = document.getElementById('chat_message').value;
			document.getElementById('chat_message').value = "";
  	}
	if (chat_status != "none")
		{
			window.setTimeout(function() { send_message(chat_message) }, 100);
			return false;
		}
	chat_message = encodeURIComponent(chat_message);
	
	keep_up_timeout = window.setTimeout(chat_error, 10000);
		
	chat_status = "send_message"
  httpobject.open("POST", html_path + "src/chat.php?task=send_message&id=" + encodeURIComponent(chat_id)+ "&client_num=" + chat_client_num, true)
  httpobject.onreadystatechange = function(){
    if(httpobject.readyState == 4 && httpobject.status == 200)
    {
    	window.clearTimeout(keep_up_timeout);
    	try
    		{
      		var answer = JSON.parse(httpobject.responseText);
      	}
      catch(e)
      	{
      		chat_error(undefined, httpobject.responseText);
      	}
      	
      if (answer != undefined)
      	handle_chat_tasks(answer);
      chat_status = "none";
    }
  }
  httpobject.setRequestHeader("Content-type","application/x-www-form-urlencoded");
 	if (chat_extra_send == undefined)
 		chat_extra_send = "bla=0";
 	var chat_post_text = chat_extra_send + "&text=" + chat_message + "&last_id=" + last_id + "&active_channel=" + chat_active_channel + "&channels=";
  for (var i = 0; i < chat_channels.length; i++)
 	{
 		if (i != 0)
 			chat_post_text += "|||";
 			
 		chat_post_text += chat_channels[i];
 	}
  httpobject.send(chat_post_text);
}


//get new messages and the userlist from the server
function get_chat(first_start, answer, error)
{
	if (chat_status != "none")
		{
			get_chat_timeout = window.setTimeout(function() { get_chat(first_start, answer, error) }, 100);
			return false;
		}
      	
  if (document.getElementById('chat_message').value != "")
  	{
			var chat_is_writing = true;
		}
	else
		var chat_is_writing = false;
		
  keep_up_timeout = window.setTimeout(chat_error, 10000);
  chat_status = "get_chat";
  httpobject.open("POST", html_path + "src/chat.php?task=get_chat&id=" + encodeURIComponent(chat_id) + "&client_num=" + chat_client_num, true);
  httpobject.onreadystatechange = function()
  	{
		  if(httpobject.readyState == 4 && httpobject.status == 200)
		  {
		  	window.clearTimeout(keep_up_timeout);
		  	try
		  	{
		    	var answer = JSON.parse(httpobject.responseText);
				}
				catch(e)
				{
					chat_error(error, httpobject.responseText);
				}
				if (answer != undefined)
				{
					handle_chat_tasks(answer, first_start);
					chat_status = "none";
					if (error == true)
					{
						chat_information(undefined, undefined, undefined, true);
						chat_information("Verbindung wiederhergestellt!", "success");
					}
					chat_timeout = window.setTimeout(get_chat, get_interval);	
				}
		  }
 		}
 	httpobject.setRequestHeader("Content-type","application/x-www-form-urlencoded");
 	if (chat_extra_send == undefined)
 		chat_extra_send = "bla=0";
 	
 	var chat_post_text = chat_extra_send + "&last_id=" + last_id + "&writing=" + chat_is_writing + "&first_start=" + first_start + "&active_channel=" + chat_active_channel + "&channels=";
 	for (var i = 0; i < chat_channels.length; i++)
 	{
 		if (i != 0)
 			chat_post_text += "|||";
 			
 		chat_post_text += chat_channels[i];
 	}
  httpobject.send(chat_post_text);
}

//add new messages, update the userlist with a given json code
function handle_chat_tasks(answer, first_start)
{
	if (first_start == true)
		document.getElementById('chat_conversation_channel_' + chat_active_channel).innerHTML = "";
	if (answer['info_text'] != undefined)
  	chat_information(answer['info_text'], answer['info_type'], answer['info_nohide']);
  	
	last_id = answer['get']['last_id'];
	if (answer['get']['username'] != undefined && chat_username != answer['get']['username'] && first_start != true || first_start == true)
		{
			if (first_start != true)
				chat_information("Du hei&szlig;t jetzt: \"" + answer['get']['username'] + "\"", "info");
				
			try{document.getElementById('chat_username').innerHTML = answer['get']['username'] + ":";}
			catch(e){}
		}
	chat_username = answer['get']['username'];
	if (answer['get']['messages'] != undefined)
	{
		for (var i = 0; i < chat_channels.length; i++)
		{
			if (answer['get']['messages'][chat_channels[i]] != undefined)
				add_entries(chat_channels[i], answer['get']['messages'][chat_channels[i]], first_start, answer['get']['messages_user'][chat_channels[i]], answer['get']['highlight']);
		}
  }
  if (answer['get']['userlist'] != undefined)
  {
		for (var i = 0; i < chat_channels.length; i++)
		{
			if (answer['get']['userlist'][chat_channels[i]] != undefined)
  			handle_userlist(answer['get']['userlist'][chat_channels[i]],chat_channels[i], first_start);
  	}
  }
  add_debug_entries(answer['debug']);
  
  if (answer['get']['actions'] != undefined)
  	handle_server_actions(answer['get']['actions']);
  
  if (answer['execute_custom_js'] == true)
		chat_custom_js(answer);
		
  old_error = "none";
  
  if (answer['get']['afk'] != undefined)
  	chat_afk = answer['get']['afk'];
  
	try{document.getElementById('chat_user_num').innerHTML = answer['get']['userlist']['users'].length;}
	catch(e){}
	
	try{chat_layout_tasks()}catch(e){};
}

//update the userlist with a given array userlist_arr
function handle_userlist(userlist_arr, channel, first_start)
{
	if (first_start == true)
		document.getElementById('chat_userlist_channel_' + channel).innerHTML = "";
		
	chat_userlist = document.getElementById("chat_userlist_channel_" + channel);
	if (userlist_arr['add_user'] != undefined)
		{
			for (var i = 0; i < userlist_arr['add_user'].length; i++)
				{
					chat_userlist.innerHTML+= "<span id='chat_" + channel + "_user_" + userlist_arr['add_user_id'][i] + "'>" + userlist_arr['add_user'][i] + "</span>";
				}
		}
	if (userlist_arr['change_user'] != undefined)
		{
			for (var i = 0; i < userlist_arr['change_user'].length; i++)
				{
					document.getElementById("chat_" + channel + "_user_" + userlist_arr['change_user_id'][i]).innerHTML = userlist_arr['change_user'][i];
				}
			restore_user_info();
		}
	if (userlist_arr['delete_user'] != undefined)
		{
			for (var i = 0; i < userlist_arr['delete_user'].length; i++)
				{
					try
						{
							var tag = document.getElementById("chat_" + channel + "_user_" + userlist_arr['delete_user'][i]);
							tag.parentNode.removeChild(tag);
						}
					catch(e){}
				}
			restore_user_info();
		}
	if (userlist_arr['user_writing'] != undefined)
	{
		for (var i = 0; i < userlist_arr['user_writing'].length; i++)
		{
			if (userlist_arr['user_writing'][i] == "0")
				document.getElementById("chat_" + channel + "_user_" + i).getElementsByClassName("chat_speech_bubble")[0].style.width = "0px";
			else
				document.getElementById("chat_" + channel + "_user_" + i).getElementsByClassName("chat_speech_bubble")[0].style.width = chat_speech_bubble_width;
		}
	}
}

function add_entries(channel, entries, first_start, users, highlight)
{
	if (entries != undefined && entries.length > 0)
		{
			var chat_window = document.getElementById("chat_conversation_channel_" + channel);
			for (var i = 0; i < entries.length; i++)
				{
					chat_window.innerHTML += entries[i];
				}
			if (first_start)
				scroll("chat_conversation", 0, true);
			else
				scroll("chat_conversation", 20, true);
				
			if (first_start != true )
				{
					if (users == undefined || users[0] != chat_username)
						{
							new_messages++;
							new_messages_status(false);
							if (chat_sound == true)
								chat_play_sound();
						}
				}
		}
}
function handle_server_actions(actions)
{
	for (var i = 0; i < actions.length; i++)
	{
		var action_parts = actions[i].split("|");
		
		if (action_parts[0] == "message" || action_parts[0] == "kick")
			chat_alert(action_parts[1]);
		if (action_parts[0] == "join")
		{
			chat_add_channel(action_parts[1]);
			chat_change_channel(action_parts[1]);
		}
		if (action_parts[0] == "kick")
		{
			chat_is_kicked = true;
			window.setTimeout(function() { chat_stop(); }, 100);
			chat_information(action_parts[1], "error", true, false, true);
		}
		else
			add_debug_entries(new Array("<div class='chat_debug_entry'><span class='debug_warn'>Unknown Action!</span></debug>"));
	}
}
function add_debug_entries(debug_entries)
{
	if (debug_entries != undefined && debug_entries.length > 0)
		{
			var debug_box = document.getElementById("chat_debug_box");
			for (var i = 0; i < debug_entries.length; i++)
				{
					debug_box.innerHTML += debug_entries[i];
				}
				scroll("chat_debug_box", 20, true);
		}
}
function new_messages_status(delete_nm)
{
  if(!delete_nm)
  {
    if(new_messages >= 1)
    {
      document.title = " (" + new_messages + ") " + old_title;
   }
  }else
  {
    new_messages = 0;
    document.title = old_title;
  }
}


function chat_add_channel(channel, noadd)
{
	if (noadd == true && channel == undefined)
	{
		for (var i = 0; i < chat_channels.length; i++)
		{
			chat_add_channel(chat_channels[i], true);
		}
	}
	else if (channel != undefined)
	{
		document.getElementById("chat_channels_ul").innerHTML += "<li id='chat_channel_" + channel + "'><a href='javascript:void(0);' onclick='chat_change_channel(this.innerHTML)'>" + channel + "</a></li>";
		document.getElementById("chat_conversation").innerHTML += "<div style='width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative;' id='chat_conversation_channel_" + channel + "'></div>";
		document.getElementById("chat_userlist").innerHTML += "<div style='width: 100%; height: 100%; top: 0px; left: 0px; padding: 0px; margin: 0px; position: relative;' id='chat_userlist_channel_" + channel + "'></div>";
		
		if (noadd == true && chat_channels[0] == channel)
			document.getElementById("chat_conversation_channel_" + channel).innerHTML = "Blaba Chat wird geladen... und so";
		if (noadd != true)
			chat_channels[chat_channels.length] = channel;
	}
	else
		alert("no channel name!");
}
function chat_change_channel(channel)
{
	for (var i = 0; i < chat_channels.length; i++)
	{
		if (chat_channels[i] == channel)
		{
			document.getElementById("chat_channel_" + chat_channels[i]).className = "chat_channel_selected";
			document.getElementById("chat_conversation_channel_" + chat_channels[i]).style.display = "block";
			document.getElementById("chat_userlist_channel_" + chat_channels[i]).style.display = "block";
			chat_active_channel = chat_channels[i];
		}
		else
		{
			document.getElementById("chat_channel_" + chat_channels[i]).className = "";
			document.getElementById("chat_conversation_channel_" + chat_channels[i]).style.display = "none";
			document.getElementById("chat_userlist_channel_" + chat_channels[i]).style.display = "none";
		}
	}
	scroll("chat_conversation", 0, true);
}