<fieldset>
	<input type="checkbox" id="gifting_<?php esc_attr_e( $id, 'woocommerce_subscriptions_gifting' ) ?>_option" class="woocommerce_subscription_gifting_checkbox" value="gift" <?php esc_attr_e( ( empty( $email ) ) ? '' : 'checked', 'woocommerce_subscriptions_gifting' ) ?> /> <?php echo esc_html__( 'This is a gift', 'woocommerce_subscriptions_gifting' ) ?> <br />
	<p class="form-row form-row <?php esc_attr_e( implode( ' ', $email_field_args['class'] ) ); ?>" style="<?php esc_attr_e( implode( '; ', $email_field_args['style_attributes'] ) );?>">
		<label for="recipient_email[<?php esc_attr_e( $id );?>]">Recipient's Email Address:</label>
		<input type="email" class="input-text recipient_email" name="recipient_email[<?php esc_attr_e( $id );?>]" id="recipient_email[<?php esc_attr_e( $id );?>]" placeholder="<?php esc_attr_e( $email_field_args['placeholder'] );?>" value="<?php esc_attr_e( $email )?>"/>
		<?php wp_nonce_field( 'wcsg_add_recipient', '_wcsgnonce' ); ?>
	</p>
</fieldset>
