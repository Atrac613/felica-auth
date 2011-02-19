<?php
/**
 * All the code required for handling FeliCa Auth administration.  These functions should not be considered public, 
 * and may change without notice.
 */

// -- WordPress Hooks
add_action( 'admin_init', 'felica_auth_admin_register_settings' );
add_action( 'admin_menu', 'felica_auth_admin_panels' );

/**
 * Register FeliCaAuth admin settings.
 */
function felica_auth_admin_register_settings() {
	//register_setting('general', 'felica_auth_required_for_registration');

	//register_setting('felica_auth', 'felica_auth_blog_owner');
	//register_setting('felica_auth', 'felica_auth_cap');
}


/**
 * Setup admin menus for FeliCa Auth options and ID management.
 *
 * @action: admin_menu
 **/
function felica_auth_admin_panels() {
	add_filter('plugin_action_links', 'felica_auth_plugin_action_links', 10, 2);

	// global options page
	$hookname = add_options_page(__('FeliCa Auth options', 'felica_auth'), __('FeliCa Auth', 'felica_auth'), 'manage_options', 'felica-auth', 'felica_auth_options_page' );
	add_action("load-$hookname", create_function('', 'add_thickbox();'));
	add_action("load-$hookname", 'felica_auth_style');
	add_action("load-$hookname", 'felica_auth_options_management' );
	
	// all users can setup external OpenIDs
	$hookname =	add_users_page(__('Your FeliCa', 'felica_auth'), __('Your FeliCa', 'felica_auth'), 'read', 'your_felica', 'felica_auth_profile_panel' );
	add_action("load-$hookname", create_function('', 'wp_enqueue_script("admin-forms");'));
	add_action("load-$hookname", 'felica_auth_profile_management' );
	add_action("load-$hookname", 'felica_auth_style' );
}

/**
 * Handle FeliCa Auth profile management.
 */
function felica_auth_profile_management() {
	global $action;
	
	wp_reset_vars( array('action') );

	switch( $action ) {
		case 'add':
			check_admin_referer('felica-auth-add_felicaid');

			$user = wp_get_current_user();

			$userid = get_user_by_felica($_POST['felica_auth_identifier']);

			if ($userid) {
				global $error;
				if ($user->ID == $userid) {
					$error = __('You already have this FeliCa!', 'felica_auth');
				} else {
					$error = __('This FeliCa is already associated with another user.', 'felica_auth');
				}
				return;
			}
			
			if (felica_auth_add_identity($user->ID, $_POST['felica_auth_identifier'])) {
				felica_auth_message( sprintf(_n('Added %d FeliCa association.', 'Added %d FeliCa associations.', 1, 'felica_auth'), 1) );
				felica_auth_status('success');
			} else {
				felica_auth_message( sprintf(_n('Failed %d FeliCa association.', 'Failed %d FeliCa associations.', 1, 'felica_auth'), 1) );
				felica_auth_status('error');
			}

			break;

		case 'delete':
			felica_auth_profile_delete_felica_ids($_REQUEST['delete']);
			break;

		default:
			if ( array_key_exists('message', $_REQUEST) ) {
				$message = $_REQUEST['message'];

				$messages = array(
					'',
					__('Unable to authenticate FeliCa.', 'felica_auth'),
					__('Added association with FeliCa.', 'felica_auth')
				);

				if (is_numeric($message)) {
					$message = $messages[$message];
				} else {
					$message = htmlentities2( $message );
				}

				$message = __($message, 'felica_auth');

				felica_auth_message($message);
				felica_auth_status($_REQUEST['status']);
			}
			break;
	}
}

/**
 * Handle FeliCa Auth options management.
 */
function felica_auth_options_management() {
	global $action;
	
	wp_reset_vars( array('action') );

	switch( $action ) {
		case 'delete':
			felica_auth_options_delete_felica_ids($_REQUEST['delete']);
			break;

		default:
			if ( array_key_exists('message', $_REQUEST) ) {
				$message = $_REQUEST['message'];

				$messages = array(
					'',
					__('Unable to authenticate FeliCa.', 'felica_auth'),
					__('Added association with FeliCa.', 'felica_auth')
				);

				if (is_numeric($message)) {
					$message = $messages[$message];
				} else {
					$message = htmlentities2( $message );
				}

				$message = __($message, 'felica_auth');

				felica_auth_message($message);
				felica_auth_status($_REQUEST['status']);
			}
			break;
	}
}

/**
 * Remove secret_key from current user account.
 *
 * @param int $id id of identity key to remove
 */
function felica_auth_profile_delete_felica_ids($delete) {

	if (empty($delete) || array_key_exists('cancel', $_REQUEST)) return;
	check_admin_referer('felica-auth-delete_felica_ids');

	$user = wp_get_current_user();
	$secret_keys = get_user_felica_ids($user->ID);

	if (sizeof($secret_keys) == sizeof($delete) && !@$_REQUEST['confirm']) {
		$html = '
			<h1>'.__('FeliCa Auth Warning', 'felica_auth').'</h1>
			<form action='.sprintf('%s?page=%s', $_SERVER['PHP_SELF'], $_REQUEST['page']).' method="post">
			<p>'.__('Are you sure you want to delete all of your FeliCa associations? Doing so may prevent you from logging in.', 'felica_auth').'</p>
			<div class="submit">
				<input type="submit" name="confirm" value="'.__("Yes I'm sure. Delete.", 'felica_auth').'" />
				<input type="submit" name="cancel" value="'.__("No, don't delete.", 'felica_auth').'" />
			</div>';

		foreach ($delete as $d) {
			$html .= '<input type="hidden" name="delete[]" value="'.$d.'" />';
		}

		$html .= wp_nonce_field('felica-auth-delete_felica_ids', '_wpnonce', true, false) . '
				<input type="hidden" name="action" value="delete" />
			</form>';

		felica_auth_page($html, __('FeliCa Auth Warning', 'felica_auth'));
		return;
	}

	$count = 0;
	foreach ($secret_keys as $secret_key) {
		if (in_array(md5($secret_key), $_REQUEST['delete'])) {
			if (felica_auth_drop_identity($user->ID, $secret_key)) {
			   	$count++;
			}
		}
	}

	if ($count) {
		felica_auth_message( sprintf(_n('Deleted %d FeliCa association.', 'Deleted %d FeliCa associations.', $count, 'felica_auth'), $count) );
		felica_auth_status('success');

		return;
	}
		
	felica_auth_message(__('FeliCa association delete failed: Unknown reason.', 'felica_auth'));
	felica_auth_status('error');
}

/**
 * Remove secret_key from options management.
 *
 * @param int $id id of identity key to remove
 */
function felica_auth_options_delete_felica_ids($delete) {
	if (empty($delete) || array_key_exists('cancel', $_REQUEST)) return;
	check_admin_referer('felica-auth-delete_felica_ids');

	$felica_ids = get_alluser_felica_ids();
	$secret_keys = array();
	foreach($felica_ids as $felica_id){
		$secret_keys[] = $felica_id->secret_key;
	}

	if (sizeof($secret_keys) == sizeof($delete) && !@$_REQUEST['confirm']) {
		$html = '
			<h1>'.__('FeliCa Auth Warning', 'felica_auth').'</h1>
			<form action='.sprintf('%s?page=%s', $_SERVER['PHP_SELF'], $_REQUEST['page']).' method="post">
			<p>'.__('Are you sure you want to delete all of your FeliCa associations? Doing so may prevent you from logging in.', 'felica_auth').'</p>
			<div class="submit">
				<input type="submit" name="confirm" value="'.__("Yes I'm sure. Delete.", 'felica_auth').'" />
				<input type="submit" name="cancel" value="'.__("No, don't delete.", 'felica_auth').'" />
			</div>';

		foreach ($delete as $d) {
			$html .= '<input type="hidden" name="delete[]" value="'.$d.'" />';
		}

		$html .= wp_nonce_field('felica-auth-delete_felica_ids', '_wpnonce', true, false) . '
				<input type="hidden" name="action" value="delete" />
			</form>';

		felica_auth_page($html, __('FeliCa Auth Warning', 'felica_auth'));
		return;
	}

	$count = 0;
	foreach ($secret_keys as $secret_key) {
		if (in_array(md5($secret_key), $_REQUEST['delete'])) {
			if (felica_auth_drop_identity(get_user_by_felica($secret_key), $secret_key)) {
			   	$count++;
			}
		}
	}

	if ($count) {
		felica_auth_message( sprintf(_n('Deleted %d FeliCa association.', 'Deleted %d FeliCa associations.', $count, 'felica_auth'), $count) );
		felica_auth_status('success');

		return;
	}
		
	felica_auth_message(__('FeliCa association delete failed: Unknown reason.', 'felica_auth'));
	felica_auth_status('error');
}

/**
 * Handle user management of FeliCa associations.
 *
 * @submenu_page: profile.php
 **/
function felica_auth_profile_panel() {
	global $error;

	if( !current_user_can('read') ) return;
	$user = wp_get_current_user();

	$status = felica_auth_status();
	if( 'success' == $status ) {
		echo '<div class="updated"><p><strong>'.__('Success:', 'felica_auth').'</strong> '.felica_auth_message().'</p></div>';
	}
	elseif( 'warning' == $status ) {
		echo '<div class="error"><p><strong>'.__('Warning:', 'felica_auth').'</strong> '.felica_auth_message().'</p></div>';
	}
	elseif( 'error' == $status ) {
		echo '<div class="error"><p><strong>'.__('Error:', 'felica_auth').'</strong> '.felica_auth_message().'</p></div>';
	}

	if (!empty($error)) {
		echo '<div class="error"><p><strong>'.__('Error:', 'felica_auth').'</strong> '.$error.'</p></div>';
		unset($error);
	}

	screen_icon('felica_auth');
	?>
	<style type="text/css">
		#icon-felica_auth { background-image: url("<?php echo plugins_url('felica-auth/f/icon.png'); ?>"); }
	</style>

	<div class="wrap">
		<form action="<?php printf('%s?page=%s', $_SERVER['PHP_SELF'], $_REQUEST['page']); ?>" method="post">
			<h2><?php _e('Your Verified FeliCa', 'felica_auth') ?></h2>

			<p><?php _e('You may associate one or more FeliCa with your account.  This will '
			. 'allow you to login to WordPress with your FeliCa instead of a username and password.  '
			. '<a href="#" target="_blank">Learn more...</a>', 'felica_auth')?></p>

		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
				<?php wp_nonce_field('felica-auth-delete_felica_ids'); ?>
			</div>
			<div class="clear"></div>
		</div>

		<div class="clear"></div>

		<table class="widefat">
			<thead>
				<tr>
					<th scope="col" class="check-column"><input type="checkbox" /></th>
					<th scope="col"><?php _e('FeliCa', 'felica_auth'); ?></th>
				</tr>
			</thead>
			<tbody>

			<?php
				$secret_keys = get_user_felica_ids($user->ID);

				if (empty($secret_keys)) {
					echo '<tr><td colspan="2">'.__('No Verified FeliCa.', 'felica_auth').'</td></tr>';
				} else {
					foreach ($secret_keys as $secret_key) {
						echo '
						<tr>
							<th scope="row" class="check-column"><input type="checkbox" name="delete[]" value="'.md5($secret_key).'" /></th>
							<td>'.felica_auth_display_identity($secret_key).'</td>
						</tr>';
					}
				}

			?>
			</tbody>
			</table>
		</form>

		<form method="post">
		<table class="form-table">
			<tr>
				<th scope="row"><label for="felica_auth_identifier"><?php _e('Add FeliCa', 'felica_auth') ?> | <span id="felica_auth_debug_flag">Debug On</span></label></th>
				<td>
					<input type="text" id="felica_auth_device_state" class="input felica_auth_device_state" value="Connecting ..." size="30" tabindex="25" />
					<input type="hidden" id="felica_auth_identifier" name="felica_auth_identifier" value="" />
					
					<div id="felica_auth_debug" style="">
					
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
							id="felica_auth_object" width="1" height="1"
							codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
							<param name="movie" value="<?php echo plugins_url('felica-auth/f/felica-auth.swf') ?>" />
							<param name="quality" value="high" />
							<param name="bgcolor" value="" />
							<param name="allowScriptAccess" value="always" />
							<embed src="<?php echo plugins_url('felica-auth/f/felica-auth.swf') ?>" quality="high" bgcolor=""
								width="1" height="1" name="FeliCaAuth" align="middle"
								play="true"
								loop="false"
								quality="high"
								allowScriptAccess="always"
								type="application/x-shockwave-flash"
								id="felica_auth_embed"
								pluginspage="http://www.adobe.com/go/getflashplayer">
							</embed>
						</object>
					
					</div>
				</td>
			</tr>
		</table>
		<?php wp_nonce_field('felica-auth-add_felicaid'); ?>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Add FeliCa', 'felica_auth') ?>" />
			<input type="hidden" name="action" value="add" >
		</p>
		</form>
	</div>
<?php
}

/*
 * Display and handle updates from the Admin screen options page.
 *
 * @options_page
 */
function felica_auth_options_page() {
	global $wpdb, $wp_roles;
	// Display the options page form
	
	$status = felica_auth_status();
	if( 'success' == $status ) {
		echo '<div class="updated"><p><strong>'.__('Success:', 'felica_auth').'</strong> '.felica_auth_message().'</p></div>';
	}
	elseif( 'warning' == $status ) {
		echo '<div class="error"><p><strong>'.__('Warning:', 'felica_auth').'</strong> '.felica_auth_message().'</p></div>';
	}
	elseif( 'error' == $status ) {
		echo '<div class="error"><p><strong>'.__('Error:', 'felica_auth').'</strong> '.felica_auth_message().'</p></div>';
	}

	if (!empty($error)) {
		echo '<div class="error"><p><strong>'.__('Error:', 'felica_auth').'</strong> '.$error.'</p></div>';
		unset($error);
	}
	
	screen_icon('felica_auth');
	?>
	<style type="text/css">
		#icon-felica_auth { background-image: url("<?php echo plugins_url('felica-auth/f/icon.png'); ?>"); }
	</style>

	<div class="wrap">
			<form action="<?php printf('%s?page=%s', $_SERVER['PHP_SELF'], $_REQUEST['page']); ?>" method="post">
			<h2><?php _e('Verified FeliCa', 'felica_auth') ?></h2>
			
			<div class="tablenav">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
				<?php wp_nonce_field('felica-auth-delete_felica_ids'); ?>
			</div>
			<div class="clear"></div>
			</div>

			<div class="clear"></div>
	
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" class="check-column"><input type="checkbox" /></th>
						<th scope="col" class="username-column"><?php _e('Username', 'felica_auth'); ?></th>
						<th scope="col"><?php _e('FeliCa', 'felica_auth'); ?></th>
					</tr>
				</thead>
				<tbody>
	
				<?php
					$felica_ids = get_alluser_felica_ids();
					if (empty($felica_ids)) {
						echo '<tr><td colspan="2">'.__('No Verified FeliCa.', 'felica_auth').'</td></tr>';
					} else {
						$sorted_felica_ids = array();
						$secret_keys = array();
						foreach ($felica_ids as $felica_id) {
							$user_info = get_userdata($felica_id->user_id);
							$sorted_felica_ids[$felica_id->felica_auth_id] = $user_info->user_login;
							$secret_keys[$felica_id->felica_auth_id] = $felica_id->secret_key;
						}
						asort($sorted_felica_ids);
						
						foreach ($sorted_felica_ids as $key => $username) {
							
							echo '
							<tr>
								<th scope="row" class="check-column"><input type="checkbox" name="delete[]" value="'.md5($secret_keys[$key]).'" /></th>
								<td class="username-column">'.$username.'</td>
								<td>'.felica_auth_display_identity($secret_keys[$key]).'</td>
							</tr>';
						}
					}
	
				?>
				</tbody>
				</table>
			</form>
			
	</div>
		<?php
}

/**
 * Add settings link to plugin page.
 */
function felica_auth_plugin_action_links($links, $file) {
	$this_plugin = felica_auth_plugin_file();

	if($file == $this_plugin) {
		$links[] = '<a href="options-general.php?page=felica-auth">' . __('Settings') . '</a>';
	}

	return $links;
}

?>