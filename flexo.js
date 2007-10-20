/* vim: set noai ts=4 sw=4: */
/*
 * flexo.js by Heath Harrelson, Copyright (C) 2007
 *
 * Version: 1.1.1.1
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
	widgetRoot : null,   // The widget's root element

	init : function () {
		var yearLinks;   // List of all the expandable links
		var numLinks;    // Number of elements in yearLinks
		var widget;      // Outermost container of the widget

		// Dom support or bust!
		if (!document.getElementById)
			return;

		// If the widget's root can't be found, we can't
		// do anything.
		this.findWidgetRoot();
		if (!this.widgetRoot) {
			return;
		} else {
			widget = this.widgetRoot;
		}

		// Get a list of all the expandable links
		yearLinks = this.getElementByClassName(widget, 'a', 'flexo-link');
		numLinks = yearLinks.length;

		// Hide each list of months
		for (var i = 0; i < numLinks; i++) {
			this.setStateForListWithLink(yearLinks[i], false);
		}

		// Add a hint to each expandable link
		for (i = 0; i < numLinks; i++) {
			yearLinks[i].title += ' (Click to Expand)';
		}

		// Hook event delegate to the start of the widget
		this.addEvent(widget, 'click', this.clickListener);
	},

	// Show or hide a list when the user clicks
	clickListener : function (e) {
		var targ;               // Element clicked

		// If event is undefined, this is IE
		if (!e)
			var e = window.event;

		// Get the element clicked
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

		// Try to toggle this link
		if (flexoToggle.setStateForListWithLink(targ, true)) {
			// Don't follow link clicked
			flexoToggle.preventDefault(e);
			return false;
		}

		// Toggle failed, allow default action
		return true;

	},

	// Show or hide the list associated with element aLink.
	// If updateCookie is true, add or remove aLink from the
	// cookie that keeps track of open lists.
	setStateForListWithLink : function (aLink, updateCookie) {
		var cookieText;     // Contents of the cookie
		var startOfMenu;    // Element where archive list starts
		var flexLists;      // Array of month lists
		var len;            // The number of flexLists

		// Grandparent should be start of archive list
		startOfMenu = aLink.parentNode.parentNode;

		// Bail if aLink node is not a Flexo Link or
		// startOfMenu node is not a list
		if (!flexoToggle.isFlexoLink(aLink) || 
		    !flexoToggle.isListElement(startOfMenu))
			return false;

		// Get list(s) of months
		flexLists = flexoToggle.getElementByClassName(startOfMenu,
							'ul', 'flexo-list');

		// Do cookie maintenance if called from click handler
		if (updateCookie) {
			cookieText = flexoToggle.getCookie('flexo');

			if (cookieText && cookieText != '') {
				if (flexoToggle.inCookie('flexo', aLink.id)) {
					// If aLink is in the cookie, remove it
					flexoToggle.replaceCookie('flexo', '');
				} else {
					// If cookie is set, but aLink is not in it, tell
					// the other list to close before adding aLink
					var otherLink = document.getElementById(cookieText);
					flexoToggle.setStateForListWithLink(otherLink, true);
					flexoToggle.replaceCookie('flexo', aLink.id);
				}
			} else {
				// If cookie unset or blank, add aLink to it
				flexoToggle.replaceCookie('flexo', aLink.id);
			}
		} else {
			// Called from init. Don't collapse open list.
			if (flexoToggle.inCookie('flexo', aLink.id))
				return true;
		}

		// Show or hide month list
		if (flexLists.length > 0) {
			len = flexLists.length;
			for (var i = 0; i < len; i++)
				flexoToggle.setDisplay(flexLists[i]);
		}

		return true;
	},

	// Determine if list id is in cookie cookieName
	inCookie : function (cookieName, id) {
		var cookieText = flexoToggle.getCookie(cookieName);

		// If the cookie is set and id is a substring
		if (cookieText && cookieText.indexOf(id) != -1)
			return true;

		return false;
	},

	// Add list id to the cookie cookieName
	replaceCookie : function (cookieName, id) {
		if (cookieName == '')
			return;

		// Replace current cookie with element id
		flexoToggle.setCookie(cookieName, id, '/heath'); // FIXME
	},

	// Attempt to get the text of cookie cookieName
	getCookie : function (cookieName) {
		var allCookies = document.cookie;
		var startIndex = allCookies.indexOf(cookieName);
		
		// Named cookie is set
		if (startIndex != -1) {
			// Move past name and equals sign
			startIndex += 1 + cookieName.length;

			var endIndex = allCookies.indexOf(';', startIndex);
			if (endIndex == -1)
				endIndex = allCookies.length;

			var cookieText = allCookies.substring(startIndex,
						endIndex);
			return unescape(cookieText);
		}

		// Named cookie not set
		return null;
	},

	// Set a nonpersistent named cookie with the given text and scope
	setCookie : function (cookieName, text, scope) {
		var theCookie = cookieName + '=' + escape(text);

		if (scope && scope != '')
			theCookie += '; path=' + scope;

		document.cookie = theCookie;
	},

	// Find the root element of the widget.
	findWidgetRoot : function () {
		// Nice themes will supply the ID of the widget.
		var el = document.getElementById('flexo-archives');
		var flexLinks;

		// This theme is not so nice. :(
		if (!el) {
			// Get the list of flexo-links
			flexLinks = flexoToggle.getElementByClassName(document, 'a',
									'flexo-link');


			// The widget's root is the great-grandparent of the
			// first flexo-link
			el = flexLinks[0].parentNode.parentNode.parentNode;
		}

		// Set class variable
		if (el)
			flexoToggle.widgetRoot = el;
	},

	// Determine if DOM element el is a list (ul, ol)
	isListElement : function (el) {
		if (el.nodeName == 'UL' || el.nodeName == 'OL' ||
			el.NodeName == 'DL')
			return true;

		return false;
	},

	// Determine if DOM element el is a Flexo Link
	isFlexoLink : function (el) {
		if (el.nodeName == 'A' && el.className == 'flexo-link')
			return true;

		return false;
	},

	// Show or hide DOM element el (and its children)
	setDisplay : function (el) {
		if (el.style.display == 'block' || el.style.display == '') {
			el.style.display = 'none';
		} else {
			el.style.display = 'block';
		}
	},

	// Prevent the default action of DOM event e
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
