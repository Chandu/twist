<?php
global $wpdb;
class Twist_Config {
	private static $ins = null;
	private $receivers_table_name = "";
	private $messages_table_name = "";
	private $sent_messages_table_name = "";

	const ACCOUNT_SID_OPTION_KEY = "twilio-account-sid";
	const AUTH_TOKEN_OPTION_KEY = "twilio-auth-token";
	const FROM_PHONE_NUMBER = "twilio-from-phone";

	private $plugin_option_keys = array(self::ACCOUNT_SID_OPTION_KEY, self::AUTH_TOKEN_OPTION_KEY, self::FROM_PHONE_NUMBER);
	
	const PLUGIN_SLUG = "twist-plugin-settings";
	const TOOL_SLUG = "twist-twilio-tool";
	const PHONE_NUMBER_REGEX = "/\+[0-9]{3}\s\([0-9]{3}\)\s-\s[0-9]{3}\s-\s[0-9]{3}/";

	private function Twist_Config() {
		global $wpdb;
		$this->receivers_table_name = $wpdb->prefix."twist_receivers";
		$this->messages_table_name = $wpdb->prefix."twist_messages";
		$this->sent_messages_table_name = $wpdb->prefix."twist_message_details";
	}

	public static function get_receivers_table_name() {
		return self::instance()->receivers_table_name;
	}

	public static function get_messages_table_name() {
		return self::instance()->messages_table_name;
	}

	public static function get_sent_messages_table_name() {
		return self::instance()->sent_messages_table_name;
	}

	public static function instance() {
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}

	public static function get_plugin_option_keys() {
		return self::instance()->plugin_option_keys;
	}

}
