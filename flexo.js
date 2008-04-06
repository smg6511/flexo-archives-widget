/* vim: set noai ts=4 sw=4: */
/*
 * flexo.js by Heath Harrelson, Copyright (C) 2007
 *
 * Version: 1.2.0
 * 
 * Expands and collapses menus.  Used by the Flexo Archives WordPress widget
 * (http://www.pointedstick.net/heath/flexo-archives-widget).
 *
 * This code is based on things found at the following page:
 *   http://www.learningjquery.com/2007/03/accordion-madness 
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

$(document).ready(
	function () {
		$('ul.flexo-list').hide();	// hide year lists
		$('li.flexo-link').click(
			function() {
				$('ul.flexo-list').hide();
				$(this).next().slideToggle('fast');
			}
		);
	}
);
