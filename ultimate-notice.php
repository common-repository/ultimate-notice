<?php
/*
Plugin Name: Ultimate Notice
Description: Ultimate Notice allows you to inform users that your site uses cookies.
Version: 1.0.0
Author: mantrabrain
Author URI: http://www.mantrabrain.com/
Plugin URI: http://www.mantrabrain.com/downloads/ultimate-notice-wordpress-plugin/
License: MIT License
License URI: http://opensource.org/licenses/MIT
Text Domain: ultimate-notice
Domain Path: /languages
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Ultimate_Notice' ) ) :

    /**
     * Main Ultimate_Notice Class.
     *
     * @since 1.0.0
     */
    final class Ultimate_Notice {
        /** Singleton *************************************************************/

        /**
         * @var Ultimate_Notice The one true Ultimate_Notice
         * @since 1.0.0
         */
        private static $instance;


        /**
         * Main Ultimate_Notice Instance.
         *
         * Insures that only one instance of Ultimate_Notice exists in memory at any one
         * time. Also prevents needing to define globals all over the place.
         *
         * @since 1.0.0
         * @static
         * @staticvar array $instance
         * @uses Ultimate_Notice::setup_constants() Setup the constants needed.
         * @uses Ultimate_Notice::includes() Include the required files.
         * @uses Ultimate_Notice::load_textdomain() load the language files.
         * @see Ultimate_Notice()
         * @return object|Ultimate_Notice The one true Ultimate_Notice
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Ultimate_Notice ) ) {
                self::$instance = new Ultimate_Notice;
                self::$instance->setup_constants();

                self::$instance->includes();
            }

            return self::$instance;
        }

        /**
         * Throw error on object clone.
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 1.0.0
         * @access protected
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-digital-downloads' ), '1.6' );
        }

        /**
         * Disable unserializing of the class.
         *
         * @since 1.0.0
         * @access protected
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'easy-digital-downloads' ), '1.6' );
        }

        /**
         * Setup plugin constants.
         *
         * @access private
         * @since 1.0.0
         * @return void
         */
        private function setup_constants() {

            // Plugin version.
            if ( ! defined( 'ULTIMATE_NOTICE_VERSION' ) ) {
                define( 'ULTIMATE_NOTICE_VERSION', '1.O.0' );
            }

            // Plugin Folder Path.
            if ( ! defined( 'ULTIMATE_NOTICE_PLUGIN_DIR' ) ) {
                define( 'ULTIMATE_NOTICE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin Folder URL.
            if ( ! defined( 'ULTIMATE_NOTICE_PLUGIN_URL' ) ) {
                define( 'ULTIMATE_NOTICE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

            }

            // Plugin Root File.
            if ( ! defined( 'ULTIMATE_NOTICE_PLUGIN_FILE' ) ) {
                define( 'ULTIMATE_NOTICE_PLUGIN_FILE', __FILE__ );
            }
        }

        /**
         * Include required files.
         *
         * @access private
         * @since 1.0.0
         * @return void
         */
        private function includes() {

            require_once ULTIMATE_NOTICE_PLUGIN_DIR . 'includes/class-ultimate-notice-core.php';

        }



    }

endif; // End if class_exists check.


/**
 * The main function for that returns Ultimate_Notice
 *
 * The main function responsible for returning the one true Ultimate_Notice
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 *
 * @since 1.0.0
 * @return object|Ultimate_Notice The one true Ultimate_Notice Instance.
 */
function Ultimate_Notice() {
    return Ultimate_Notice::instance();
}

// Get Ultimate_Notice Running.
Ultimate_Notice();
