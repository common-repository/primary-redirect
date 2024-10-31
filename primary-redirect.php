<?php
/*
Plugin Name: Primary Redirect
Plugin URI: http://blog.uysalmustafa.com/primary-redirect/
Description: Redirects users to special url or their primary blog's dashboard after they've logged in  replacing the default 'go to dashboard' behavior.
Author: Mustafa Uysal
Version:1.0
Text Domain: primary_redirect
Domain Path: /languages/
Author URI: http://blog.uysalmustafa.com
License: GPLv2 (or later)
Network: true
*/


class Primary_Redirect {


	/**
	 * PHP 5 constructor
	 **/
	function __construct() {
		add_filter( 'login_redirect', array( &$this, 'redirect' ), 10, 3 );
		add_action( 'wpmu_options', array( &$this, 'network_option' ) );
		add_action( 'update_wpmu_options', array( &$this, 'update_network_option' ) );
		add_action( 'admin_init', array( &$this, 'add_settings_field' ) );

	}
	
	//get locale
	function plugin_localization() {  
		load_plugin_textdomain( 'primary_redirect', false, '/primary-redirect/languages/' );
	}
	
	/**
	 * Redirect user on login
	 **/
	function redirect( $redirect_to, $requested_redirect_to, $user ) {
	global $wpdb;
	$primary_redirection = get_site_option( 'primary_dashboard_true' );
		
		if(($primary_redirection == 1)&&($user->ID != 0)){	
				$user_info = get_userdata($user->ID);
			if ($user_info->primary_blog) {
				$primary_url = get_blogaddress_by_id($user_info->primary_blog) . 'wp-admin/';
				if ($primary_url) {
					wp_redirect($primary_url);
                exit();
            }
        }
		}	
	else{
		
		$interim_login = isset( $_REQUEST['interim-login'] );
		$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

		if( $this->is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			$primary_redirect_url = get_site_option( 'primary_redirect_url' );
		else
			$primary_redirect_url = get_option( 'primary_redirect_url' );

		if ( !is_wp_error( $user ) && !$reauth && !$interim_login && !empty( $primary_redirect_url ) ) {
			wp_redirect( $primary_redirect_url );
			exit();
		}
}
		return $redirect_to;
	}
	
	

	/**
	 * Network option
	 **/
	function network_option() {
		if( ! $this->is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			return;
		?>
		<h3><?php _e( 'Primary Redirect', 'primary_redirect' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="primary_redirect_url"><?php _e( 'Redirect to', 'primary_redirect' ) ?></label></th>
				<td style="width:280px;">
					<input name="primary_redirect_url" type="text" id="primary_redirect_url" value="<?php echo esc_attr( get_site_option( 'primary_redirect_url' ) ) ?>" size="40" />
					<br />
					<?php _e( 'The URL users will be redirected to after login.', 'primary_redirect' ) ?>
				</td>
				<td>
					<input type="checkbox" name="primary_dashboard_true" value="1" <?php if ( get_site_option( 'primary_dashboard_true') == '1') { echo 'checked="checked"';} ?> /><?php _e( 'Redirect to Primary Dashboard' , 'primary_redirect' ); ?><br>
					
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save option in the option
	 **/
	function update_network_option() {
		update_site_option( 'primary_redirect_url', stripslashes( $_POST['primary_redirect_url'] ) );
		update_site_option( 'primary_dashboard_true', stripslashes( $_POST['primary_dashboard_true'] ) );
		
	}

	/**
	 * Add setting field for singlesite
	 **/
	function add_settings_field() {
		if( $this->is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			return;

		add_settings_section( 'primary_redirect_setting_section', __( 'Primary Redirect', 'primary_redirect' ), '__return_false', 'general' );

		add_settings_field( 'primary_redirect_url', __( 'Redirect to', 'primary_redirect' ), array( &$this, 'site_option' ), 'general', 'primary_redirect_setting_section' );
		
		register_setting( 'general', 'primary_redirect_url' );
		
	}

	/**
	 * Setting field for singlesite
	 **/
	function site_option() {
		echo '<input name="primary_redirect_url" type="text" id="primary_redirect_url" value="' . esc_attr( get_option( 'primary_redirect_url' ) ) . '" size="40" />';
	}

	/**
	 * Verify if plugin is network activated
	 **/
	function is_plugin_active_for_network( $plugin ) {
		if ( !is_multisite() )
			return false;

		$plugins = get_site_option( 'active_sitewide_plugins');
		if ( isset($plugins[$plugin]) )
			return true;

		return false;
	}

}


$primary_redirect =& new Primary_Redirect();

?>