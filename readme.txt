=== Flexo Archives Widget ===
Contributors: heathharrelson
Donate link: 
Tags: sidebar, archive, archives, collapsible archive, collapsible, collapse, widget
Requires at least: 2.7
Tested up to: 3.1-RC4
Stable tag: 2.0.1

Displays your archives as a compact list of years that expands when clicked, with optional animation.

== Description ==

The Flexo Archives Widget displays your archives as a list of years that expands when clicked (thanks to some JavaScript magic) to show the months with posts. You also have the option to animate the expansion and to display the number of posts in each month.

This widget is designed to be a more compact alternative to the default archives widget supplied with WordPress. If you've been blogging regularly for several years, the list of your archives might be quite long. If you use this widget, though, it will be displayed as a much smaller list of years.

== Installation ==

Note: Because of some of the functions used internally, you must be using at least WordPress 2.7.  For ancient versions of WordPress (back to 2.2 and earlier), you should be using the 1.X version.

If your server is cooperative, you can install the widget automatically by going to your blog's plugin administration panel, clicking the 'Add New' button, searching for the term 'Flexo', and clicking the 'Install' link.  If the automatic install fails for some reason, do the following:

1. Download the zip file (`flexo-archives-widget.VERSION.zip`) from the WordPress plugins site.
1. Expand `flexo-archives-widget.VERSION.zip`
1. Upload the whole `flexo-archives-widget` directory to the `/wp-content/plugins/` directory.
1. Activate the Flexo Archives Widget plugin through the 'Plugins' menu in the WordPress admin interface.
1. To add the widget to your sidebar, go to the widgets panel in your admin interface.
1. Configure the widget's title and whether expansion is animated and post counts are displayed.

== Frequently Asked Questions ==

= The colors of the archive lists are funny. =

This is something I hear a lot about in connection with the Flexo Archives Widget, but it isn't the widget's fault. The colors of the lists are set (or not) by your theme.  All the widget does is hide or display the lists. It's likely that your theme doesn't have rules in its stylesheet to match the nested lists generated.

To test whether the problem is your theme, temporarily configure your blog to use the default WordPress theme. Expand and contract a few year links in the sidebar. If things don't look odd, the problem is probably with your theme.

== Screenshots ==

1. Before and after expansion with the default theme

== ChangeLog ==

= 2.0.1 =

* Add nonce field and check to enhance widget form security.

= 2.0.0 =

* Rewrite using jQuery for expand / contract code.
* Add animation.
* Drop support for ancient versions of WordPress.
* Test for WordPress 3.1.

== Upgrade Notice ==

== 2.0.1 ==
Enhanced security.

= 2.0.0 =
Adds animation when the list is expanded / contracted.
