<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';

foreach ( $items as $item_id => $item ) {
	$product = $item->get_product();
	if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {

		// Product name
		echo sprintf( __( 'Product: %s', 'woocommerce-subscriptions-gifting' ), $item->get_name() ) . "\n";

		// SKU
		if ( $show_sku && is_object( $product ) && $product->get_sku() ) {
			echo sprintf( __( 'SKU: #%s', 'woocommerce-subscriptions-gifting' ), $product->get_sku() ) . "\n";
		}

		// allow other plugins to add additional product information here
		do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

		echo strip_tags( wc_display_item_meta( $item, array(
			'before'    => "\n- ",
			'separator' => "\n- ",
			'after'     => "",
			'echo'      => false,
			'autop'     => false,
		) ) );

		// allow other plugins to add additional product information here
		do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );

		// Quantity
		echo "\n" . sprintf( __( 'Quantity: %s', 'woocommerce-subscriptions-gifting' ), $item->get_quantity() ) . "\n";
	}

	if ( $show_purchase_note && is_object( $product ) && ( $purchase_note = $product->get_purchase_note() ) ) {
		echo sprintf( __( 'Purchase Note: %s', 'woocommerce-subscriptions-gifting' ), do_shortcode( $purchase_note ) ) . "\n\n";
	}

}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
