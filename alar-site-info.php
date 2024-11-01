<?php

/**
 * Plugin Name: Site Info
 * Description: Discover your current versions for WordPress, PHP, plugins, themes, and various other server information.
 * Version: 1.2.1
 * Author: John Alarcon
 * Author URI: https://twitter.com/realJohnAlarcon
 * Text Domain: alar-site-info
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * This is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * See http://www.gnu.org/licenses/gpl-2.0.txt.
 *
 * © 2018, John Alarcon
 * https://twitter.com/realJohnAlarcon
 * https://www.linkedin.com/in/alarconjohn
 *
 */

// Declare the namespace.
namespace ALARSITEINFO;

// Prevent direct access.
if(! defined('ABSPATH')) {
	die();
}

/**
 * The primary plugin class.
 *
 * This class sets up the plugin with constants and autoloads needed classes, as
 * well as registering essentials for plugin settings, admin menu, and the basic
 * scripts and styles used by the plugin.
 *
 * @author John Alarcon
 *
 */
class SiteInfo {

	/**
	 * Minimum PHP version required.
	 *
	 * If you use a single digit, x, all versions >= x.0.0 will be allowed. When
	 * you need to be more specific, you can do so. For example, if the property
	 * is set to '7.2.3', then sites running on PHP < 7.2.3 would not be able to
	 * complete the installation – sites running on PHP >= 7.2.3 would get right
	 * through the installation transparently.
	 *
	 * @var	string Minimum PHP version required for this plugin.
	 * @since 1.0.0
	 */
	public $min_php = '5.3';

	/**
	 * Default platform.
	 *
	 * @var string
	 */
	public $platform = 'WordPress';

	/**
	 * The constructor provides a PHP gate to ensure that installation cannot be
	 * completed on an incompatible PHP version.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// This plugin requires at least PHP 5.3. Abort if less than that.
		if(version_compare('5.3', $this->min_php, '<')) {
			add_action('admin_notices', [$this, 'prevent_installation']);
			return;
		}

		// Ensure minimum PHP is met or abort installation.
		if(version_compare(PHP_VERSION, $this->min_php, '<')) {
			add_action('admin_notices', [$this, 'prevent_installation']);
			return;
		}

		if (function_exists('classicpress_version')) {
			$this->platform = 'ClassicPress';
		}

		// If here, PHP version is acceptable – load the plugin.
		$this->init();
	}

	/**
	 * Load the plugin.
	 *
	 * @since 1.0.0
	 */
	private function init() {

		// Load constants used within the plugin; do this first.
	 	require_once plugin_dir_path(__FILE__).'includes/constants.php';

		// Register hooks for plugin activation and deactivation; use $this.
		register_activation_hook(__FILE__,   [$this, 'activate_plugin']);
		register_deactivation_hook(__FILE__, [$this, 'deactivate_plugin']);

		// Register hook for plugin deletion; use __CLASS__.
		register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall_plugin']);

		// Register admin menu and notices.
		add_action('admin_menu', [$this, 'register_admin_menu']);

		// Register scripts and styles.
		add_action('admin_enqueue_scripts', [$this, 'register_backend_scripts'], 10);
		add_action('admin_enqueue_scripts', [$this, 'register_backend_styles'], 10);
	}

	/**
	 * Register the plugin's menu item under the Tools menu; only displays to
	 * admins.
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_submenu_page(
			'tools.php',
			__(PLUGIN_NAME, 'alar-site-info'),
			__(PLUGIN_NAME, 'alar-site-info'),
			'manage_options',
			PLUGIN_SLUG,
			[$this, 'render_site_info']
		);
	}

	/**
	 * Register backend scripts
	 *
	 * @since 1.0.0
	 */
	public function register_backend_scripts() {
		if(strstr($GLOBALS['plugin_page'], PLUGIN_SLUG)) {
			wp_enqueue_script('jquery-ui-tooltip');
			wp_enqueue_script(PLUGIN_SLUG.'-admin', URL_PLUGINS.'/'.SERVER_SLUG.'/scripts/alar-site-info.js', ['jquery']);
		}
	}

	/**
	 * Register backend styles
	 *
	 * @since 1.0.0
	 */
	public function register_backend_styles() {
		if(strstr($GLOBALS['plugin_page'], PLUGIN_SLUG)) {
			wp_enqueue_style(PLUGIN_SLUG.'-tooltips', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
			wp_enqueue_style(PLUGIN_SLUG.'-admin', URL_PLUGINS.'/'.SERVER_SLUG.'/styles/alar-site-info.css');
		}
	}

	/**
	 * Get markup for a list item.
	 *
	 * @since 1.0.0
	 */
	public function get_list_item($dashicon, $tooltip, $message) {
		$item = '<li>';
		$item .= '<a href="#" title="'.$tooltip.'" class="site-info"><span class="dashicons-before dashicons-'.$dashicon.'"></span></a> ';
		$item .= $message;
		$item .= '</li>';
		return $item;
	}

	/**
	 * Render the information page.
	 *
	 * @since 1.0.0
	 */
	public function render_site_info() {

		// Globalize the database object.
		global $wpdb;

		// Include core version.php file.
		include(PATH_HOME.'/wp-includes/version.php');

		// Include core file.php file.
		if(!function_exists('get_home_path')) {
			$inc_file_path = str_replace(
				untrailingslashit(home_url()),
				untrailingslashit(ABSPATH),
				untrailingslashit(get_admin_url())
				);
			require_once $inc_file_path.'/includes/file.php';
		}

		// Open container and add plugin title.
		echo '<div class="wrap">'."\n";
		echo '<h1 class="wp-heading">'.$this->platform.' '.__('Information', 'alar-site-info').'</h1>'."\n";

		// WP VERSION
		echo '<h2 class="alar-site-info-subheading">'.__('Installed Version', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';

		if (function_exists('classicpress_version')) {
			// If ClassicPress.
			$dashicon = 'yes';
			$tooltip = '';
			$message = classicpress_version();
			echo $this->get_list_item($dashicon, $tooltip, $message);
		} else {
			// If WordPress.

			if (!isset($wp_version)) {
				$dashicon = 'warning';
				$tooltip = __('Platform version not detected.', 'alar-site-info');
				$message = $tooltip;
				echo $this->get_list_item($dashicon, $tooltip, $message);
			} else {
				$response = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');
				$obj = json_decode($response['body']);
				$latest_version = $obj->offers[0]->version;
				if (version_compare($wp_version, $latest_version, '=')) {
					$dashicon = 'yes';
					$tooltip = '';
					$message = $wp_version;
				} else {
					$dashicon = 'warning';
					$tooltip = __('For maximum security and stability, please update your site to the latest version.', 'alar-site-info');
					$message = $wp_version.' [<a href="'.admin_url().'update-core.php">'.__('Update Recommended', 'alar-site-info').'</a>]';
				}
				echo $this->get_list_item($dashicon, $tooltip, $message);
			}

		}

		echo '</ul>';

		// DATABASE
		echo '<h2 class="alar-site-info-subheading">'.__('Database', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		echo $this->get_list_item('yes', '', '<strong>'.__('Host', 'alar-site-info').'</strong>: '.DB_HOST);
		echo $this->get_list_item('yes', '', '<strong>'.__('Prefix', 'alar-site-info').'</strong>: '.$wpdb->prefix);
		echo $this->get_list_item('yes', '', '<strong>'.__('Charset', 'alar-site-info').'</strong>: '.$wpdb->charset);
		echo $this->get_list_item('yes', '', '<strong>'.__('Collation', 'alar-site-info').'</strong>: '.$wpdb->collate);
		if (function_exists('mysqli_connect')) {
			$db_handle = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			$db_server = mysqli_get_server_info($db_handle);
			echo $this->get_list_item('yes', '', '<strong>'.__('Type', 'alar-site-info').'</strong>: '.$db_server);
		}
		echo '</ul>';

		// THEMES
		$all_themes = wp_get_themes();
		$total_themes = count($all_themes);
		$current_theme_slug = get_option('template');
		echo '<h2 class="alar-site-info-subheading">'.__('Installed Themes', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		foreach ($all_themes as $theme_slug=>$theme_info) {
			if ($theme_slug === $current_theme_slug) {
				$dashicon = 'yes';
				$tooltip = '';
			} else {
				$dashicon = 'warning';
				$tooltip = __('This theme is inactive. As a security precaution, it is strongly recommended to delete unused themes, rather than leave them deactivated on the site.', 'alar-site-info');
			}
			$message = $theme_info->get('Name').' ['.$theme_info->get('Version').'] <span class="alar-site-info-author">'.__('by', 'alar-site-info').' <em>'.$theme_info->get('Author').'</em></span>';
			echo $this->get_list_item($dashicon, $tooltip, $message);
		}
		echo '</ul>';

		// PLUGINS
		$all_plugins = get_plugins();
		$active_plugins = get_option('active_plugins');
		$total_plugins = count($all_plugins);
		$total_active_plugins = count($active_plugins);
		echo '<h2 class="alar-site-info-subheading">'.__('Installed Plugins', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		foreach ($all_plugins as $plugin_file=>$plugin_info) {
			if (in_array($plugin_file, $active_plugins)) {
				$dashicon = 'yes';
				$tooltip = '';
			} else {
				$dashicon = 'warning';
				$tooltip = __('This plugin is deactivated. As a security precaution, it is strongly recommended to delete unused plugins, rather than leave them deactivated on the site.', 'alar-site-info');
			}
			$message = $plugin_info['Name'].' ['.$plugin_info['Version'].'] <span class="alar-site-info-author">'.__('by', 'alar-site-info').' <em>'.$plugin_info['Author'].'</em></span>';
			echo $this->get_list_item($dashicon, $tooltip, $message);
		}
		echo '</ul>';

		// URIs
		$uploads = wp_upload_dir();
		echo '<h2 class="alar-site-info-subheading">'.__('Paths and URLs', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		echo '<li class="alar-site-info-list-heading"><strong>'.__('Absolute Paths', 'alar-site-info').'</strong></li>';
		echo $this->get_list_item('yes', '', '<strong>'.__('Website', 'alar-site-info').'</strong>: '.get_home_path());
		echo $this->get_list_item('yes', '', '<strong>'.__('Theme', 'alar-site-info').'</strong>: '.get_stylesheet_directory());
		echo $this->get_list_item('yes', '', '<strong>'.__('Uploads', 'alar-site-info').'</strong>: '.$uploads['basedir']);
		echo '<li class="alar-site-info-list-heading"><strong>'.__('Absolute URLs', 'alar-site-info').'</strong></li>';
		echo $this->get_list_item('yes', '', '<strong>'.__('Website', 'alar-site-info').'</strong>: '.get_home_url());
		echo $this->get_list_item('yes', '', '<strong>'.__('Theme', 'alar-site-info').'</strong>: '.get_stylesheet_directory_uri());
		echo $this->get_list_item('yes', '', '<strong>'.__('Uploads', 'alar-site-info').'</strong>: '.$uploads['baseurl']);
		echo '</ul>';


		// --------------------------------------------------
		// HEADING: Server information
		// --------------------------------------------------
		echo '<h1 class="alar-site-info-heading">'.__('Server Information', 'alar-site-info').'</h1>';

		// SERVER OPERATING SYSTEM
		echo '<h2 class="alar-site-info-subheading">'.__('Operating System', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		echo $this->get_list_item('yes', '', php_uname());
		echo '</ul>';

		// SERVER SOFTWARE
 		if (isset($_SERVER['SERVER_SOFTWARE']) && !empty($_SERVER['SERVER_SOFTWARE'])) {
			$dashicon = 'yes';
			$tooltip = '';
			$message = $_SERVER['SERVER_SOFTWARE'];
		} else {
			if (isset($_SERVER['SERVER_SIGNATURE']) && !empty($_SERVER['SERVER_SIGNATURE'])) {
				$dashicon = 'yes';
				$tooltip = '';
				$message = str_replace(array('<address>','</address>'), '', $_SERVER['SERVER_SIGNATURE']);
			} else {
				$dashicon = 'warning';
				$tooltip = $message = __('Server software could not be determined.', 'alar-site-info');
			}
		}
		echo '<h2 class="alar-site-info-subheading">'.__('Server Software', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		echo $this->get_list_item($dashicon, $tooltip, $message);
		echo '</ul>';

		// SERVER PROTOCOL
		if (isset($_SERVER['SERVER_PROTOCOL']) && !empty($_SERVER['SERVER_PROTOCOL'])) {
			$dashicon = 'yes';
			$tooltip = '';
			$message = $_SERVER['SERVER_PROTOCOL'];
		} else {
			$dashicon = 'warning';
			$tooltip = $message = __('Server protocol not detected.', 'alar-site-info');
		}
		echo '<h2 class="alar-site-info-subheading">'.__('Protocol', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		echo $this->get_list_item($dashicon, $tooltip, $message);
		echo '</ul>';

		// SERVER PORT
		if (isset($_SERVER['SERVER_PORT']) && !empty($_SERVER['SERVER_PORT'])) {
			$dashicon = 'yes';
			$tooltip = '';
			$message = $_SERVER['SERVER_PORT'];
		} else {
			$dashicon = 'warning';
			$tooltip = $message = __('Server port not detected.', 'alar-site-info');
		}
		echo '<h2 class="alar-site-info-subheading">'.__('Port', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		echo $this->get_list_item($dashicon, $tooltip, $message);
		echo '</ul>';

		// SSL
		echo '<h2 class="alar-site-info-subheading">'.__('SSL', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		$message = '<strong>'.__('Supported', 'alar-site-info').'</strong>: ';
		if (isset($_SERVER['SSL_VERSION_LIBRARY']) && !empty($_SERVER['SSL_VERSION_LIBRARY'])) {
			$dashicon = 'yes';
			$tooltip = '';
			$message .= $_SERVER['SSL_VERSION_LIBRARY'];
		} else if (defined('OPENSSL_VERSION_TEXT')) {
			$dashicons = 'yes';
			$tooltip = '';
			$message .= OPENSSL_VERSION_TEXT;
		} else {
			$dashicon = 'warning';
			$tooltip = __('Unable to determine SSL version.', 'alar-site-info');
			$message .= $tooltip;
		}
		echo $this->get_list_item($dashicon, $tooltip, $message);
		$message = '<strong>'.__('Protocol', 'alar-site-info').'</strong>: ';
		if (isset($_SERVER['SSL_PROTOCOL']) && !empty($_SERVER['SSL_PROTOCOL'])) {
			$dashicon = 'yes';
			$tooltip = '';
			$message .= $_SERVER['SSL_PROTOCOL'];
		} else if (function_exists('curl_init')) {
			$ch = curl_init('https://www.howsmyssl.com/a/check');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($ch);
			curl_close($ch);
			$json = json_decode($data);
			$message .= $json->tls_version;
		} else {
			$dashicon = 'warning';
			$tooltip = __('Unable to determine SSL protocol.', 'alar-site-info');
			$message .= $tooltip;
		}
		echo $this->get_list_item($dashicon, $tooltip, $message);
		echo '</ul>';

		// HTTPS
		echo '<h2 class="alar-site-info-subheading">'.__('HTTPS', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		if ((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ||
			(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME']==='https') ||
			(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https')) {
			$dashicon = 'yes';
			$tooltip = '';
			$message = __('Enabled', 'alar-site-info');
		} else {
			$dashicon = 'warning';
			$tooltip = __('Data sent over this connection can be read by outside parties. Using a secure HTTPS connection provides encryption while transmitting data, and even gives your site a slight SEO boost. It is strongly recommended to update your site to only use HTTPS secure connections.', 'alar-site-info');
			$message = __('Not Used', 'alar-site-info');
		}
		echo $this->get_list_item($dashicon, $tooltip, $message);
		echo '</ul>';


		// cURL
		echo '<h2 class="alar-site-info-subheading">'.__('cURL', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		if (function_exists('curl_version')) {
			$curl = curl_version();
			$dashicon = 'yes';
			$tooltip = $message = $curl['version'];
			echo $this->get_list_item($dashicon, '', $message);
		} else {
			$dashicon = 'warning';
			$tooltip = $message = __('cURL is not enabled.', 'alar-site-info');
			echo $this->get_list_item('warning', $tooltip, $message);
		}
		echo '</ul>';

		// TRANSPORTS
		echo '<h2 class="alar-site-info-subheading">'.__('Supported Transports', 'alar-site-info').'</h2>';
		$transports = stream_get_transports();
		echo '<ul class="alar-site-info-indent-two">';
		foreach ($transports as $n=>$transport) {
			echo $this->get_list_item('yes', '', $transport);
		}
		echo '</ul>';

		// ------------------------------
		// PHP version & extensions
		// ------------------------------
		echo '<h2 class="alar-site-info-subheading">'.__('PHP', 'alar-site-info').'</h2>';
		echo '<ul class="alar-site-info-indent-two">';
		$dashicon = 'yes';
		$tooltip = '';
		$message = '<strong>'.__('Version', 'alar-site-info').'</strong>: '.PHP_VERSION.' [<a id="alar-site-info-show-full-php-info" href="#php-info-table">'.__('Show all PHP info', 'alar-site-info').'</a>]';
		echo $this->get_list_item($dashicon, $tooltip, $message);
		if (version_compare(PHP_VERSION, '5.6', '<')) {
			$dashicon = 'flag';
			$tooltip = __('Your PHP version is dangerously out of date. Update to PHP 7.x at your earliest opportunity.', 'alar-site-info');
			$message = '<strong>'.__('WARNING', 'alar-site-info').'</strong>: ';
			$message .= __('Your PHP version is dangerously out of date and may contain unpatched security holes. Updating to PHP 7.x immediately is strongly recommended.', 'alar-site-info');
			echo $this->get_list_item($dashicon, $tooltip, $message);
		} else if (version_compare(PHP_VERSION, 7, '<')) {
			$now = new \DateTime();
			$php5_eol = new \DateTime('01/01/2019 12:00AM');
			if ($now >= $php5_eol) {
				$dashicon = 'flag';
				$tooltip = __('Your PHP version is dangerously out of date. Update to PHP 7.x at your earliest opportunity.', 'alar-site-info');
				$message = '<strong>'.__('WARNING', 'alar-site-info').'</strong>: ';
				$message .= __('Your PHP version has reached <a href="https://secure.php.net/eol.php">end of life</a> and is no longer receiving security updates. Updating to PHP 7.x immediately is strongly recommended.', 'alar-site-info');
			} else {
				$dashicon = 'warning';
				$tooltip = __('Your PHP version reaches end of life on January 1, 2019. Update to PHP 7.x at your earliest opportunity.', 'alar-site-info');
				$message = '<strong>'.__('NOTICE', 'alar-site-info').'</strong>: ';
				$message .= __('Your PHP version reaches <a href="https://secure.php.net/eol.php">end of life</a> on January 1, 2019. At that time, security updates will be discontinued. Updating to PHP 7.x immediately is strongly recommended.', 'alar-site-info');
			}
			echo $this->get_list_item($dashicon, $tooltip, $message);
		}
		echo '</ul>';

		// FULL PHP INFO
		echo '<ul class="alar-site-info-indent-two alar-site-info-full-php-info">';
		echo '<li style="width:934px;"><a id="php-info-table"></a>';
		ob_start();
		phpinfo();
		$pinfo = ob_get_contents();
		ob_end_clean();
		$pinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$pinfo);
		$styles = '<style type="text/css">table {border-collapse: collapse; border: 0; width: 934px; box-shadow: 1px 2px 3px #ccc;}.center {text-align: center;}.center table {text-align: left;}.center th {text-align: center !important;}td, th {border: 1px solid #666; font-size: 100%; vertical-align: baseline; padding: 4px 5px;font-family:sans-serif;}.alar-site-info-indent-two h1 {font-size: 32pt;}.alar-site-info-indent-two h2 {font-size: 24pt;}.p {text-align: left;}.e {background-color: #ccf; width: 300px; font-weight: bold;}.h {background-color: #99c; font-weight: bold;}.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}.v i {color: #999;}img {float: right; border: 0;}hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}</style>';
		echo $styles;
		echo $pinfo;
		echo '</li>';
		echo '</ul>';

		// OUTER CONTAINER
		echo '</div><!-- .wrap -->';
	}

	/**
	 * Prevent installation
	 *
	 * @since 1.0.0
	 */
	public function prevent_installation() {
		// Prevent installation.
		deactivate_plugins(plugin_basename(__FILE__));
		if(isset($_GET['activate'])) {
			unset($_GET['activate']);
		}
		// Print an error message.
		$markup = '<div class="error">'."\n";
		$markup .= '<p><strong>'.__('Installation Cancelled', 'alar-site-info').'</strong></p>'."\n";
		$markup .= '<p>';
		$markup .= sprintf(
			__('This plugin requires PHP %s, or later. Your current version is PHP %s.', 'alar-site-info'),
			$this->min_php,
			phpversion()
		);
		$markup .= ' '.__('Please contact your web hosting provider for assistance in upgrading.', 'alar-site-info').'</p>'."\n";
		$markup .= '</div>'."\n";
		echo $markup;
	}

	/**
	 * Plugin activation
	 *
	 * This method has no current use, but is included for completeness.
	 *
	 * @since 1.0.0
	 */
	public function activate_plugin() {
		if (!current_user_can('activate_plugins')) {
			return;
		}
	}

	/**
	 * Plugin deactivation
	 *
	 * This method has no current use, but is included for completeness.
	 *
	 * @since 1.0.0
	 */
	public function deactivate_plugin() {
		if (!current_user_can('activate_plugins')) {
			return;
		}
	}

	/**
	 * Plugin deletion
	 *
	 * This method has no current use, but is included for completeness.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall_plugin() {
		if (!current_user_can('delete_plugins')) {
			return;
		}
	}

}

new SiteInfo;