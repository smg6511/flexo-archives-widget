/*
 * flexo.js by Heath Harrelson, Copyright (C) 2007
 *
 * Version: 1.0.1
 * 
 * Expands and collapses menus.  Used by the Flexo Archives WordPress widget
 * (http://www.pointedstick.net/heath/flexo-archives-widget).
 *
 * This code is based on things found at the following pages:
 *   http://www.456bereastreet.com/archive/200705/accessible_expanding_and_collapsing_menu/
 *   http://icant.co.uk/sandbox/eventdelegation/
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

var flexoToggle = {
	init : function () {
		var monthLists;  // List of ul.flexo-list elements
		var yearLinks;   // List of all the expandable links
		var numLists;    // Number of elements in monthLists
		var numLinks;    // Number of elements in yearLinks
		var hiddenList;  // The list we want to hide
		var widget;      // Outermost container of the widget

		// Dom support or bust!
		if (!document.getElementById)
			return;

		// Get a list of all the yearly lists
		monthLists = this.getElementByClassName(document, 'ul', 'flexo-list');
		numLists = monthLists.length;

		// Get a list of all the expandable links
		yearLinks = this.getElementByClassName(document, 'a', 'flexo-link');
		numLinks = yearLinks.length;

		// Hide each list of months
		for (var i = 0; i < numLists; i++) {
			hiddenList = monthLists[i];
			this.toggle(hiddenList);
		}

		// Add a hint to each expandable link
		for (i = 0; i < numLinks; i++) {
			yearLinks[i].title += ' (Click to Expand)';
		}

		// Hook event delegate to the start of the widget
		widget = document.getElementById('flexo-archives');
		flexoToggle.addEvent(widget, 'click', flexoToggle.clickListener);
	},

	// Show or hide an element (and its children)
	toggle : function (el) {
		if (el.style.display == 'block' || el.style.display == '') {
			el.style.display = 'none';
		} else {
			el.style.display = 'block';
		}
	},

	// Show or hide a list when the user clicks
	clickListener : function (e) {
		var targ;               // Element clicked
		var startOfMenu;        // Element where the year's list starts
		var flexLists;		// List of month lists
		var aList;		// A month list
		var len;                // Number of lists to toggle

		// If event is undefined, this is IE
		if (!e)
			var e = window.event;

		// More browser compat
		if (e.target) {
			// W3C DOM events
			targ = e.target;
		} else if (e.srcElement) {
			// IE
			targ = e.srcElement;
		}

		// Back up to the link if this is a text node.
		// Hack for bug in Safari
		if (targ.nodeType == 3)
			targ = targ.parentNode;


		// Find the elements to toggle (if any) and toggle them
		if (targ.nodeName == 'A' && targ.className == 'flexo-link') {
			// Grandparent should be start of the menu
			startOfMenu = targ.parentNode.parentNode;
			if (startOfMenu.nodeName != 'UL')
				return true;

			// Okay, we're in a menu. Try to get the month list.
			flexLists = flexoToggle.getElementByClassName(startOfMenu, 'ul', 'flexo-list');
			// If there's at least one month list
			if (flexLists.length > 0) {
				len = flexLists.length;
				for (var i = 0; i < len; i++) {
					aList = flexLists[i];
					flexoToggle.toggle(aList);
				}

				// Don't follow link clicked
				flexoToggle.preventDefault(e);
				return false;
			}

		}

	},

	// Prevent the default action of the DOM event
	preventDefault : function (e) {
		if (e.preventDefault) {
			e.preventDefault(); // W3C DOM style
		} else if (!e.preventDefault) {
			window.event.returnValue = false; // IE style
		}
	},

	// Attach an event listener to an object
	addEvent : function (obj, type, callback) {
		if (obj.addEventListener) {
			obj.addEventListener(type, callback, false);
		} else if (obj.attachEvent) {
			obj['e' + type + callback] = callback;
			obj[type + callback] = function () {obj['e'+type+callback](window.event);}
			obj.attachEvent('on' + type, obj[type+callback]);
		}
	},

	// Get a list of tags bearing a certain CSS class
	getElementByClassName: function (obj, tagName, className) {
		var allSuchElements = obj.getElementsByTagName(tagName);
		var classElements = new Array();
		var len = allSuchElements.length;

		// Find elements with the specified class
		for (var i = 0; i < len; i++) {
			if (allSuchElements[i].className == className)
				classElements.push(allSuchElements[i]);
		}

		return classElements;
	}
};
/* Attach our click listener when the page has loaded. */
flexoToggle.addEvent(window, 'load', function () { flexoToggle.init(); });
