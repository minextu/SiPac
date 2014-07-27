<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013 Jan Houben

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
$chat_text = array(

/* Userlist*/
"online-status-text" => "online",
"afk-status-text" => "afk",
"writing-status" => "Writing...",
"kick-head" => "Kick",
"kick-user"=>"Kick %1",
"private-message-head" => "Message",
"send-private-message-text" => "Send a private message",
"private-message-prompt-text" => "Private message",

/* Notifications */
"rename-notification" => "[user]%1[/user] is now [user]%2[/user]",
"user-left-notification" =>"[user]%1[/user] has left",
"user-join-notification" => "[user]%1[/user] has joined",
"user-kicked-user-notification" => "[user]%1[/user] was kicked by [user]%2[/user] (%3)",
"user-kicked-notification" => "[user]%1[/user] was kicked (%2)",
"user-now-afk-no-reason-notification" => "[user]%1[/user] is now away",
"user-now-afk-notification" => "[user]%1[/user] is now away (%2)",
"user-now-not-afk-notification" => "[user]%1[/user] is back again",

/* Commands */
"command-list-head" => "You can use the following Commands:",
"command-syntax-head" => "Command Syntax:",
"command-not-found-text" => "Command %1 not found! (Type /help for a command list)",
"no-permissions-text" => "No permissions!",
"no-permissions-rename-other-user" => "No permissions to rename an other user!",
"user-not-found-text" => "User %1 not found",
"newname-not-entered-text" => "You didn't enter a new name!",
"no-reason-for-not-afk-text" => "You can't give a reason for not being afk!",
"no-channel-entered" =>"You must enter a channel!",
"arguments-missing-text" => "Missing argument! Type /help for the syntax.",
"no-user-entered-text" => "You have to enter a user!",
"user-invited-successfully-text" => "%1 was  successfully invited to join this channel!",
"debug-changed-text" => "Debug was successfully	changed!",

"me-random-text-1" => "has something better to do",
"me-random-text-2" => "has fallen in love with %1",
"me-random-text-3" => "wishes he had not forgotten the text",
"me-random-text-4" => "thinks he/she is beautiful",
"me-random-text-5" => "is watching for %1",

/* Kick */
"reason-for-kick-text" => "Reason for the kick:",
"kick-no-reason-text" => "no reason",
"you-were-kicked-text" => "You were kicked! (%1)",
"you-were-kicked-by-user-text" => "You were kicked by %1! (%2)",

/* Notice Popup */
"info-head" => "Info:",
"error-head" => "Error:",
"warning-head" => "Warning:",
"success-head" => "Success:",	

"message-empty-text" => "Nothing entered",
"name-change-text" => "You are now called \"%1\"",
"user-invited-you-to-channel-text" => "You were invited by %1 to join the channel %2. Do you want to join this channel now?",

/* Desktop Notifications */
"desktop-notifications-enabled-head" => "Success",
"desktop-notifications-enabled-text" => "Desktop Notifications are now enabled",

/* Log */
"log-message" => "Message",
"log-info"=>"Info",

/* theme translations */
"message-input-placeholder" => "Please enter a message...",
"send-button-text" => "send",
"room-loading-text" => "Loading the Room. Please wait...",
"userlist-head" => "Userlist (%1)",
"settings-head" => "Settings",
"smileys-head" => "Smileys",
"enter-channel-name-text" => "Please enter a channel name",
"enable-desktop-notifications-text" => "Enable Desktop Notifications",
"enable-sound-text" => "Enable Sound",
"enable-invite-text" => "Enable Invitations"
);
?>