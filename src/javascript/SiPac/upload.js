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

SiPac.prototype.upload_picture = function (picture)
{
	var sipac = this;
	var data = new FormData();
	data.append('picture', picture);

	var httpobject = new XMLHttpRequest();
	httpobject.open('POST', sipac_html_path + "src/php/SiPac.php?task=upload_picture&id=" + this.id + "&client=" + this.client_num, true);
	httpobject.upload.addEventListener('progress',function(e){ console.log((e.loaded/e.total)+'%');}, false);
	httpobject.onload = function()
	{
		var answer = JSON.parse(httpobject.responseText);
		if (answer['status'] == true)
		{
			console.debug("Picture uploaded successfully");
			sipac.send_message("[img]" + answer['picture'] + "[/img]", sipac.active_channel);
		}
		else
		{
			console.debug("Picture upload went wrong'");
			if (answer['message'] != undefined)
				sipac.information(answer['message'],  "error");
		}
	};
	httpobject.send(data);
};