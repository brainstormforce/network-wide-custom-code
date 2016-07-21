<?php
/**
* Plugin Name: Network Wide Custom Code
* Plugin URI: https://www.brainstormforce.com/
* Description: This plugin is for WordPress Multisite setup. It allows to add custom CSS & JS code in the network admin which will be enqueued on all sites under the network. The custom code can be anything like Google analytics, Facebook Pixel or a simple CSS snippet.
* Version: 1.0.1
* Author: Brainstorm Force
* Author URI: https://www.brainstormforce.com/
*/

//Block direct access to plugin files
defined( 'ABSPATH' ) or die();

if(!class_exists('Multisite_Script_Class')){
	class Multisite_Script_Class{

		//Class variables
		private $multisite_script_option;
		private $current_blog;
		
		/*
		 * Function Name: __construct
		 * Function Description: Constructor
		 */
		
		function __construct() {
			$this->current_blog = get_current_blog_id();
			switch_to_blog( 1 );
			$this->multisite_script_option = get_option( 'multisite_script_option' );
			switch_to_blog( $this->current_blog );

			add_action( 'network_admin_menu', array( $this, 'add_plugin_page' ), 9999 );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'wp_head', array( $this, 'wp_head' ) );
			add_action( '
				', array( $this, 'wp_footer' ) );
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'init', array( $this, 'admin_post_edit_options' ) );
		}

		public function admin_post_edit_options(){
			if( $_GET['page'] == 'multisite-script' ) {
				//echo '<xmp>'; print_r($_POST['multisite_script_option']); echo '</xmp>'; //die;
				if( isset( $_POST['multisite_script_option'] ) ) {
					update_option( 'multisite_script_option', $_POST['multisite_script_option'] );
					wp_redirect( admin_url( '/network/admin.php?page=multisite-script' ) );
					exit;
				}
			}
		}

		public function init() {
			$blogs = get_blog_list($start, $num, $deprecated);
			
			if( count( $blogs ) > 0 ) {
				foreach( $blogs as $b ) {
					//echo '<xmp>'; print_r(get_option( 'multisite_script_option' )); echo '</xmp>';
					//var_dump(switch_to_blog( $b['blog_id'] ));

					add_action( 'wp_head', array( $this, 'wp_head' ) );
					add_action( 'wp_footer', array( $this, 'wp_footer' ) );
				}
			}
			switch_to_blog( $this->current_blog );
		}

		/*
		 * Function Name: wp_head
		 * Function Description: Add a script in header
		 */

		public function wp_head() {
			echo stripslashes($this->multisite_script_option['header_script']);
		}

		/*
		 * Function Name: wp_footer
		 * Function Description: Add a script in footer
		 */

		public function wp_footer() {
			echo stripslashes($this->multisite_script_option['footer_script']);
		}

		/*
		 * Function Name: admin_init
		 * Function Description: Admin initialization
		 */

		public function admin_init() {

        	// Register the setting tab
			register_setting(
	            'multisite_script_group', // Option group
	            'multisite_script_option', // Option name
	            array( $this, 'sanitize' ) // Sanitize
	        );

	        add_settings_section(
	            'multisite_script_setting', // ID
	            '', // Title
	            array( $this, 'print_section_info' ), // Callback
	            'multisite-script-admin' // Page
	        );

	        add_settings_field(
	            'header_script', // ID
	            'These scripts will be printed to the <code>&lt;head&gt;</code> section.', // Title
	            array( $this, 'header_script_callback' ), // Callback
	            'multisite-script-admin', // Page
	            'multisite_script_setting' // Section
	        );

	        add_settings_field(
	            'footer_script', // ID
	            'These scripts will be printed to the <code>&lt;footer&gt;</code> section.', // Title
	            array( $this, 'footer_script_callback' ), // Callback
	            'multisite-script-admin', // Page
	            'multisite_script_setting' // Section
	        );
		}


		/*
		 * Function Name: add_plugin_page
		 * Function Description: Add a setting page in WP Setting
		 */
		public function add_plugin_page() {

			add_menu_page (
				__("Custom Code","smile"),
				__("Custom Code","smile"),
				"administrator",
				'multisite-script',
				array( $this, 'create_admin_page' ),
				'dashicons-editor-code',
				99
			);
	    }


	    /*
		 * Function Name: create_admin_page
		 * Function Description: callback function to callback admin setting page
		 */
	    public function create_admin_page() {
	        ?>
	        <div class="wrap about-wrap">
	            <div class="heading-section">
					<h1><?php echo __( 'Network Wide Custom Code', 'smile' ); ?></h1>
					<div class="about-text about-text"><?php echo __( 'This plugin is for WordPress Multisite setup. It allows to add custom CSS & JS code in the network admin which will be enqueued on all sites under the network. The custom code can be anything like Google analytics, Facebook Pixel or a simple CSS snippet.', 'smile' ); ?></div>
					<div class="badge"></div>
					<div class="tabs">
						<form method="post" action="" autocomplete="off" id="multisite_admin_setting_form">
						<?php
							settings_fields( 'multisite_script_group' ); 
							do_settings_sections( 'multisite-script-admin' );
							submit_button();
						?>
						</form>
					</div>
	        	</div>
	        </div>
	        <?php
	    }


	    /*
     	 * Sanitize each setting field as needed
	     *
	     * @param array $input Contains all settings fields as array keys
	     */
	    public function sanitize( $input ) {

	        $new_input = array();
	        if( isset( $input['header_script'] ) )
	            $new_input['header_script'] = stripslashes($input['header_script']);

			if( isset( $input['footer_script'] ) )
	            $new_input['footer_script'] = stripslashes($input['footer_script']);

	        return $new_input;
	    }


	    /*
		 * Function Name: print_section_info
		 * Function Description: Prints information about the section
		 */
	    public function print_section_info() {
	        //Nothing to do here
	    }

	    /*
	     * Callback function for Header Script input
	     */
	    public function header_script_callback() {
	    	$script = ( isset( $this->multisite_script_option['header_script'] ) ) ? stripslashes($this->multisite_script_option['header_script']) : '';
	        printf(
	        	'<textarea id="header_script" name="multisite_script_option[header_script]" rows="4" cols="50" placeholder="Add your script here.">%s</textarea>', $script
	        );
	    }

	    /*
	     * Callback function for Footer Script input
	     */
	    public function footer_script_callback() {
	    	$script = ( isset( $this->multisite_script_option['footer_script'] ) ) ? stripslashes($this->multisite_script_option['footer_script']) : '';
	        printf(
				'<textarea id="footer_script" name="multisite_script_option[footer_script]" rows="4" cols="50" placeholder="Add your script here.">%s</textarea>', $script
	        );
	    }
	}

	new Multisite_Script_Class;
}
