=== Flexo Archives ===
Contributors: heathharrelson
Donate link: http://amzn.com/w/J010ZTQZM654 
Tags: sidebar, archive, archives, collapsible archive, collapsible, collapse, widget
Requires at least: 2.7
Tested up to: 3.1-RC4
Stable tag: 2.1.0

Displays your archives as a compact list of years that expands when clicked, with optional animation.

== Description ==

Flexo Archives displays your archives as a list of years. When you click a year,it expands to show the months that had posts. You also have the option to animate the expansion.

This widget is designed to be a more compact alternative to the default archives widget supplied with WordPress. If you've been blogging regularly for several years, the list of your archives might be quite long. If you use this widget, though, it will be displayed as a much smaller list of years.

A standalone version that simply prints the HTML for the archive lists and attaches the JavaScript to normal pages is now provided for users who cannot use the widget.

== Installation ==

Note: Because of some of the functions used internally, you must be using at least WordPress 2.7.  For ancient versions of WordPress (back to 2.2 and earlier), you should be using the 1.X version.

If your server is cooperative, you can install the widget automatically by going to your blog's plugin administration panel, clicking the 'Add New' button, searching for the term 'Flexo', and clicking the 'Install' link.  If the automatic install fails for some reason, do the following:

1. Download the zip file (`flexo-archives-widget.VERSION.zip`) from the WordPress plugins site.
1. Expand `flexo-archives-widget.VERSION.zip`
1. Upload the whole `flexo-archives-widget` directory to the `/wp-content/plugins/` directory.
1. Activate the Flexo Archives plugin through the 'Plugins' menu in the WordPress admin interface.
1. To add the widget to your sidebar, go to the widgets panel in your admin interface. Drag the widget to the desired widget area of your theme, then configure the widget's title and whether to use animation.

If you need to use the standalone function:

1. Install the plugin automatically or follow steps one through four as above.
1. From the WordPress dashboard, click the 'Settings' menu (near the bottom of the left column).
1. Click the 'Flexo Archives' option in the expanded menu.
1. Enable the standalone function and modify your theme files as described.

== Frequently Asked Questions ==

= The colors of the archive lists are funny. =

This is something I hear a lot about in connection with the Flexo Archives Widget, but it isn't the widget's fault. The colors of the lists are set (or not) by your theme.  All the widget does is hide or display the lists. It's likely that your theme doesn't have rules in its stylesheet to match the nested lists generated.

To test whether the problem is your theme, temporarily configure your blog to use the default WordPress theme. Expand and contract a few year links in the sidebar. If things don't look odd, the problem is probably with your theme.

== Screenshots ==

1. An expanded archive list and a collapsed archive list.

== Changelog ==

= 2.1.0 =

* Reimplemented as a class.
* Fixed issue where users of the standalone function couldn't enable post counts.
* Play nice with the getarchives_where and getarchives_join filters.
* Initial internationalization support.

= 2.0.3 =

* Added a standalone function for users who can't use the widget.

= 2.0.2 = 

* Fixed a typo in the uninstall function, changed comments. Not released.

= 2.0.1 =

* Add nonce field and check to enhance widget form security.

= 2.0.0 =

* Rewrite using jQuery for expand / contract code.
* Add animation.
* Drop support for ancient versions of WordPress.
* Test for WordPress 3.1.

== Upgrade Notice ==

= 2.1.0 =
Fixed an issue with the standalone function and added initial internationalization support. Users of the standalone function or wishing to localize the plugin should upgrade.

= 2.0.3 =
Added a standalone function for users who can't use the widget.

= 2.0.1 =
Enhanced security.

= 2.0.0 =
Adds animation when the list is expanded / collapsed.
