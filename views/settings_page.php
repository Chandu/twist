<div id="twist-admin-settings-container" data-plugin-slug= "<?php echo Twist_Config::PLUGIN_SLUG?>" >
	<div id="theme-options-wrap">
		<h2><?php esc_attr_e("Twilio SMS Texting Settings");?></h2>
		<p><?php esc_attr_e("Use this page manage Twilio API settings needed for the Twilio integration.");?></p> 
		<form method="post" action="options.php">
			<?php settings_fields(Twist_Config::PLUGIN_SLUG); ?>
			<?php $options = get_option(Twist_Config::PLUGIN_SLUG); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row"><?php _e("Twilio Account SID") ?>:</th>
					<td><input type="text" class="in-twist-account-sid" name="<?php echo Twist_Config::PLUGIN_SLUG.'['.Twist_Config::ACCOUNT_SID_OPTION_KEY.']'; ?>" value="<?php echo $options[Twist_Config::ACCOUNT_SID_OPTION_KEY]; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Twilio Auth Token") ?>:</th>
					<td><input type="text" class="in-twist-auth-token" name="<?php echo Twist_Config::PLUGIN_SLUG.'['.Twist_Config::AUTH_TOKEN_OPTION_KEY.']'; ?>" value="<?php echo $options[Twist_Config::AUTH_TOKEN_OPTION_KEY]; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e("Twilio From Phone Number") ?>:</th>
					<td><input type="text" class="in-twist-phone"  placeholder="(001) XXX-XXX-XXXX" name="<?php echo Twist_Config::PLUGIN_SLUG.'['.Twist_Config::FROM_PHONE_NUMBER.']'; ?>" value="<?php echo $options[Twist_Config::FROM_PHONE_NUMBER]; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>	
</div>
