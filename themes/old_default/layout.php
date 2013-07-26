<?php // !!SMILEYS!! -> Smileys, <||t20||> -> Loading the Chat. Please wait..., <||t12||> -> send !!ID!! -> chat id
$default_smiley_height = 30;
$chat_layout_user_entry = '<div onmouseover="chat_objects[!!NUM!!].ui_dropdown_sign(\'!!USER_ID!!_dropdown_info\', \'show\');" onmouseout="chat_objects[!!NUM!!].ui_dropdown_sign(\'!!USER_ID!!_dropdown_info\', \'hide\');" onclick="chat_objects[!!NUM!!].user_info(\'!!USER_ID!!_user_info\');" class="!!USER_AFK!!_user"><span class="chat_speech_bubble" style="width: 0px;"><img src="/SiPac/themes/default/speech_bubble.gif" alt="writing" title="writing"></span>!!USER!!&nbsp;<span class="user_online_status">[!!USER_STATUS!!]</span><span id="!!USER_ID!!_dropdown_info"></span></div><div id="!!USER_ID!!_user_info" class="user_info_box">!!USER_INFO!!</div>';
$chat_layout = "
<div class='chat_main'>
	<div class='chat_channels'>
		<ul class='chat_channels_ul'></ul>
	</div>
	<div class='chat_conversation'></div><!-- end: chat_conversations -->
	<div class='chat_userlist'></div><!-- end: chat_userlist -->
	<div class='chat_user_area'>
		<div class='chat_left'>
		<div class='chat_top'>
			<button class='functions_button' onclick='chat_objects[!!NUM!!].layout_functions_menu()'>Functions</button><!-- end: functions_button -->
			<div class='functions_box' style='display: none;' onclick='chat_objects[!!NUM!!].layout_functions_menu()'>
				<ul>
					<li><a href='javascript:void(null)' class='chat_afk_button' onclick='chat_objects[!!NUM!!].insert_command(\"afk\", true);'>Loading...</a></li>
					<li><a href='javascript:void(null)' class='chat_sound_button' onclick='chat_objects[!!NUM!!].sound_status(); chat_objects[!!NUM!!].layout_check_sound_text(this)'>Loading...</a></li>
				</ul>
			</div><!-- end: functions_box -->
		</div><!-- end: chat_top -->
		<div class='chat_bottom'>
		<div class='chat_user_input'>
			<div class='chat_user_message_area'>
				<span class='chat_username'></span>
				<input class='chat_message'>
				<button class='chat_send_button'><||t12||></button>
			</div><!-- end: chat_user_message_area -->
			<div class='chat_notice_msg'></div><!-- end: chat_information_msg -->
			<div class='chat_smiley_bar'>!!SMILEYS!!</div><!-- end: chat_smiley_bar -->
			
		</div><!-- end: chat_user_input -->
		</div><!-- end: chat_bottom -->
		</div><!-- end: chat_left -->
		<div class='chat_right'>
		<div class='chat_debug_box'></div>
		</div><!-- end: chat_right -->
	</div><!-- end: chat_user_area -->
</div><!-- end: chat_main -->
";
$chat_layout_functions['layout_check_sound_text'] = "
function layout_check_sound_text(e)
{
  if (this.enable_sound == 1)
    e.innerHTML=\"<||t31||>\"; 
  else 
    e.innerHTML=\"<||t32||>\";
}
";
$chat_layout_functions['layout_init'] = "
function layout_init()
{
  this.layout_check_sound_text(this.chat.getElementsByClassName('chat_sound_button')[0]);
}
";
$chat_layout_functions['layout_tasks'] = "
function chat_layout_tasks()
{
  var afk_button = this.chat.getElementsByClassName('chat_afk_button')[0];
  if (this.chat_afk == true)
   afk_button.innerHTML=\"<||t29||>\"; 
  else 
    afk_button.innerHTML=\"<||t30||>\";
}
";
$chat_layout_functions['layout_functions_menu'] = "
function layout_functions_menu()
{
  var function_box = this.chat.getElementsByClassName('functions_box')[0];
	  
  if(function_box.style.display == 'none')
    function_box.style.display = 'block';
  else if(function_box.style.display == 'block')
    function_box.style.display = 'none';
}
";
?>
