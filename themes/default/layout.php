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

$chat_layout = "
<div class='chat_main'>
  <div class='chat_left'>
    <div class='chat_userlist'></div><!-- end: chat_userlist-class -->
  </div><!-- end: chat_left-class -->
  <div class='chat_right'>
    <div class='chat_conversation'></div><!-- end: chat_conversation-class -->
    <div class='chat_user_area'>
      <div class='chat_extra_bar'>
      
      <div class='chat_notice_msg'></div>
      </div><!-- end: chat_extra_bar-class -->
      <div class='chat_user_input'>
	<input type='text' class='chat_message' placeholder='<||t34||>'>
	<button class='chat_send_button'><||t12||></button><!-- end: chat_send_button-class -->
      </div><!-- end: chat_user_input-class -->
    </div><!-- end: chat_user_area-class -->
  </div><!-- end: chat_right-class -->
</div><!-- end: chat_main-class -->
";

$chat_layout_functions['user_options'] = "
function user_options(user_id, action)
	{
		console.debug(user_id);
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
?>
