<?php // !!SMILEYS!! -> Smileys, <||t20||> -> Loading the Chat. Please wait..., <||t12||> -> send !!ID!! -> chat id
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
	<div class='chat_user_top'>
		<div class='chat_user_name'>!!USER!!<span class='chat_user_status'>[!!USER_STATUS!!]</span></div>
	</div><!-- end: chat_user_top-class -->
	<div class='chat_user_bottom' style='display: none;'>
		<ul>
		!!USER_INFO!!
		</ul>
	</div><!-- end: chat_user_bottom-class -->
</div><!-- end: chat_user-class -->
";
$chat_layout_post_entry = "
<div class='chat_entry_!!TYPE!!'>
  <div class='chat_entry_content'>
  <div class='chat_entry_info'>
  <span class='chat_entry_user'>!!USER!!</span><span class='chat_entry_date'>!!TIME!!</span>
  </div>
  <span class='chat_entry_message'>!!MESSAGE!!</span>
  </div>
</div>
";
$chat_layout = "
<meta name='viewport' content='width=device-width, height=device-height, user-scalable=no'>
<div class='chat_main'>
    <div class='chat_top'><ul><li class='chat_userlist_closed' onclick='chat_objects[!!NUM!!].layout_show_userlist(this)'>Userliste (X Online)</li><li class='chat_top_n'>Channel</li></ul></div>
    <div class='chat_userlist'></div>
    <div class='chat_conversation'></div>
    <div class='chat_user_area'>
		<div class='chat_notice_msg'></div>
      <div class='chat_user_input'>
	<input type='text' class='chat_message' placeholder='<||t34||>'>
	<button class='chat_send_button'><||t12||></button><!-- end: chat_send_button-class -->
      </div><!-- end: chat_user_input-class -->
    </div><!-- end: chat_user_area-class -->
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
  if (document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML != "[" + this.texts[55] + "]")
    this.old_user_status[username] = document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML;
  
  if (status == 1)
    document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML = "[" + this.texts[55] + "]";
  else if (this.old_user_status[username] != undefined)
  {
    document.getElementById(user_id).getElementsByClassName("chat_user_status")[0].innerHTML =  this.old_user_status[username];
  }
}
';

$chat_layout_functions['layout_show_userlist'] = '
function layout_show_userlist(userlist_button)
	{
		if(userlist_button.className == "chat_userlist_closed")
			{
				userlist_button.className = "chat_userlist_opened";
				this.chat.getElementsByClassName("chat_userlist")[0].style.width = "50%";
				this.chat.getElementsByClassName("chat_userlist")[0].style.display = "block";
			}
		else if(userlist_button.className == "chat_userlist_opened")
			{
				userlist_button.className = "chat_userlist_closed";
				this.chat.getElementsByClassName("chat_userlist")[0].style.width = "0%";
				this.chat.getElementsByClassName("chat_userlist")[0].style.display = "none";
			}
	}
';
?>
