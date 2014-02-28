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
  <span class='chat_entry_user'>!!USER!!</span>
  <span class='chat_entry_message'>!!MESSAGE!!</span>
  <span class='chat_entry_date'>!!TIME!!</span>
</div>
";
$chat_layout_notify_entry = "
<div class='chat_entry_notify'>
  <span class='chat_entry_user'>!!USER!!</span>
  <span class='chat_entry_message'>!!MESSAGE!!</span>
  <span class='chat_entry_date'>!!TIME!!</span>
</div>
";
$chat_layout = "
<div class='chat_main'>
  <div class='chat_left'>
    <div class='chat_userlist'></div><!-- end: chat_userlist-class -->
  </div><!-- end: chat_left-class -->
  <div class='chat_right'>
    <div class='chat_conversation'></div><!-- end: chat_conversation-class -->
    <div class='chat_user_area'>
		<div class='chat_notice_msg'></div>
      <div class='chat_extra_bar'>
			<button onclick='chat_objects[!!NUM!!].smiley_bar(this);' class='chat_smiley_bar_button_closed'>s</button>
			<div class='chat_smiley_bar'>!!SMILEYS!!</div>
      </div><!-- end: chat_extra_bar-class -->
      <div class='chat_user_input'>
	<input type='text' class='chat_message' placeholder='<||message-input-placeholder||>'>
	<button class='chat_send_button'><||send-button-text||></button><!-- end: chat_send_button-class -->
      </div><!-- end: chat_user_input-class -->
    </div><!-- end: chat_user_area-class -->
  </div><!-- end: chat_right-class -->
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

$chat_layout_functions['smiley_bar'] = '
function smiley_bar(smiley_button)
	{
		if(smiley_button.className == "chat_smiley_bar_button_closed")
			{
				smiley_button.className = "chat_smiley_bar_button_opened";
				document.getElementsByClassName("chat_smiley_bar")[0].style.width = "50%";
				document.getElementsByClassName("chat_smiley_bar")[0].style.display = "block";
			}
		else if(smiley_button.className == "chat_smiley_bar_button_opened")
			{
				smiley_button.className = "chat_smiley_bar_button_closed";
				document.getElementsByClassName("chat_smiley_bar")[0].style.width = "0%";
				document.getElementsByClassName("chat_smiley_bar")[0].style.display = "none";
			}
	}
';
?>
