<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @link              http://www.marcianneoday.com
 * @since             1.0.0
 * @package           Fbeventslist
 * @wordpress-plugin
 * Plugin Name:       Facebook Events List
 * Plugin URI:        https://github.com/marcianne/Facebook-Events-List
 * Description:       An unbranded, minimally-styled list of facebook events with options page.
 * Version:           1.2
 * Author:            Marcianne O'Day
 * Author URI:        http://www.marcianneoday.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fbeventslist
 * Domain Path:       /languages
 */
 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Define plugin path
define( 'FBEL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FBEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Includes & dependencies
require( FBEL_PLUGIN_PATH . 'lib/webdevstudios/cmb2/init.php');
// require options page fields & setup
require( FBEL_PLUGIN_PATH . 'fbel-options.php');
// if it's not already loaded by another plugin, add mustardbees cmb-field-select2 https://github.com/mustardBees/cmb-field-select2
if (! function_exists('pw_select2_enqueue')){ // pw_select2_enqueue() is just an arbitrary funciton in cmb-field-select2
require_once( FBEL_PLUGIN_PATH . 'lib/cmb-field-select2/cmb-field-select2.php');
}
// load plugin updater
add_action('admin_init', 'fbel_load_updater', 99);
function fbel_load_updater() {
// load and instantiate github updater 
	include_once(FBEL_PLUGIN_PATH . 'lib/updater/updater.php');
	if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
        $config = array(
            'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
            'proper_folder_name' => 'Facebook-Events-List', // this is the name of the folder your plugin lives in
            'api_url' => 'https://api.github.com/repos/marcianne/Facebook-Events-List', // the GitHub API url of your GitHub repo
            'raw_url' => 'https://raw.github.com/marcianne/Facebook-Events-List/master', // the GitHub raw url of your GitHub repo
            'github_url' => 'https://github.com/marcianne/Facebook-Events-List', // the GitHub url of your GitHub repo
            'zip_url' => 'https://github.com/marcianne/Facebook-Events-List/zipball/master', // the zip url of the GitHub repo
            'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
            'requires' => '3.0', // which version of WordPress does your plugin require?
            'tested' => '4.3', // which version of WordPress is your plugin tested up to?
            'readme' => 'README.md' // which file to use as the readme for the version number
          //  'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
        );
        new WP_GitHub_Updater($config);
    }
}



function fbel_output(){ // retrieves, formats and displays events from JSON fb feed 
// Options from options page created earlier
$fb_page_id = fbel_get_option( 'fb_page_id' ); // get page ID to retrieve events
$app_id = fbel_get_option( 'fb_app_id' ); // get app ID 
$app_secret = fbel_get_option( 'fb_app_secret' ); // get app secret
$tz_1 = fbel_get_option('event_tz1'); // get first timezone (used to calculate date)
$tz_id_1 = fbel_get_option('event_tz_id1'); // get preferred manner of displaying timezone (appears next to calculated time)
$tz_2 = fbel_get_option('event_tz2'); // get second timezone (optional)
$tz_id_2 = fbel_get_option('event_tz_id2'); // get preferred manner of display for second selected timezone
$readmore_link = fbel_get_option('read_more'); // get custom text for the "read more" link. 
$desc_length = fbel_get_option('desc_length'); // modify length of the description

if(fbel_get_option('event_fields')){
$fields = implode(',', fbel_get_option('event_fields')); // get all the selected event fields to retrieve in the JSON link
}
else {
$fields="description,end_time,place,name,id,start_time,timezone,cover,picture"; // default display, only if fields aren't specified in options page
}
$fbv = "v2.4";

if ($fb_page_id && $app_id && $app_secret) { // conditions to check if there are valid options entered 
$json = file_get_contents("https://graph.facebook.com/{$fbv}/{$fb_page_id}/events?fields={$fields}&access_token={$app_id}|{$app_secret}"); // get contents of JSON feed using link
$events = json_decode($json, true, 512, JSON_BIGINT_AS_STRING); // decode JSON 
$event_array = file_get_contents("https://graph.facebook.com/{$fbv}/{$fb_page_id}/events?fields={$fields}&access_token={$app_id}|{$app_secret}");
$event_count = fbel_get_option('events_num'); // determine how many events to display, set on options page
/*
?>

<div class="event_list_title">
<a href="<?php ?>">
<?php echo fbel_get_option('cal_title'); ?>
</a></div> <!~~ title container, outside events loop --> 
<?php
*/

for($event_index=0; $event_index<$event_count; $event_index++){

// get events date & time from facebook

$start_date = date( 'l, F jS', strtotime($events['data'][$event_index]['start_time']));
$start_time  = date( 'H:i e', strtotime($events['data'][$event_index]['start_time']) );

// set timezone to UTC (do I need this step?)
$date = date_create($start_time, timezone_open('Etc/GMT+0'));
date_timezone_set($date, timezone_open($tz_1));
$tz1_start_time = date_format($date, 'g:ia');

if ($tz_2){
date_timezone_set($date, timezone_open($tz_2));
$tz2_start_time = date_format($date, 'g:ia');
}
$eid = $events['data'][$event_index]['id'];
$event_url = "http://facebook.com/{$eid}/"; 
$pic_sm = isset($events['data'][$event_index]['cover']['source']) ? $events['data'][$event_index]['cover']['source'] : "";
$name = $events['data'][$event_index]['name'];
$description = isset($events['data'][$event_index]['description']) ? $events['data'][$event_index]['description'] : "";
$description_excerpt = substr($description, 0, $desc_length)."<a href='https://www.facebook.com/events/{$eid}' target='_blank' class='inline_link'>".$readmore_link ."</a>";
$place_name = isset($events['data'][$event_index]['place']['name']) ? $events['data'][$event_index]['place']['name'] : "";
$city = isset($events['data'][$event_index]['place']['location']['city']) ? $events['data'][$event_index]['place']['location']['city'] : "";
$street = isset($events['data'][$event_index]['place']['location']['street']) ? $events['data'][$event_index]['place']['location']['street'] : "";
$country = isset($events['data'][$event_index]['place']['location']['country']) ? $events['data'][$event_index]['place']['location']['country'] : "";
$state = isset($events['data'][$event_index]['place']['location']['zip']) ? $events['data'][$event_index]['place']['location']['state'] : "";
$zip = isset($events['data'][$event_index]['place']['location']['zip']) ? $events['data'][$event_index]['place']['location']['zip'] : "";
$event_times = $tz1_start_time. " ". $tz_id_1;
if ($tz_2){
$event_times = $tz1_start_time. " ". $tz_id_1 . "/".$tz2_start_time. " ". $tz_id_2;
}

if($place_name && $city && $street && $state && $zip){
$location="{$place_name}<br>{$street}<br> {$city}, {$state} {$zip}";
} elseif ($place_name && $state) {
$location = "{$place_name} <br> {$state}";
} 
elseif ($place_name) {
$location = "{$place_name}";
} else {
$location = "<a href={$event_url}>Location Details</a>";
}

$today = strtotime("now");
$raw_start = strtotime($start_date);

if ($raw_start >= $today){

if ( file_exists(get_stylesheet_directory() .'/fbel-template.php') ){
require  get_stylesheet_directory() . '/fbel-template.php';
}
else {
require 'fbel-template.php';
}
}
}
?>
<!-- </div> <!~~ outer fb events container (outside events loop)~~> -->
<?php } else {

echo '<div> Set <a href=' . admin_url('admin.php?page=fbel_options') . '>FB Events List options,</a> in order to view list of facebook events</div>';

}





}

// create shortcode to run fbel_output() in designated spot
function fbel_shortcode( $atts ) {

	// Attributes
	extract( shortcode_atts(array('div_title' => 'Events', 'events_qty' => 5, ), $atts ));
	// Code
$fbel_output = fbel_output();
return $fbel_output;
}
add_shortcode( 'fbel', 'fbel_shortcode' );

// allow shortcodes to run from widgets
add_filter('widget_text', 'do_shortcode');

/**
 * Adds fbel_Widget widget.
 */
class fbel_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	 
	function __construct() {
		parent::__construct(
			'fbel_widget', // Base ID
			__( 'Facebook Events List', 'text_domain' ), // Name
			array( 'description' => __( 'Display the a list of Facebook events. Uses settings from "Facebook Events List" options page.', 'text_domain' ), ) // Args 
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	 	 
	public function widget( $args, $instance ) {     
	    
	    //Load default styles 
        
        $load_styles_option = fbel_get_option( 'disable_styles' );
        if ($load_styles_option != "disable") {
        $plugin_url = plugin_dir_url( __FILE__ );
        wp_enqueue_style( 'fbel_styles', $plugin_url . '/output-style.css' );
        }
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
	    echo "<div id='fb_events_container'>";
		echo do_shortcode('[fbel]');
		echo "</div><!-- end #fb_events_container -->";
		
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Events', 'text_domain' );
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

} // class fbel_Widget

// register fbel_Widget widget
function register_fbel_widget() {
    register_widget( 'fbel_Widget' );
}
add_action( 'widgets_init', 'register_fbel_widget' );


/* Only load admin CSS on admin page */
if (is_admin('page=fbel_options')) {
add_action('admin_print_styles', 'fbel_options_enqueue');
add_action('wp_enqueue_scripts', 'fbel_options_enqueue');
function fbel_options_enqueue($hook) {
     wp_enqueue_style("fbel_styles", FBEL_PLUGIN_URL . '/options-style.css', false, false, "all");
}
}
