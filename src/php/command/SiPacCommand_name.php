<?php

class SiPacCommand_name implements SiPacCommand
{
  public $usage = "/name {new name}";
  public function set_variables($chat, $parameters)
  {
    $this->chat= $chat;
    $this->parameters = $parameters;
  }
  public function check_permission()
  {
    if ($this->chat->settings['can_rename'] == true)
      return true;
    else
      return false;
  }
  
  public function execute()
  {
    $this->chat->db->add_task("new_name|".$this->parameters);
    return array("info_type"=>"warn", "info_text"=>"Not working, yet");
  }
}

?>