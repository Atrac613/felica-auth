<?php 
/**
 * store.php
 *
 * Database Connector for WordPress FeliCa Auth
 * Dual Licence: GPL & Modified BSD
 */

/**
 * Create FeliCa Auth related tables in the WordPress database.
 */
function felica_auth_create_tables()
{
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// Create the SQL and call the WP schema upgrade function
	$statements = array(
		"CREATE TABLE ".felica_auth_identity_table()." (
		felica_auth_id bigint(20) NOT NULL auto_increment,
		user_id bigint(20) NOT NULL default '0',
		secret_key text,
		PRIMARY KEY  (felica_auth_id),
		KEY user_id (user_id)
		)",
	);

	$sql = implode(';', $statements);
	dbDelta($sql);

	update_option('felica_auth_db_revision', FELICA_AUTH_DB_REVISION);
}

/**
 * Undo any database changes made by the FeliCa Auth plugin.  Do not attempt to preserve any data.
 */
function felica_auth_delete_tables() {
	global $wpdb;
	$wpdb->query('DROP TABLE IF EXISTS ' . felica_auth_identity_table());
}

function felica_auth_table_prefix($blog_specific = false) {
	global $wpdb;
	if (isset($wpdb->base_prefix)) {
		return $wpdb->base_prefix . ($blog_specific ? $wpdb->blogid . '_' : '');
	} else {
		return $wpdb->prefix;
	}
}

function felica_auth_identity_table() { 
	return (defined('CUSTOM_FELICA_AUTH_IDENTITY_TABLE') ? CUSTOM_FELICA_AUTH_IDENTITY_TABLE : felica_auth_table_prefix() . 'felica_auth_identities'); 
}

?>