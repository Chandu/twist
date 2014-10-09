<?php
require_once( TWIST__PLUGIN_DIR . "class.twist-config.php" );
class Twist_Settings_Manager {
	private static $ins = null;

	public static function init() {
		if ( is_admin() ){
			self::instance()->register_actions();
		}
		
	}

	private static function instance() {
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}

	private function register_actions() {
		add_action("admin_menu", array($this, "add_twist_admin_menu_item"));
		add_action("admin_init", array($this, "register_and_build_fields"));
		add_action('admin_enqueue_scripts', array($this, "enqueue_assets"));
	}

	public function add_twist_admin_menu_item() {
		add_options_page(__("Twilio SMS Texting Settings"), __("Twilio SMS Texting Settings"), "manage_options", Twist_Config::PLUGIN_SLUG, array($this, "render_settings_page"));
	}

	function enqueue_assets($hook_suffix) {
		if("settings_page_" . Twist_Config::PLUGIN_SLUG == $hook_suffix) {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		} else {
			
		}
	}

	function enqueue_styles() {

	}

	function enqueue_scripts() {
		wp_register_script("jquery-maskedinput", plugins_url()."/twist/assets/js/vendor/jquery.maskedinput.js", array("jquery"));
		wp_enqueue_script("jquery-maskedinput");

		wp_register_script("twist-admin-settings-app", plugins_url()."/twist/assets/js/twist-admin-settings-app.js", array("jquery-maskedinput"));
		wp_enqueue_script("twist-admin-settings-app");
	}

	
	function render_settings_page() {
		if(!current_user_can("manage_options")) {
			wp_die(__("You do not have sufficient permissions to access this page."));
		}
		include_once(TWIST__PLUGIN_DIR . "views/settings_page.php");
	}

	function register_and_build_fields() {
		register_setting(Twist_Config::PLUGIN_SLUG, Twist_Config::PLUGIN_SLUG, array($this, "validate_twilio_keys"));

	}

	function validate_twilio_keys($input) {
		
		$input[Twist_Config::ACCOUNT_SID_OPTION_KEY] = sanitize_text_field($input[Twist_Config::ACCOUNT_SID_OPTION_KEY]);
		$input[Twist_Config::AUTH_TOKEN_OPTION_KEY] = sanitize_text_field($input[Twist_Config::AUTH_TOKEN_OPTION_KEY]);
		$input[Twist_Config::FROM_PHONE_NUMBER] = sanitize_text_field($input[Twist_Config::FROM_PHONE_NUMBER]);
		
		if (strlen($input[Twist_Config::ACCOUNT_SID_OPTION_KEY]) == 0) {
			add_settings_error(
					Twist_Config::ACCOUNT_SID_OPTION_KEY, // Setting title
					Twist_Config::ACCOUNT_SID_OPTION_KEY.'_error', // Error ID
					'Please enter a Twilio Account SID', // Error message
					'error' // Type of message
			);
		}
		if (strlen($input[Twist_Config::AUTH_TOKEN_OPTION_KEY]) == 0) {
			add_settings_error(
					Twist_Config::AUTH_TOKEN_OPTION_KEY, // Setting title
					Twist_Config::AUTH_TOKEN_OPTION_KEY.'_error', // Error ID
					'Please enter a  Twilio Auth Token', // Error message
					'error' // Type of message
			);
		}
		if (strlen($input[Twist_Config::FROM_PHONE_NUMBER]) == 0) {
			add_settings_error(
					Twist_Config::FROM_PHONE_NUMBER, // Setting title
					Twist_Config::FROM_PHONE_NUMBER.'_error', // Error ID
					'Please enter a phone number', // Error message
					'error' // Type of message
			);
		}

		if (!preg_match(Twist_Config::PHONE_NUMBER_REGEX, $input[Twist_Config::FROM_PHONE_NUMBER])) {
			add_settings_error(
					Twist_Config::FROM_PHONE_NUMBER, // Setting title
					Twist_Config::FROM_PHONE_NUMBER.'_error', // Error ID
					'Please enter phone number in the format +XXX (XXX) - XXX - XXXX. Country code should contain 3 digits (e.g: 001, 044 etc).  ', // Error message
					'error' // Type of message
			);
		}
		
		return $input;
	}
	
}