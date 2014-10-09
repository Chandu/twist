<?php 
require_once(TWIST__PLUGIN_DIR . "lib/Twilio/Twilio.php");
class Twist_Twilio_Broker {
	private  $from_phone;
	private $client = null;

	function Twist_Twilio_Broker($account_sid, $auth_token, $from_phone) {
		$this->client = new Services_Twilio($account_sid, $auth_token);
		$this->from_phone = $from_phone;
	}

	function send_message($phone_number, $message) {
		$to_return = "";
			try {
				$sms = $this->client->account->messages->sendMessage(
					str_replace("+0", "+", str_replace("+00", "+", $this->from_phone)), //Regex is too much for this?
					str_replace("+0", "+", str_replace("+00", "+", $phone_number)), 
					$message
				);	
		
			} catch (Exception $e) {
				$to_return = $e->getMessage();
			}
		return $to_return;
	}
}