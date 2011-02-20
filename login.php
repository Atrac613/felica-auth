<?php
/**
 * All the code required for handling logins via wp-login.php.  These functions should not be considered public, 
 * and may change without notice.
 */

add_action( 'login_head', 'felica_auth_wp_login_head');
add_action( 'login_form', 'felica_auth_wp_login_form');
add_action( 'init', 'felica_auth_login_errors' );
add_action( 'authenticate', 'felica_auth_authenticate' );

add_action('login_head', 'wp_print_head_scripts', 1);
add_action('login_head', 'wp_enqueue_scripts', 1);

/**
 * Authenticate user to WordPress using FeliCa.
 *
 * @param mixed $user authenticated user object, or WP_Error or null
 */
function felica_auth_authenticate($user) {
	if ( array_key_exists('felica_auth_identifier', $_POST) && $_POST['felica_auth_identifier'] ) {
		$user_id = get_user_by_felica($_POST['felica_auth_identifier']);
		if ( $user_id ) {
			$user = new WP_User($user_id);
		} else {
			felica_auth_status('error');
			felica_auth_message(
				__('Could not discover an FeliCa identity.', 'felica_auth')
			);
		
			global $error;
			$error = felica_auth_message();
			$user = new WP_Error( 'felica_auth_login_error', $error );
		}
	}

	return $user;
}

/**
 * Setup FeliCa Auth errors to be displayed to the user.
 */
function felica_auth_login_errors() {
	$self = basename( $GLOBALS['pagenow'] );
	if ($self != 'wp-login.php') return;

	if ( array_key_exists('felica_auth_error', $_REQUEST) ) {
		global $error;
		$error = htmlentities2($_REQUEST['felica_auth_error']);
	}
}

/**
 * Handle WordPress registration errors.
 */
function felica_auth_registration_errors($errors) {
	if (!empty($_POST['felica_auth_identifier'])) {
		$errors->add('invalid_felica', __('<strong>ERROR</strong>: ', 'felica_auth') . felica_auth_message());
	}

	return $errors;
}

/**
 * Add style and script to login page.
 */
function felica_auth_wp_login_head() {
	felica_auth_style();
}

/**
 * Add FeliCa input field to wp-login.php
 *
 * @action: login_form
 **/
function felica_auth_wp_login_form() {
	$server_token = get_option('felica_auth_server_token');
	echo '<hr id="felica_auth_split" style="clear: both; margin-bottom: 1.0em; border: 0; border-top: 1px solid #999; height: 1px;" />';
	echo '
	<p style="margin-bottom: 8px;">
		<label style="display: block; margin-bottom: 5px;">' . __('Or login using an FeliCa', 'felica_auth') . ' | <span id="felica_auth_debug_flag">Debug On</span><br />
		
		<div id="felica_auth_debug" style="">
		
			<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
				id="felica_auth_object" width="1" height="1"
				codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
				<param name="movie" value="' . plugins_url('felica-auth/f/felica-auth.swf') .'" />
				<param name="quality" value="high" />
				<param name="bgcolor" value="" />
				<param name="flashVars" value="serverToken=' . $server_token . '" />
				<param name="allowScriptAccess" value="always" />
				<embed src="' . plugins_url('felica-auth/f/felica-auth.swf') .'" quality="high" bgcolor=""
					width="1" height="1" name="FeliCaAuth" align="middle"
					flashVars="serverToken=' . $server_token . '"
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
		<input type="hidden" name="felica_auth_identifier" id="felica_auth_identifier" value="" />
		<input type="text" id="felica_auth_device_state" class="input felica_auth_device_state" value="Connecting ..." size="20" tabindex="25" /></label>
	</p>

	<p style="font-size: 0.9em; margin: 8px 0 24px 0;" id="what_is_felica_auth">
		<a href="#" target="_blank">'.__('Learn about FeliCa Auth', 'felica_auth').'</a>
	</p>
	
	';
}

?>