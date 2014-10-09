<?php
require_once( TWIST__PLUGIN_DIR . 'class.twist-config.php' );
class Twist_Installer {
	private static $ins = null;

	private function Twist_Installer() {
		
	}

	public static function install() {
		self::ensure_tables();
	}

	public static function uninstall() {
		self::remove_tables();	
		delete_option( Twist_Config::PLUGIN_SLUG);
	}

	private static function ensure_tables() {
		$recievers_table_sql = "CREATE TABLE " . Twist_Config::get_receivers_table_name() . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				first_name varchar(255)  NOT NULL,
				last_name varchar(255)  NOT NULL,
				phone_number varchar(255)  NOT NULL,
				department  varchar(255),
				created_date datetime NOT NULL,
				updated_date datetime,
				status int DEFAULT 1,
				PRIMARY KEY (id),
				UNIQUE KEY (phone_number)
			);";
		
		$messages_table_sql = "CREATE TABLE " . Twist_Config::get_messages_table_name() . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				template text NOT NULL,
				created_date datetime NOT NULL,
				PRIMARY KEY (id)
			);";
		
		$sent_messages_table_sql = "CREATE TABLE " . Twist_Config::get_sent_messages_table_name() . " (
				id int(11) NOT NULL AUTO_INCREMENT,
				message_id int(11) NOT NULL,
				first_name varchar(255)  NOT NULL,
				last_name varchar(255)  NOT NULL,
				phone_number varchar(255)  NOT NULL,
				department varchar(255),
				sent_date datetime NOT NULL,
				response_code int,
				response_message varchar(1000),
				receiver_id int(11),
				PRIMARY KEY (id),
				FOREIGN KEY (message_id) REFERENCES " . Twist_Config::get_messages_table_name() . "(id) ON DELETE CASCADE
			);";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $recievers_table_sql );
		dbDelta( $messages_table_sql );
		dbDelta( $sent_messages_table_sql );
	}

	private static function remove_tables() {
		global $wpdb;
		$wpdb->query("DROP TABLE IF EXISTS " . Twist_Config::get_sent_messages_table_name());
		$wpdb->query("DROP TABLE IF EXISTS " . Twist_Config::get_messages_table_name());
		$wpdb->query("DROP TABLE IF EXISTS " . Twist_Config::get_receivers_table_name());
	}
}