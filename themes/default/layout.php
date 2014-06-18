<?php // !!SMILEYS!! -> Smileys
$default_smiley_height = 30;

/*
!!USER!! -> the User name, 
!!USER_ID!! -> a unique id for the user, 
!!USER_AFK!! -> will replaced with 'afk' or 'online',
!!USER_STATUS!! -> will be replace with the user Status (online, afk, admin)
!!USER_INFO!! -> will be replace with user info (IP, Kick/Ban user)
!!NUM!! -> gives the chat num (chat_objects[!!NUM!!])
*/

$chat_layout_user_entry = "
<div class='chat_user' id='!!USER_ID!!' onmouseover='chat_objects[!!NUM!!].user_options(\"!!USER_ID!!\", \"show\");' onmouseout='chat_objects[!!NUM!!].user_options(\"!!USER_ID!!\", \"hide\");'>
	<div class='chat_user_name' style='color: !!USER_COLOR!!;'>!!USER!!<span class='chat_user_status'>[!!USER_STATUS!!]</span></div>

	<div class='chat_user_bottom' style='display: none;'>
		<div class='chat_user_info'>!!USER_INFO!!</div>
	</div>
</div>
";
$chat_layout_user_info_entry = "
<div><span>!!INFO_HEAD!!:</span><span style='float: right'>!!INFO!!</span></div>
";
$chat_layout_post_entry = "
<div class='chat_entry_!!TYPE!!'>
  <span class='chat_entry_user' style='color: !!USER_COLOR!!;'>!!USER!!</span>:
  <span class='chat_entry_message'>!!MESSAGE!!</span>
  <span class='chat_entry_date'>!!TIME!!</span>
</div>
";
$chat_layout_notify_user = "
<span class='chat_entry_user'>!!USER!!</span>
";
$chat_layout_notify_entry = "
<div class='chat_entry_notify'>
  <span class='chat_entry_message'>!!MESSAGE!!</span>
  <span class='chat_entry_date'>!!TIME!!</span>
</div>
";
$chat_layout_channel_tab = "
<li id='!!ID!!'>
	<span class='chat_channel_span'>
		<a class='chat_channel' href='javascript:void(0);' onclick='!!CHANNEL_CHANGE_FUNCTION!!'>!!CHANNEL!!</a><a href='javascript:void(0);' onclick='!!CHANNEL_CLOSE_FUNCTION!!' class='chat_channel_close'>X</a>
	</span>
</li>
";
$chat_layout = "
<div class='chat_main'>
	<nav class='chat_channels_nav'>
		<span class='chat_header'>SiPac</span>
		<ul class='chat_channels_ul'>
		</ul>
		<span class='chat_add_channel'><a href='javascript:void(0);' onclick='var channel_name = prompt(\"Please enter a channel name\"); if (channel_name != null) { chat_objects[!!NUM!!].insert_command(\"join \" + channel_name, true); }'>+</a></span>
	</nav>
	<div class='chat_container'>
		<div class='chat_left'>
			<div class='chat_conversation'></div>
			<div class='chat_user_input'>
				<div class='chat_notice_msg'></div>
				<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
				<button class='chat_send_button'><||send-button-text||></button>
			</div>
		</div>
		<div class='chat_vr'></div>
		<div class='chat_right'>
			<div class='chat_element'>
				<div class='chat_element_head'><||userlist-head|!!USER_NUM!!||></div>
				<div class='chat_userlist'></div>
			</div>
			<div class='chat_element'>
				<div class='chat_element_head'><||settings-head||></div>
				<input type ='checkbox' checked='checked' onclick='if (chat_objects[!!NUM!!].enable_sound == true) { chat_objects[!!NUM!!].enable_sound = false; } else { chat_objects[!!NUM!!].enable_sound = true; } '>Enable Sound
				<br><input type ='checkbox' onclick='if (chat_objects[!!NUM!!].enable_notifications == true) { chat_objects[!!NUM!!].enable_notifications= false; } else { chat_objects[!!NUM!!].enable_notifications = true; chat_objects[!!NUM!!].show_notification(\"Success\", \"Notifications are now enabled\");} '>Enable Desktop Notifications (experimental)
			</div>
			<div class='chat_element' style='text-align: center;'>
				<div class='chat_element_head'><||smileys-head||></div>
				<span>!!SMILEYS!!</span>
			</div>
		</div>
	</div>
</div><!-- end: chat_main-class -->
";
$chat_layout_functions['layout_init'] = '
function layout_init()
{
  this.old_user_status = new Array();
}
';
$chat_layout_functions['user_options'] = "
function user_options(user_id, action)
	{
		if(action == 'show')
			{
				document.getElementById(user_id).getElementsByClassName('chat_user_bottom')[0].style.display = 'block';
			}
		else if(action == 'hide')
			{
				document.getElementById(user_id).getElementsByClassName('chat_user_bottom')[0].style.display = 'none';
			}
	}
";
$chat_layout_functions['layout_user_writing_status'] = '
function layout_user_writing_status (status, username, user_id)
{
  if (document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML != "[" + this.texts["writing-status"] + "]")
    this.old_user_status[username] = document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML;
  
  if (status == 1)
    document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML = "[" + this.texts["writing-status"] + "]";
  else if (this.old_user_status[username] != undefined)
  {
    document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML =  this.old_user_status[username];
  }
}
';
?>
