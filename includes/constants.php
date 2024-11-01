<?php

/**
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
if(!defined('ABSPATH')) {
	die();
}

// Constants to define the plugin's basic properties within the ecosystem.
const PLUGIN_NAME  = 'Site Info';
const PLUGIN_SLUG  = 'alar-site-info';
const SERVER_SLUG  = 'site-info';
const SETTINGS_VAR = 'alar_site_info_settings';
const MENU_ICON    = 'dashicons-star-filled';
const MENU_POS     = 66;

// Get the core file.php file.
require_once(ABSPATH.'wp-admin/includes/file.php');

/**
 * Now that we've got access to the get_home_path() function, we can define some
 * paths and URLs that tend to be repeated in a plugin. These are defined mostly
 * to allow for faster IDE autocompletion. Trailing slashes are not added. Using
 * define() for the PATH_HOME and URL_HOME constants is intentional; it allows a
 * function to be in the value, whereas using the const keyword does not. To get
 * those constants namespaced, the namespace must be manually prepended.
 */
define(__NAMESPACE__.'\PATH_HOME',   untrailingslashit(get_home_path()));
define(__NAMESPACE__.'\URL_HOME',    untrailingslashit(home_url()));
define(__NAMESPACE__.'\URL_PLUGINS', plugins_url());
define(__NAMESPACE__.'\URL_SELF',    URL_PLUGINS.'/'.PLUGIN_SLUG);
define(__NAMESPACE__.'\URL_SCRIPTS', URL_SELF.'/scripts');
define(__NAMESPACE__.'\URL_STYLES',  URL_SELF.'/styles');
