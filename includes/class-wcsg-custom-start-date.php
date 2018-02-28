<?php

class WCSG_Custom_Start_Date {

	public function __construct() {
		// Register "Pending Start" as a new status.
		add_filter( 'woocommerce_subscriptions_registered_statuses', array( $this, 'register_pending_start_status' ) );
		add_filter( 'wcs_subscription_statuses', array( $this, 'add_subscription_status' ) );
		add_filter( 'woocommerce_can_subscription_be_updated_to_pending-start', array( $this, 'can_update_to_pending_start' ), 10, 2 );
		add_filter( 'woocommerce_can_subscription_be_updated_to_active', array( $this, 'can_update_to_active' ), 10, 2 );
		add_action( 'woocommerce_subscription_status_changed', array( $this, 'subscription_status_changed' ), 10, 4 );

		// Add date field to product page.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'maybe_add_start_date_field' ), 10 );

		// Cart item key/meta.
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_data_to_cart_item' ), 1, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_items_from_session' ), 1, 3 );
		add_filter( 'woocommerce_subscriptions_recurring_cart_key', array( $this, 'add_to_cart_key' ), 1, 2 );

		// Display start date in cart.
		add_filter( 'woocommerce_cart_item_name', array( $this, 'show_start_date_on_checkout' ), 11, 3 );

		// Adjust things.
		add_filter( 'wcs_recurring_cart_next_payment_date', array( $this, 'adjust_next_payment_date' ), 10, 3 );
		add_action( 'woocommerce_checkout_subscription_created', array( $this, 'add_meta_to_subscription' ), 12, 3 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'complete_initial_order' ), 10, 3 );

		// Displays the start date instead of the creation date admin-side.
		add_filter( 'woocommerce_subscription_get_date_created', array( $this, 'maybe_fake_start_date_in_admin' ), 10, 2 );

		// Take care of activating subscription when the time comes.
		add_action( 'wcgs_scheduled_subscription_start', array( $this, 'maybe_activate_subscription' ) );
	}

	/**
	 * Registers 'wc-pending-start' as a new subscription status.
	 */
	public function register_pending_start_status( $statuses ) {
		$statuses['wc-pending-start'] = _nx_noop( 'Pending Start <span class="count">(%s)</span>', 'Pending Start <span class="count">(%s)</span>', 'post status label', 'woocommerce-subscriptions-gifting' );
		return $statuses;
	}

	/**
	 * Adds 'wc-pending-start' to the list of subscription statuses.
	 */
	public function add_subscription_status( $statuses ) {
		$statuses['wc-pending-start'] = _x( 'Pending Start', 'subscription status', 'woocommerce-subscriptions-gifting' );
		return $statuses;
	}

	/**
	 * Controls whether a subscription's status can be updated to "pending-start" or not.
	 */
	public function can_update_to_pending_start( $can_be, $subscription ) {
		if ( $subscription->has_status( 'pending' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow subscriptions to be updated from "pending-start" to "active".
	 */
	public function can_update_to_active( $can_be, $subscription ) {
		if ( $subscription->has_status( 'pending-start' ) ) {
			return true;
		}

		return $can_be;
	}

	/**
	 * Enqueues some basic styles and scripts.
	 */
	public function enqueue_scripts() {
		$plugin_file = dirname( dirname( __FILE__ ) ) . '/woocommerce-subscriptions-gifting.php';

		if ( ! wp_style_is( 'jquery-ui-style', 'registered' ) ) {
			global $wp_scripts;

			$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';
			wp_register_style( 'jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css', array(), $jquery_version );
		}

		wp_enqueue_style( 'wcsg_custom_start_date', plugins_url( '/css/wcsg-custom-start-date.css', $plugin_file ), array( 'jquery-ui-style' ) );
		wp_enqueue_script( 'wcsg_custom_start_date', plugins_url( '/js/wcsg-custom-start-date.js', $plugin_file ), array( 'jquery', 'jquery-ui-datepicker' ) );
	}

	/**
	 * Displays the custom start date fields on the product page of subscription products.
	 */
	public function maybe_add_start_date_field() {
		global $product;

		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( ! WC_Subscriptions_Product::is_subscription( $product ) ) {
			return;
		}
?>
<fieldset class="wcsg-custom-start-date-fields">
	<input type="checkbox" name="wcsg_use_custom_start_date" id="wcsg_custom_start_date_enabled_checkbox" value="yes" />
	<label for="wcsg_custom_start_date_enabled_checkbox"><?php echo esc_html_x( 'Start subscription on a specific date', 'custom start date fieldset', 'woocommerce-subscriptions-gifting' ); ?></label>

	<div class="wcsg-custom-start-date-input">
		<input type="text" name="wcsg_custom_start_date" value="" />
	</div>
	<?php wp_nonce_field( 'wcsg_custom_start_date', '_wcsgnonce_custom_start_date' ); ?>
</fieldset>
<?php
	}

	public function add_to_cart_key( $cart_key, $cart_item ) {
		if ( ! empty( $cart_item['wcsg_custom_start_date'] ) ) {
			$cart_key .= '_custom_start_' . $cart_item['wcsg_custom_start_date'];
		}

		return $cart_key;
	}

	/**
	 * Adds the custom start date as data when an item is added to the cart.
	 */
	public function add_data_to_cart_item( $data ) {
		if ( ! isset( $_POST['_wcsgnonce_custom_start_date'] ) || ! wp_verify_nonce( $_POST['_wcsgnonce_custom_start_date'], 'wcsg_custom_start_date' ) || ! isset( $_POST['wcsg_use_custom_start_date'] ) || 'yes' != $_POST['wcsg_use_custom_start_date'] ) {
			return $data;
		}

		if ( ! wp_verify_nonce( $_POST['_wcsgnonce_custom_start_date'], 'wcsg_custom_start_date' ) ) {
			return $data;
		}

		$start_date = ! empty( $_POST['wcsg_custom_start_date'] ) ? trim( $_POST['wcsg_custom_start_date'] ) : '';
		if ( ! $start_date ) {
			return $data;
		}

		$start_date_utc = wcs_get_datetime_utc_string( wcs_get_datetime_from( $start_date ) );

		if ( wcs_date_to_time( $start_date_utc ) <= current_time( 'timestamp', true ) ) {
			wc_add_notice( __( 'Entered activation date is in the past. Item was not added to cart.', 'woocommerce-subscriptions-gifting' ), 'error' );
			throw new Exception();
		}

		// Custom start date is stored in UTC.
		$data['wcsg_custom_start_date'] = $start_date_utc;

		return $data;
	}

	public function get_cart_items_from_session( $item, $values ) {
		if ( array_key_exists( 'wcsg_custom_start_date', $values ) ) {
			$item['wcsg_custom_start_date'] = $values['wcsg_custom_start_date'];
			unset( $values['wcsg_custom_start_date'] );
		}

		return $item;
	}

	/**
	 * Displays the custom start date on the cart.
	 */
	public function show_start_date_on_checkout( $title, $cart_item, $cart_item_key ) {
		$is_mini_cart = did_action( 'woocommerce_before_mini_cart' ) && ! did_action( 'woocommerce_after_mini_cart' );

		if ( $is_mini_cart || ! is_cart() ) {
			return $title;
		}

		$product = $cart_item['data'];
		if ( ! WC_Subscriptions_Product::is_subscription( $product ) ) {
			return $title;
		}

		$start_date = ! empty( $cart_item['wcsg_custom_start_date'] ) ? $cart_item['wcsg_custom_start_date'] : '';
		if ( $start_date ) {
			$title .= '<div class="wcsg-cart-item-custom-start-date">';
			$title .= sprintf( _x( 'Starts on: %s', 'custom start date', 'woocommerce-subscriptions-gifting' ), '<span>' . date_i18n( wc_date_format(), wcs_date_to_time( get_date_from_gmt( $start_date ) ) ) . '</span>' );
			$title .= '</div>';
		}

		return $title;
	}

	/**
	 * Sets up things for subscriptions that need to be activated in the future.
	 */
	public function add_meta_to_subscription( $subscription, $order, $recurring_cart ) {
		$item       = reset( $recurring_cart->cart_contents );
		$start_date = ! empty( $item['wcsg_custom_start_date'] ) ? $item['wcsg_custom_start_date'] : '';

		if ( ! $start_date ) {
			return;
		}

		update_post_meta( $order->get_id(), '_wcsg_with_custom_start_date', 1 );
		update_post_meta( $subscription->get_id(), '_wcsg_with_custom_start_date', 1 );
		update_post_meta( $subscription->get_id(), '_wcsg_custom_start_date', $start_date );

		$subscription->set_status( 'pending-start' );
		$subscription->save();

		// Schedule activation of this subscription. $start_date is UTC.
		wc_schedule_single_action( wcs_date_to_time( $start_date ), 'wcgs_scheduled_subscription_start', array( 'subscription_id' => $subscription->get_id() ) );

		// Prevent WC_Subscriptions_Order from changing the subscription's status to "Active" once the initial order is paid.
		remove_action( 'woocommerce_order_status_changed', 'WC_Subscriptions_Order::maybe_record_subscription_payment', 9, 3 );
	}

	/**
	 * Automatically completes the initial order for a subscription in "pending-start" status.
	 */
	public function complete_initial_order( $order_id, $old_status, $new_status ) {
		if ( ! wcs_order_contains_subscription( $order_id, 'parent' ) || 'completed' == $new_status ) {
			return;
		}

		$order           = wc_get_order( $order_id );
		$order_completed = in_array( $new_status, array( apply_filters( 'woocommerce_payment_complete_order_status', 'processing', $order_id, $order ), 'processing', 'completed' ) ) && in_array( $old_status, apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'on-hold', 'failed' ), $order ) );

		if ( ! $order_completed ) {
			return;
		}

		$subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'parent' ) );

		foreach ( $subscriptions as $subscription ) {
			if ( $subscription->has_status( 'pending-start' ) ) {
				$order->update_status( 'completed' );
				break;
			}
		}
	}

	/**
	 * Moves the "next_payment" date according to the scheduled start date.
	 * TODO: We should do something similar for the trial and also consider synchronization.
	 */
	public function adjust_next_payment_date( $next_payment_date, $recurring_cart, $product ) {
		$item       = reset( $recurring_cart->cart_contents );
		$start_date = ! empty( $item['wcsg_custom_start_date'] ) ? $item['wcsg_custom_start_date'] : '';

		if ( $start_date ) {
			// $start_date is UTC.
			$next_payment_date = WC_Subscriptions_Product::get_first_renewal_payment_date( $product, $start_date );
		}

		return $next_payment_date;
	}

	/**
	 * Activates a subscription that is scheduled for activation.
	 */
	public function maybe_activate_subscription( $subscription_id ) {
		$subscription = wcs_get_subscription( $subscription_id );

		if ( ! $subscription->has_status( 'pending-start' ) ) {
			return;
		}

		$msg = __( 'Subscription scheduled activation performed.', 'woocommerce-subscriptions-gifting' );
		$subscription->payment_complete();
		// $subscription->update_status( 'active', $msg );

		// TODO: We whould generate a new order (for shipping) when the subscription is finally activated.
	}

	/**
	 * Perform post-activation dance for pending-start subscriptions that were activated.
	 */
	public function subscription_status_changed( $subscription_id, $from, $to, $subscription ) {
		if ( 'pending-start' != $from || 'active' != $to ) {
			return;
		}

		// Prevent infinite loops.
		remove_action( 'woocommerce_subscription_status_changed', array( $this, 'subscription_status_changed' ), 10, 4 );

		// Remove custom start date and use the current time as new 'date_created'.
		$now = current_time( 'timestamp', true );

		delete_post_meta( $subscription_id, '_wcsg_with_custom_start_date' );
		delete_post_meta( $subscription_id, '_wcsg_custom_start_date' );

		$subscription->set_date_created( $now ); // XXX: Do we really want to do this?
		$subscription->set_date_modified( $now );
		$subscription->save();

		add_action( 'woocommerce_subscription_status_changed', array( $this, 'subscription_status_changed' ), 10, 4 );
	}

	/**
	 * Displays the scheduled start date as "Start Date" on admin screens, instead of the creation date.
	 */
	public function maybe_fake_start_date_in_admin( $date, $subscription ) {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return $date;
		}

		$current_screen = get_current_screen();
		if ( 'shop_subscription' != $current_screen->post_type || ! in_array( $current_screen->base, array( 'edit', 'post' ) ) ) {
			return $date;
		}

		$custom_start_date = $subscription->get_meta( '_wcsg_custom_start_date' );
		if ( $custom_start_date ) {
			$date = wcs_get_datetime_from( $custom_start_date );
		}

		return $date;
	}

}


new WCSG_Custom_Start_Date();

