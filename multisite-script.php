<?php
/**
* Plugin Name: Network Wide Custom Code
* Plugin URI: https://www.brainstormforce.com/
* Description: This plugin is for WordPress Multisite setup. It allows to add custom CSS & JS code in the network admin which will be enqueued on all sites under the network. The custom code can be anything like Google analytics, Facebook Pixel or a simple CSS snippet.
* Version: 1.0.3
* Author: Brainstorm Force
* Author URI: https://www.brainstormforce.com/
 * Text Domain: nwcc
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
			if( function_exists( 'switch_to_blog' ) ) {
				$this->current_blog = get_current_blog_id();
				switch_to_blog( 1 );
				$this->multisite_script_option = get_option( 'multisite_script_option' );
				switch_to_blog( $this->current_blog );

				add_action( 'network_admin_menu', array( $this, 'add_plugin_page' ), 9999 );
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'wp_head', array( $this, 'wp_head' ) );
				add_action( 'wp_footer', array( $this, 'wp_footer' ) );
				add_action( 'init', array( $this, 'init' ) );
				add_action( 'init', array( $this, 'admin_post_edit_options' ) );
				$this->load_plugin_textdomain();
			} else {
	    		add_action( 'admin_notices', array( $this, 'error_notice' ) );
	    	}
		}

		/*
		 * Function Name: error_notice
		 * Function Description: Admin notice
		 */

		public function error_notice() {
			$msg = __( '<strong>Network Wide Custom Code</strong> works for WordPress Multisite setup only.', 'nwcc');
			echo "<div class=\"error\"> <p>" . $msg . "</p></div>"; 
		}


		public function load_plugin_textdomain() {
			//Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'nwcc' );

			//Setup paths to current locale file
			$mofile_global = trailingslashit( WP_LANG_DIR ) . 'plugins/network-wide-custom-code/' . $locale . '.mo';
			$mofile_local  = trailingslashit( BB_ULTIMATE_ADDON_DIR ) . 'languages/' . $locale . '.mo';

			if ( file_exists( $mofile_global ) ) {
				//Look in global /wp-content/languages/plugins/network-wide-custom-code/ folder
				return load_textdomain( 'nwcc', $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				//Look in local /wp-content/plugins/network-wide-custom-code/languages/ folder
				return load_textdomain( 'nwcc', $mofile_local );
			} 

			//Nothing found
			return false;
		}

		public function admin_post_edit_options(){

			if( isset( $_GET['page'] ) ) {
				if( $_GET['page'] == 'multisite-script' ) {
					if( isset( $_POST['multisite_script_option'] ) ) {
						update_option( 'multisite_script_option', $_POST['multisite_script_option'] );
						wp_redirect( network_admin_url( 'admin.php?page=multisite-script' ) );
						exit;
					}

				}
			}
		}

		public function init() {
			$wp_version = get_bloginfo('version');
			$p = '#(\.0+)+($|-)#';
			$ver1 = preg_replace($p, '', $wp_version);
		    $ver2 = preg_replace($p, '', '4.6.0');
		    $blogs = ( version_compare( $ver1, $ver2 ) < 0 ) ? wp_get_sites() : get_sites();

			if( count( $blogs ) > 0 ) {
				foreach( $blogs as $b ) {
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
			echo $this->multisite_script_option['header_script'];
		}

		/*
		 * Function Name: wp_footer
		 * Function Description: Add a script in footer
		 */

		public function wp_footer() {
			echo $this->multisite_script_option['footer_script'];
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
	            __('These scripts will be printed to the <code>&lt;head&gt;</code> section.','nwcc'), // Title
	            array( $this, 'header_script_callback' ), // Callback
	            'multisite-script-admin', // Page
	            'multisite_script_setting' // Section
	        );

	        add_settings_field(
	            'footer_script', // ID
	            __( 'These scripts will be printed to the <code>&lt;footer&gt;</code> section.','nwcc'), // Title
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
				__("Custom Code","nwcc"),
				__("Custom Code","nwcc"),
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
					<h1><?php echo __( 'Network Wide Custom Code', 'nwcc' ); ?></h1>
					<div class="about-text about-text"><?php echo __( 'This plugin is for WordPress Multisite setup. It allows to add custom CSS & JS code in the network admin which will be enqueued on all sites under the network. The custom code can be anything like Google analytics, Facebook Pixel or a simple CSS snippet.', 'nwcc' ); ?></div>
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
	            $new_input['header_script'] = $input['header_script'];


			if( isset( $input['footer_script'] ) )
	            $new_input['footer_script'] = stripslashes( $input['footer_script'] );

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

	    	$script = ( isset( $this->multisite_script_option['header_script'] ) ) ? stripslashes( $this->multisite_script_option['header_script'] ) : '';
	    	$placeholder = __('Add your script here.', 'nwcc');

	        printf(
	        	'<textarea id="header_script" name="multisite_script_option[header_script]" rows="4" cols="50" placeholder="%s">%s</textarea>', $placeholder, stripslashes($script)
	        );
	    }

	    /*
	     * Callback function for Footer Script input
	     */
	    public function footer_script_callback() {

	    	$script = ( isset( $this->multisite_script_option['footer_script'] ) ) ? stripslashes( $this->multisite_script_option['footer_script'] ) : '';
	    	$placeholder = __('Add your script here.', 'nwcc');

	        printf(
				'<textarea id="footer_script" name="multisite_script_option[footer_script]" rows="4" cols="50" placeholder="%s">%s</textarea>', $placeholder, stripslashes($script)
	        );
	    }
	}

	new Multisite_Script_Class;
}
