<?php
/**
 * Common functions.
 */

/**
 * Called on plugin activation and upgrading.
 *
 * @see register_activation_hook
 */
function felica_auth_activate_plugin() {
	global $wp_rewrite;

	// if first time activation, set FeliCa Auth capability for administrators
	if (get_option('felica_auth_plugin_revision') === false) {
		global $wp_roles;
		$role = $wp_roles->get_role('administrator');
		if ($role) $role->add_cap('use_felica_auth_administrator');
	}

	// for some reason, show_on_front is not always set, causing is_front_page() to fail
	$show_on_front = get_option('show_on_front');
	if ( empty($show_on_front) ) {
		update_option('show_on_front', 'posts');
	}

	// Add custom FeliCa Auth options
	add_option( 'felica_auth_plugin_enabled', true );
	add_option( 'felica_auth_plugin_revision', 0 );
	add_option( 'felica_auth_db_revision', 0 );

	felica_auth_create_tables();
	//felica_auth_migrate_old_data();

	// set current revision
	update_option( 'felica_auth_plugin_revision', FELICA_AUTH_PLUGIN_REVISION );

	//felica_auth_remove_historical_options();
}

/**
 * Called on plugin deactivation.  Cleanup all transient data.
 *
 * @see register_deactivation_hook
 */
function felica_auth_deactivate_plugin() {
	//delete_option('felica_auth_nonces');
}

/**
 * Delete options in database
 */
function felica_auth_uninstall_plugin() {
	felica_auth_delete_tables();

	// current options
	delete_option('felica_auth_plugin_enabled');
	delete_option('felica_auth_plugin_revision');
	delete_option('felica_auth_db_revision');
	//delete_option('felica_auth_blog_owner');

	// historical options
	//felica_auth_remove_historical_options();
}

/**
 * Include FeliCa Auth stylesheet.  
 *
 * "Intelligently" decides whether to enqueue or print the CSS file, based on whether * the 'wp_print_styles' 
 * action has been run.  (This logic taken from the core wp_admin_css function)
 **/
function felica_auth_style() {
	if ( !wp_style_is('felica_auth', 'registered') ) {
		wp_register_style('felica_auth', plugins_url('felica-auth/f/felica-auth.css'), array(), FELICA_AUTH_PLUGIN_REVISION);
	}
	
	if ( !wp_script_is('felica_auth', 'registered') ) {
		wp_register_script('felica_auth', plugins_url('felica-auth/f/felica-auth.js'), array(), FELICA_AUTH_PLUGIN_REVISION);
	}

	if ( did_action('wp_print_styles') ) {
		wp_print_styles('felica_auth');
	} else {
		wp_enqueue_style('felica_auth');
	}
	
	if ( did_action('wp_print_scripts') ) {
		wp_print_scripts('felica_auth');
	} else {
		wp_enqueue_script('felica_auth');
	}
}

function felica_auth_status($new = null) {
	static $status;
	return ($new == null) ? $status : $status = $new;
}

function felica_auth_message($new = null) {
	static $message;
	return ($new == null) ? $message : $message = $new;
}

/**
 * Remove secret_key from user.
 *
 * @param int $user_id user id
 * @param string $secret_key secret_key to remove
 */
function felica_auth_drop_identity($user_id, $secret_key) {
	global $wpdb;
	if (empty($secret_key)) return false;
	return $wpdb->query( $wpdb->prepare('DELETE FROM '.felica_auth_identity_table().' WHERE user_id = %s AND secret_key = %s', $user_id, $secret_key) );
}

/**
 * Add secret_key to user.
 *
 * @param int $user_id user id
 * @param string $secret_key secret_key to add
 */
function felica_auth_add_identity($user_id, $secret_key) {
	global $wpdb;
	if (empty($secret_key)) return false;
	$sql = $wpdb->prepare('INSERT INTO ' . felica_auth_identity_table() . ' (user_id,secret_key) VALUES ( %s, %s )', $user_id, $secret_key);
	return $wpdb->query( $sql );
}

function felica_auth_page($message, $title = '') {
	global $wp_locale;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title ?></title>
<?php
	wp_admin_css('install', true);
	if ( ($wp_locale) && ('rtl' == $wp_locale->text_direction) ) {
		wp_admin_css('login-rtl', true);
	}

	do_action('admin_head');
	//do_action('felica_auth_page_head');
?>
</head>
<body id="felica-auth-page">
	<?php echo $message; ?>
</body>
</html>
<?php
	die();
}

/**
 * Format FeliCa for display... namely, remove the fragment if present.
 * @param string $key key to display
 * @return url formatted for display
 */
function felica_auth_display_identity($key) {
	return substr($key, 0, 10);
}

function felica_auth_error($msg) {
	error_log('[FeliCaAuth] ' . $msg);
}

function felica_auth_debug($msg) {
	if (defined('WP_DEBUG') && WP_DEBUG) {
		felica_auth_error($msg);
	}
}

?>
