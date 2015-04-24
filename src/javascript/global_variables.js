/*
    SiPac is highly customizable PHP and AJAX chat
    Copyright (C) 2013-2015 Jan Houben

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
function sipac_set_vars()
{
  sipac_new_id = 0;
  sipac_objects = new Array();
  sipac_objects_id = {};
  sipac_theme_functions = new Array();
  sipac_ajax_timeout = 1000;
  sipac_ajax_reconnect_timeout = 40000;
  sipac_html_path = false;
  
  sipac_main_request();
}

if (typeof sipac_objects == 'undefined')
{
  sipac_set_vars();
}