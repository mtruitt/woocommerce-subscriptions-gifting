<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<?php $subscriptions = wcs_get_subscriptions_for_renewal_order( $order ); ?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Your subscription renewal order has been received and is now being processed. Your order details are shown below for your reference:', 'woocommerce-subscriptions-gifting' ); ?></p>

<?php
if ( 0 < count( $subscriptions ) ) : ?>
	<table cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
<?php endif;

foreach ( $subscriptions as $subscription ) { ?>
	<thead>
		<tr>
			<td style="padding: -6" colspan="3"><h3><?php printf( esc_html__( 'Subscription #%s', 'woocommerce-subscriptions-gifting' ), esc_attr( $subscription->get_order_number() ) ) ?></h3></td>
		</tr>
	</thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'woocommerce-subscriptions-gifting' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Quantity', 'woocommerce-subscriptions-gifting' ); ?></th>
		</tr>
	<tbody>
		<?php echo wp_kses_post( WC_Subscriptions_Email::email_order_items_table( $subscription, array(
			'show_download_links' => true,
			'show_sku'            => false,
			'show_purchase_note'  => true,
		) ) ); ?>
	</tbody><?php
}
echo '</table>';
?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php if ( ! empty( $subscriptions ) ) : ?>
<h2><?php esc_html_e( 'Subscription Information:', 'woocommerce-subscriptions-gifting' ); ?></h2>
<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Subscription', 'woocommerce-subscriptions-gifting' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Start Date', 'table heading',  'woocommerce-subscriptions-gifting' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'End Date', 'table heading',  'woocommerce-subscriptions-gifting' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php echo esc_html_x( 'Period',  'table heading', 'woocommerce-subscriptions-gifting' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $subscriptions as $subscription ) : ?>
		<tr>
			<td class="td" scope="row" style="text-align:left;"><a href="<?php echo esc_url( $subscription->get_view_order_url() ); ?>"><?php echo sprintf( esc_html_x( '#%s', 'subscription number in email table. (eg: #106)', 'woocommerce-subscriptions-gifting' ), esc_html( $subscription->get_order_number() ) ); ?></a></td>
			<td class="td" scope="row" style="text-align:left;"><?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ); ?></td>
			<td class="td" scope="row" style="text-align:left;"><?php echo esc_html( ( 0 < $subscription->get_time( 'end' ) ) ? date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) : _x( 'When Cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-subscriptions-gifting' ) ); ?></td>
			<td class="td" scope="row" style="text-align:left;">
				<?php
				$subscription_details = array(
					'recurring_amount'            => '',
					'subscription_period'         => $subscription->get_billing_period(),
					'subscription_interval'       => $subscription->get_billing_interval(),
					'initial_amount'              => '',
					'use_per_slash'               => false,
				);
				$subscription_details = apply_filters( 'woocommerce_subscription_price_string_details', $subscription_details, $subscription );
				echo wp_kses_post( wcs_price_string( $subscription_details ) );?>
			</td>
		</tr>
	<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>

<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
			<td style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;" valign="top" width="50%">
				<h2><?php echo esc_html__( 'Shipping address', 'woocommerce-subscriptions-gifting' ); ?></h2>

				<address class="address"><?php echo wp_kses_post( $shipping ); ?></address>
			</td>
		<?php endif; ?>
	</tr>
</table>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
