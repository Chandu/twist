<?php
require_once( TWIST__PLUGIN_DIR . "class.twist-config.php" );

class Twist_DB {
	public function get_all_recievers() {
		 global $wpdb;	
		 $query = "
			SELECT * 
			FROM " . Twist_Config::get_receivers_table_name() . "
			ORDER BY id
		";
		$to_return = $wpdb->get_results ($query);
		return $to_return;
	}

	public function get_receivers($receivers) {
		 global $wpdb;	
		 $tokens = array_fill(0, count($receivers), "%d");
		 $ids = implode(",", $tokens);
		 $query = "
			SELECT * 
			FROM " . Twist_Config::get_receivers_table_name() . "
			WHERE id  IN  ($ids)
		";
		$to_return = $wpdb->get_results ($wpdb->prepare($query, $receivers), ARRAY_A );
		return $to_return;
	}

	public function get_all_active_recievers() {
		 global $wpdb;	
		 $query = "
			SELECT * 
			FROM " . Twist_Config::get_receivers_table_name() . "
			WHERE status = 1
			ORDER BY id
		";
		$to_return = $wpdb->get_results ($query);
		return $to_return;
	}

	public function get_messages($start = 1, $limit = 20) {
		 global $wpdb;	
		 $query = "
			SELECT a.*, b.template
			  FROM " . Twist_Config::get_sent_messages_table_name() . " a JOIN " . Twist_Config::get_messages_table_name() . " b
			    ON a.message_id = b.id
			 ORDER BY sent_date
			 LIMIT %d, %d
		";
		$to_return = $wpdb->get_results($wpdb->prepare($query, $start, $limit));
		return $to_return;
	}


	public function get_messages_count() {
		 global $wpdb;	
		 $query = "
			SELECT COUNT(*) 
			FROM " . Twist_Config::get_sent_messages_table_name();
		$to_return = $wpdb->get_var($query);
		return $to_return;
	}

	public function phone_number_exists($phone_number, $exclude_receiver = null) {
		global $wpdb;
		$receiver_count  = 0;
		if($exclude_receiver == null) {
			$receiver_count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM " . Twist_Config::get_receivers_table_name() . " WHERE phone_number = %s" , $phone_number));
		} else {
			$receiver_count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM " . Twist_Config::get_receivers_table_name() . " WHERE phone_number = %s AND id <> %d" , $phone_number, $exclude_receiver));
		}
		
		return $receiver_count != 0;
	}

	public function insert_receiver($first_name, $last_name, $phone_number, $department) {
		global $wpdb;
		$wpdb->insert( Twist_Config::get_receivers_table_name(), array(
			"first_name" => $first_name,
			"last_name" => $last_name,
			"status" => 1,
			"department" => $department,
			"phone_number" => $phone_number,
			"created_date" =>  date("Y-m-d H:i:s", time ()) 
		));
		return $wpdb->insert_id;
	}

	public function update_receiver($receiver_id, $first_name, $last_name, $phone_number, $status, $department) {
		global $wpdb;
		$wpdb->update( Twist_Config::get_receivers_table_name(), array(
			"first_name" => $first_name,
			"last_name" => $last_name,
			"phone_number" => $phone_number,
			"status" =>  $status,
			"department" => $department,
			"updated_date" =>  date("Y-m-d H:i:s", time ()) 
		), array("id" => $receiver_id), array(
			 "%s" ,
			 "%s" ,
			 "%s" ,
			 "%d",
			 "%s"  
		), array("%d"));
	}

	public function delete_receiver($receiver_id) {
		global $wpdb;
		$wpdb->delete( Twist_Config::get_receivers_table_name(), array( 'id' => $receiver_id ), array( '%d' ) );
	}

	public function get_receiver($receiver_id) {
		global $wpdb;
		$to_return = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . Twist_Config::get_receivers_table_name() . " WHERE id = %d", $receiver_id));
		return $to_return;
	}

	public function insert_message($message_template) {
		global $wpdb;
		$wpdb->insert( Twist_Config::get_messages_table_name(), array(
			"template" => $message_template,
			"created_date" =>  date("Y-m-d H:i:s", time ()) 
		));
		return $wpdb->insert_id;
	}

	public function insert_sent_message($message_template_id, $receiver_id, $first_name, $last_name, $phone_number, $response_code, $response_message, $department) {
		global $wpdb;
		$wpdb->insert( Twist_Config::get_sent_messages_table_name(), array(
			"first_name" => $first_name,
			"last_name" =>  $last_name,
			"message_id" =>  $message_template_id,
			"phone_number" =>  $phone_number,
			"department" => $department,
			"response_code" =>  $response_code,
			"response_message" =>  $response_message,
			"receiver_id" =>  $receiver_id,
			"sent_date" => date("Y-m-d H:i:s", time ()) 
		));
		return $wpdb->insert_id;
	}
}