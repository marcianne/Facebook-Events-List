<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/* CMB2 Options Page for Setting fbel options */
// function to populate designated fields with timezones list 
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
/**
 * CMB2 Plugin Options
 * @version 0.1.0
 */
class Fbel_Admin {

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
	protected $title = '';

	/**
	 * Options Page hook
	 * @var string
	 */
	protected $options_page = '';

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'FB Events List', 'fbel' );
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
		?>
		<div class="wrap cmb2-options-page <?php echo $this->key; ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<div id="fbel_instructions">To activate the Facebook Events List, register a facebook app for your facebook page & enter the app secret and app id below.  to output the list, either add it as a widget, or place the [fbel] shortcode in a Wordpress template file in the location where you'd like it to appear.</div>
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
         'end_time' => 'End Time',
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
            'desc' => __( 'Events list has minimal styling built in by design. To completely disable default styles, check this box.', 'fbel' ),
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
 * Helper function to get/return the Fbel_Admin object
 * @since  0.1.0
 * @return Fbel_Admin object
 */
function fbel_admin() {
	static $object = null;
	if ( is_null( $object ) ) {
		$object = new Fbel_Admin();
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


