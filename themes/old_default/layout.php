<?php // !!SMILEYS!! -> Smileys, <||t20||> -> Loading the Chat. Please wait..., <||t12||> -> send !!ID!! -> chat id
$default_smiley_height = 30;
$chat_layout_user_entry = '<div onmouseover="chat_objects[!!NUM!!].ui_dropdown_sign(\'!!USER_ID!!_dropdown_info\', \'show\');" onmouseout="chat_objects[!!NUM!!].ui_dropdown_sign(\'!!USER_ID!!_dropdown_info\', \'hide\');" onclick="chat_objects[!!NUM!!].user_info(\'!!USER_ID!!_user_info\');" class="!!USER_AFK!!_user"><span class="chat_speech_bubble" style="width: 0px;"><img src="/SiPac/themes/default/speech_bubble.gif" alt="writing" title="writing"></span>!!USER!!&nbsp;<span class="user_online_status">[!!USER_STATUS!!]</span><span id="!!USER_ID!!_dropdown_info"></span></div><div id="!!USER_ID!!_user_info" class="user_info_box">!!USER_INFO!!</div>';
$chat_layout = "
<div class='chat_main'>
		<span class='chat_speech_bubble' style='display: none;'></span>
	<div class='chat_channels'>
		<ul class='chat_channels_ul'></ul>
	</div>
	<div class='chat_conversation'></div><!-- end: chat_conversations -->
	<div class='chat_userlist'></div><!-- end: chat_userlist -->
	<div class='chat_user_area'>
		<div class='chat_left'>
		<div class='chat_top'>
			<button class='functions_button' onclick='chat_objects[!!NUM!!].layout_functions_menu()'><||t54||></button><!-- end: functions_button -->
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
  this.user_info_status = new Array();
  
  var element = this.chat.getElementsByClassName('chat_speech_bubble')[0];
  var style = window.getComputedStyle(element);
  this.speech_bubble_width = style.getPropertyValue('width');
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
    
  this.restore_user_info();
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


$chat_layout_functions['user_info'] = '
function (box_id)
{
  box_id = addslashes(box_id);
  if (this.user_info_status[box_id] == undefined || this.user_info_status[box_id] == "closed")
  {
    document.getElementById(box_id).style.display = "block";
    this.user_info_status[box_id] = "opened";
  }
  else
  {
    document.getElementById(box_id).style.display = "none";
    this.user_info_status[box_id] = "closed";
  }

}
';
$chat_layout_functions['restore_user_info'] = '
function ()
{
  for (var key in this.user_info_status)
  {
    if (this.user_info_status[key] == "closed")
    {
      try
      {
        document.getElementById(key).style.display = "none";
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
        document.getElementById(key).style.display = "block";
      }
      catch (e)
      {
        this.user_info_status[key] = "closed";
      }
    }
  }
}
';
$chat_layout_functions['ui_dropdown_sign'] = '
function (id, action)
{
  id = addslashes(id);

  if (action == "show")
    document.getElementById(id).innerHTML = "<img src=\'" + chat_html_path + "themes/" + this.theme + "/icons/arrow_down.png\'>";
  else if (action == "hide")
    document.getElementById(id).innerHTML = "";
};';
$chat_layout_functions['layout_user_writing_status'] = '
function layout_user_writing_status (status, username, user_id)
{
  if (status == 0)
    document.getElementById(user_id).getElementsByClassName("chat_speech_bubble")[0].style.width = "0px";
  else
    document.getElementById(user_id).getElementsByClassName("chat_speech_bubble")[0].style.width = this.speech_bubble_width;
}
';
?>
