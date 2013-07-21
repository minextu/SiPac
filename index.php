<?php

/*


SiPac minimal config


*/

require_once dirname(__FILE__)."/src/chat.php";
//db config
$chat_settings['host'] = "localhost";
$chat_settings['user'] = "example_user";
$chat_settings['pw'] = "example_password";
$chat_settings['db'] = "example_database";
?>

<html>
<head>
<title>SiPac (minimal config)</title>
</head>
<body>

<?php 
echo draw_chat("example_id"); // Replace example_id with a custom id for the chat
?>

</body>
</html>