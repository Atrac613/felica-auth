<?php
/*
 Plugin Name: FeliCa Auth
 Plugin URI: https://github.com/Atrac613/felica-auth
 Description: Allows the use of FeliCa for account authentication.
 Author: Atrac613
 Author URI: http://twitter.com/Atrac613
 Version: 1.0.0
 License: Dual GPL (http://www.fsf.org/licensing/licenses/info/GPLv2.html) and Modified BSD (http://www.fsf.org/licensing/licenses/index_html#ModifiedBSD)
 */

define ( 'FELICA_AUTH_PLUGIN_REVISION', preg_replace( '/\$Rev: (.+) \$/', '\\1',
	'$Rev: 1 $') ); // this needs to be on a separate line so that svn:keywords can work its magic

// last plugin revision that required database schema changes
define ( 'FELICA_AUTH_DB_REVISION', 1);

$felica_auth_include_path = dirname(__FILE__);

set_include_path( $felica_auth_include_path . PATH_SEPARATOR . get_include_path() );
require_once 'common.php';
require_once 'admin_panels.php';
require_once 'login.php';
require_once 'store.php';
restore_include_path();

// register activation (and similar) hooks
register_activation_hook('felica-auth/felica-auth.php', 'felica_auth_activate_plugin');
register_deactivation_hook('felica-auth/felica-auth.php', 'felica_auth_deactivate_plugin');
register_uninstall_hook('felica-auth/felica-auth.php', 'felica_auth_uninstall_plugin');

// run activation function if new revision of plugin
if ( get_option('felica_auth_plugin_revision') === false || FELICA_AUTH_PLUGIN_REVISION != get_option('felica_auth_plugin_revision') ) {
	add_action('admin_init', 'felica_auth_activate_plugin');
}

// ---------------- //
// Public Functions //
// ---------------- //

/**
 * Get the FeliCa Auth identities for the specified user.
 *
 * @param mixed $id_or_name the username or ID.  If not provided, the current user will be used.
 * @return array array of user's FeliCa Auth identities
 * @access public
 * @since 1.0
 */
function get_user_felica_ids($id_or_name = null) {
	$user = get_userdata_by_various($id_or_name);

	if ( $user ) {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare('SELECT secret_key FROM '.felica_auth_identity_table().' WHERE user_id = %s', $user->ID) );
	} else {
		return array();
	}
}

/**
 * Get the user associated with the specified FeliCa Auth.
 *
 * @param string $secret_key identifier to match
 * @return int|null ID of associated user, or null if no associated user
 * @access public
 * @since 1.0
 */
function get_user_by_felica($secret_key) {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare('SELECT user_id FROM '.felica_auth_identity_table().' WHERE secret_key = %s', $secret_key) );
}

/**
 * Convenience method to get user data by ID, username, or from current user.
 *
 * @param mixed $id_or_name the username or ID.  If not provided, the current user will be used.
 * @return bool|object False on failure, User DB row object
 * @access public
 * @since 3.0
 */
if (!function_exists('get_userdata_by_various')) :
function get_userdata_by_various($id_or_name = null) {
	if ( $id_or_name === null ) {
		$user = wp_get_current_user();
		if ($user == null) return false;
		return $user->data;
	} else if ( is_numeric($id_or_name) ) {
		return get_userdata($id_or_name);
	} else {
		return get_userdatabylogin($id_or_name);
	}
}
endif;

/**
 * Get the file for the plugin, including the path.  This method will handle the case where the 
 * actual plugin files do not reside within the WordPress directory on the filesystem (such as 
 * a symlink).  The standard value should be 'felica-auth/felica-auth.php' unless files or folders have
 * been renamed.
 *
 * @return string plugin file
 */
function felica_auth_plugin_file() {
	static $file;

	if ( empty($file) ) {
		$path = 'felica-auth';

		$base = plugin_basename(__FILE__);
		if ( $base != __FILE__ ) {
			$path = basename(dirname($base));
		}

		$file = $path . '/' . basename(__FILE__);
	}

	return $file;
}

// -- end of public functions