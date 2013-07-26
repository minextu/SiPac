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

$chat_layout_user_entry = "!!USER!! <button onclick='chat_objects[!!NUM!!].test()'>click me!</button>";
//$chat_layout_user_entry = '<div onmouseover="chat_objects[!!NUM!!].ui_dropdown_sign(\'!!USER_ID!!_dropdown_info\', \'show\');" onmouseout="chat_objects[!!NUM!!].ui_dropdown_sign(\'!!USER_ID!!_dropdown_info\', \'hide\');" onclick="chat_objects[!!NUM!!].user_info(\'!!USER_ID!!_user_info\');" class="!!USER_AFK!!_user"><span class="chat_speech_bubble" style="width: 0px;"><img src="/SiPac/themes/default/speech_bubble.gif" alt="writing" title="writing"></span>!!USER!!&nbsp;<span class="user_online_status">[!!USER_STATUS!!]</span><span id="!!USER_ID!!_dropdown_info"></span></div><div id="!!USER_ID!!_user_info" class="user_info_box">!!USER_INFO!!</div>';
$chat_layout = "
<div class='chat_main'>
  <div class='chat_left'>
    <div class='chat_userlist'></div><!-- end: chat_userlist-class -->
  </div><!-- end: chat_left-class -->
  <div class='chat_right'>
    <div class='chat_conversation'></div><!-- end: chat_conversation-class -->
    <div class='chat_user_area'>
      <div class='chat_extra_bar'></div><!-- end: chat_extra_bar-class -->
      <div class='chat_user_input'>
	<input type='text' class='chat_message' placeholder='<||t34||>'>
	<button class='chat_send_button'><||t12||></button><!-- end: chat_send_button-class -->
      </div><!-- end: chat_user_input-class -->
    </div><!-- end: chat_user_area-class -->
  </div><!-- end: chat_right-class -->
</div><!-- end: chat_main-class -->
";

$chat_layout_functions['test'] = "
function test()
{
  alert('test');
}";
$chat_layout_functions['test2'] ="
function test2(test)
{
  alert('testus');
}
"
?>
