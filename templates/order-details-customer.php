<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="woocommerce-customer-details">

	<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">

		<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

			<h2 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'woocommerce-subscriptions-gifting' ); ?></h2>

			<address>
				<?php $address = $order->get_formatted_shipping_address() ? $address : __( 'N/A', 'woocommerce-subscriptions-gifting' );
				sprintf( __( '%s', 'woocommerce-subscriptions-gifting' ), $address ); ?>
			</address>

		</div><!-- /.col-1 -->

	</section><!-- /.col1-set -->

</section>
