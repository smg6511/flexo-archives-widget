<?php
/*
Plugin Name: Flexo Archives
Description: Displays archives as a list of years that expand when clicked
Author: Heath Harrelson
Version: 2.0.0
Plugin URI: http://wordpress.org/extend/plugins/flexo-archives-widget/
*/

/*
 * Flexo Archives Widget by Heath Harrelson, Copyright (C) 2011
 *
 * This is a heavily modified version of the default WordPress archives widget, 
 * with bits from wp_get_archives() and Ady Romantika's random posts widget 
 * (http://www.romantika.name/v2/2007/05/02/wordpress-plugin-random-posts-widget/).
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

// WP plugin directory
define('PLUGIN_DIR', 'wp-content/plugins');

// Name of the base JavaScript file
define('FLEXO_JS', 'flexo.js');

// Name of the animated JavaScript file
define('FLEXO_ANIM_JS', 'flexo-anim.js');

// Subdirectory of plugins dir where our plugin is to be found
$exploded_path = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
define('FLEXO_DIR', $exploded_path[count($exploded_path) - 1]);


// Function to register our sidebar widget with WordPress
function flexo_widget_archives_init () {
	// Check for required functions
	if (!function_exists('register_sidebar_widget'))
		return;

	// Call the registration function on init
	flexo_widget_register();
}

// Handle widget configuration
function flexo_widget_archives_control () {
	$options = $newoptions = get_option('widget_flexo');
	if ( !empty($_POST["flexo-submit"]) &&
	     check_admin_referer('flexo-archives-widget-options') )
	{
		$newoptions['count'] = isset($_POST['flexo-count']);
		$newoptions['animate'] = isset($_POST['flexo-animate']);
		$newoptions['title'] = strip_tags(stripslashes($_POST["flexo-title"]));
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_flexo', $options);
	}
	$count = $options['count'] ? 'checked="checked"' : '';
	$animate = $options['animate'] ? 'checked="checked"' : '';
	$title = attribute_escape($options['title']);

	wp_nonce_field('flexo-archives-widget-options');
?>
		<p><label for="flexo-title"><?php _e('Title:'); ?> <input style="width: 90%;" id="flexo-title" name="flexo-title" type="text" value="<?php echo $title; ?>" /></label></p>
		<p style="text-align:right;margin-right:40px;"><label for="flexo-animate"><?php _e('Animate lists'); ?> <input class="checkbox" type="checkbox" <?php echo $animate; ?> id="flexo-animate" name="flexo-animate"/></label></p>
		<p style="text-align:right;margin-right:40px;"><label for="flexo-count"><?php _e('Show post counts'); ?> <input class="checkbox" type="checkbox" <?php echo $count; ?> id="flexo-count" name="flexo-count"/></label></p>
		<input type="hidden" id="flexo-submit" name="flexo-submit" value="1" />
<?php
}

// Helper function to print first bit of year list
function flexo_year_start ($year = '') {
	// Ugly strings used in building the tags
	$year_start = '<ul><li><a href="%s" class="flexo-link" ';
	$year_start .= 'id="flexo-%s"  title="Year %s archives">';
	$year_start .= '%s</a><ul class="flexo-list">';

	return sprintf($year_start, get_year_link($year), $year, $year, $year);
}

// Perform database query to get archives.  Archives are sorted in
// *descending* order or year and *ascending* order of month
function flexo_get_archives () {
	global $wpdb;

	// Query string
	$qstring = "SELECT DISTINCT YEAR(post_date) AS `year`,";
	$qstring .= " MONTH(post_date) AS `month`,";
	$qstring .= " count(ID) AS posts FROM  $wpdb->posts";
	$qstring .= " WHERE post_type = 'post' AND post_status = 'publish'";
	$qstring .= " GROUP BY YEAR(post_date), MONTH(post_date)";
	$qstring .= " ORDER BY YEAR(post_date) DESC, MONTH(post_date) ASC";

	// Query database
	$flexo_results = $wpdb->get_results($qstring);

	// Check we actually got results
	if ($flexo_results) {
		return $flexo_results;
	} else {
		// No results or database error
		return false;
	}
}

// Helper function that prints the url for our javascript
function flexo_script_url () {
	$url = get_bloginfo('wpurl') .'/'.  PLUGIN_DIR . '/' . FLEXO_DIR . '/';

	$options = get_option('widget_flexo');
	if ($options['animate']) {
		$url .= FLEXO_ANIM_JS;
	} else {
		$url .= FLEXO_JS;
	}

	return $url;
}

// Munge archive list and print output
function flexo_widget_archives ($args) {
	global $wp_locale;
	extract($args);

	// Fetch widget options
	$options = get_option('widget_flexo');
	$title = empty($options['title']) ? __('Archives') : $options['title'];
	$count = $options['count'] ? '1' : '0';

	// Print out the title
	echo $before_widget; 
	echo $before_title . $title . $after_title;

	// Get database results
	$results = flexo_get_archives();
	
	// Detect year change in loop.
	$a_year = '0';

	// Loop over results and print our archive lists
	foreach ($results as $a_result) {
		$before = '';
		$after = '';

		if ($a_result->year != $a_year) {
			// If not first iteration, close previous list
			if ($a_year != '0')
				echo '</ul></li></ul>';

			$a_year = $a_result->year;
			echo flexo_year_start($a_result->year) . "\n";
		}

		$url = get_month_link($a_result->year, $a_result->month);
		$text = sprintf(__('%1$s'), $wp_locale->get_month($a_result->month));

		// Append number of posts in month, if they want it
		if ($count)
			$after = '&nbsp;(' . $a_result->posts . ')' . $after;

		echo get_archives_link($url, $text, 'html', $before, $after);
	}

	// Close the last list
	echo '</ul></li></ul>';

	// Close out the widget
	echo $after_widget; 
}

// Register our widgets with the widget system and add a callback to print our CSS
function flexo_widget_register () {
	$name = __('Flexo Archives');
	$desc = __('Your archives as an expandable list of years');
	$widget_cb = 'flexo_widget_archives';
	$control_cb = 'flexo_widget_archives_control';
	$css_class = 'flexo';

	// Tell the dynamic sidebar about our widget
	if (function_exists('wp_register_sidebar_widget')) {
		$widget_ops = array('class' => $css_class, 'description' => $desc);
		$control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'flexo-archives');
		$id = 'flexo-archives'; // Never never never translate an id

		wp_register_sidebar_widget($id, $name, $widget_cb, $widget_ops);
		wp_register_widget_control($id, $name, $control_cb, $control_ops);
	}

	// Register the function to delete options on uninstall
	register_uninstall_hook(__FILE__, 'flexo_widget_uninstall');

	// Add CSS and JavaScript to header if we're active
	if (is_active_widget('flexo_widget_archives')) {
		wp_enqueue_script('jquery');
		wp_enqueue_script('flexo', flexo_script_url(), array('jquery'), '2.0');
	}
}

function flexo_widget_uninstall () {
	$options = get_options('widget_flexo');

	if (is_array($options)) {
		delete_option('widget_flexo');
	}
}

// Delay plugin execution until sidebar is loaded
add_action('widgets_init', 'flexo_widget_archives_init');

?>
