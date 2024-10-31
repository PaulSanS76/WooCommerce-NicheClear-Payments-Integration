<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://meadowlark.com
 * @since      1.0.0
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/admin
 */

require_once ABSPATH . 'wp-content/plugins/nicheclear_api/includes/class-nicheclear_api-common.php';

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nicheclear_api
 * @subpackage Nicheclear_api/admin
 * @author     Meadowlark <meadowlark@meadowlark.com>
 */
class NicheclearAPI_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nicheclear_api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nicheclear_api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nicheclear_api-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nicheclear_api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nicheclear_api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nicheclear_api-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function plugin_add_settings_link( $links ) {
		$settings_link = "<a href='/wp-admin/admin.php?page=ncapi-settings'>" . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );

		return $links;
	}

	public function ncapi_add_settings_page() {
		add_options_page(
			"$this->plugin_name Settings",           // Page title
			"$this->plugin_name Settings",           // Menu title
			'manage_options',            // Capability
			'ncapi-settings',              // Menu slug
			[ $this, 'ncapi_render_settings_page' ]   // Callback function to render the page
		);
	}

	public function ncapi_register_settings() {
		// Register settings fields
		register_setting( 'ncapi_settings', 'ncapi_key', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => ''
		] );

		register_setting( 'ncapi_settings', 'ncapi_signing_key', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => ''
		] );
	}

// Render the settings page
	public function ncapi_render_settings_page() {
		?>
        <div class="wrap">
            <h1><?php echo $this->plugin_name; ?> Settings</h1>
            <form method="post" action="options.php">
				<?php
				// Output nonce, action, and option_page fields for the settings page
				settings_fields( 'ncapi_settings' );
				do_settings_sections( 'ncapi_settings' );
				?>
                <table class="form-table" style="width: auto;">
                    <tr valign="top">
                        <th scope="row" style="width: 100px;"><label for="ncapi_key">API Key</label></th>
                        <td style="width: 300px;">
                            <input type="text" id="ncapi_key" name="ncapi_key" style="width: 100%;"
                                   value="<?php echo esc_attr( get_option( 'ncapi_key' ) ); ?>" required/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row" style="width: 100px;"><label for="ncapi_signing_key">Signing Key</label></th>
                        <td style="width: 300px;">
                            <input type="text" id="ncapi_signing_key" name="ncapi_signing_key" style="width: 100%;"
                                   value="<?php echo esc_attr( get_option( 'ncapi_signing_key' ) ); ?>" required/>
                        </td>
                    </tr>
                </table>
				<?php submit_button(); ?>
            </form>
        </div>
		<?php
	}

}
