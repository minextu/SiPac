<?php // !!SMILEYS!! -> Smileys, <||t20||> -> Loading the Chat. Please wait..., <||t12||> -> send
$default_smiley_height = 30;
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
			<button class='functions_button' onclick='chat_functions_menu();'>Functions</button><!-- end: functions_button -->
			<div class='functions_box' style='display: none;' onclick='chat_functions_menu()'>
				<ul>
					<li><a href='javascript:void(null)' id='chat_afk_button' onclick='chat_insert_command(\"afk\");'>Loading...</a></li>
					<li><a href='javascript:void(null)' id='chat_sound_button' onclick='chat_sound_status(); chat_layout_check_sound_text(this)'>Chat-Sound deaktivieren</a></li>
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

<script type='text/javascript' class='chatengine_script'>
function chat_functions_menu()
	{
		if(document.getElementById('functions_box').style.display == 'none')
			document.getElementById('functions_box').style.display = 'block';
		else if(document.getElementById('functions_box').style.display == 'block')
			document.getElementById('functions_box').style.display = 'none';
	}
	
function chat_layout_init()
{
	chat_layout_check_sound_text(document.getElementById('chat_sound_button'));
}
function chat_layout_tasks()
{
	chat_layout_check_afk_text(document.getElementById('chat_afk_button'));
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