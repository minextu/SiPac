<?php // !!SMILEYS!! -> Smileys, <||t20||> -> Loading the Chat. Please wait..., <||t12||> -> send
//$chat_deactivate_afk = true;
$chat_layout = "
<div id='chat_main'>
	<div id='chat_channels'>
	<ul id='chat_channels_ul'></ul>
	</div>
	
	<div id='chat_conversation'><||t20||></div><!-- end: chat_conversations -->
	<div id='chat_userlist'></div><!-- end: chat_userlist -->
	<div id='chat_user_area'>
		<div id='left'>
		<div id='top'>
			<button id='functions_button' onclick='chat_functions_menu();'>Chat Funktionen</button><!-- end: functions_button -->
			<div id='functions_box' style='display: none;' onclick='chat_functions_menu()'>
				<ul>
					<li><a href='javascript:void(null)' id='chat_afk_button' onclick='chat_user_status(); chat_layout_check_afk_text(this)'>Loading...</a></li>
					<li><a href='javascript:void(null)' id='chat_sound_button' onclick='chat_sound_status(); chat_layout_check_sound_text(this)'>Chat-Sound deaktivieren</a></li>
					<li><a href='#'>Eigenen Nutzernamen ändern</a></li>
				</ul>
			</div><!-- end: functions_box -->
		</div><!-- end: top -->
		<div id='bottom'>
		<div id='chat_user_input'>
			<div id='chat_user_message_area'>
				<span id='chat_username'></span>
				<input id='chat_message' x-webkit-speech='x-webkit-speech' onwebkitspeechchange='send_message()'>
				<button id='chat_send_button' onclick='send_message()'><||t12||></button>
			</div><!-- end: chat_user_message_area -->
			<div id='chat_notice_msg'></div><!-- end: chat_information_msg -->
			<div id='chat_smiley_bar'>!!SMILEYS!!</div><!-- end: chat_smiley_bar -->
			
		</div><!-- end: chat_user_input -->
		</div><!-- end: bottom -->
		</div><!-- end: left -->
		<div id='right'>
		<div id='chat_debug_box'></div>
		</div><!-- end: right -->
	</div><!-- end: chat_user_area -->
</div><!-- end: chat_main -->

<script type='text/javascript' class='chatengine_script'>
function chat_layout_init()
{
	chat_layout_check_afk_text(document.getElementById('chat_afk_button'));
	chat_layout_check_sound_text(document.getElementById('chat_sound_button'));
}

function chat_layout_check_afk_text(e)
{
	if (chat_afk == true)
		e.innerHTML=\"<||t29||>\"; 
	else 
		e.innerHTML=\"<||t30||>\";

}

function chat_layout_check_sound_text(e)
{
	if (chat_sound == 1)
		e.innerHTML=\"<||t31||>\"; 
	else 
		e.innerHTML=\"<||t32||>\";
}
</script>
";
?>