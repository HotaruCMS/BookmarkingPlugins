/* ****************************************************************************************************
 *  File: /plugins/hovercard/javascript/hovercard.js
 *  Purpose: js for hovercard to display/hide
 *  Notes:
 *  License:
 *
 *   This file is part of Hotaru CMS (http://www.hotarucms.org/).
 *
 *   Hotaru CMS is free software: you can redistribute it and/or modify it under the terms of the
 *   GNU General Public License as published by the Free Software Foundation, either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   Hotaru CMS is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 *   even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License along with Hotaru CMS. If not,
 *   see http://www.gnu.org/licenses/.
 *
 *   Copyright (C) 2009 Hotaru CMS - http://www.hotarucms.org/
 *
 **************************************************************************************************** */

jQuery('document').ready(function($) {

	$('.avatar_wrapper .avatar').hover(function () {
            alert('hover');
                $hoverCard = $('#hover_card_content');
		$(this).add($hoverCard);
                $hoverCard.removeClass('hidden');
	});

});

