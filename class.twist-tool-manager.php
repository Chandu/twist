<?php
require_once( TWIST__PLUGIN_DIR . "class.twist-config.php" );
require_once( TWIST__PLUGIN_DIR . "class.twist-db.php" );
require_once( TWIST__PLUGIN_DIR . "class.twist-twilio-broker.php" );
require_once(TWIST__PLUGIN_DIR . "class.helper.stringformat.php");
class Twist_Tool_Manager {
	private static $ins = null;
	private $db = null;
	private $twilio_broker = null;

	function Twist_Tool_Manager() {
		$this->db = new Twist_DB();
		$options = get_option(Twist_Config::PLUGIN_SLUG);
		$account_sid = $options[Twist_Config::ACCOUNT_SID_OPTION_KEY];
		$auth_token = $options[Twist_Config::AUTH_TOKEN_OPTION_KEY];
		$from_phone = $options[Twist_Config::FROM_PHONE_NUMBER];
		$this->twilio_broker = new Twist_Twilio_Broker($account_sid, $auth_token, $from_phone);
	}

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
		add_action('admin_menu', array($this, "add_twist_tools_menu_item"));
		add_action('admin_enqueue_scripts', array($this, "enqueue_assets"));
		add_action( "wp_ajax_get_twist_receivers", array($this,  "get_twist_receivers" ));
		add_action( "wp_ajax_get_twist_receiver", array($this,  "get_twist_receiver" ));
		add_action( "wp_ajax_add_twist_receiver", array($this,  "add_twist_receiver" ));
		add_action( "wp_ajax_update_twist_receiver", array($this,  "update_twist_receiver" ));
		add_action( "wp_ajax_delete_twist_receiver", array($this,  "delete_twist_receiver" ));
		add_action( "wp_ajax_get_active_twist_receivers", array($this,  "get_active_twist_receivers" ));
		add_action( "wp_ajax_send_twist_messages", array($this,  "send_twist_messages" ));
		add_action( "wp_ajax_get_messages_log", array($this,  "get_messages_log" ));
	}

	public function add_twist_tools_menu_item() {
		 add_submenu_page( 'tools.php', __("Twilio SMS Texting") , __("Twilio SMS Texting"), "import",  Twist_Config::TOOL_SLUG, array($this, "render_tool_page"));
	}

	function delete_twist_receiver() {
		$to_return = array();
		try {
			$receiver_id = $_POST['receiverId'];
			$this->db->delete_receiver($receiver_id);
			$to_return = array(
				"status" => 0,
				"message" => "Recipient deleted successfully."
			);	
		} catch (Exception $e) {
			$to_return = array(
				"status" => 500,
				"message" => $e->getMessage()
			); 
		}
		wp_send_json($to_return);
	}

	function get_twist_receiver() {
		$to_return = array();
		try {
			$receiver_id = $_GET['receiverId'];
			$receiver = $this->db->get_receiver($receiver_id);
			$to_return = array(
				"status" => 0,
				"message" => "Recipient retrieved successfully.",
				"receiver" => $receiver
			);	
		} catch (Exception $e) {
			$to_return = array(
				"status" => 500,
				"message" => $e->getMessage()
			); 
		}
		wp_send_json($to_return);
	}

	function add_twist_receiver() {
		$to_return = array();
		try {
			$first_name = sanitize_text_field($_POST['firstName']);
			$last_name = sanitize_text_field($_POST['lastName']);
			$phone_number = sanitize_text_field($_POST['phoneNumber']);
			$department = sanitize_text_field($_POST['department']);
			$errors = array();
			
			if(strlen($first_name) < 3) {
				array_push($errors, "Please enter valid First Name. It should be atleast 3 characters.");
			} else if( strlen($last_name) < 1) {
				array_push($errors, "Please enter valid Last Name. It should be atleast 1 characters.");
			}
			else if(!preg_match(Twist_Config::PHONE_NUMBER_REGEX, $phone_number )) {
				array_push($errors, "'Please enter phone number in the format +XXX (XXX) - XXX - XXXX. Country code should contain 3 digits (e.g: 001, 044 etc). ");
			} 
			if(!empty($errors)) {
				$to_return = array(
					"status" => 400,
					"message" => "Please fix the error/s:",
					"errors" => $errors
				); 

			} else {
				if($this->db->phone_number_exists($phone_number)) {
					$to_return = array(
						"status" => 400,
						"message" => "Phone number already registered."
					);
				} else {
					$new_receiver_id = $this->db->insert_receiver($first_name, $last_name, $phone_number, $department);
					$to_return = array(
						"status" => 0,
						"message" => "Recipient added successfully.",
						"receiver_id" => $new_receiver_id
					);	
				}
				 
			}
		} catch (Exception $e) {
			$to_return = array(
				"status" => 500,
				"message" => $e->getMessage()
			); 
		}
		wp_send_json($to_return);
	}

	function update_twist_receiver() {
		$to_return = array();
		try {
			$receiver_id = intval($_POST['receiverId']);
			$first_name = sanitize_text_field($_POST['firstName']);
			$last_name = sanitize_text_field($_POST['lastName']);
			$phone_number = sanitize_text_field($_POST['phoneNumber']);
			$department = sanitize_text_field($_POST['department']);
			$status = $_POST['status'];
			$errors = array();

			if($receiver_id < 1) {
				array_push($errors, "Please select a valid recipient." .  $receiver_id);
			}
			else if(strlen($first_name) < 3) {
				array_push($errors, "Please enter valid First Name. It should be atleast 3 characters.");
			} else if( strlen($last_name) < 1) {
				array_push($errors, "Please enter valid Last Name. It should be atleast 1 characters.");
			}
			else if(!preg_match(Twist_Config::PHONE_NUMBER_REGEX, $phone_number )) {
				array_push($errors, "'Please enter phone number in the format +XXX (XXX) - XXX - XXXX. Country code should contain 3 digits (e.g: 001, 044 etc). ");
			} 

			if(!empty($errors)) {
				$to_return = array(
					"status" => 400,
					"message" => "Please fix the error/s:",
					"errors" => $errors
				); 

			} else {
				if($this->db->phone_number_exists($phone_number, $receiver_id)) {
					$to_return = array(
						"status" => 400,
						"message" => "Phone number already registered."
					);
				} else {
					$this->db->update_receiver($receiver_id, $first_name, $last_name, $phone_number, $status, $department);
					$to_return = array(
						"status" => 0,
						"message" => "Recipient updated successfully.",
						"receiver_id" => $receiver_id
					);	
				}
			}
		} catch (Exception $e) {
			$to_return = array(
				"status" => 500,
				"message" => $e->getMessage()
			); 
		}
		wp_send_json($to_return);
	}

	function get_twist_receivers() {
		try {
			$to_return = array(
				"status" => 0,
				"data" => $this->db->get_all_recievers()
			); 
			wp_send_json($to_return);
		} catch (Exception $e) {
			$to_return = array(
				"status" => -1,
				"message" => $e->getMessage()
			); 
			wp_send_json($to_return);
		}
	}

	function get_active_twist_receivers() {
		try {
				$to_return = array(
				"status" => 0,
				"data" => $this->db->get_all_active_recievers()
			); 
			wp_send_json($to_return);
		} catch (Exception $e) {
			$to_return = array(
				"status" => -1,
				"message" => $e->getMessage()
			); 
			wp_send_json($to_return);
		}
	}

	function send_twist_messages() {
		try {
			$errors = array();
			$receivers = array();
			if(isset($_POST["receivers"])) {
				$receivers = $_POST["receivers"];
			}
			$message = sanitize_text_field($_POST["message"]);

			if(empty($receivers)) {
				array_push($errors, "Please select atleast one recipient.");
			}
			else if(strlen($message) < 3) {
				array_push($errors, "Please enter a valid message.");
			} 
			if(!empty($errors)) {
				$to_return = array(
					"status" => 400,
					"message" => "Please fix the error/s:",
					"errors" => $errors
				); 

			} else {
				$twilio_results = array();
				$db_receivers = $this->db->get_receivers($receivers);
				
				$message_template_id = $this->db->insert_message($message);
				foreach ($db_receivers as $receiver) {
					$formatted_message = Helper_StringFormat::sprintf($message, (array)$receiver);
					$response = "";

					 try {
						$response = $this->twilio_broker->send_message($receiver["phone_number"], $formatted_message);

					 } catch(Exception $ex) {
					 	$response = $e->getMessage();
					 }

					
					 $response_code = strlen($response);
					 $response_message = ( strlen($response) == 0)? "Success" : $response;

					 $receiver["sms_result_code"] = $response_code;
					 $receiver["sms_result_message"] = $response_message;

					 $twilio_results[strval($receiver["id"])] = $receiver;

					 $this->db->insert_sent_message(
					 	$message_template_id,
					 	$receiver["id"],
					 	$receiver["first_name"],
					 	$receiver["last_name"],
					 	$receiver["phone_number"],
					 	$response_code,
					 	$response_message,
					 	$receiver["department"]
					 );
				}
				
				$to_return = array(
					"status" => 0,
					"data" => $twilio_results
				); 
			}
			wp_send_json($to_return);
		} catch (Exception $e) {
			$to_return = array(
				"status" => -1,
				"message" => $e->getMessage()
			); 
			wp_send_json($to_return);
		}
	}

	function get_messages_log() {
		$start = intval(isset($_GET["start"])?$_GET["start"]:1);
		$limit = intval(isset($_GET["limit"])?$_GET["limit"]:20);
		$start = ($start < 1 )? 1:$start;
		$limit = ($limit < 1 )? 20:$limit;
		$messages = $this->db->get_messages($start-1, $limit);
		$total_count = $this->db->get_messages_count();
		wp_send_json(array(
			"total_count" => $total_count,
			"total_pages" => ceil($total_count/$limit),
			"messages" => $messages,
			"page" => ceil(floatval($start)/floatval($limit)),
			"limit" => $limit
		));
	}


	function render_tool_page() {
		if(!current_user_can("import")) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		if(self::are_twist_settings_valid()) {
			include_once(TWIST__PLUGIN_DIR . "views/tool_page.php");
		} else {
		?>
		<div class="error settings-error"> 
			<p><strong><?php _e("Please configure Twilio SMS Texting settings using the Setings > Twilio SMS Texting menu before using the Twilio SMS Texting tool.") ?></strong></p>
		</div>
		<?php
		}
	}

	function enqueue_assets($hook_suffix) {
		$screen = get_current_screen();
		if (  $screen->id ==  "tools_page_". Twist_Config::TOOL_SLUG ) {
			$this->enqueue_styles();
			$this->enqueue_scripts();
		}
	}

	function enqueue_styles() {
		wp_register_style('twitter-pure-css', plugins_url().'/twist/assets/css/purecss/pure-min.css', __FILE__);
		wp_enqueue_style('twitter-pure-css');

		wp_register_style('twist-simplemodal-css', plugins_url().'/twist/assets/css/simple-modal.css', __FILE__);
		wp_enqueue_style('twist-simplemodal-css');

		wp_register_style('twist-css', plugins_url().'/twist/assets/css/twist.css', __FILE__);
		wp_enqueue_style('twist-css');
	}

	function enqueue_scripts() {
		wp_register_script("handlebars", plugins_url()."/twist/assets/js/vendor/handlebars.js");
		wp_enqueue_script("handlebars");

		wp_register_script("jquery-maskedinput", plugins_url()."/twist/assets/js/vendor/jquery.maskedinput.js", array("jquery"));
		wp_enqueue_script("jquery-maskedinput");

		
		wp_register_script("twist-simple-modal", plugins_url()."/twist/assets/js/vendor/jquery.simplemodal.js", array("jquery"));
		wp_enqueue_script("twist-simple-modal");

		wp_register_script("twist-spin", plugins_url()."/twist/assets/js/vendor/spin.min.js", array("jquery"));
		wp_enqueue_script("twist-spin");
		
		wp_register_script("twist-jquery-spin", plugins_url()."/twist/assets/js/vendor/jquery.spin.js", array("jquery", "twist-spin"));
		wp_enqueue_script("twist-jquery-spin");
		
		wp_register_script("twist-templatr", plugins_url()."/twist/assets/js/templatr.js", array("jquery", "handlebars"));
		wp_enqueue_script("twist-templatr");

		wp_register_script("twist-admin-tool-app", plugins_url()."/twist/assets/js/twist-admin-tool-app.js", array("twist-templatr"));
		wp_enqueue_script("twist-admin-tool-app");
	}


	static function are_twist_settings_valid() {
		$options = get_option(Twist_Config::PLUGIN_SLUG);
		if(empty($options)) {
			return false;
		}
		foreach (Twist_Config::get_plugin_option_keys() as $option_key) {
			if(!isset($options[$option_key])) {
				return false;
			}
			if(strlen($options[$option_key]) < 1) {
				return false;
			}
		}
		return true;
	}
}