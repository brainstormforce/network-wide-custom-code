<?php
/**
* Plugin Name: Network Wide Custom Code
* Plugin URI: https://www.brainstormforce.com/
* Description: This plugin is for WordPress Multisite setup. It allows to add custom CSS & JS code in the network admin which will be enqueued on all sites under the network. The custom code can be anything like Google analytics, Facebook Pixel or a simple CSS snippet.
* Version: 1.0.0
* Author: Brainstorm Force
* Author URI: https://www.brainstormforce.com/
* Text Domain: nwcc
*
* @package NWCC.
*/

//Block direct access to plugin files.
defined( 'ABSPATH' ) or die();

if ( ! defined( 'NWCC_ROOT' ) ) {
	define( 'NWCC_ROOT', dirname( plugin_basename( __FILE__ ) ) );
}

if ( ! defined( 'NWCC_DIR' ) ) {
	define( 'NWCC_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Multisite_Script_Class initial setup
 *
 * @since 1.0.0
 */
if( ! class_exists( 'Multisite_Script_Class' ) ) {

	/**
	 * Multisite_Script_Class initial setup
	 */
	class Multisite_Script_Class{

		/**
		 * Member Variable for update sitewide option
		 *
		 * @var multisite_script_option
		 */
		private $multisite_script_option;

		/**
		 * Member Variable for getting current site ID
		 *
		 * @var current_blog
		 */
		private $current_blog;
		
		/**
		 *  Constructor
		 */
		public function __construct() {

			if( function_exists( 'switch_to_blog' ) ) {

				$this->current_blog = get_current_blog_id();
				switch_to_blog( 1 );
				$this->multisite_script_option = get_site_option( 'multisite_script_option' );
				switch_to_blog( $this->current_blog );

				// Add required actions.
				add_action( 'network_admin_menu', array( $this, 'add_plugin_page' ), 9999 );
				add_action( 'admin_init', array( $this, 'admin_init' ) );
				add_action( 'init', array( $this, 'admin_post_edit_options' ) );
				add_action( 'init', array( $this, 'init' ) );

				// Load textdomain translations.
				$this->load_textdomain();
			} else {
	    		add_action( 'admin_notices', array( $this, 'error_notice' ) );
	    	}
		}

		/**
		 * Function Name: error_notice
		 * Function Description: Admin notice
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function error_notice() {
			$msg = __( '<strong>Network Wide Custom Code</strong> works for WordPress Multisite setup only.', 'nwcc' );
			echo "<div class=\"error\"> <p>" . $msg . "</p> </div>"; 
		}

		/**
		 * Load nwcc Text Domain.
		 * This will load the translation textdomain depending on the file priorities.
		 *      1. Global Languages /wp-content/languages/network-wide-custom-code/ folder
		 *      2. Local dorectory /wp-content/plugins/network-wide-custom-code/languages/ folder
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			/**
			 * Filters the languages directory path to use for AffiliateWP.
			 *
			 * @param string $lang_dir The languages directory path.
			 */
			$lang_dir = apply_filters( 'nwcc_languages_directory', NWCC_ROOT . '/languages/' );

			load_plugin_textdomain( 'nwcc', false, $lang_dir );
		}

		/**
		 * Function Name: admin_post_edit_options
		 * Function Description: Sanitize text fields and update site option
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function admin_post_edit_options() {

			if( isset( $_GET['page'] ) ) {

				if( $_GET['page'] == 'multisite-script' ) {

					if( isset( $_POST['multisite_script_option'] ) ) {

						$options['header_style'] = $_POST['multisite_script_option']['header_style'];
						$options['header_script'] = $_POST['multisite_script_option']['header_script'];

						$options['footer_style'] = $_POST['multisite_script_option']['footer_style'];
						$options['footer_script'] = $_POST['multisite_script_option']['footer_script'];

						update_site_option( 'multisite_script_option', $options );
						wp_redirect( network_admin_url( 'admin.php?page=multisite-script' ) );
						exit;
					}
				}
			}
		}

		/**
		 * Function Name: init
		 * Function Description: Initiator and add required WP actions
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function init() {

			$wp_version = get_bloginfo( 'version' );
			$p = '#(\.0+)+($|-)#';
			$ver1 = preg_replace( $p, '', $wp_version );
		    $ver2 = preg_replace( $p, '', '4.6.0' );
		    $blogs = ( version_compare( $ver1, $ver2 ) < 0 ) ? wp_get_sites() : get_sites();

			if( count( $blogs ) > 0 ) {
				foreach( $blogs as $b ) {
					// Add required actions for wp_head script and style.
					add_action( 'wp_head', array( $this, 'wp_head_style' ) );
					add_action( 'wp_head', array( $this, 'wp_head_script' ) );

					// Add required actions for wp_footer script and style.
					add_action( 'wp_footer', array( $this, 'wp_footer_style' ) );
					add_action( 'wp_footer', array( $this, 'wp_footer_script' ) );
				}
			}
			switch_to_blog( $this->current_blog );
		}

		/**
		 * Function Name: wp_head_style
		 * Function Description: Add a style CSS in header
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function wp_head_style() {
			echo '<style type="text/css">' . wp_unslash( $this->multisite_script_option['header_style'] ) . '</style>';
		}

		/**
		 * Function Name: wp_head_script
		 * Function Description: Add a script JS in header
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function wp_head_script() {
			echo '<script type="text/javascript">' . wp_unslash( $this->multisite_script_option['header_script'] ) . '</script>';
		}

		/**
		 * Function Name: wp_footer_style
		 * Function Description: Add a style CSS in footer
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function wp_footer_style() {
			echo '<style type="text/css">' . wp_unslash( $this->multisite_script_option['footer_style'] ) . '</style>';
		}

		/**
		 * Function Name: wp_footer_script
		 * Function Description: Add a script in footer
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function wp_footer_script() {
			echo '<script type="text/javascript">' . wp_unslash( $this->multisite_script_option['footer_script'] ) . '</script>';
		}

		/**
		 * Function Name: admin_init
		 * Function Description: Admin initialization
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function admin_init() {

        	// Register the setting tab.
			register_setting(
	            'multisite_script_group', // Option group.
	            'multisite_script_option', // Option name.
	            array( $this, 'sanitize' ) // Sanitize.
	        );

	        add_settings_section(
	            'multisite_script_setting', // ID.
	            '', // Title
	            array( $this, 'print_section_info' ), // Callback.
	            'multisite_script_admin' // Page.
	        );

	        add_settings_field(
	            'header_script', // ID.
	            __( 'These scripts & styles will be printed to the <code>&lt;head&gt;</code> section.', 'nwcc' ), // Title.
	            array( $this, 'header_script_callback' ), // Callback.
	            'multisite_script_admin', // Page.
	            'multisite_script_setting' // Section.
	        );

	        add_settings_field(
	            'footer_script', // ID.
	            __( 'These scripts & styles will be printed to the <code>&lt;footer&gt;</code> section.', 'nwcc' ), // Title.
	            array( $this, 'footer_script_callback' ), // Callback.
	            'multisite_script_admin', // Page.
	            'multisite_script_setting' // Section.
	        );
		}

		/**
		 * Function Name: add_plugin_page
		 * Function Description: Add a setting page in WP Setting
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function add_plugin_page() {

			if ( ! is_super_admin() ) {
				return;
			}

			add_menu_page (
				__( 'Custom Code', 'nwcc' ),
				__( 'Custom Code', 'nwcc' ),
				"administrator",
				'multisite-script',
				array( $this, 'create_admin_page' ),
				'dashicons-editor-code',
				99
			);
	    }

	    /**
		 * Function Name: create_admin_page
		 * Function Description: callback function to callback admin setting page
		 *
		 * @since  1.0.0
		 * @return void
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
							do_settings_sections( 'multisite_script_admin' );
							echo __( '<b>Note-</b> No need to use <code>&lt;script&gt;</code> and <code>&lt;style&gt;</code> tag.', 'nwcc' );
							submit_button();
						?>
						</form>
					</div>
	        	</div>
	        </div>
	        <?php
	    }

	    /**
     	 * Sanitize each setting field as needed
	     *
		 * @since  1.0.0
	     * @param array $input Contains all settings fields as array keys
	     */
	    public function sanitize( $input ) {

			$new_input = array();

			// Header script and style.
			if( isset( $input['header_style'] ) ) {
				$new_input['header_style'] = $input['header_style'];
			}

			if( isset( $input['header_script'] ) ) {
				$new_input['header_script'] = $input['header_script'];
			}

			// Footer script and style.
			if( isset( $input['footer_style'] ) ) {
				$new_input['footer_style'] = $input['footer_style'];
			}

			if( isset( $input['footer_script'] ) ) {
				$new_input['footer_script'] = $input['footer_script'];
			}

	        return $new_input;
	    }

	    /**
		 * Function Name: print_section_info
		 * Function Description: Prints information about the section
		 *
		 * @since  1.0.0
		 * @return void
		 */
	    public function print_section_info() {
	        //Nothing to do here.
	    }

	    /**
	     * Callback function for Header Style and Script inputs
		 *
		 * @since  1.0.0
		 * @return void
	     */
	    public function header_script_callback() {

			$style = ( isset( $this->multisite_script_option['header_style'] ) ) ? wp_unslash( $this->multisite_script_option['header_style'] ) : '';
			$script = ( isset( $this->multisite_script_option['header_script'] ) ) ? wp_unslash( $this->multisite_script_option['header_script'] ) : '';

			$placeholder_style = __( 'Add your CSS style here.', 'nwcc' );
			$placeholder_script = __( 'Add your JS script here.', 'nwcc' );

			printf(
	        	'<textarea id="header_style" style="margin-right: 20px;" name="multisite_script_option[header_style]" rows="5" cols="50" placeholder="%s">%s</textarea>', $placeholder_style, wp_unslash($style)
	        );

			printf(
	        	'<textarea id="header_script" name="multisite_script_option[header_script]" rows="5" cols="50" placeholder="%s">%s</textarea>', $placeholder_script, wp_unslash($script)
	        );
	    }

	    /**
	     * Callback function for Footer Style and Script inputs
		 *
		 * @since  1.0.0
		 * @return void
	     */
	    public function footer_script_callback() {

			$style = ( isset( $this->multisite_script_option['footer_style'] ) ) ? wp_unslash( $this->multisite_script_option['footer_style'] ) : '';
			$script = ( isset( $this->multisite_script_option['footer_script'] ) ) ? wp_unslash( $this->multisite_script_option['footer_script'] ) : '';

			$placeholder_style = __( 'Add your CSS style here.', 'nwcc' );
	    	$placeholder_script = __( 'Add your JS script here.', 'nwcc' );

			printf(
				'<textarea id="footer_style" style="margin-right: 20px;" name="multisite_script_option[footer_style]" rows="5" cols="50" placeholder="%s">%s</textarea>', $placeholder_style, wp_unslash($style)
	        );
			
			printf(
				'<textarea id="footer_script" name="multisite_script_option[footer_script]" rows="5" cols="50" placeholder="%s">%s</textarea>', $placeholder_script, wp_unslash($script)
	        );
	    }
	}

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 */
	new Multisite_Script_Class;
}
