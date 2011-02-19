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

class FlexoArchives {
	// Options constants
	const OPTIONS_NAME = 'widget_flexo';
	const OPT_STANDALONE = 'standalone'; // bool: standalone func enabled?
	const OPT_ANIMATE    = 'animate';    // bool: list animation enabled
	const OPT_COUNT      = 'count';      // bool: post counts in lists
	const OPT_WTITLE     = 'title';      // string; widget title string

	// Filename constants
	const FLEXO_JS = 'flexo.js';
	const FLEXO_ANIM_JS = 'flexo-anim.js';

	// Subdirectory where the plugin is located
	private $flexo_dir;

	// Options array
	private $options;

	/**
	 * PHP4 constructor
 	 */
	function FlexoArchives () {
		return $this->__construct();
	}

	/**
	 * PHP5 constructor
	 */
	function __construct () {
		$this->flexo_dir = basename(dirname(__FILE__));
		$this->initialize();
	}

	/**
	 * Register plugin callbacks with WordPress
	 */
	function initialize () {
		// get translations loaded
		add_action('init', array(&$this, 'load_translations'));

		// make sure options are initialized
		$this->set_default_options();

		// register standalone callbacks
		add_action('init', array(&$this, 'enqueue_standalone_scripts'));
		add_action('admin_menu', array(&$this, 'options_menu_item'));

		// register widget callbacks
		add_action('widgets_init', array(&$this, 'widget_init'));

		// register uninstall function
		register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
	}

	/**
	 * Sets the default values for unset options
	 */
	function set_default_options () {
		$options = $this->get_opts();

		if (!isset($options[self::OPT_STANDALONE])) {
			$options[self::OPT_STANDALONE] = false;
		}

		if (!isset($options[self::OPT_ANIMATE])) {
			$options[self::OPT_ANIMATE] = true;
		}

		if (!isset($options[self::OPT_WTITLE])) {
			$options[self::OPT_WTITLE] = strip_tags(__('Archives', 'flexo-archives'));
		}

		if (!isset($options[self::OPT_COUNT])) {
			$options[self::OPT_COUNT] = false;
		}

		$this->set_opts($options);
	}

	/**
	 * Gets the entire options array from the database
	 * 
	 * Returns: An array of options. Individual options
	 * can be accessed by their keys, defined as class
	 * constants (see above).
	 */
	function get_opts () {
		if (is_null($this->options)) {
			$this->options = get_option(self::OPTIONS_NAME);
		}
		return $this->options;
	}

	/**
	 * Save a modified options array to the database
	 *
	 * Arguments: An array containing the options. Array
	 * keys are defined as class constants (see above).
	 */
	function set_opts ($newoptions = null) {
		$options = $this->get_opts();
		if ($options != $newoptions) {
			$this->options = $newoptions;
			update_option(self::OPTIONS_NAME, $newoptions);
		}
	}

	/**
	 * Gets the widget title set in the database
	 */
	function widget_title () {
		$options = $this->get_opts();
		return attribute_escape($options[self::OPT_WTITLE]);
	}

	/**
	 * Reports whether the user enabled post counts
	 */
	function count_enabled () {
		$options = $this->get_opts();
		return $options[self::OPT_COUNT];
	}

	/**
	 * Reports whether standalone archive function is enabled
	 */
	function standalone_enabled () {
		$options = $this->get_opts();
		return $options[self::OPT_STANDALONE];
	}

	/**
	 * Reports whether list animation is enabled
	 */
	function animation_enabled () {
		$options = $this->get_opts();
		return $options[self::OPT_ANIMATE];
	}

	/**
	 * Loads translated strings from catalogs in ./lang
	 */
	function load_translations () {
		$lang_dir = $this->flexo_dir . '/lang';
		load_plugin_textdomain('flexo-archives', null, $lang_dir);
	}

	/**
	 * Function to register our sidebar widget with WordPress
	 */
	function widget_init () {
		// Check for required functions
		if (!function_exists('wp_register_sidebar_widget'))
			return;

		// Call the registration function on init
		$this->register_widget();
	}

	/**
	 * Register the configuration page for the standalone function
	 */
	function options_menu_item () {
		$page_title = __('Standalone Flexo Archives Options', 'flexo-archives');
		$menu_title = __('Flexo Archives', 'flexo-archives');
		$menu_slug  = 'flexo-archvies-options';

		add_options_page($page_title, $menu_title, 'manage_options',
				 $menu_slug, array(&$this, 'options_page'));
	}

	/**
	 * Output plugin configuration page
	 */
	function options_page () {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient priveleges to access this page.', 'flexo-archives'));
		}

		// form submitted
		$options = $newoptions = $this->get_opts();
		if ( !empty($_POST["flexo-submit"]) &&
		     check_admin_referer('flexo-archives-options-page') )
		{
			$newoptions[self::OPT_STANDALONE] = isset($_POST['flexo-standalone']);
			$newoptions[self::OPT_ANIMATE] = isset($_POST['flexo-animate']);
			$newoptions[self::OPT_COUNT] = isset($_POST['flexo-count']);
		}

		// save if options changed
		if ($options != $newoptions) {
			$options = $newoptions;
			$this->set_opts($options);
		}

		$standalone = $this->standalone_enabled() ? 'checked="checked"' : '';
		$animate = $this->animation_enabled() ? 'checked="checked"' : '';
		$count = $this->count_enabled() ? 'checked="checked"' : '';

?>
<div class="wrap">
  <h2><?php _e('Standalone Flexo Archives Options', 'flexo-archives'); ?></h2>
  <div class="narrow">
  <p><?php _e('These options are only relevant to users who cannot use or do not want to use the sidebar widget. If you are using the widget, then you should ignore the following settings.', 'flexo-archives'); ?></p>
  <p><?php _e('To use the standalone version of the archives, check the "enable standalone theme function" box below, and then add the following code your theme where you want the expandable archive lists to be:', 'flexo-archives'); ?></p>

  <code>&lt;?php if (function_exists('flexo_standalone_archives')){ flexo_standalone_archives(); } ?&gt;</code>

  <p><?php _e('The code will output the nested archive lists into the HTML at that point in the theme. JavaScript automatically attached to the pages generated by WordPress will make the lists expand and collapse.', 'flexo-archives'); ?></p>

  <h3><?php _e('Change Options', 'flexo-archives'); ?></h3>

  <form name="flexo-options-form" method="post" action="">
    <?php wp_nonce_field('flexo-archives-options-page'); ?>
    <p><label for="flexo-standalone"><input type="checkbox" class="checkbox" id="flexo-standalone" name="flexo-standalone" <?php echo $standalone; ?>/> <?php _e('enable standalone theme function', 'flexo-archives'); ?></label></p>
    <p><label for="flexo-animate"><input type="checkbox" class="checkbox" id="flexo-animate" name="flexo-animate" <?php echo $animate; ?>/> <?php _e('animate collapsing and expanding lists', 'flexo-archives'); ?></label></p>
    <p><label for="flexo-count"><input type="checkbox" class="checkbox" id="flexo-count" name="flexo-count" <?php echo $count; ?>/> <?php _e('include post counts in lists', 'flexo-archives'); ?></label></p>
    <input type="submit" name="flexo-submit" class="button-primary" value="<?php _e('Submit', 'flexo-archives'); ?>"/>
  </form>
  </div>
</div>
<?php
	}

	/**
	 * Handle widget configuration
	 */
	function widget_control () {
		$options = $newoptions = $this->get_opts();
		if ( !empty($_POST["flexo-submit"]) &&
		     check_admin_referer('flexo-archives-widget-options') )
		{
			$newoptions[self::OPT_COUNT] = isset($_POST['flexo-count']);
			$newoptions[self::OPT_ANIMATE] = isset($_POST['flexo-animate']);
			$newoptions[self::OPT_WTITLE] = strip_tags(stripslashes($_POST["flexo-title"]));
		}

		if ($options != $newoptions) {
			$options = $newoptions;
			$this->set_opts($options);
		}

		$count = $this->count_enabled() ? 'checked="checked"' : '';
		$animate = $this->animation_enabled() ? 'checked="checked"' : '';
		$title = $this->widget_title();

		wp_nonce_field('flexo-archives-widget-options');
?>
  <p><label for="flexo-title"><?php _e('Title:', 'flexo-archives'); ?> <input style="width: 90%;" id="flexo-title" name="flexo-title" type="text" value="<?php echo $title; ?>" /></label></p>
  <p style="text-align:right;margin-right:40px;"><label for="flexo-animate"><?php _e('Animate lists', 'flexo-archives'); ?> <input class="checkbox" type="checkbox" <?php echo $animate; ?> id="flexo-animate" name="flexo-animate"/></label></p>
  <p style="text-align:right;margin-right:40px;"><label for="flexo-count"><?php _e('Show post counts', 'flexo-archives'); ?> <input class="checkbox" type="checkbox" <?php echo $count; ?> id="flexo-count" name="flexo-count"/></label></p>
  <input type="hidden" id="flexo-submit" name="flexo-submit" value="1" />
<?php
	}

	/**
	 * Helper function to print first bit of year list
	 */
	function year_start_tags ($year = '') {
		$link_title = __('Year %s archives', 'flexo-archives');

		// Ugly strings used in building the tags
		$year_start = '<ul><li><a href="%s" class="flexo-link" ';
		$year_start .= 'id="flexo-%s"  title="' . $link_title . '">';
		$year_start .= '%s</a><ul class="flexo-list">';

		return sprintf($year_start, get_year_link($year), $year, $year, $year);
	}

	/**
	 * Perform database query to get archives.  Archives are sorted in
	 * *descending* order or year and *ascending* order of month
	 *
	 * Returns: result of query if successful, null otherwise
	 */
	function query_archives () {
		global $wpdb;

		// Support archive filters other plugins may have inserted
		$join = apply_filters('getarchives_join', '');
		$default_where = "WHERE post_type='post' AND post_status='publish'";
		$where = apply_filters('getarchives_where', $default_where);

		// Query string
		$qstring = "SELECT DISTINCT YEAR(post_date) AS `year`,";
		$qstring .= " MONTH(post_date) AS `month`,";
		$qstring .= " count(ID) AS posts FROM  $wpdb->posts ";
		$qstring .= $join . ' ';
		$qstring .= $where;
		$qstring .= " GROUP BY YEAR(post_date), MONTH(post_date)";
		$qstring .= " ORDER BY YEAR(post_date) DESC, MONTH(post_date) ASC";

		// Query database
		$flexo_results = $wpdb->get_results($qstring);

		// Check we actually got results
		if ($flexo_results) {
			return $flexo_results;
		} else {
			// No results or database error
			return null;
		}
	}

	/**
	 * Constructs the nested unordered lists from data obtained from
	 * the database.
	 *
	 * Returns: An HTML fragment containing the archives lists
	 */
	function build_archives_list ($count = false) {
		global $wp_locale;
		$list_html = "";

		// Get archives from database
		$results = $this->query_archives();

		// Log and retrun an error if query failed.
		if (is_null($results)) {
			$error_str = __('Database query unexpectedly failed.', 'flexo-archives');
			error_log(__('ERROR: ', 'flexo-archives') . __FILE__ . 
				  '(' . __LINE__ . ') ' .  $error_str);
			return "<p>$error_str</p>";
		}
		
		// Detect year change in loop.
		$a_year = '0';

		// Loop over results and print our archive lists
		foreach ($results as $a_result) {
			$before = '';
			$after = '';

			if ($a_result->year != $a_year) {
				// If not first iteration, close previous list
				if ($a_year != '0')
					$list_html .= '</ul></li></ul>';

				$a_year = $a_result->year;
				$list_html .= $this->year_start_tags($a_result->year) . "\n";
			}

			$url = get_month_link($a_result->year, $a_result->month);
			$text = sprintf(__('%1$s'), $wp_locale->get_month($a_result->month));

			// Append number of posts in month, if they want it
			if ($count)
				$after = '&nbsp;(' . $a_result->posts . ')' . $after;

			$list_html .= get_archives_link($url, $text, 'html', $before, $after);
		}

		// Close the last list
		$list_html .= '</ul></li></ul>';

		return $list_html;
	}

	/**
	 * Output the archive list as a sidebar widget
	 *
	 * Arguments: $args array passed by WordPress's widgetized
	 * sidebar code
	 */
	function widget_archives ($args) {
		extract($args);

		// Fetch widget options
		$title = $this->widget_title();
		$count = $this->count_enabled();

		// Print out the title
		echo $before_widget; 
		echo $before_title . $title . $after_title;

		// Print out the archive list
		echo $this->build_archives_list($count);

		// Close out the widget
		echo $after_widget; 
	}

	/**
	 * Attach JavaScript to normal pages if the standalone archives
	 * function is enabled
	 */
	function enqueue_standalone_scripts () {
		if (!is_admin() && $this->standalone_enabled()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('flexo', $this->script_url(), array('jquery'),
					  '2.0');
		}
	}

	/**
	 * Helper function that prints the url for our javascript
	 */
	function script_url () {
		$url = WP_PLUGIN_URL . '/' . $this->flexo_dir . '/';

		if ($this->animation_enabled()) {
			$url .= self::FLEXO_ANIM_JS;
		} else {
			$url .= self::FLEXO_JS;
		}

		return $url;
	}

	/**
	 * Register our widgets with the widget system and add a
	 * callback to print our CSS
	 */
	function register_widget () {
		$name = __('Flexo Archives', 'flexo-archives');
		$desc = __('Your archives as an expandable list of years', 'flexo-archives');
		$widget_cb = array(&$this, 'widget_archives');
		$control_cb = array(&$this, 'widget_control');
		$css_class = 'flexo';

		// Tell the dynamic sidebar about our widget
		if (function_exists('wp_register_sidebar_widget')) {
			$widget_ops = array('class' => $css_class, 'description' => $desc);
			$control_ops = array('width' => 250, 'height' => 100, 'id_base' => 'flexo-archives');
			$id = 'flexo-archives'; // Never never never translate an id

			wp_register_sidebar_widget($id, $name, $widget_cb, $widget_ops);
			wp_register_widget_control($id, $name, $control_cb, $control_ops);
		}

		// Add CSS and JavaScript to header if we're active
		if (is_active_widget(array(&$this, 'widget_archives'))) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('flexo', $this->script_url(), array('jquery'), '2.0');
		}
	}

	/**
	 * Uninstall Function. Deletes plugin configuration from the
	 * database.
	 */
	function uninstall () {
		$options = $this->get_opts();

		if (is_array($options)) {
			delete_option(self::OPTIONS_NAME);
		}
	}
}

/**
 * Output the archive lists as a standalone function, for users
 * can't or don't want to use the widget.
 */
function flexo_standalone_archives () {
	$archives = new FlexoArchives();

	if ($archives->standalone_enabled()) {
		echo $archives->build_archives_list($archives->count_enabled());
	}
}

$flexo_archives = & new FlexoArchives();
?>
