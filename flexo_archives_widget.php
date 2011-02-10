<?php
/*
Plugin Name: Flexo Archives
Description: Displays archives as a list of years that expand when clicked
Author: Heath Harrelson
Version: 2.1.0
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

class Flexo_Archives_Widget extends WP_Widget {
	// option constants
	const OPTIONS_NAME = 'widget_flexo';
	const TITLE_OPTION = 'title';
	const COUNT_OPTION = 'count';
	const ANIMATE_OPTION = 'animate';

	// javascript filename constants
	const FLEXO_JS_FILE = 'flexo.js';
	const FLEXO_ANIM_JS_FILE = 'flexo-anim.js';

	// instance variables
	private $flexo_dir;	// name of directory with widget's files

	// FIXME: understand all arguments of superclass constructor
	// FIXME: understand how this will register
	// FIXME: understand how this will load options
	// FIXME: figure out how to hook script to page
	// FIXME: register uninstall hook
	function Flexo_Archives_Widget() {
		$name = __('Flexo Archives');
		$desc = __('Your archives as an expandable list of years');
		$widget_cb = 'flexo_widget_archives';
		$control_cb = 'flexo_widget_archives_control';
		$css_class = 'flexo';

                $widget_ops = array('class' => $css_class, 'description' => $desc);
                $control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'flexo-archives');


		$this->flexo_dir = basename(dirname(__FILE__));
		parent::WP_WIDGET('flexo', $name, $widget_ops, $control_ops);
	}

	// FIXME: understand what this does, particularly $instance
	function form($instance) {
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

	// FIXME: understand what this will do
	function update($new_instance, $old_instance) {
		// processes widget options to be saved
	}

	// FIXME: undestand the $instance argument
	/**
	 * Print the archives widget's output.
	 */
	function widget($args, $instance) {
		global $wp_locale;
		extract($args);

		// Fetch widget options
		$options = get_option(self::OPTIONS_NAME);
		$title = empty($options[self::TITLE_OPTION]) ? __('Archives') : $options[self::TITLE_OPTION];
		$count = $options[self::COUNT_OPTION] ? '1' : '0';

		// Print out the title
		echo $before_widget;
		echo $before_title . $title . $after_title;

		// Get database results
		$results = $this->get_archives();

		if (!$results) {
			// FIXME print error string
			echo $after_widget;
			return;
		}

		// Detect year change in loop.
		$a_year = '0';

		foreach ($results as $a_result) {
			$before = '';
			$after = '';

			if ($a_result->year != $a_year) {
				// Not first iteration, close previous list
				if ($a_year != '0')
					echo '</ul></li></ul>';

				$a_year = $a_result->year;
				echo $this->print_year_start($a_result->year) . "\n";
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

	/**
	 * Performs database query to get archives. Archives are sorted
	 * in *descending* order of year and *ascending* order of month.
	 *
	 * Returns: Query result if query successful, null if not.
	 */
	function get_archives () {
		global $wpdb;

		// query string
		$qstring = 'SELECT DISTINCT YEAR(post_date) AS `year`,';
		$qstring .= ' MONTH(post_date) AS `month`,';
		$qstring .= ' count(ID) AS posts FROM  %s';
		$qstring .= ' WHERE post_type = \'post\' AND';
		$qstring .= ' post_status = \'publish\'';
		$qstring .= ' GROUP BY YEAR(post_date), MONTH(post_date)';
		$qstring .= ' ORDER BY YEAR(post_date) DESC, MONTH(post_date) ASC';

		// query database
		$flexo_results = $wpdb->get_results(sprintf($qstring, $wpdb->posts));

		// check we actually got results
		if ($flexo_results) {
			return $flexo_results;
		} else {
			// No results or database error
	                return null;
        	}
	}

	/**
	 * Helper function to print first bit of year list.
	 * 
	 * Returns: HTML for the start of the year list.
	 */
	function print_year_start ($year = '') {
		// ugly strings used in building the tags
		$year_start = '<ul><li><a href="%s" class="flexo-link" ';
		$year_start .= 'id="flexo-%s"  title="Year %s archives">';
		$year_start .= '%s</a><ul class="flexo-list">';

		return sprintf($year_start, get_year_link($year), $year, $year, $year);
	}

	/**
	 * Helper function to construct the URL to the appropriate
	 * JS file.
	 */
	function script_url () {
		$url = WP_PLUGIN_URL . '/' . $this->flexo_dir . '/';

		$options = get_option(self::OPTIONS_NAME);
		if ($options[self::ANIMATE_OPTION]) {
			$url .= self::FLEXO_ANIM_JS;
		} else {
			$url .= self::FLEXO_JS;
		}

		return $url;
	}

	/**
	 * Uninstall function. Removes settings from the database 
	 * before the plugin files are deleted.
	 */
	function uninstall_widget () {
		$options = get_option(self::OPTIONS_NAME);

		if (is_array($options)) {
			delete_option(self::OPTIONS_NAME);
		}
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Flexo_Archives_Widget");'));
?>
