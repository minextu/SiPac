<?php

/*


SiPac minimal config


*/



$chat_settings['chat_id'] = "example_id"; //replace exmaple_id with a custom id for the chat

//mysql config (fill out everything here)
$chat_settings['mysql_hostname'] = "localhost";
$chat_settings['mysql_username'] = "example_user";
$chat_settings['mysql_password'] = "example_password";
$chat_settings['mysql_database'] = "example_database";


require_once dirname(__FILE__)."/src/php/SiPac.php"; //correct the path, if the src folder is not next to this file
$chat = new SiPac_Chat($chat_settings);



?>

<!DOCTYPE html>
<html style='width: 100%; height: 100%;'>
<head>
<title>SiPac (minimal config)</title>
</head>
<body style='width: 100%; height: 100%; margin: 0px;'>

<?php 

//put the next line somewhere, where the chat should be shown
echo $chat->draw();


?>

</body>
</html>