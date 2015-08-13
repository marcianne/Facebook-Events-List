<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.marcianneoday.com
 * @since      1.0.0
 *
 * @package    Fbeventslist
 * @subpackage Fbeventslist/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fbeventslist
 * @subpackage Fbeventslist/admin
 * @author     Marcianne O'Day <oday.marcianne@me.com>
 */
class Fbeventslist_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fbeventslist_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fbeventslist_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fbeventslist-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fbeventslist_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fbeventslist_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fbeventslist-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Autoload composer dependencies cmb2 and github-updater
	 *
	 * @since    1.0.0
	 */
	 
	 public function fbeventslist_composer() {
	 require('../lib/autoload.php');
	 require('../wp-content/github-updater/github-updater.php');
	  require('../wp-content/cmb2/init.php');
	 }


}
