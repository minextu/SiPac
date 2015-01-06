<?php
/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2014 Jan Houben

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
"writing-status" => "Schreibt...",
"kick-head" => "Kicken",
"kick-user"=>"%1 kicken",
"ban-head" => "Ban",
"ban-user"=>"%1 bannen",
"private-message-head" => "Nachricht",
"send-private-message-text" => "Private Nachricht senden",
"private-message-prompt-text" => "Private Nachricht",

/* Notifications */
"rename-notification" => "[user]%1[/user] ist jetzt [user]%2[/user]",
"user-left-notification" =>"[user]%1[/user] ist gegangen",
"user-join-notification" => "[user]%1[/user] ist beigetreten",
"user-kicked-user-notification" => "[user]%1[/user] wurde von [user]%2[/user] gekickt (%3)",
"user-kicked-notification" => "[user]%1[/user] wurde gekickt (%2)",
"user-banned-user-notification" => "[user]%2[/user] hat [user]%1[/user]  gebannt (%3)",
"user-banned-notification" => "[user]%1[/user] wurde gebannt (%2)",
"user-now-afk-no-reason-notification" => "[user]%1[/user] ist jetzt abwesend",
"user-now-afk-notification" => "[user]%1[/user] ist jetzt abwesend (%2)",
"user-now-not-afk-notification" => "[user]%1[/user] ist wieder zurück",

/* Commands */
"command-list-head" => "Die folgenden Kommandos können verwendet werden:",
"command-syntax-head" => "Kommando Syntax:",
"command-not-found-text" => "Kommando %1 wurde nicht gefunden! (Schreibe /help für eine Liste von Kommandos)",
"no-permissions-text" => "Keine Berechtigung!",
"no-permissions-rename-other-user" => "Keine Berechtigung andere Benutzer umzubennen!",
"user-not-found-text" => "Benutzer %1 wurde nicht gefunden",
"newname-not-entered-text" => "Es wurde kein neuer Name eingegeben!",
"no-reason-for-not-afk-text" => "Es kann kein Grund dafür angegeben werden, dass du zurück bist!",
"no-channel-entered" =>"Es muss ein Channel angegeben werden!",
"arguments-missing-text" => "Fehlendes Argument! Schreib /help für den Syntax.",
"no-user-entered-text" => "Es muss ein Benutzer angegeben werden!",
"user-invited-successfully-text" => "%1 wurde erfolgreich eingeladen diesem Channel beizutreten!",
"debug-changed-text" => "Debug wurde erfolgreich geändert!",

"banlist-head" => "Banliste:",
"banlist-user-entry" => "%1 (Grund: \"%2\", gebannt bis: %3)",
"banlist-no-banned-users-text" => "Keine gebannten Nutzer!",

"me-random-text-1" => "hat etwas besseres zu tun",
"me-random-text-2" => "Hat sich in %1 verliebt",
"me-random-text-3" => "wünscht sich er/sie hätte den Text nicht vergessen",
"me-random-text-4" => "denkt er/sie wäre hübsch",
"me-random-text-5" => "hat %1 im Auge",

/* Kick */
"reason-for-kick-text" => "Grund für den Kick:",
"kick-no-reason-text" => "Kein Grund",
"you-were-kicked-text" => "Du wurdest gekickt! (%1)",
"you-were-kicked-by-user-text" => "Du wurdest von %1 gekickt! (%2)",

/* Ban */
"reason-for-ban-text" => "Grund für den Ban:",
"ban-no-reason-text" => "Kein Grund",
"you-were-banned-text" => "Du wurdest gebannt! (%1)",
"you-were-banned-by-user-text" => "%1 hat dich gebannt! (%2)",
"user-no-longer-banned-text" => "%1 ist jetzt  nicht mehr gebannt!",

/* Notice Popup */
"info-head" => "Info:",
"error-head" => "Fehler:",
"warn-head" => "Warnung:",
"success-head" => "Erfolg:",

"message-empty-text" => "Es wurde nichts eingegeben!",
"name-change-text" => "Du heißt jetzt \"%1\"",
"user-invited-you-to-channel-text" => "Du wurdest von %1 eingeladen, dem Channel %2 beizutreten. Möchtest du diesem Channel jetzt beitreten?",

/* Desktop Notifications */
"desktop-notifications-enabled-head" => "Erfolg",
"desktop-notifications-enabled-text" => "Desktop Benachrichtigungen wurden aktiviert",

/* Log */
"log-message" => "Nachricht",
"log-info"=>"Info",

/* theme translations */
"noscript-text" => "JavaScript muss aktiviert werden, um den Chat zu benutzen!",
"message-input-placeholder" => "Bitte eine Nachricht eingeben...",
"send-button-text" => "Senden",
"room-loading-text" => "Der Raum wird geladen. Bitte warten...",
"userlist-head" => "Benutzerliste (%1)",
"settings-head" => "Einstellungen",
"smileys-head" => "Smileys",
"enter-channel-name-text" => "Bitte gib einen Channel Namen ein",
"enable-desktop-notifications-text" => "Desktop Benachrichtigungen aktivieren",
"enable-sound-text" => "Sound aktivieren",
"enable-autoscroll-text" => "Automatisches Scrollen aktivieren",
"enable-invite-text" => "Einladungen aktivieren"
);
?>