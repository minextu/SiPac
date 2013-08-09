<?php

/*


SiPac minimal config


*/



$chat_settings['chat_id'] = "example_id"; //replace exmaple_id with a custom id for the chat

//mysql config (fill out everything right here)
$chat_settings['mysql_hostname'] = "localhost";
$chat_settings['mysql_username'] = "exmaple_user";
$chat_settings['mysql_password'] = "exmaple_password";
$chat_settings['mysql_database'] = "exmaple_database";


require_once dirname(__FILE__)."/src/server/SiPac.php";
$chat = new Chat($chat_settings);



?>

<html>
<head>
<title>SiPac (minimal config)</title>
</head>
<body>

<?php 

//put the next line somewhere, where the chat should be shown
echo $chat->draw();


?>

</body>
</html>