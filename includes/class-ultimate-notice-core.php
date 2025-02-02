<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit;

// set plugin instance
$ultimate_notice = new Ultimate_Notice_Core();

include_once ULTIMATE_NOTICE_PLUGIN_DIR . 'includes/upgrade.php';

/**
 * Ultimate Notice class.
 *
 * @class Ultimate_Notice_Core
 * @version	1.0.0
 */
class Ultimate_Notice_Core {

    /**
     * @var $defaults
     */
    private $defaults = array(
        'general' => array(
            'position'						=> 'bottom',
            'message_text'					=> '',
            'css_style'						=> 'bootstrap',
            'css_class'						=> 'button',
            'accept_text'					=> '',
            'refuse_text'					=> '',
            'refuse_opt'					=> 'no',
            'refuse_code'					=> '',
            'refuse_code_head'				=> '',
            'revoke_cookies'				=> false,
            'revoke_cookies_opt'			=> 'automatic',
            'revoke_text'					=> '',
            'redirection'					=> false,
            'see_more'						=> 'no',
            'link_target'					=> '_blank',
            'time'							=> 'month',
            'hide_effect'					=> 'fade',
            'on_scroll'						=> false,
            'on_scroll_offset'				=> 100,
            'colors' => array(
                'text'							=> '#fff',
                'bar'							=> '#000'
            ),
            'see_more_opt' => array(
                'text'						=> '',
                'link_type'					=> 'page',
                'id'						=> 'empty',
                'link'						=> '',
                'sync'						=> false
            ),
            'script_placement'				=> 'header',
            'translate'						=> true,
            'deactivation_delete'			=> 'no'
        ),
        'version'							=> '1.0.0'
    );
    private $positions 			= array();
    private $styles 			= array();
    private $choices 			= array();
    private $links 				= array();
    private $link_target 		= array();
    private $colors 			= array();
    private $options 			= array();
    private $effects 			= array();
    private $times 				= array();
    private $script_placements 	= array();

    /**
     * Constructor.
     */
    public function __construct() {
        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

        // settings
        $this->options = array(
            'general' => array_merge( $this->defaults['general'], get_option( 'ultimate_notice_options', $this->defaults['general'] ) )
        );

        if ( ! isset( $this->options['general']['see_more_opt']['sync'] ) )
            $this->options['general']['see_more_opt']['sync'] = $this->defaults['general']['see_more_opt']['sync'];

        // actions
        add_action( 'init', array( $this, 'register_shortcode' ) );
        add_action( 'init', array( $this, 'add_wpsc_cookie' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu_options' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'after_setup_theme', array( $this, 'load_defaults' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
        add_action( 'wp_head', array( $this, 'wp_print_header_scripts' ) );
        add_action( 'wp_print_footer_scripts', array( $this, 'wp_print_footer_scripts' ) );
        add_action( 'wp_footer', array( $this, 'add_ultimate_notice' ), 1000 );

        // filters
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
        add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
        add_filter( 'body_class', array( $this, 'change_body_class' ) );

        // load other files
        include_once ULTIMATE_NOTICE_PLUGIN_DIR . 'includes/functions.php' ;
    }

    /**
     * Add WP Super Cache compatibility.
     *
     * @return void
     */
    public function add_wpsc_cookie() {
        do_action( 'wpsc_add_cookie', 'ultimate_notice_accepted' );
    }

    /**
     * Load plugin defaults
     */
    public function load_defaults() {
        $this->positions = array(
            'top'	 			=> __( 'Top', 'ultimate-notice' ),
            'bottom' 			=> __( 'Bottom', 'ultimate-notice' )
        );

        $this->styles = array(
            'none'		 		=> __( 'None', 'ultimate-notice' ),
            'wp-default' 		=> __( 'WordPress', 'ultimate-notice' ),
            'bootstrap'	 		=> __( 'Bootstrap', 'ultimate-notice' )
        );

        $this->revoke_opts = array(
            'automatic'	 		=> __( 'Automatic', 'ultimate-notice' ),
            'manual' 			=> __( 'Manual', 'ultimate-notice' )
        );

        $this->links = array(
            'page'	 			=> __( 'Page link', 'ultimate-notice' ),
            'custom' 			=> __( 'Custom link', 'ultimate-notice' )
        );

        $this->link_target = array(
            '_blank',
            '_self'
        );

        $this->colors = array(
            'text'	 			=> __( 'Text color', 'ultimate-notice' ),
            'bar'	 			=> __( 'Bar color', 'ultimate-notice' ),
        );

        $this->times = array(
            'hour'				=> array( __( 'An hour', 'ultimate-notice' ), 3600 ),
            'day'		 		=> array( __( '1 day', 'ultimate-notice' ), 86400 ),
            'week'		 		=> array( __( '1 week', 'ultimate-notice' ), 604800 ),
            'month'		 		=> array( __( '1 month', 'ultimate-notice' ), 2592000 ),
            '3months'	 		=> array( __( '3 months', 'ultimate-notice' ), 7862400 ),
            '6months'	 		=> array( __( '6 months', 'ultimate-notice' ), 15811200 ),
            'year'		 		=> array( __( '1 year', 'ultimate-notice' ), 31536000 ),
            'infinity'	 		=> array( __( 'infinity', 'ultimate-notice' ), 2147483647 )
        );

        $this->effects = array(
            'none'	 			=> __( 'None', 'ultimate-notice' ),
            'fade'	 			=> __( 'Fade', 'ultimate-notice' ),
            'slide'	 			=> __( 'Slide', 'ultimate-notice' )
        );

        $this->script_placements = array(
            'header' 			=> __( 'Header', 'ultimate-notice' ),
            'footer' 			=> __( 'Footer', 'ultimate-notice' ),
        );

        if ( $this->options['general']['translate'] === true ) {
            $this->options['general']['translate'] = false;

            $this->options['general']['message_text'] = __( 'We use cookies to ensure that we give you the best experience on our website. If you continue to use this site we will assume that you are happy with it.', 'ultimate-notice' );
            $this->options['general']['accept_text'] = __( 'Ok', 'ultimate-notice' );
            $this->options['general']['refuse_text'] = __( 'No', 'ultimate-notice' );
            $this->options['general']['revoke_text'] = __( 'Revoke cookies', 'ultimate-notice' );
            $this->options['general']['see_more_opt']['text'] = __( 'Privacy policy', 'ultimate-notice' );

            update_option( 'ultimate_notice_options', $this->options['general'] );
        }

        // WPML >= 3.2
        if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
            $this->register_wpml_strings();
            // WPML and Polylang compatibility
        } elseif ( function_exists( 'icl_register_string' ) ) {
            icl_register_string( 'Ultimate Notice', 'Message in the notice', $this->options['general']['message_text'] );
            icl_register_string( 'Ultimate Notice', 'Button text', $this->options['general']['accept_text'] );
            icl_register_string( 'Ultimate Notice', 'Refuse button text', $this->options['general']['refuse_text'] );
            icl_register_string( 'Ultimate Notice', 'Revoke button text', $this->options['general']['revoke_text'] );
            icl_register_string( 'Ultimate Notice', 'Privacy policy text', $this->options['general']['see_more_opt']['text'] );
            icl_register_string( 'Ultimate Notice', 'Custom link', $this->options['general']['see_more_opt']['link'] );
        }
    }

    /**
     * Add new body classes.
     *
     * @param array $classes Body classes
     * @return array
     */
    public function change_body_class( $classes ) {
        if ( is_admin() )
            return $classes;

        if ( $this->cookies_set() ) {
            $classes[] = 'cookies-set';

            if ( $this->cookies_accepted() )
                $classes[] = 'cookies-accepted';
            else
                $classes[] = 'cookies-refused';
        } else
            $classes[] = 'cookies-not-set';

        return $classes;
    }

    /**
     * Register shortcode.
     *
     * @return void
     */
    public function register_shortcode() {
        add_shortcode( 'cookies_accepted', array( $this, 'cookies_accepted_shortcode' ) );
        add_shortcode( 'cookies_revoke', array( $this, 'cookies_revoke_shortcode' ) );
    }

    /**
     * Register cookies accepted shortcode.
     *
     * @param array $args
     * @param mixed $content
     * @return mixed
     */
    public function cookies_accepted_shortcode( $args, $content ) {
        if ( $this->cookies_accepted() ) {
            $scripts = html_entity_decode( trim( wp_kses( $content, $this->get_allowed_html() ) ) );

            if ( ! empty( $scripts ) ) {
                if ( preg_match_all( '/' . get_shortcode_regex() . '/', $content ) ) {
                    $scripts = do_shortcode( $scripts );
                }
                return $scripts;
            }
        }

        return '';
    }

    /**
     * Register cookies accepted shortcode.
     *
     * @param array $args
     * @param mixed $content
     * @return mixed
     */
    public function cookies_revoke_shortcode( $args, $content ) {
        // get options
        $options = $this->options['general'];

        // defaults
        $defaults = array(
            'title'	=> $options['revoke_text'],
            'class'	=> $options['css_class']
        );

        // combine shortcode arguments
        $args = shortcode_atts( $defaults, $args );

        // escape class(es)
        $args['class'] = esc_attr( $args['class'] );

        return '<a href="#" class="ultimate-notice-revoke-cookie ultimate-notice-button ultimate-notice-revoke-inline' . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $args['class'] !== '' ? ' ' . $args['class'] : '' ) . '" title="' . esc_html( $args['title'] ) . '">' . esc_html( $args['title'] ) . '</a>';
    }

    /**
     * Register WPML (>= 3.2) strings if needed.
     *
     * @return	void
     */
    private function register_wpml_strings() {
        global $wpdb;

        // prepare strings
        $strings = array(
            'Message in the notice'	=> $this->options['general']['message_text'],
            'Button text'			=> $this->options['general']['accept_text'],
            'Refuse button text'	=> $this->options['general']['refuse_text'],
            'Revoke button text'	=> $this->options['general']['revoke_text'],
            'Privacy policy text'		=> $this->options['general']['see_more_opt']['text'],
            'Custom link'			=> $this->options['general']['see_more_opt']['link']
        );

        // get query results
        $results = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM " . $wpdb->prefix . "icl_strings WHERE context = %s", 'Ultimate Notice' ) );

        // check results
        foreach( $strings as $string => $value ) {
            // string does not exist?
            if ( ! in_array( $string, $results, true ) ) {
                // register string
                do_action( 'wpml_register_single_string', 'Ultimate Notice', $string, $value );
            }
        }
    }

    /**
     * Load textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'ultimate-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Add submenu.
     */
    public function admin_menu_options() {
        add_options_page( __( 'Ultimate Notice', 'ultimate-notice' ), __( 'Ultimate Notice', 'ultimate-notice' ), apply_filters( 'ultimate_notice_manage_ultimate_notice_cap', 'manage_options' ), 'ultimate-notice', array( $this, 'options_page' ) );
    }

    /**
     * Options page output.
     *
     * @return mixed
     */
    public function options_page() {
        echo '
		<div class="wrap">
			<h2>' . __( 'Ultimate Notice', 'ultimate-notice' ) . '</h2>
			<div class="ultimate-notice-settings">
			
				<form action="options.php" method="post">';

        settings_fields( 'ultimate_notice_options' );
        do_settings_sections( 'ultimate_notice_options' );

        echo '
				<p class="submit">';
        submit_button( '', 'primary', 'save_ultimate_notice_options', false );
        echo ' ';
        submit_button( __( 'Reset to defaults', 'ultimate-notice' ), 'secondary', 'reset_ultimate_notice_options', false );
        echo '
				</p>
				</form>
			</div>
			<div class="clear"></div>
		</div>';
    }

    /**
     * Regiseter plugin settings.
     */
    public function register_settings() {
        register_setting( 'ultimate_notice_options', 'ultimate_notice_options', array( $this, 'validate_options' ) );

        // configuration
        add_settings_section( 'ultimate_notice_configuration', __( 'Configuration', 'ultimate-notice' ), array( $this, 'ultimate_notice_section_configuration' ), 'ultimate_notice_options' );
        add_settings_field( 'ultimate_notice_message_text', __( 'Message', 'ultimate-notice' ), array( $this, 'ultimate_notice_message_text' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_accept_text', __( 'Button text', 'ultimate-notice' ), array( $this, 'ultimate_notice_accept_text' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_see_more', __( 'Privacy policy', 'ultimate-notice' ), array( $this, 'ultimate_notice_see_more' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_link_target', __( 'Link target', 'ultimate-notice' ), array( $this, 'ultimate_notice_link_target' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_refuse_opt', __( 'Refuse cookies', 'ultimate-notice' ), array( $this, 'ultimate_notice_refuse_opt' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_revoke_opt', __( 'Revoke cookies', 'ultimate-notice' ), array( $this, 'ultimate_notice_revoke_opt' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_refuse_code', __( 'Script blocking', 'ultimate-notice' ), array( $this, 'ultimate_notice_refuse_code' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_redirection', __( 'Reloading', 'ultimate-notice' ), array( $this, 'ultimate_notice_redirection' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_on_scroll', __( 'On scroll', 'ultimate-notice' ), array( $this, 'ultimate_notice_on_scroll' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_time', __( 'Cookie expiry', 'ultimate-notice' ), array( $this, 'ultimate_notice_time' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_script_placement', __( 'Script placement', 'ultimate-notice' ), array( $this, 'ultimate_notice_script_placement' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );
        add_settings_field( 'ultimate_notice_deactivation_delete', __( 'Deactivation', 'ultimate-notice' ), array( $this, 'ultimate_notice_deactivation_delete' ), 'ultimate_notice_options', 'ultimate_notice_configuration' );

        // design
        add_settings_section( 'ultimate_notice_design', __( 'Design', 'ultimate-notice' ), array( $this, 'ultimate_notice_section_design' ), 'ultimate_notice_options' );
        add_settings_field( 'ultimate_notice_position', __( 'Position', 'ultimate-notice' ), array( $this, 'ultimate_notice_position' ), 'ultimate_notice_options', 'ultimate_notice_design' );
        add_settings_field( 'ultimate_notice_hide_effect', __( 'Animation', 'ultimate-notice' ), array( $this, 'ultimate_notice_hide_effect' ), 'ultimate_notice_options', 'ultimate_notice_design' );
        add_settings_field( 'ultimate_notice_css_style', __( 'Button style', 'ultimate-notice' ), array( $this, 'ultimate_notice_css_style' ), 'ultimate_notice_options', 'ultimate_notice_design' );
        add_settings_field( 'ultimate_notice_css_class', __( 'Button class', 'ultimate-notice' ), array( $this, 'ultimate_notice_css_class' ), 'ultimate_notice_options', 'ultimate_notice_design' );
        add_settings_field( 'ultimate_notice_colors', __( 'Colors', 'ultimate-notice' ), array( $this, 'ultimate_notice_colors' ), 'ultimate_notice_options', 'ultimate_notice_design' );
    }

    /**
     * Section callback: fix for WP < 3.3
     */
    public function ultimate_notice_section_configuration() {}
    public function ultimate_notice_section_design() {}

    /**
     * Delete plugin data on deactivation.
     */
    public function ultimate_notice_deactivation_delete() {
        echo '
		<fieldset>
			<label><input id="ultimate_notice_deactivation_delete" type="checkbox" name="ultimate_notice_options[deactivation_delete]" value="1" ' . checked( 'yes', $this->options['general']['deactivation_delete'], false ) . '/>' . __( 'Enable if you want all plugin data to be deleted on deactivation.', 'ultimate-notice' ) . '</label>
		</fieldset>';
    }

    /**
     * Cookie message option.
     */
    public function ultimate_notice_message_text() {
        echo '
		<fieldset>
			<div id="ultimate_notice_message_text">
				<textarea name="ultimate_notice_options[message_text]" class="large-text" cols="50" rows="5">' . esc_textarea( $this->options['general']['message_text'] ) . '</textarea>
				<p class="description">' . __( 'Enter the ultimate notice message.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Accept cookie label option.
     */
    public function ultimate_notice_accept_text() {
        echo '
		<fieldset>
			<div id="ultimate_notice_accept_text">
				<input type="text" class="regular-text" name="ultimate_notice_options[accept_text]" value="' . esc_attr( $this->options['general']['accept_text'] ) . '" />
			<p class="description">' . __( 'The text of the option to accept the usage of the cookies and make the notification disappear.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Enable/Disable third party non functional cookies option.
     */
    public function ultimate_notice_refuse_opt() {
        echo '
		<fieldset>
			<label><input id="ultimate_notice_refuse_opt" type="checkbox" name="ultimate_notice_options[refuse_opt]" value="1" ' . checked( 'yes', $this->options['general']['refuse_opt'], false ) . ' />' . __( 'Enable to give to the user the possibility to refuse third party non functional cookies.', 'ultimate-notice' ) . '</label>
			<div id="ultimate_notice_refuse_opt_container"' . ( $this->options['general']['refuse_opt'] === 'no' ? ' style="display: none;"' : '' ) . '>
				<div id="ultimate_notice_refuse_text">
					<input type="text" class="regular-text" name="ultimate_notice_options[refuse_text]" value="' . esc_attr( $this->options['general']['refuse_text'] ) . '" />
					<p class="description">' . __( 'The text of the button to refuse the usage of the cookies.', 'ultimate-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
    }

    /**
     * Non functional cookies code.
     */
    public function ultimate_notice_refuse_code() {
        $allowed_html = $this->get_allowed_html();
        $active = ! empty( $this->options['general']['refuse_code'] ) && empty( $this->options['general']['refuse_code_head'] ) ? 'body' : 'head';

        echo '
		<fieldset>
			<div id="ultimate_notice_refuse_code">
				<div id="ultimate_notice_refuse_code_fields">
					<h2 class="nav-tab-wrapper">
						<a id="refuse_head-tab" class="nav-tab' . ( $active === 'head' ? ' nav-tab-active' : '' ) . '" href="#refuse_head">' . __( 'Head', 'ultimate-notice' ) . '</a>
						<a id="refuse_body-tab" class="nav-tab' . ( $active === 'body' ? ' nav-tab-active' : '' ) . '" href="#refuse_body">' . __( 'Body', 'ultimate-notice' ) . '</a>
					</h2>
					<div id="refuse_head" class="refuse-code-tab' . ( $active === 'head' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site header, before the closing head tag.', 'ultimate-notice' ) . '</p>
						<textarea name="ultimate_notice_options[refuse_code_head]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code_head'], $allowed_html ) ) ) . '</textarea>
					</div>
					<div id="refuse_body" class="refuse-code-tab' . ( $active === 'body' ? ' active' : '' ) . '">
						<p class="description">' . __( 'The code to be used in your site footer, before the closing body tag.', 'ultimate-notice' ) . '</p>
						<textarea name="ultimate_notice_options[refuse_code]" class="large-text" cols="50" rows="8">' . html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code'], $allowed_html ) ) ) . '</textarea>
					</div>
				</div>
				<p class="description">' . __( 'Enter non functional cookies Javascript code here (for e.g. Google Analitycs) to be used after cookies are accepted.', 'ultimate-notice' ) . '</br>' . __( 'To get the ultimate notice status use <code>ultimate_notice_cookies_accepted()</code> function.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Revoke cookies option.
     */
    public function ultimate_notice_revoke_opt() {
        echo '
		<fieldset>
			<label><input id="ultimate_notice_revoke_cookies" type="checkbox" name="ultimate_notice_options[revoke_cookies]" value="1" ' . checked( true, $this->options['general']['revoke_cookies'], false ) . ' />' . __( 'Enable to give to the user the possibility to revoke their cookie consent <i>(requires "Refuse cookies" option enabled)</i>.', 'ultimate-notice' ) . '</label>
			<div id="ultimate_notice_revoke_opt_container"' . ( $this->options['general']['revoke_cookies'] ? '' : ' style="display: none;"' ) . '>
				<input type="text" class="regular-text" name="ultimate_notice_options[revoke_text]" value="' . esc_attr( $this->options['general']['revoke_text'] ) . '" />
				<p class="description">' . __( 'The text of the button to revoke the cookie consent.', 'ultimate-notice' ) . '</p>';

        foreach ( $this->revoke_opts as $value => $label ) {
            echo '
				<label><input id="ultimate_notice_revoke_cookies-' . $value . '" type="radio" name="ultimate_notice_options[revoke_cookies_opt]" value="' . $value . '" ' . checked( $value, $this->options['general']['revoke_cookies_opt'], false ) . ' />' . esc_html( $label ) . '</label>';
        }

        echo '
				<p class="description">' . __( 'Select the method for displaying the revoke button - automatic (in the Ultimate Notice container) or manual using <code>[cookies_revoke]</code> shortcode.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Redirection on cookie accept.
     */
    public function ultimate_notice_redirection() {
        echo '
		<fieldset>
			<label><input id="ultimate_notice_redirection" type="checkbox" name="ultimate_notice_options[redirection]" value="1" ' . checked( true, $this->options['general']['redirection'], false ) . ' />' . __( 'Enable to reload the page after cookies are accepted.', 'ultimate-notice' ) . '</label>
		</fieldset>';
    }

    /**
     * Privacy policy link option.
     */
    public function ultimate_notice_see_more() {
        $pages = get_pages(
            array(
                'sort_order'	=> 'ASC',
                'sort_column'	=> 'post_title',
                'hierarchical'	=> 0,
                'child_of'		=> 0,
                'parent'		=> -1,
                'offset'		=> 0,
                'post_type'		=> 'page',
                'post_status'	=> 'publish'
            )
        );

        echo '
		<fieldset>
			<label><input id="ultimate_notice_see_more" type="checkbox" name="ultimate_notice_options[see_more]" value="1" ' . checked( 'yes', $this->options['general']['see_more'], false ) . ' />' . __( 'Enable privacy policy link.', 'ultimate-notice' ) . '</label>
			<p class="description">' . sprintf( __( 'Need a Cookie Policy? Generate one with <a href="%s" target="_blank" title="iubenda">iubenda</a>.', 'ultimate-notice' ), 'http://iubenda.refr.cc/MXRWXMP' ) . '</p>
			<div id="ultimate_notice_see_more_opt"' . ($this->options['general']['see_more'] === 'no' ? ' style="display: none;"' : '') . '>
				<input type="text" class="regular-text" name="ultimate_notice_options[see_more_opt][text]" value="' . esc_attr( $this->options['general']['see_more_opt']['text'] ) . '" />
				<p class="description">' . __( 'The text of the privacy policy button.', 'ultimate-notice' ) . '</p>
				<div id="ultimate_notice_see_more_opt_custom_link">';

        foreach ( $this->links as $value => $label ) {
            $value = esc_attr( $value );

            echo '
					<label><input id="ultimate_notice_see_more_link-' . $value . '" type="radio" name="ultimate_notice_options[see_more_opt][link_type]" value="' . $value . '" ' . checked( $value, $this->options['general']['see_more_opt']['link_type'], false ) . ' />' . esc_html( $label ) . '</label>';
        }

        echo '
				</div>
				<p class="description">' . __( 'Select where to redirect user for more information about cookies.', 'ultimate-notice' ) . '</p>
				<div id="ultimate_notice_see_more_opt_page"' . ($this->options['general']['see_more_opt']['link_type'] === 'custom' ? ' style="display: none;"' : '') . '>
					<select name="ultimate_notice_options[see_more_opt][id]">
						<option value="empty" ' . selected( 'empty', $this->options['general']['see_more_opt']['id'], false ) . '>' . __( '-- select page --', 'ultimate-notice' ) . '</option>';

        if ( $pages ) {
            foreach ( $pages as $page ) {
                echo '
						<option value="' . $page->ID . '" ' . selected( $page->ID, $this->options['general']['see_more_opt']['id'], false ) . '>' . esc_html( $page->post_title ) . '</option>';
            }
        }

        echo '
					</select>
					<p class="description">' . __( 'Select from one of your site\'s pages.', 'ultimate-notice' ) . '</p>';

        global $wp_version;

        if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
            echo '
						<label><input id="ultimate_notice_see_more_opt_sync" type="checkbox" name="ultimate_notice_options[see_more_opt][sync]" value="1" ' . checked( true, $this->options['general']['see_more_opt']['sync'], false ) . ' />' . __( 'Synchronize with WordPress Privacy Policy page.', 'ultimate-notice' ) . '</label>';
        }

        echo '
				</div>
				<div id="ultimate_notice_see_more_opt_link"' . ($this->options['general']['see_more_opt']['link_type'] === 'page' ? ' style="display: none;"' : '') . '>
					<input type="text" class="regular-text" name="ultimate_notice_options[see_more_opt][link]" value="' . esc_attr( $this->options['general']['see_more_opt']['link'] ) . '" />
					<p class="description">' . __( 'Enter the full URL starting with http(s)://', 'ultimate-notice' ) . '</p>
				</div>
			</div>
		</fieldset>';
    }

    /**
     * Link target option.
     */
    public function ultimate_notice_link_target() {
        echo '
		<fieldset>
			<div id="ultimate_notice_link_target">
				<select name="ultimate_notice_options[link_target]">';

        foreach ( $this->link_target as $target ) {
            echo '
					<option value="' . $target . '" ' . selected( $target, $this->options['general']['link_target'] ) . '>' . esc_html( $target ) . '</option>';
        }

        echo '
				</select>
				<p class="description">' . __( 'Select the privacy policy link target.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Expiration time option.
     */
    public function ultimate_notice_time() {
        echo '
		<fieldset>
			<div id="ultimate_notice_time">
				<select name="ultimate_notice_options[time]">';

        foreach ( $this->times as $time => $arr ) {
            $time = esc_attr( $time );

            echo '
					<option value="' . $time . '" ' . selected( $time, $this->options['general']['time'] ) . '>' . esc_html( $arr[0] ) . '</option>';
        }

        echo '
				</select>
				<p class="description">' . __( 'The amount of time that cookie should be stored for.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Script placement option.
     */
    public function ultimate_notice_script_placement() {
        echo '
		<fieldset>';

        foreach ( $this->script_placements as $value => $label ) {
            echo '
			<label><input id="ultimate_notice_script_placement-' . $value . '" type="radio" name="ultimate_notice_options[script_placement]" value="' . esc_attr( $value ) . '" ' . checked( $value, $this->options['general']['script_placement'], false ) . ' />' . esc_html( $label ) . '</label>';
        }

        echo '
			<p class="description">' . __( 'Select where all the plugin scripts should be placed.', 'ultimate-notice' ) . '</p>
		</fieldset>';
    }

    /**
     * Position option.
     */
    public function ultimate_notice_position() {
        echo '
		<fieldset>
			<div id="ultimate_notice_position">';

        foreach ( $this->positions as $value => $label ) {
            $value = esc_attr( $value );

            echo '
				<label><input id="ultimate_notice_position-' . $value . '" type="radio" name="ultimate_notice_options[position]" value="' . $value . '" ' . checked( $value, $this->options['general']['position'], false ) . ' />' . esc_html( $label ) . '</label>';
        }

        echo '
				<p class="description">' . __( 'Select location for your ultimate notice.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Animation effect option.
     */
    public function ultimate_notice_hide_effect() {
        echo '
		<fieldset>
			<div id="ultimate_notice_hide_effect">';

        foreach ( $this->effects as $value => $label ) {
            $value = esc_attr( $value );

            echo '
				<label><input id="ultimate_notice_hide_effect-' . $value . '" type="radio" name="ultimate_notice_options[hide_effect]" value="' . $value . '" ' . checked( $value, $this->options['general']['hide_effect'], false ) . ' />' . esc_html( $label ) . '</label>';
        }

        echo '
				<p class="description">' . __( 'ultimate notice acceptance animation.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * On scroll option.
     */
    public function ultimate_notice_on_scroll() {
        echo '
		<fieldset>
			<label><input id="ultimate_notice_on_scroll" type="checkbox" name="ultimate_notice_options[on_scroll]" value="1" ' . checked( 'yes', $this->options['general']['on_scroll'], false ) . ' />' . __( 'Enable ultimate notice acceptance when users scroll.', 'ultimate-notice' ) . '</label>
			<div id="ultimate_notice_on_scroll_offset"' . ( $this->options['general']['on_scroll'] === 'no' || $this->options['general']['on_scroll'] == false ? ' style="display: none;"' : '' ) . '>
				<input type="text" class="text" name="ultimate_notice_options[on_scroll_offset]" value="' . esc_attr( $this->options['general']['on_scroll_offset'] ) . '" /> <span>px</span>
				<p class="description">' . __( 'Number of pixels user has to scroll to accept the usage of the cookies and make the notification disappear.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * CSS style option.
     */
    public function ultimate_notice_css_style() {
        echo '
		<fieldset>
			<div id="ultimate_notice_css_style">';

        foreach ( $this->styles as $value => $label ) {
            $value = esc_attr( $value );

            echo '
				<label><input id="ultimate_notice_css_style-' . $value . '" type="radio" name="ultimate_notice_options[css_style]" value="' . $value . '" ' . checked( $value, $this->options['general']['css_style'], false ) . ' />' . esc_html( $label ) . '</label>';
        }

        echo '
				<p class="description">' . __( 'Choose buttons style.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * CSS style option.
     */
    public function ultimate_notice_css_class() {
        echo '
		<fieldset>
			<div id="ultimate_notice_css_class">
				<input type="text" class="regular-text" name="ultimate_notice_options[css_class]" value="' . esc_attr( $this->options['general']['css_class'] ) . '" />
				<p class="description">' . __( 'Enter additional button CSS classes separated by spaces.', 'ultimate-notice' ) . '</p>
			</div>
		</fieldset>';
    }

    /**
     * Colors option.
     */
    public function ultimate_notice_colors() {
        echo '
		<fieldset>';

        foreach ( $this->colors as $value => $label ) {
            $value = esc_attr( $value );

            echo '
			<div id="ultimate_notice_colors-' . $value . '"><label>' . esc_html( $label ) . '</label><br />
				<input class="ultimate_notice_color" type="text" name="ultimate_notice_options[colors][' . $value . ']" value="' . esc_attr( $this->options['general']['colors'][$value] ) . '" />' .
                '</div>';
        }

        echo '
		</fieldset>';
    }

    /**
     * Validate options.
     *
     * @param array $input
     * @return array
     */
    public function validate_options( $input ) {
        if ( ! check_admin_referer( 'ultimate_notice_options-options') )
            return $input;

        if ( ! current_user_can( apply_filters( 'ultimate_notice_manage_ultimate_notice_cap', 'manage_options' ) ) )
            return $input;

        if ( isset( $_POST['save_ultimate_notice_options'] ) ) {
            // position
            $input['position'] = sanitize_text_field( isset( $input['position'] ) && in_array( $input['position'], array_keys( $this->positions ) ) ? $input['position'] : $this->defaults['general']['position'] );

            // colors
            $input['colors']['text'] = sanitize_text_field( isset( $input['colors']['text'] ) && $input['colors']['text'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['text'] ) === 1 ? $input['colors']['text'] : $this->defaults['general']['colors']['text'] );
            $input['colors']['bar'] = sanitize_text_field( isset( $input['colors']['bar'] ) && $input['colors']['bar'] !== '' && preg_match( '/^#[a-f0-9]{6}$/', $input['colors']['bar'] ) === 1 ? $input['colors']['bar'] : $this->defaults['general']['colors']['bar'] );

            // texts
            $input['message_text'] = wp_kses_post( isset( $input['message_text'] ) && $input['message_text'] !== '' ? $input['message_text'] : $this->defaults['general']['message_text'] );
            $input['accept_text'] = sanitize_text_field( isset( $input['accept_text'] ) && $input['accept_text'] !== '' ? $input['accept_text'] : $this->defaults['general']['accept_text'] );
            $input['refuse_text'] = sanitize_text_field( isset( $input['refuse_text'] ) && $input['refuse_text'] !== '' ? $input['refuse_text'] : $this->defaults['general']['refuse_text'] );
            $input['revoke_text'] = sanitize_text_field( isset( $input['revoke_text'] ) && $input['revoke_text'] !== '' ? $input['revoke_text'] : $this->defaults['general']['revoke_text'] );
            $input['refuse_opt'] = (bool) isset( $input['refuse_opt'] ) ? 'yes' : 'no';
            $input['revoke_cookies'] = isset( $input['revoke_cookies'] );
            $input['revoke_cookies_opt'] = isset( $input['revoke_cookies_opt'] ) && array_key_exists( $input['revoke_cookies_opt'], $this->revoke_opts ) ? $input['revoke_cookies_opt'] : $this->defaults['general']['revoke_cookies_opt'];

            // get allowed HTML
            $allowed_html = $this->get_allowed_html();

            // body refuse code
            $input['refuse_code'] = wp_kses( isset( $input['refuse_code'] ) && $input['refuse_code'] !== '' ? $input['refuse_code'] : $this->defaults['general']['refuse_code'], $allowed_html );

            // head refuse code
            $input['refuse_code_head'] = wp_kses( isset( $input['refuse_code_head'] ) && $input['refuse_code_head'] !== '' ? $input['refuse_code_head'] : $this->defaults['general']['refuse_code_head'], $allowed_html );

            // css button style
            $input['css_style'] = sanitize_text_field( isset( $input['css_style'] ) && in_array( $input['css_style'], array_keys( $this->styles ) ) ? $input['css_style'] : $this->defaults['general']['css_style'] );

            // css button class
            $input['css_class'] = sanitize_text_field( isset( $input['css_class'] ) ? $input['css_class'] : $this->defaults['general']['css_class'] );

            // link target
            $input['link_target'] = sanitize_text_field( isset( $input['link_target'] ) && in_array( $input['link_target'], array_keys( $this->link_target ) ) ? $input['link_target'] : $this->defaults['general']['link_target'] );

            // time
            $input['time'] = sanitize_text_field( isset( $input['time'] ) && in_array( $input['time'], array_keys( $this->times ) ) ? $input['time'] : $this->defaults['general']['time'] );

            // script placement
            $input['script_placement'] = sanitize_text_field( isset( $input['script_placement'] ) && in_array( $input['script_placement'], array_keys( $this->script_placements ) ) ? $input['script_placement'] : $this->defaults['general']['script_placement'] );

            // hide effect
            $input['hide_effect'] = sanitize_text_field( isset( $input['hide_effect'] ) && in_array( $input['hide_effect'], array_keys( $this->effects ) ) ? $input['hide_effect'] : $this->defaults['general']['hide_effect'] );

            // on scroll
            $input['on_scroll'] = (bool) isset( $input['on_scroll'] ) ? 'yes' : 'no';

            // on scroll
            $input['redirection'] = isset( $input['redirection'] );

            // on scroll offset
            $input['on_scroll_offset'] = absint( isset( $input['on_scroll_offset'] ) && $input['on_scroll_offset'] !== '' ? $input['on_scroll_offset'] : $this->defaults['general']['on_scroll_offset'] );

            // deactivation
            $input['deactivation_delete'] = (bool) isset( $input['deactivation_delete'] ) ? 'yes' : 'no';

            // privacy policy
            $input['see_more'] = (bool) isset( $input['see_more'] ) ? 'yes' : 'no';
            $input['see_more_opt']['text'] = sanitize_text_field( isset( $input['see_more_opt']['text'] ) && $input['see_more_opt']['text'] !== '' ? $input['see_more_opt']['text'] : $this->defaults['general']['see_more_opt']['text'] );
            $input['see_more_opt']['link_type'] = sanitize_text_field( isset( $input['see_more_opt']['link_type'] ) && in_array( $input['see_more_opt']['link_type'], array_keys( $this->links ) ) ? $input['see_more_opt']['link_type'] : $this->defaults['general']['see_more_opt']['link_type'] );

            if ( $input['see_more_opt']['link_type'] === 'custom' )
                $input['see_more_opt']['link'] = esc_url( $input['see_more'] === 'yes' ? $input['see_more_opt']['link'] : 'empty' );
            elseif ( $input['see_more_opt']['link_type'] === 'page' ) {
                $input['see_more_opt']['id'] = ( $input['see_more'] === 'yes' ? (int) $input['see_more_opt']['id'] : 'empty' );
                $input['see_more_opt']['sync'] = isset( $input['see_more_opt']['sync'] );

                if ( $input['see_more_opt']['sync'] )
                    update_option( 'wp_page_for_privacy_policy', $input['see_more_opt']['id'] );
            }

            $input['translate'] = false;

            // WPML >= 3.2
            if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
                do_action( 'wpml_register_single_string', 'Ultimate Notice', 'Message in the notice', $input['message_text'] );
                do_action( 'wpml_register_single_string', 'Ultimate Notice', 'Button text', $input['accept_text'] );
                do_action( 'wpml_register_single_string', 'Ultimate Notice', 'Refuse button text', $input['refuse_text'] );
                do_action( 'wpml_register_single_string', 'Ultimate Notice', 'Revoke button text', $input['revoke_text'] );
                do_action( 'wpml_register_single_string', 'Ultimate Notice', 'Privacy policy text', $input['see_more_opt']['text'] );

                if ( $input['see_more_opt']['link_type'] === 'custom' )
                    do_action( 'wpml_register_single_string', 'Ultimate Notice', 'Custom link', $input['see_more_opt']['link'] );
            }
        } elseif ( isset( $_POST['reset_ultimate_notice_options'] ) ) {

            $input = $this->defaults['general'];

            add_settings_error( 'reset_ultimate_notice_options', 'reset_ultimate_notice_options', __( 'Settings restored to defaults.', 'ultimate-notice' ), 'updated' );

        }

        return $input;
    }

    /**
     * ultimate notice output.
     *
     * @return mixed
     */
    public function add_ultimate_notice() {
        if ( ! $this->cookies_set() || $this->options['general']['refuse_opt'] === 'yes' ) {
            // WPML >= 3.2
            if ( defined( 'ICL_SITEPRESS_VERSION' ) && version_compare( ICL_SITEPRESS_VERSION, '3.2', '>=' ) ) {
                $this->options['general']['message_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['message_text'], 'Ultimate Notice', 'Message in the notice' );
                $this->options['general']['accept_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['accept_text'], 'Ultimate Notice', 'Button text' );
                $this->options['general']['refuse_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['refuse_text'], 'Ultimate Notice', 'Refuse button text' );
                $this->options['general']['revoke_text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['revoke_text'], 'Ultimate Notice', 'Revoke button text' );
                $this->options['general']['see_more_opt']['text'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['see_more_opt']['text'], 'Ultimate Notice', 'Privacy policy text' );
                $this->options['general']['see_more_opt']['link'] = apply_filters( 'wpml_translate_single_string', $this->options['general']['see_more_opt']['link'], 'Ultimate Notice', 'Custom link' );
                // WPML and Polylang compatibility
            } elseif ( function_exists( 'icl_t' ) ) {
                $this->options['general']['message_text'] = icl_t( 'Ultimate Notice', 'Message in the notice', $this->options['general']['message_text'] );
                $this->options['general']['accept_text'] = icl_t( 'Ultimate Notice', 'Button text', $this->options['general']['accept_text'] );
                $this->options['general']['refuse_text'] = icl_t( 'Ultimate Notice', 'Refuse button text', $this->options['general']['refuse_text'] );
                $this->options['general']['revoke_text'] = icl_t( 'Ultimate Notice', 'Revoke button text', $this->options['general']['revoke_text'] );
                $this->options['general']['see_more_opt']['text'] = icl_t( 'Ultimate Notice', 'Privacy policy text', $this->options['general']['see_more_opt']['text'] );
                $this->options['general']['see_more_opt']['link'] = icl_t( 'Ultimate Notice', 'Custom link', $this->options['general']['see_more_opt']['link'] );
            }

            if ( function_exists( 'icl_object_id' ) )
                $this->options['general']['see_more_opt']['id'] = icl_object_id( $this->options['general']['see_more_opt']['id'], 'page', true );

            // get cookie container args
            $options = apply_filters( 'ultimate_notice_ultimate_notice_args', array(
                'position'			=> $this->options['general']['position'],
                'css_style'			=> $this->options['general']['css_style'],
                'css_class'			=> $this->options['general']['css_class'],
                'button_class'		=> 'ultimate-notice-button',
                'colors'			=> $this->options['general']['colors'],
                'message_text'		=> $this->options['general']['message_text'],
                'accept_text'		=> $this->options['general']['accept_text'],
                'refuse_text'		=> $this->options['general']['refuse_text'],
                'revoke_text'		=> $this->options['general']['revoke_text'],
                'refuse_opt'		=> $this->options['general']['refuse_opt'],
                'see_more'			=> $this->options['general']['see_more'],
                'see_more_opt'		=> $this->options['general']['see_more_opt'],
                'link_target'		=> $this->options['general']['link_target'],
            ) );

            $options['css_class'] = esc_attr( $options['css_class'] );

            // message output
            $output = '
			<div id="ultimate-notice" role="banner" class="ultimate-notice-' . ( $options['position'] ) . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . '" style="color: ' . $options['colors']['text'] . '; background-color: ' . $options['colors']['bar'] . ';">'
                . '<div class="ultimate-notice-container"><span id="ultimate-notice-notice-text">'. $options['message_text'] .'</span>'
                . '<a href="#" id="ultimate-notice-accept-cookie" data-cookie-set="accept" class="ultimate-notice-set-cookie ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '">' . $options['accept_text'] . '</a>'
                . ( $options['refuse_opt'] === 'yes' ? '<a href="#" id="ultimate-notice-refuse-cookie" data-cookie-set="refuse" class="ultimate-notice-set-cookie ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '">' . $options['refuse_text'] . '</a>' : '' )
                . ( $options['see_more'] === 'yes' ? '<a href="' . ( $options['see_more_opt']['link_type'] === 'custom' ? $options['see_more_opt']['link'] : get_permalink( $options['see_more_opt']['id'] ) ) . '" target="' . $options['link_target'] . '" id="ultimate-notice-more-info" class="ultimate-notice-more-info ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '">' . $options['see_more_opt']['text'] . '</a>' : '' ) . '
				</div>
				' . ( $options['refuse_opt'] === 'yes' ? '<div class="ultimate-notice-revoke-container"><a href="#" class="ultimate-notice-revoke-cookie ' . $options['button_class'] . ( $options['css_style'] !== 'none' ? ' ' . $options['css_style'] : '' ) . ( $options['css_class'] !== '' ? ' ' . $options['css_class'] : '' ) . '">' . esc_html( $options['revoke_text'] ) . '</a></div>' : '' ) . '
			</div>';

            echo apply_filters( 'ultimate_notice_ultimate_notice_output', $output, $options );
        }
    }

    /**
     * Check if cookies are accepted.
     *
     * @return bool
     */
    public static function cookies_accepted() {
        return apply_filters( 'ultimate_notice_is_cookie_accepted', isset( $_COOKIE['ultimate_notice_accepted'] ) && $_COOKIE['ultimate_notice_accepted'] === 'true' );
    }

    /**
     * Check if cookies are set.
     *
     * @return boolean Whether cookies are set
     */
    public function cookies_set() {
        return apply_filters( 'ultimate_notice_is_cookie_set', isset( $_COOKIE['ultimate_notice_accepted'] ) );
    }

    /**
     * Get default settings.
     */
    public function get_defaults() {
        return $this->defaults;
    }

    /**
     * Add links to support forum.
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function plugin_row_meta( $links, $file ) {
        if ( ! current_user_can( apply_filters( 'ultimate_notice_manage_ultimate_notice_cap', 'manage_options' ) ) )
            return $links;

        if ( $file == plugin_basename( __FILE__ ) )
            return array_merge( $links, array( sprintf( '<a href="http://www.mantrabrain.com/support/forum/ultimate-notice/" target="_blank">%s</a>', __( 'Support', 'ultimate-notice' ) ) ) );

        return $links;
    }

    /**
     * Add links to settings page.
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function plugin_action_links( $links, $file ) {
        if ( ! current_user_can( apply_filters( 'ultimate_notice_manage_ultimate_notice_cap', 'manage_options' ) ) )
            return $links;

        if ( $file == plugin_basename( __FILE__ ) )
            array_unshift( $links, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=ultimate-notice' ), __( 'Settings', 'ultimate-notice' ) ) );

        return $links;
    }

    /**
     * Activate the plugin.
     */
    public function activation() {
        add_option( 'ultimate_notice_options', $this->defaults['general'], '', 'no' );
        add_option( 'ultimate_notice_version', $this->defaults['version'], '', 'no' );
    }

    /**
     * Deactivate the plugin.
     */
    public function deactivation() {
        if ( $this->options['general']['deactivation_delete'] === 'yes' )
            delete_option( 'ultimate_notice_options' );
    }

    /**
     * Get allowed script blocking HTML.
     *
     * @return array
     */
    public function get_allowed_html() {
        return apply_filters(
            'ultimate_notice_refuse_code_allowed_html',
            array_merge(
                wp_kses_allowed_html( 'post' ),
                array(
                    'script' => array(
                        'type' => array(),
                        'src' => array(),
                        'charset' => array(),
                        'async' => array()
                    ),
                    'noscript' => array(),
                    'style' => array(
                        'type' => array()
                    ),
                    'iframe' => array(
                        'src' => array(),
                        'height' => array(),
                        'width' => array(),
                        'frameborder' => array(),
                        'allowfullscreen' => array()
                    )
                )
            )
        );
    }

    /**
     * Load scripts and styles - admin.
     */
    public function admin_enqueue_scripts( $page ) {
        if ( $page !== 'settings_page_ultimate-notice' )
            return;

        wp_enqueue_script(
            'ultimate-notice-admin', ULTIMATE_NOTICE_PLUGIN_URL.'/assets/js/admin.js'
        );

        wp_localize_script(
            'ultimate-notice-admin', 'cnArgs', array(
                'resetToDefaults'	=> __( 'Are you sure you want to reset these settings to defaults?', 'ultimate-notice' )
            )
        );

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'ultimate-notice-admin', ULTIMATE_NOTICE_PLUGIN_URL.'/assets/css/admin.css',array(),'1.0.0');
    }

    /**
     * Load scripts and styles - frontend.
     */
    public function wp_enqueue_scripts() {
        wp_enqueue_script(
            'ultimate-notice-front', ULTIMATE_NOTICE_PLUGIN_URL.'/assets/js/main.js'
        );

        wp_localize_script(
            'ultimate-notice-front',
            'cnArgs',
            array(
                'ajaxurl'				=> admin_url( 'admin-ajax.php' ),
                'hideEffect'			=> $this->options['general']['hide_effect'],
                'onScroll'				=> $this->options['general']['on_scroll'],
                'onScrollOffset'		=> $this->options['general']['on_scroll_offset'],
                'cookieName'			=> 'ultimate_notice_accepted',
                'cookieValue'			=> 'true',
                'cookieTime'			=> $this->times[$this->options['general']['time']][1],
                'cookiePath'			=> ( defined( 'COOKIEPATH' ) ? COOKIEPATH : '' ),
                'cookieDomain'			=> ( defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '' ),
                'redirection'			=> $this->options['general']['redirection'],
                'cache'					=> defined( 'WP_CACHE' ) && WP_CACHE,
                'refuse'				=> $this->options['general']['refuse_opt'],
                'revoke_cookies'		=> (int) $this->options['general']['revoke_cookies'],
                'revoke_cookies_opt'	=> $this->options['general']['revoke_cookies_opt'],
                'secure'				=> (int) is_ssl()
            )
        );

        wp_enqueue_style( 'ultimate-notice-front', ULTIMATE_NOTICE_PLUGIN_URL.'/assets/css/main.css',array(),'1.0.0');
    }

    /**
     * Print non functional JavaScript in body.
     *
     * @return mixed
     */
    public function wp_print_footer_scripts() {
        if ( $this->cookies_accepted() ) {
            $scripts = apply_filters( 'ultimate_notice_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code'], $this->get_allowed_html() ) ) ) );

            if ( ! empty( $scripts ) )
                echo $scripts;
        }
    }

    /**
     * Print non functional JavaScript in header.
     *
     * @return mixed
     */
    public function wp_print_header_scripts() {
        if ( $this->cookies_accepted() ) {
            $scripts = apply_filters( 'ultimate_notice_refuse_code_scripts_html', html_entity_decode( trim( wp_kses( $this->options['general']['refuse_code_head'], $this->get_allowed_html() ) ) ) );

            if ( ! empty( $scripts ) )
                echo $scripts;
        }
    }
}