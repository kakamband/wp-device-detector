<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Plugin
 * @author  Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since   1.0.0
 */

namespace PODeviceDetector\Plugin;

use PODeviceDetector\Plugin\Feature\Analytics;
use PODeviceDetector\Plugin\Feature\AnalyticsFactory;
use PODeviceDetector\System\Assets;
use PODeviceDetector\System\Environment;
use PODeviceDetector\System\Logger;
use PODeviceDetector\System\Role;
use PODeviceDetector\System\Option;
use PODeviceDetector\System\Form;
use PODeviceDetector\System\Blog;
use PODeviceDetector\System\Date;
use PODeviceDetector\System\Timezone;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Plugin
 * @author  Pierre Lannoy <https://pierre.lannoy.fr/>.
 * @since   1.0.0
 */
class Device_Detector_Admin {

	/**
	 * The assets manager that's responsible for handling all assets of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Assets    $assets    The plugin assets manager.
	 */
	protected $assets;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->assets = new Assets();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		$this->assets->register_style( PODD_ASSETS_ID, PODD_ADMIN_URL, 'css/device-detector.min.css' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$this->assets->register_script( PODD_ASSETS_ID, PODD_ADMIN_URL, 'js/device-detector.min.js', [ 'jquery' ] );
	}

	/**
	 * Set the items in the settings menu.
	 *
	 * @since 1.0.0
	 */
	public function init_admin_menus() {
		if ( Role::SUPER_ADMIN === Role::admin_type() || Role::SINGLE_ADMIN === Role::admin_type() ) {
			/* translators: as in the sentence "Device Detector Settings" or "WordPress Settings" */
			$settings = add_submenu_page( 'options-general.php', sprintf( esc_html__( '%s Settings', 'device-detector' ), PODD_PRODUCT_NAME ), PODD_PRODUCT_NAME, 'manage_options', 'podd-settings', [ $this, 'get_settings_page' ] );
		}
	}

	/**
	 * Initializes settings sections.
	 *
	 * @since 1.0.0
	 */
	public function init_settings_sections() {
		add_settings_section( 'podd_plugin_features_section', esc_html__( 'Plugin Features', 'device-detector' ), [ $this, 'plugin_features_section_callback' ], 'podd_plugin_features_section' );
		add_settings_section( 'podd_plugin_options_section', esc_html__( 'Plugin options', 'device-detector' ), [ $this, 'plugin_options_section_callback' ], 'podd_plugin_options_section' );
	}

	/**
	 * Add links in the "Actions" column on the plugins view page.
	 *
	 * @param string[] $actions     An array of plugin action links. By default this can include 'activate',
	 *                              'deactivate', and 'delete'.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data. See `get_plugin_data()`.
	 * @param string   $context     The plugin context. By default this can include 'all', 'active', 'inactive',
	 *                              'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 * @return array Extended list of links to print in the "Actions" column on the Plugins page.
	 * @since 1.0.0
	 */
	public function add_actions_links( $actions, $plugin_file, $plugin_data, $context ) {
		$actions[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'options-general.php?page=podd-settings' ) ), esc_html__( 'Settings', 'device-detector' ) );
		$actions[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'tools.php?page=podd-tools' ) ), esc_html__( 'Tools', 'device-detector' ) );
		$actions[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'tools.php?page=podd-viewer' ) ), esc_html__( 'Statistics', 'device-detector' ) );
		return $actions;
	}

	/**
	 * Add links in the "Description" column on the plugins view page.
	 *
	 * @param array  $links List of links to print in the "Description" column on the Plugins page.
	 * @param string $file Path to the plugin file relative to the plugins directory.
	 * @return array Extended list of links to print in the "Description" column on the Plugins page.
	 * @since 1.0.0
	 */
	public function add_row_meta( $links, $file ) {
		if ( 0 === strpos( $file, PODD_SLUG . '/' ) ) {
			$links[] = '<a href="https://wordpress.org/support/plugin/' . PODD_SLUG . '/">' . __( 'Support', 'device-detector' ) . '</a>';
			$links[] = '<a href="https://github.com/Pierre-Lannoy/wp-device-detector">' . __( 'GitHub repository', 'device-detector' ) . '</a>';
		}
		return $links;
	}

	/**
	 * Get the content of the tools page.
	 *
	 * @since 1.0.0
	 */
	public function get_tools_page() {
		include PODD_ADMIN_DIR . 'partials/device-detector-admin-tools.php';
	}

	/**
	 * Get the content of the viewer page.
	 *
	 * @since 1.0.0
	 */
	public function get_viewer_page() {
		$analytics = AnalyticsFactory::get_analytics();
		include PODD_ADMIN_DIR . 'partials/device-detector-admin-view-analytics.php';
	}

	/**
	 * Get the content of the settings page.
	 *
	 * @since 1.0.0
	 */
	public function get_settings_page() {
		if ( ! ( $tab = filter_input( INPUT_GET, 'tab' ) ) ) {
			$tab = filter_input( INPUT_POST, 'tab' );
		}
		if ( ! ( $action = filter_input( INPUT_GET, 'action' ) ) ) {
			$action = filter_input( INPUT_POST, 'action' );
		}
		if ( $action && $tab ) {
			switch ( $tab ) {
				case 'misc':
					switch ( $action ) {
						case 'do-save':
							if ( Role::SUPER_ADMIN === Role::admin_type() || Role::SINGLE_ADMIN === Role::admin_type() ) {
								if ( ! empty( $_POST ) && array_key_exists( 'submit', $_POST ) ) {
									$this->save_options();
								} elseif ( ! empty( $_POST ) && array_key_exists( 'reset-to-defaults', $_POST ) ) {
									$this->reset_options();
								}
							}
							break;
					}
					break;
			}
		}
		include PODD_ADMIN_DIR . 'partials/device-detector-admin-settings-main.php';
	}

	/**
	 * Save the plugin options.
	 *
	 * @since 1.0.0
	 */
	private function save_options() {
		if ( ! empty( $_POST ) ) {
			if ( array_key_exists( '_wpnonce', $_POST ) && wp_verify_nonce( $_POST['_wpnonce'], 'podd-plugin-options' ) ) {
				$old_frequency = Option::network_get( 'reset_frequency' );
				Option::network_set( 'use_cdn', array_key_exists( 'podd_plugin_options_usecdn', $_POST ) ? (bool) filter_input( INPUT_POST, 'podd_plugin_options_usecdn' ) : false );
				Option::network_set( 'display_nag', array_key_exists( 'podd_plugin_options_nag', $_POST ) ? (bool) filter_input( INPUT_POST, 'podd_plugin_options_nag' ) : false );
				Option::network_set( 'analytics', array_key_exists( 'podd_plugin_features_analytics', $_POST ) ? (bool) filter_input( INPUT_POST, 'podd_plugin_features_analytics' ) : false );
				Option::network_set( 'history', array_key_exists( 'podd_plugin_features_history', $_POST ) ? (string) filter_input( INPUT_POST, 'podd_plugin_features_history', FILTER_SANITIZE_NUMBER_INT ) : Option::network_get( 'history' ) );
				Option::network_set( 'reset_frequency', array_key_exists( 'podd_plugin_features_reset_frequency', $_POST ) ? (string) filter_input( INPUT_POST, 'podd_plugin_features_reset_frequency', FILTER_SANITIZE_STRING ) : $old_frequency );
				Option::network_set( 'warmup', array_key_exists( 'podd_plugin_features_warmup', $_POST ) ? (bool) filter_input( INPUT_POST, 'podd_plugin_features_warmup' ) : false );
				if ( Option::network_get( 'reset_frequency' ) !== $old_frequency ) {
					wp_clear_scheduled_hook( PODD_CRON_RESET_NAME );
				}
				if ( ! Option::network_get( 'analytics' ) ) {
					wp_clear_scheduled_hook( PODD_CRON_STATS_NAME );
				}
				$message = esc_html__( 'Plugin settings have been saved.', 'device-detector' );
				$code    = 0;
				add_settings_error( 'podd_no_error', $code, $message, 'updated' );
				Logger::info( 'Plugin settings updated.', $code );
			} else {
				$message = esc_html__( 'Plugin settings have not been saved. Please try again.', 'device-detector' );
				$code    = 2;
				add_settings_error( 'podd_nonce_error', $code, $message, 'error' );
				Logger::warning( 'Plugin settings not updated.', $code );
			}
		}
	}

	/**
	 * Reset the plugin options.
	 *
	 * @since 1.0.0
	 */
	private function reset_options() {
		if ( ! empty( $_POST ) ) {
			if ( array_key_exists( '_wpnonce', $_POST ) && wp_verify_nonce( $_POST['_wpnonce'], 'podd-plugin-options' ) ) {
				Option::reset_to_defaults();
				$message = esc_html__( 'Plugin settings have been reset to defaults.', 'device-detector' );
				$code    = 0;
				add_settings_error( 'podd_no_error', $code, $message, 'updated' );
				Logger::info( 'Plugin settings reset to defaults.', $code );
			} else {
				$message = esc_html__( 'Plugin settings have not been reset to defaults. Please try again.', 'device-detector' );
				$code    = 2;
				add_settings_error( 'podd_nonce_error', $code, $message, 'error' );
				Logger::warning( 'Plugin settings not reset to defaults.', $code );
			}
		}
	}

	/**
	 * Callback for plugin options section.
	 *
	 * @since 1.0.0
	 */
	public function plugin_options_section_callback() {
		$form = new Form();
		if ( defined( 'DECALOG_VERSION' ) ) {
			$help  = '<img style="width:16px;vertical-align:text-bottom;" src="' . \Feather\Icons::get_base64( 'thumbs-up', 'none', '#00C800' ) . '" />&nbsp;';
			$help .= sprintf( esc_html__('Your site is currently using %s.', 'device-detector' ), '<em>DecaLog v' . DECALOG_VERSION .'</em>' );
		} else {
			$help  = '<img style="width:16px;vertical-align:text-bottom;" src="' . \Feather\Icons::get_base64( 'alert-triangle', 'none', '#FF8C00' ) . '" />&nbsp;';
			$help .= sprintf( esc_html__('Your site does not use any logging plugin. To log all events triggered in Device Detector, I recommend you to install the excellent (and free) %s. But it is not mandatory.', 'device-detector' ), '<a href="https://wordpress.org/plugins/decalog/">DecaLog</a>' );
		}
		add_settings_field(
			'podd_plugin_options_logger',
			esc_html__( 'Logging', 'device-detector' ),
			[ $form, 'echo_field_simple_text' ],
			'podd_plugin_options_section',
			'podd_plugin_options_section',
			[
				'text' => $help
			]
		);
		register_setting( 'podd_plugin_options_section', 'podd_plugin_options_logger' );
		add_settings_field(
			'podd_plugin_options_usecdn',
			esc_html__( 'Resources', 'device-detector' ),
			[ $form, 'echo_field_checkbox' ],
			'podd_plugin_options_section',
			'podd_plugin_options_section',
			[
				'text'        => esc_html__( 'Use public CDN', 'device-detector' ),
				'id'          => 'podd_plugin_options_usecdn',
				'checked'     => Option::network_get( 'use_cdn' ),
				'description' => esc_html__( 'If checked, Device Detector will use a public CDN (jsDelivr) to serve scripts and stylesheets.', 'device-detector' ),
				'full_width'  => true,
				'enabled'     => true,
			]
		);
		register_setting( 'podd_plugin_options_section', 'podd_plugin_options_usecdn' );
		add_settings_field(
			'podd_plugin_options_nag',
			esc_html__( 'Admin notices', 'device-detector' ),
			[ $form, 'echo_field_checkbox' ],
			'podd_plugin_options_section',
			'podd_plugin_options_section',
			[
				'text'        => esc_html__( 'Display', 'device-detector' ),
				'id'          => 'podd_plugin_options_nag',
				'checked'     => Option::network_get( 'display_nag' ),
				'description' => esc_html__( 'Allows Device Detector to display admin notices throughout the admin dashboard.', 'device-detector' ) . '<br/>' . esc_html__( 'Note: Device Detector respects DISABLE_NAG_NOTICES flag.', 'device-detector' ),
				'full_width'  => true,
				'enabled'     => true,
			]
		);
		register_setting( 'podd_plugin_options_section', 'podd_plugin_options_nag' );
	}

	/**
	 * Get the available frequencies.
	 *
	 * @return array An array containing the history modes.
	 * @since  3.2.0
	 */
	protected function get_frequencies_array() {
		$result   = [];
		$result[] = [ 'never', esc_html__( 'Never', 'device-detector' ) ];
		$result[] = [ 'hourly', esc_html__( 'Once Hourly', 'device-detector' ) ];
		$result[] = [ 'twicedaily', esc_html__( 'Twice Daily', 'device-detector' ) ];
		$result[] = [ 'daily', esc_html__( 'Once Daily', 'device-detector' ) ];
		return $result;
	}

	/**
	 * Get the available history retentions.
	 *
	 * @return array An array containing the history modes.
	 * @since  3.2.0
	 */
	protected function get_retentions_array() {
		$result = [];
		for ( $i = 1; $i < 4; $i++ ) {
			// phpcs:ignore
			$result[] = [ (int) ( 7 * $i ), esc_html( sprintf( _n( '%d week', '%d weeks', $i, 'device-detector' ), $i ) ) ];
		}
		for ( $i = 1; $i < 4; $i++ ) {
			// phpcs:ignore
			$result[] = [ (int) ( 30 * $i ), esc_html( sprintf( _n( '%d month', '%d months', $i, 'device-detector' ), $i ) ) ];
		}
		return $result;
	}

	/**
	 * Callback for plugin features section.
	 *
	 * @since 1.0.0
	 */
	public function plugin_features_section_callback() {
		$form = new Form();
		add_settings_field(
			'podd_plugin_features_analytics',
			esc_html__( 'Analytics', 'device-detector' ),
			[ $form, 'echo_field_checkbox' ],
			'podd_plugin_features_section',
			'podd_plugin_features_section',
			[
				'text'        => esc_html__( 'Activated', 'device-detector' ),
				'id'          => 'podd_plugin_features_analytics',
				'checked'     => Option::network_get( 'analytics' ),
				'description' => esc_html__( 'If checked, Device Detector will analyze OPcache operations and store statistics every five minutes.', 'device-detector' ) . '<br/>' . esc_html__( 'Note: for this to work, your WordPress site must have an operational CRON.', 'device-detector' ),
				'full_width'  => true,
				'enabled'     => true,
			]
		);
		register_setting( 'podd_plugin_features_section', 'podd_plugin_features_analytics' );
		add_settings_field(
			'podd_plugin_features_history',
			esc_html__( 'Historical data', 'device-detector' ),
			[ $form, 'echo_field_select' ],
			'podd_plugin_features_section',
			'podd_plugin_features_section',
			[
				'list'        => $this->get_retentions_array(),
				'id'          => 'podd_plugin_features_history',
				'value'       => Option::network_get( 'history' ),
				'description' => esc_html__( 'Maximum age of data to keep for statistics.', 'device-detector' ),
				'full_width'  => true,
				'enabled'     => true,
			]
		);
		register_setting( 'podd_plugin_features_section', 'podd_plugin_features_history' );
		add_settings_field(
			'podd_plugin_features_reset_frequency',
			esc_html__( 'Site invalidation', 'device-detector' ),
			[ $form, 'echo_field_select' ],
			'podd_plugin_features_section',
			'podd_plugin_features_section',
			[
				'list'        => $this->get_frequencies_array(),
				'id'          => 'podd_plugin_features_reset_frequency',
				'value'       => Option::network_get( 'reset_frequency' ),
				'description' => esc_html__( 'Frequency at which files belonging to this site must be automatically reset.', 'device-detector' ),
				'full_width'  => true,
				'enabled'     => true,
			]
		);
		register_setting( 'podd_plugin_features_section', 'podd_plugin_features_reset_frequency' );
		if ( Environment::is_wordpress_multisite() ) {
			$warmup      = esc_html__( 'Network warm-up', 'device-detector' );
			$description = esc_html__( 'If checked, Device Detector will warm-up the full network (all sites) after each automatic site invalidation.', 'device-detector' );
		} else {
			$warmup      = esc_html__( 'Site warm-up', 'device-detector' );
			$description = esc_html__( 'If checked, Device Detector will warm-up the full site after each automatic site invalidation.', 'device-detector' );
		}
		add_settings_field(
			'podd_plugin_features_warmup',
			$warmup,
			[ $form, 'echo_field_checkbox' ],
			'podd_plugin_features_section',
			'podd_plugin_features_section',
			[
				'text'        => esc_html__( 'Activated', 'device-detector' ),
				'id'          => 'podd_plugin_features_warmup',
				'checked'     => Option::network_get( 'warmup' ),
				'description' => $description,
				'full_width'  => true,
				'enabled'     => true,
			]
		);
		register_setting( 'podd_plugin_features_section', 'podd_plugin_features_warmup' );
	}

}
