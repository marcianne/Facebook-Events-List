<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * @link              http://www.marcianneoday.com
 * @since             1.0.0
 * @package           Fbeventslist
 * @wordpress-plugin
 * Plugin Name:       Facebook Events List
 * Plugin URI:        https://github.com/marcianne/Facebook-Events-List
 * Description:       An unbranded, minimally-styled list of facebook events with options page.
 * Version:           1.0.2
 * Author:            Marcianne O'Day
 * Author URI:        http://www.marcianneoday.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fbeventslist
 * Domain Path:       /languages
 * GitHub Plugin URI: marcianne/Facebook-Events-List
 * GitHub Branch: 	master
 */
 // If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Define plugin path
define( 'FBEL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'FBEL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// check for and load dependencies 

function fbel_load_dependencies() {
// Check to see if cmb2 is loaded, if not use composer version from plugin folder
if ( ! class_exists( 'CMB2_Bootstrap_210', false ) ) {
require_once( FBEL_PLUGIN_PATH . "wp-content/plugins/cmb2/init.php");
}
// load github updater from composer plugin dir if it's not already loaded
if ( ! class_exists( 'WPUpdatePhp' ) ) {
require_once ( FBEL_PLUGIN_PATH . '/wp-content/plugins/github-updater/github-updater.php' );
}
// if it's not already loaded by another plugin, add mustardbees cmb-field-select2 https://github.com/mustardBees/cmb-field-select2
if (! function_exists('pw_select2_enqueue')){ // pw_select2_enqueue() is just an arbitrary funciton in cmb-field-select2
require_once( FBEL_PLUGIN_PATH . '/cmb-field-select2/cmb-field-select2.php');
}
}

add_action('admin_init', 'fbel_load_dependencies');

// populate designated pw_select fields with timezones list 
function fbel_cal_timezones(){
static $regions = array(
						DateTimeZone::AFRICA,
						DateTimeZone::AMERICA,
						DateTimeZone::ANTARCTICA,
						DateTimeZone::ASIA,
						DateTimeZone::ATLANTIC,
						DateTimeZone::AUSTRALIA,
						DateTimeZone::EUROPE,
						DateTimeZone::INDIAN,
						DateTimeZone::PACIFIC
						);

$timezones = array();
foreach( $regions as $region )
			{
			$timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
			}

$timezone_offsets = array();
foreach( $timezones as $timezone )
			{
			$tz = new DateTimeZone($timezone);
			$timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
			}

// sort timezone by offset
asort($timezone_offsets);

$timezone_list = array();
		foreach( $timezone_offsets as $timezone => $offset )
		{
		$offset_prefix = $offset < 0 ? '-' : '+';
		$offset_formatted = gmdate( 'H:i', abs($offset) );

		$pretty_offset = "UTC${offset_prefix}${offset_formatted}";

		$timezone_list[$timezone] = "(${pretty_offset}) $timezone";
		}

return $timezone_list;

}

// Setup options page using https://github.com/WebDevStudios/CMB2-Snippet-Library/blob/master/options-and-settings-pages/theme-options-cmb.php
/**
 * CMB2 Theme Options
 * @version 0.1.0
 */

class fbel_Admin {
	/**
 	 * Option key, and option page slug
 	 * @var string
 	 */
	private $key = 'fbel_options';
	/**
 	 * Options page metabox id
 	 * @var string
 	 */
	private $metabox_id = 'fbel_option_metabox';
	/**
	 * Options Page title
	 * @var string
	 */
	protected $title = 'fbel_options';
	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = 'fbel_options';
	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Facebook Events List', 'fbel' );
	}
	/**
	 * Initiate our hooks
	 * @since 0.1.0
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		add_action( 'cmb2_init', array( $this, 'add_options_page_metabox' ) );
	}
	/**
	 * Register our setting to WP
	 * @since  0.1.0
	 */
	public function init() {
		register_setting( $this->key, $this->key );
	}
	/**
	 * Add menu options page
	 * @since 0.1.0
	 */
	public function add_options_page() {
		$this->options_page = add_menu_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		// Include CMB CSS in the head to avoid FOUT
		add_action( "admin_print_styles-{$this->options_page}", array( 'CMB2_hookup', 'enqueue_cmb_css' ) );
	}
	/**
	 * Admin page markup. Mostly handled by CMB2
	 * @since  0.1.0
	 */
	public function admin_page_display() {
	global $fbel;
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<div id="fbel_instructions">To activate the Facebook Events List, register a facebook app for your facebook page & enter the app secret and app id below.  to output the calendar, either add it as a widget, or place the [fbel_cal] shortcode in a Wordpress template file in the location where you'd like it to appear.</div>
			<?php cmb2_metabox_form( $this->metabox_id, $this->key, array( 'cmb_styles' => false ) ); ?>
		</div>
		<?php
	}
	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	function add_options_page_metabox() {

		$cmb = new_cmb2_box( array(
			'id'      => $this->metabox_id,
			'hookup'  => false,
			'show_on' => array(
				// These are important, don't remove
				'key'   => 'options-page',
				'value' => array( $this->key, )
			),
		) );
		 $cmb->add_field( array(
            'name' => __( 'Page ID:', 'fbel' ),
            'desc' => __( 'Facebook page ID', 'fbel' ),
            'id'   => 'fb_page_id',
            'type' => 'text_medium',
            ) );
            // Set our CMB2 fields
            $cmb->add_field( array(
            'name' => __( 'App ID:', 'fbel' ),
            'desc' => __( 'Facebook app ID', 'fbel' ),
            'id'   => 'fb_app_id',
            'type' => 'text_medium',
            ) );

            $cmb->add_field( array(
            'name' => __( 'App Secret:', 'fbel' ),
            'desc' => __( 'Facebook app secret', 'fbel' ),
            'id'   => 'fb_app_secret',
            'type' => 'text_medium',
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Display Event Fields:', 'fbel' ),
            'id'   => 'event_fields',
            'type'    => 'multicheck',
            'options' =>  array(

            'name' => 'Event Title',
            'description' => 'Description',
            'place' => 'Location',
            'start_time' => 'Start Time',
//             'end_time' => 'End Time',
            'cover' => 'Thumbnail',
            ),
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Number of Events:', 'fbel' ),
            'desc' => __( 'How many events should the list display?', 'fbel' ),
            'id'   => 'events_num',
            'type' => 'text_small',
            'default' => 5,
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Description Length:', 'fbel' ),
            'desc' => __( 'Enter max character count for event descriptions.', 'fbel' ),
            'id'   => 'desc_length',
            'type' => 'text_small',
            'default' => 150,
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Link to Full Event Info:', 'fbel' ),
            'desc' => __( 'Modify text of "read more" link to the event details on facebook.', 'fbel' ),
            'id'   => 'read_more',
            'type' => 'text_medium',
            'default' => "...read more",
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Default Styling:', 'fbel' ),
            'desc' => __( 'Basic FB Calendar has minimal styling built in by design. To completely disable default styles, check this box.', 'fbel' ),
            'id'   => 'disable_styles',
            'type' => 'radio',
            'options' => array( 'disable'=>'Disable', 'enable'=>'Enable')
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Timezone #1:', 'fbel' ),
            'desc' => __( '', 'fbel' ),
            'id'   => 'event_tz1',
            'type' => 'pw_select',
            'options' =>  fbel_cal_timezones(),
            ) );

            $cmb->add_field( array(
            'name' => 'Timezone #1 Format:',
            'description' => 'Ex. CST, Central Time, Central Standard Time, Central, etc.',
            'id'   => 'event_tz_id1',
            'type' => 'text_small',
            ) );
            
            $cmb->add_field( array(
            'name' => __( 'Timezone #2 (opt):', 'fbel' ),
            'desc' => __( '', 'fbel' ),
            'id'   => 'event_tz2',
            'type' => 'pw_select',
            'options' =>  fbel_cal_timezones(),
            ) );

            $cmb->add_field( array(
            'name' => 'Timezone #2 Format:',
            'description' => 'Ex. CST, Central Time, Central Standard Time, Central, etc.',
            'id'   => 'event_tz_id2',
            'type' => 'text_small',
            ) );
	}
	
/**
* Public getter method for retrieving protected/private variables
* @since  0.1.0
* @param  string  $field Field to retrieve
* @return mixed          Field value or exception is thrown
*/
public function __get( $field ) {
// Allowed fields to retrieve
if ( in_array( $field, array( 'key', 'metabox_id', 'title', 'options_page' ), true ) ) {
return $this->{$field};
}
throw new Exception( 'Invalid property: ' . $field );
}
}
/**
* Helper function to get/return the fbel_Admin object
* @since  0.1.0
* @return fbel_Admin object
*/
function fbel_admin() {
static $object = null;
if ( is_null( $object ) ) {
$object = new fbel_Admin();
$object->hooks();
}
return $object;
}
/**
* Wrapper function around cmb2_get_option
* @since  0.1.0
* @param  string  $key Options array key
* @return mixed        Option value
*/
function fbel_get_option( $key = '' ) {
return cmb2_get_option( fbel_admin()->key, $key );
}
// Get it started
fbel_admin();




function fbel_calendar(){ // retrieves, formats and displays events from JSON fb feed

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
$fields="description,end_time,place,name,id,start_time,timezone,cover"; // default display, only if fields aren't specified in options page
}
$fbv = "v2.4";
$json = file_get_contents("https://graph.facebook.com/{$fbv}/{$fb_page_id}/events?fields={$fields}&access_token={$app_id}|{$app_secret}"); // get contents of JSON feed using link
$events = json_decode($json, true, 512, JSON_BIGINT_AS_STRING); // decode JSON events list
$event_count = fbel_get_option('events_num'); // determine how many events to display, set on options page
/*
?>
<div class="calendar_title">
<a href="<?php ?>">
<?php echo fbel_get_option('cal_title'); ?>
</a></div> <!~~ title container, outside events loop --> 
<?php
*/
for($event_index=0; $event_index<$event_count; $event_index++){

// get events date & time from facebook

$start_date = date( 'D, F jS', strtotime($events['data'][$event_index]['start_time']));
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
$pic_sm = "https://graph.facebook.com/{$eid}/picture?type=normal";
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
} elseif ($place_name) {
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
<?php
}

// create shortcode to run fbel_calendar() in designated spot
function basic_fb_cal_shortcode( $atts ) {

	// Attributes
	extract( shortcode_atts(array('div_title' => 'Events', 'events_qty' => 5, ), $atts ));
	// Code
$calendar_output = fbel_calendar();
return $calendar_output;
}
add_shortcode( 'basic_fb_calendar', 'basic_fb_cal_shortcode' );

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
			__( 'Basic Facebook Calendar', 'text_domain' ), // Name
			array( 'description' => __( 'Display the a list of Facebook events. Uses settings from "Basic Facebook Calendar" options page.', 'text_domain' ), ) // Args 
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
		echo do_shortcode('[basic_fb_calendar]');
		echo "</div> <!-- end #fb_events_container -->";
		
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
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
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




