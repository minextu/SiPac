<?php

class SiPacCommand_help implements SiPacCommand
{
  public $usage = "/help [<command>]";
  
  public function set_variables($chat, $parameters)
  {
    $this->chat = $chat;
    $this->parameters = $parameters;
  }
  public function check_permission()
  {
    return true;
  }
  public function execute()
  {
	if ($handle = opendir(dirname(__FILE__)))
	{
		while (false !== ($file = readdir($handle)))
		{
			if ($file != "." && $file != "..") 
			{
				include_once($file);
				$class_name = str_replace(".php", "", $file);
				
				if (class_exists($class_name) AND empty($this->parameters) OR class_exists($class_name) AND str_replace("SiPacCommand_", "", $class_name)  == $this->parameters)
				{
					$check_comment = new $class_name;
					$check_comment->set_variables($this->chat, false);
					if ($check_comment->check_permission() === true)
					{
						if (!empty($command_syntax))
							$command_syntax = $command_syntax."<br>";
						else
							$command_syntax = "";
			
						$command_syntax = $command_syntax.htmlentities($check_comment->usage);
					}
				}
			}
		}
		closedir($handle);
    }

     if (empty($command_syntax))
      $command_syntax = "<||command-not-found-text|".htmlentities($this->parameters)."||>";
    else if (empty($this->parameters))
      $command_syntax = "<||command-list-head||><br>".$command_syntax;
    else
		$command_syntax = "<||command-syntax-head||><br>".$command_syntax;
	
    return array("info_type"=>"info", "info_text"=>$command_syntax, "info_nohide"=>true);
  }
}

?>